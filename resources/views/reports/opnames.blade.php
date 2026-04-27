<x-app-layout>
    <x-slot name="header">Laporan Stok Opname</x-slot>

    <div class="mb-6 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Laporan</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Laporan <span class="text-primary italic">Stok Opname</span>
            </h2>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.opnames.export', request()->query()) }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-sm">download</span>
                Export Excel
            </a>
            <a href="{{ route('reports.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">← Laporan</a>
        </div>
    </div>

    <form method="GET" class="mb-6 flex gap-3 flex-wrap items-end">
        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Status</label>
            <select name="status" class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
                <option value="">Semua</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
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
        @if(request()->hasAny(['status','from','to']))
            <a href="{{ route('reports.opnames') }}" class="px-5 py-2.5 bg-surface-container text-slate-500 rounded-xl text-sm font-bold">Reset</a>
        @endif
    </form>

    @foreach($opnames as $opname)
    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden mb-6">
        <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="font-manrope font-bold text-blue-900">Opname {{ $opname->date->format('d F Y') }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $opname->user->name }} · {{ $opname->items->count() }} varian</p>
            </div>
            <div class="flex items-center gap-3">
                @if($opname->notes)
                    <span class="text-xs text-slate-400 italic">{{ $opname->notes }}</span>
                @endif
                @if($opname->status === 'confirmed')
                    <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-tighter rounded border border-emerald-100">Dikonfirmasi</span>
                @else
                    <span class="px-2 py-1 bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-tighter rounded border border-amber-100">Draft</span>
                @endif
            </div>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/30">
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Produk</th>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Varian</th>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Stok Sistem</th>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Stok Fisik</th>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Selisih</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @foreach($opname->items as $item)
                @php $diff = $item->difference; @endphp
                <tr class="{{ $diff != 0 ? 'bg-amber-50/20' : '' }}">
                    <td class="px-6 py-3 font-manrope font-bold text-blue-900 text-sm">{{ $item->variant->product->name }}</td>
                    <td class="px-6 py-3 text-sm text-slate-500">
                        {{ collect([$item->variant->model, $item->variant->color, $item->variant->size])->filter()->implode(' · ') ?: 'Default' }}
                    </td>
                    <td class="px-6 py-3 text-center font-bold text-blue-900">{{ $item->system_stock }}</td>
                    <td class="px-6 py-3 text-center font-bold text-blue-900">{{ $item->physical_stock }}</td>
                    <td class="px-6 py-3 text-center">
                        @if($diff > 0)
                            <span class="text-emerald-600 font-black text-sm">+{{ $diff }}</span>
                        @elseif($diff < 0)
                            <span class="text-rose-600 font-black text-sm">{{ $diff }}</span>
                        @else
                            <span class="text-slate-300 text-sm">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-16 text-center">
        <span class="material-symbols-outlined text-slate-200 text-5xl block mb-3">inventory</span>
        <p class="text-slate-400 text-sm">Tidak ada data opname.</p>
    </div>
    @endforelse
</x-app-layout>
