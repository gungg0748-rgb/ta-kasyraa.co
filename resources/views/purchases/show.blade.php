<x-app-layout>
    <x-slot name="header">Detail Pembelian</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Barang Masuk</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Invoice <span class="text-primary italic">#{{ $purchase->invoice_number }}</span>
            </h2>
        </div>
        <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-tighter rounded-full border border-emerald-100">
            Selesai
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Info --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8 space-y-5">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Supplier</p>
                <p class="font-manrope font-bold text-blue-900">{{ $purchase->supplier->name }}</p>
                @if($purchase->supplier->email)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $purchase->supplier->email }}</p>
                @endif
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Tanggal</p>
                <p class="text-sm font-bold text-blue-900">{{ $purchase->date->format('d F Y') }} · <svg class="inline w-3 h-3 mr-0.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>{{ $purchase->created_at->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Dicatat oleh</p>
                <p class="text-sm font-bold text-blue-900">{{ $purchase->user->name }}</p>
            </div>
            @if($purchase->notes)
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Catatan</p>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $purchase->notes }}</p>
            </div>
            @endif
            <div class="pt-4 border-t border-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Total</p>
                <p class="text-3xl font-manrope font-black text-blue-900">Rp {{ number_format($purchase->total, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Items --}}
        <div class="lg:col-span-2 bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100">
                <h3 class="font-manrope font-bold text-blue-900 text-sm uppercase tracking-widest">Item Pembelian</h3>
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low/50">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Produk</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Varian</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center">Qty</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-right">Harga</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50">
                    @foreach($purchase->items as $item)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4 font-manrope font-bold text-blue-900 text-sm">
                            {{ $item->variant->product->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ collect([$item->variant->model, $item->variant->color, $item->variant->size])->filter()->implode(' · ') ?: '—' }}
                            <span class="block text-[10px] font-mono text-slate-300">{{ $item->variant->barcode }}</span>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-blue-900">{{ $item->qty }}</td>
                        <td class="px-6 py-4 text-right text-sm text-slate-600">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-manrope font-bold text-blue-900">
                            Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-surface-container-low/30 border-t border-slate-100">
                        <td colspan="4" class="px-6 py-4 text-right font-black text-sm uppercase tracking-widest text-slate-500">Total</td>
                        <td class="px-6 py-4 text-right font-manrope font-black text-xl text-blue-900">
                            Rp {{ number_format($purchase->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <a href="{{ route('purchases.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">
        ← Kembali ke riwayat pembelian
    </a>
</x-app-layout>
