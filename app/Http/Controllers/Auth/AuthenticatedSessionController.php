<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Controller untuk mengelola sesi autentikasi pengguna.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Display the login view.
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Handle an incoming authentication request.
        $request->authenticate();
        $request->session()->regenerate();

        // Hapus flag invalidasi scanner untuk user ini
        $user  = auth()->user();
        $date  = now()->format('Y-m-d');
        $token = hash_hmac('sha256', "{$date}:{$user->id}", config('app.key'));
        \Illuminate\Support\Facades\Cache::forget("scanner:invalidated:{$token}");

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Destroy an authenticated session.
        $user = auth()->user();

        // Invalidate scanner token untuk user ini
        if ($user) {
            $date  = now()->format('Y-m-d');
            $token = hash_hmac('sha256', "{$date}:{$user->id}", config('app.key'));
            Cache::put("scanner:invalidated:{$token}", true, now()->addDay());
            Cache::forget("scanner:connected:{$token}");
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
