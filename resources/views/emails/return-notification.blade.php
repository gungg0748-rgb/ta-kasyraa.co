<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Notifikasi Return</title></head>
<body style="font-family: Inter, sans-serif; background: #f7f9fb; padding: 40px 0; color: #191c1e;">
<div style="max-width: 560px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08);">
    <div style="background: #b45309; padding: 32px 40px;">
        <h1 style="font-family: Manrope, sans-serif; color: #fff; margin: 0; font-size: 22px; font-weight: 800;">Kasyraa.co</h1>
        <p style="color: rgba(255,255,255,0.7); font-size: 11px; letter-spacing: 2px; text-transform: uppercase; margin: 4px 0 0;">Notifikasi Return Pembelian</p>
    </div>
    <div style="padding: 40px;">
        <p style="color: #444; font-size: 14px; line-height: 1.6;">Kepada Yth. <strong>{{ $return->supplier->name }}</strong>,</p>
        <p style="color: #444; font-size: 14px; line-height: 1.6;">
            Kami menginformasikan bahwa terdapat pengembalian barang (return) dari transaksi pembelian berikut.
        </p>

        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px 24px; margin: 24px 0;">
            <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                <tr>
                    <td style="color: #888; padding: 4px 0; width: 140px;">No. Return</td>
                    <td style="font-weight: 700; color: #b45309;">#{{ $return->return_number }}</td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 4px 0;">Ref. Pembelian</td>
                    <td style="font-weight: 600;">#{{ $return->purchase->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 4px 0;">Tanggal Return</td>
                    <td style="font-weight: 600;">{{ $return->date->format('d F Y') }}</td>
                </tr>
                <tr>
                    <td style="color: #888; padding: 4px 0;">Diproses oleh</td>
                    <td style="font-weight: 600;">{{ $return->user->name }}</td>
                </tr>
            </table>
        </div>

        <table style="width: 100%; font-size: 13px; border-collapse: collapse; margin-bottom: 24px;">
            <thead>
                <tr style="background: #f2f4f6;">
                    <th style="text-align: left; padding: 10px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Produk</th>
                    <th style="text-align: center; padding: 10px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Qty</th>
                    <th style="text-align: left; padding: 10px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #888;">Alasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($return->items as $item)
                <tr style="border-bottom: 1px solid #f0f0f0;">
                    <td style="padding: 10px 12px;">
                        {{ $item->variant->product->name }}
                        <span style="color: #888; font-size: 11px; display: block;">
                            {{ collect([$item->variant->model, $item->variant->color, $item->variant->size])->filter()->implode(' · ') }}
                        </span>
                    </td>
                    <td style="padding: 10px 12px; text-align: center; font-weight: 700;">{{ $item->qty }}</td>
                    <td style="padding: 10px 12px; color: #666;">{{ $item->reason ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($return->notes)
        <p style="font-size: 13px; color: #666; background: #fffbeb; padding: 12px 16px; border-radius: 8px; border-left: 3px solid #b45309;">
            <strong>Catatan:</strong> {{ $return->notes }}
        </p>
        @endif

        <p style="font-size: 13px; color: #888; margin-top: 32px;">Mohon konfirmasi penerimaan return ini. Terima kasih.</p>
        <p style="font-size: 13px; font-weight: 700; color: #00236f;">Tim Kasyraa.co</p>
    </div>
</div>
</body>
</html>
