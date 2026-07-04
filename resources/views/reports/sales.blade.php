<x-app-layout>
    <x-slot name="header">Laporan Penjualan</x-slot>

    <div class="mb-6 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Laporan</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Laporan <span class="text-primary italic">Penjualan</span>
            </h2>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.sales.export', request()->query()) }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-sm">download</span>
                Export Excel
            </a>
            <a href="{{ route('reports.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">← Laporan</a>
        </div>
    </div>

    <form method="GET" class="mb-6 flex gap-3 flex-wrap items-end">
        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Dari</label>
            <input type="date" name="from" value="{{ request('from') }}" class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ request('to') }}" class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:opacity-90">Filter</button>
        @if(request()->hasAny(['from','to']))
            <a href="{{ route('reports.sales') }}" class="px-5 py-2.5 bg-surface-container text-slate-500 rounded-xl text-sm font-bold">Reset</a>
        @endif
    </form>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Total Transaksi</p>
            <p class="text-3xl font-manrope font-black text-blue-900">{{ $sales->count() }}</p>
        </div>
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Total Pendapatan</p>
            <p class="text-3xl font-manrope font-black text-blue-900">Rp {{ number_format($total, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Invoice</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Tanggal (Date)</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Kasir (Cashier)</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center">Item</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @forelse($sales as $s)
                <tr class="hover:bg-blue-50/20 transition-colors">
                    <td class="px-6 py-3 text-xs font-mono font-bold text-slate-600">
                        <a href="{{ route('sales.show', $s) }}" class="text-primary hover:underline">{{ $s->invoice_number }}</a>
                    </td>
                    <td class="px-6 py-3 text-sm text-slate-600">{{ $s->date->format('d M Y') }}</td>
                    <td class="px-6 py-3 text-sm text-slate-500">{{ $s->user->name }}</td>
                    <td class="px-6 py-3 text-center text-sm text-slate-500">{{ $s->items->count() }}</td>
                    <td class="px-6 py-3 text-right font-manrope font-bold text-blue-900">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-8 py-12 text-center text-slate-400 text-sm">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
            @if($sales->count())
            <tfoot>
                <tr class="bg-surface-container-low/30 border-t border-slate-100">
                    <td colspan="4" class="px-6 py-4 text-right font-black text-xs uppercase tracking-widest text-slate-500">Total</td>
                    <td class="px-6 py-4 text-right font-manrope font-black text-lg text-blue-900">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-app-layout>
