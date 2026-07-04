<x-app-layout>
    <x-slot name="header">Laporan Return</x-slot>

    <div class="mb-6 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Laporan</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Laporan <span class="text-primary italic">Return</span>
            </h2>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.returns.export', request()->query()) }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-sm">download</span>
                Export Excel
            </a>
            <a href="{{ route('reports.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">← Laporan</a>
        </div>
    </div>

    <form method="GET" class="mb-6 flex gap-3 flex-wrap items-end">
        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Supplier</label>
            <select name="supplier" class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
                <option value="">Semua</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Dari</label>
            <input type="date" name="from" value="{{ request('from') }}" class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ request('to') }}" class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:opacity-90">Filter</button>
        @if(request()->hasAny(['supplier','from','to']))
            <a href="{{ route('reports.returns') }}" class="px-5 py-2.5 bg-surface-container text-slate-500 rounded-xl text-sm font-bold">Reset</a>
        @endif
    </form>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Total Return</p>
            <p class="text-3xl font-manrope font-black text-blue-900">{{ $returns->count() }}</p>
        </div>
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Total Item Di-return</p>
            <p class="text-3xl font-manrope font-black text-amber-700">{{ $returns->sum(function($r) { return $r->items->sum('qty'); }) }} pcs</p>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">No. Return (Return #)</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Tanggal (Date)</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Supplier</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Ref. PO (Purchase Ref)</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center">Qty</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @forelse($returns as $r)
                <tr class="hover:bg-blue-50/20 transition-colors">
                    <td class="px-6 py-3 text-xs font-mono font-bold text-amber-700">
                        <a href="{{ route('returns.show', $r) }}" class="hover:underline">{{ $r->return_number }}</a>
                    </td>
                    <td class="px-6 py-3 text-sm text-slate-600">{{ $r->date->format('d M Y') }}</td>
                    <td class="px-6 py-3 font-manrope font-bold text-blue-900 text-sm">{{ $r->supplier->name }}</td>
                    <td class="px-6 py-3 text-xs font-mono text-slate-500">{{ $r->purchase->invoice_number }}</td>
                    <td class="px-6 py-3 text-center font-bold text-amber-700">{{ $r->items->sum('qty') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-8 py-12 text-center text-slate-400 text-sm">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
