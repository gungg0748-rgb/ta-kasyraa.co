<x-app-layout>
    <x-slot name="header">Detail Return</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Return Pembelian</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Return <span class="text-primary italic">#{{ $return->return_number }}</span>
            </h2>
        </div>
        <span class="px-3 py-1.5 bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-tighter rounded-full border border-amber-100">
            Return Diproses
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Info --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8 space-y-5">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Supplier</p>
                <p class="font-manrope font-bold text-blue-900">{{ $return->supplier->name }}</p>
                @if($return->supplier->email)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $return->supplier->email }}</p>
                @endif
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Referensi Pembelian</p>
                <a href="{{ route('purchases.show', $return->purchase) }}"
                   class="text-sm font-bold text-primary hover:underline">
                    #{{ $return->purchase->invoice_number }}
                </a>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Tanggal Return</p>
                <p class="text-sm font-bold text-blue-900">{{ $return->date->format('d F Y') }} · <svg class="inline w-3 h-3 mr-0.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>{{ $return->created_at->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Dicatat oleh</p>
                <p class="text-sm font-bold text-blue-900">{{ $return->user->name }}</p>
            </div>
            @if($return->notes)
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Catatan</p>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $return->notes }}</p>
            </div>
            @endif
            <div class="pt-4 border-t border-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Total Item Di-return</p>
                <p class="text-3xl font-manrope font-black text-amber-700">
                    {{ $return->items->sum('qty') }} pcs
                </p>
            </div>
        </div>

        {{-- Items --}}
        <div class="lg:col-span-2 bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100">
                <h3 class="font-manrope font-bold text-blue-900 text-sm uppercase tracking-widest">Item yang Di-return</h3>
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low/50">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Produk</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Varian</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center">Qty</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Alasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50">
                    @foreach($return->items as $item)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4 font-manrope font-bold text-blue-900 text-sm">
                            {{ $item->variant->product->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ collect([$item->variant->model, $item->variant->color, $item->variant->size])->filter()->implode(' · ') ?: '—' }}
                            <span class="block text-[10px] font-mono text-slate-300">{{ $item->variant->barcode }}</span>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-amber-700 text-lg">{{ $item->qty }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $item->reason ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('returns.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">
        ← Kembali ke riwayat return
    </a>
</x-app-layout>
