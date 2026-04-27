<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Hampir Habis</title></head>
<body style="font-family: Inter, sans-serif; background: #f7f9fb; padding: 40px 0; color: #191c1e;">
<div style="max-width: 560px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08);">
    <div style="background: #ba1a1a; padding: 32px 40px;">
        <h1 style="font-family: Manrope, sans-serif; color: #fff; margin: 0; font-size: 22px; font-weight: 800;">⚠ Hampir Habis</h1>
        <p style="color: rgba(255,255,255,0.7); font-size: 11px; letter-spacing: 2px; text-transform: uppercase; margin: 4px 0 0;">Kasyraa.co · Notifikasi Reorder Level</p>
    </div>
    <div style="padding: 40px;">
        <p style="color: #444; font-size: 14px; line-height: 1.6;">Stok produk berikut telah menyentuh atau berada di bawah <strong>reorder level</strong>. Segera lakukan pembelian ulang.</p>

        <div style="background: #fff5f5; border: 1px solid #fecaca; border-radius: 12px; padding: 20px 24px; margin: 24px 0;">
            <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                <tr>
                    <td style="color: #888; padding: 4px 0; width: 140px;">Produk</td>
                    <td style="font-weight: 700; color: #191c1e;">{{ $variant->product->name }}</td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 4px 0;">Varian</td>
                    <td style="font-weight: 600;">
                        {{ collect([$variant->model, $variant->color, $variant->size])->filter()->implode(' · ') ?: 'Default' }}
                    </td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 4px 0;">Barcode</td>
                    <td style="font-family: monospace; color: #666;">{{ $variant->barcode }}</td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 4px 0;">Stok Saat Ini</td>
                    <td style="font-weight: 800; color: #ba1a1a; font-size: 18px;">{{ $variant->stock }}</td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 4px 0;">Reorder Level</td>
                    <td style="font-weight: 600; color: #444;">{{ $variant->product->reorder_level }}</td>
                </tr>
            </table>
        </div>

        <p style="font-size: 13px; color: #888; margin-top: 32px;">Segera hubungi supplier untuk melakukan pemesanan ulang.</p>
        <p style="font-size: 13px; font-weight: 700; color: #00236f;">Tim Kasyraa.co</p>
    </div>
</div>
</body>
</html>
