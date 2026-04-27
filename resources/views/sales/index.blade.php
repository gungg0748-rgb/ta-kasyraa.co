<x-app-layout>
    <x-slot name="header">Penjualan</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Barang Keluar</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Riwayat <span class="text-primary italic">Penjualan</span>
            </h2>
        </div>
        <a href="{{ route('sales.create') }}"
           class="flex items-center gap-2 px-5 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-base">point_of_sale</span>
            Catat Penjualan
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-6 flex gap-3 flex-wrap items-end">
        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Dari</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:opacity-90 transition-opacity">Filter</button>
        @if(request()->hasAny(['from','to']))
            <a href="{{ route('sales.index') }}" class="px-5 py-2.5 bg-surface-container text-slate-500 rounded-xl text-sm font-bold hover:bg-surface-container-high transition-colors">Reset</a>
        @endif
    </form>

    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Invoice</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Tanggal</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Kasir</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Total</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @forelse($sales as $sale)
                <tr class="hover:bg-blue-50/30 even:bg-slate-50/40 transition-colors">
                    <td class="px-6 py-4 text-xs font-mono font-bold text-slate-600"><div class="max-w-[160px] truncate">{{ $sale->invoice_number }}</div></td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-slate-600">{{ $sale->date->format('d M Y') }}</p>
                        <p class="text-xs text-slate-400 mt-0.5"><svg class="inline w-3 h-3 mr-0.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>{{ $sale->created_at->format('H:i') }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $sale->user->name }}</td>
                    <td class="px-6 py-4 font-manrope font-bold text-blue-900">
                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('sales.show', $sale) }}"
                           class="text-xs font-bold text-primary hover:underline underline-offset-4 tracking-widest uppercase">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-16 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-5xl block mb-3">point_of_sale</span>
                        <p class="text-slate-400 text-sm">Belum ada transaksi penjualan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 bg-surface-container-low/30 border-t border-slate-100/50 flex items-center justify-between">
            <span class="text-xs text-slate-400">{{ $sales->total() }} transaksi</span>
            {{ $sales->links() }}
        </div>
    </div>
</x-app-layout>
