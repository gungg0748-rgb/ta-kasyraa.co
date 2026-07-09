<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Controller untuk mengelola koneksi dan data scanner barcode.
 */
class ScannerController extends Controller
{
    /**
     * Generate token deterministik: HMAC(date+user_id, APP_KEY).
     * Reset otomatis tiap tengah malam, tidak perlu disimpan di DB/cache.
     */
    private function makeToken(int $userId): string
    {
        // Generate token deterministik: HMAC(date+user_id, APP_KEY).
        $date    = now()->format('Y-m-d');
        $secret  = config('app.key');
        return hash_hmac('sha256', "{$date}:{$userId}", $secret);
    }

    /**
     * Validasi token dan cocokkan dengan user aktif yang ada.
     */
    private function resolveToken(string $token): ?array
    {
        // Validasi token dan cocokkan dengan user aktif yang ada.
        // Cek apakah token sudah di-invalidate (user logout)
        if (Cache::has("scanner:invalidated:{$token}")) {
            return null;
        }

        $users = \App\Models\User::whereIn('role', ['admin', 'kasir', 'gudang'])
            ->where('is_active', true)
            ->get(['id', 'name']);

        foreach ($users as $user) {
            if (hash_equals($this->makeToken($user->id), $token)) {
                return ['id' => $user->id, 'name' => $user->name];
            }
        }
        return null;
    }

    /**
     * PC: generate token dan URL untuk ditampilkan sebagai QR code.
     */
    public function generateToken(Request $request)
    {
        // PC: generate token dan URL untuk ditampilkan sebagai QR code.
        $user  = auth()->user();
        $token = $this->makeToken($user->id);

        // Hapus flag invalidasi kalau user login lagi
        Cache::forget("scanner:invalidated:{$token}");

        $base  = rtrim(config('app.url'), '/');
        $url   = $base . '/scanner/' . $token;

        return response()->json(['token' => $token, 'url' => $url]);
    }

    /**
     * HP: halaman scanner mobile yang diakses tanpa autentikasi via token.
     */
    public function mobile(string $token)
    {
        // HP: halaman scanner mobile yang diakses tanpa autentikasi via token.
        $user = $this->resolveToken($token);
        if (!$user) {
            abort(403, 'Token tidak valid atau sudah kadaluarsa.');
        }

        // Daftarkan scanner ini sebagai aktif (heartbeat awal)
        $this->registerScanner($token, $user['name']);

        return view('scanner.mobile', [
            'token' => $token,
            'kasir' => $user['name'],
        ]);
    }

    /**
     * HP: kirim hasil scan barcode dari HP ke server.
     */
    public function push(Request $request, string $token)
    {
        // HP: kirim hasil scan barcode dari HP ke server.
        $request->validate(['barcode' => 'required|string|max:255']);

        $user = $this->resolveToken($token);
        if (!$user) {
            return response()->json(['error' => 'Token tidak valid.'], 403);
        }

        // Update heartbeat scanner
        $this->registerScanner($token, $user['name']);

        // Coba cari varian dulu, lalu fallback ke produk.
        $barcode = trim($request->barcode);
        $variant = \App\Models\ProductVariant::with('product')
            ->where('barcode', $barcode)
            ->first();

        if (!$variant) {
            // Fallback: cari produk by barcode, ambil varian pertama
            $product = \App\Models\Product::where('barcode', $barcode)->first();
            if ($product && $product->variants()->count() > 0) {
                $variant = $product->variants()->first();
            }
        }

        if ($variant) {
            // Simpan barcode varian valid untuk di-poll PC.
            Cache::put("scanner:scan:{$token}", $variant->barcode, now()->addMinutes(5));

            $productName = $variant->product->name . ($variant->color || $variant->size
                ? ' (' . collect([$variant->color, $variant->size])->filter()->implode(', ') . ')'
                : '');
            $found = true;
        } else {
            $productName = null;
            $found = false;
        }

        return response()->json([
            'ok'          => true,
            'found'       => $found,
            'productName' => $productName,
        ]);
    }

