<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk #{{ $sale->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #e5e7eb; font-family: 'Courier New', monospace; color: #111; padding: 20px; }
        .struk {
            width: 300px; margin: 0 auto; background: #fff; padding: 18px 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,.12);
        }
        .center { text-align: center; }
        .store { font-size: 16px; font-weight: bold; letter-spacing: 1px; }
        .sub { font-size: 11px; color: #444; }
        .hr { border-top: 1px dashed #999; margin: 8px 0; }
        table { width: 100%; font-size: 12px; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .right { text-align: right; }
        .row { display: flex; justify-content: space-between; font-size: 12px; margin: 2px 0; }
        .bold { font-weight: bold; }
        .big { font-size: 14px; }
        .foot { text-align: center; font-size: 11px; color: #444; margin-top: 10px; }
        .actions { width: 300px; margin: 14px auto 0; display: flex; gap: 8px; }
        .btn { flex: 1; padding: 10px; border: 0; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer; }
        .btn-print { background: #1e3a8a; color: #fff; }
        .btn-back { background: #e5e7eb; color: #111; text-decoration: none; text-align: center; }
        @media print {
            body { background: #fff; padding: 0; }
            .struk { box-shadow: none; width: 100%; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="struk">
        <div class="center">
            <div class="store">{{ config('app.name', 'Kasyraa.co') }}</div>
            <div class="sub">Manajemen Stok · Kasyraa.co</div>
        </div>
        <div class="hr"></div>
        <div class="row"><span>No</span><span>#{{ $sale->invoice_number }}</span></div>
        <div class="row"><span>Tanggal</span><span>{{ $sale->date->format('d/m/Y') }} {{ $sale->created_at->format('H:i') }}</span></div>
        <div class="row"><span>Kasir</span><span>{{ $sale->user->name }}</span></div>
        <div class="hr"></div>

        <table>
            @foreach($sale->items as $item)
            <tr>
                <td colspan="2">{{ $item->variant->product->name }}
                    @php $v = collect([$item->variant->model, $item->variant->color, $item->variant->size])->filter()->implode(' '); @endphp
                    @if($v)<br><span style="color:#555">{{ $v }}</span>@endif
                </td>
            </tr>
            <tr>
                <td>{{ $item->qty }} x {{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($item->qty * $item->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>

        <div class="hr"></div>
        <div class="row bold big"><span>TOTAL</span><span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span></div>
        <div class="row"><span>Metode</span><span style="text-transform:uppercase">{{ $sale->payment_method }}</span></div>
        <div class="row"><span>Bayar</span><span>Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</span></div>
        <div class="row bold"><span>Kembalian</span><span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span></div>
        <div class="hr"></div>

        <div class="foot">
            Terima kasih telah berbelanja<br>
            Barang yang sudah dibeli tidak dapat ditukar
        </div>
    </div>

    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">Cetak Struk</button>
        <a class="btn btn-back" href="{{ route('sales.show', $sale) }}">Kembali</a>
    </div>

    <script>
        // Otomatis buka dialog cetak saat halaman dibuka
        window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    </script>
</body>
</html>