    /**
     * PC: polling untuk ambil barcode terbaru yang dikirim dari HP.
     */
    public function poll(Request $request, string $token)
    {
        // PC: polling untuk ambil barcode terbaru yang dikirim dari HP.
        $user = $this->resolveToken($token);
        if (!$user) {
            return response()->json(['expired' => true]);
        }

        $key     = "scanner:scan:{$token}";
        $barcode = Cache::get($key);

        if ($barcode !== null) {
            Cache::forget($key);

            $variant = \App\Models\ProductVariant::with('product')
                ->where('barcode', $barcode)->first();

            if ($variant) {
                $productName = $variant->product->name . ($variant->color || $variant->size
                    ? ' (' . collect([$variant->color, $variant->size])->filter()->implode(', ') . ')'
                    : '');
                return response()->json(['barcode' => $barcode, 'productName' => $productName]);
            }

            // Guard tambahan: bila cache berisi barcode non-varian, abaikan.
            return response()->json(['barcode' => null]);
        }

        return response()->json(['barcode' => null]);
    }

    /**
     * HP: ping heartbeat untuk menandai HP masih terhubung ke scanner.
     */
    public function ping(Request $request, string $token)
    {
        // HP: ping heartbeat untuk menandai HP masih terhubung ke scanner.
        $user = $this->resolveToken($token);
        if (!$user) return response()->json(['error' => 'Invalid'], 403);

        // TTL 12 detik — kalau HP tutup browser, otomatis expired setelah 12 detik
        Cache::put("scanner:connected:{$token}", true, now()->addSeconds(12));
        $this->registerScanner($token, $user['name']);

        return response()->json(['ok' => true]);
    }

    /**
     * PC: putus koneksi scanner dari sisi PC.
     */
    public function disconnect(Request $request, string $token)
    {
        // PC: putus koneksi scanner dari sisi PC.
        Cache::forget("scanner:connected:{$token}");
        return response()->json(['ok' => true]);
    }

    /**
     * PC: cek apakah HP sudah terhubung ke scanner.
     */
    public function checkConnected(Request $request, string $token)
    {
        // PC: cek apakah HP sudah terhubung ke scanner.
        $connected = Cache::get("scanner:connected:{$token}", false);
        return response()->json(['connected' => $connected]);
    }

    /**
     * Menampilkan daftar perangkat scanner yang masih aktif hari ini.
     */
    public function activeDevices(Request $request)
    {
        // Menampilkan daftar perangkat scanner yang masih aktif hari ini.
        $devices = Cache::get('scanner:devices:' . now()->format('Y-m-d'), []);

        // Filter hanya yang masih aktif (heartbeat belum expired)
        // Memfilter perangkat scanner yang heartbeat-nya masih aktif.
        $active = array_filter($devices, function ($device) {
            return Cache::has("scanner:connected:{$device['token_full']}");
        });

        return response()->json(['devices' => array_values($active)]);
    }

    /**
     * Daftarkan atau update scanner aktif dengan data heartbeat terbaru.
     */
    private function registerScanner(string $token, string $kasir): void
    {
        // Daftarkan atau update scanner aktif dengan data heartbeat terbaru.
        $dateKey = 'scanner:devices:' . now()->format('Y-m-d');
        $devices = Cache::get($dateKey, []);

        $devices[$token] = [
            'token_full' => $token,
            'token'      => substr($token, 0, 8) . '...',
            'kasir'      => $kasir,
            'last_seen'  => now()->format('H:i:s'),
            'scan_count' => ($devices[$token]['scan_count'] ?? 0) + 1,
        ];

        $secondsUntilMidnight = now()->secondsUntilEndOfDay();
        Cache::put($dateKey, $devices, $secondsUntilMidnight);
    }
}
