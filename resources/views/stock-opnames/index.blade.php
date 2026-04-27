<x-app-layout>
    <x-slot name="header">Stok Opname</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Rekonsiliasi Stok</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Riwayat <span class="text-primary italic">Stok Opname</span>
            </h2>
        </div>
        <a href="{{ route('stock-opnames.create') }}"
           class="flex items-center gap-2 px-5 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-base">inventory</span>
            Mulai Opname
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-6 flex gap-3 flex-wrap items-end">
        <div>
            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Status</label>
            <select name="status"
                    class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi</option>
            </select>
        </div>
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
        @if(request()->hasAny(['status','from','to']))
            <a href="{{ route('stock-opnames.index') }}" class="px-5 py-2.5 bg-surface-container text-slate-500 rounded-xl text-sm font-bold hover:bg-surface-container-high transition-colors">Reset</a>
        @endif
    </form>

    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">#</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Tanggal</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Dicatat oleh</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Catatan</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Status</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @forelse($opnames as $i => $opname)
                <tr class="hover:bg-blue-50/30 even:bg-slate-50/40 transition-colors">
                    <td class="px-6 py-4 text-xs text-slate-400 font-mono">{{ $opnames->firstItem() + $i }}</td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-blue-900">{{ $opname->date->format('d M Y') }}</p>
                        <p class="text-xs text-slate-400 mt-0.5"><svg class="inline w-3 h-3 mr-0.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>{{ $opname->created_at->format('H:i') }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $opname->user->name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-400 max-w-[200px] truncate">{{ $opname->notes ?: '—' }}</td>
                    <td class="px-6 py-4">
                        @if($opname->status === 'confirmed')
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-tighter rounded border border-emerald-100">
                                Dikonfirmasi
                            </span>
                        @else
                            <span class="px-2 py-1 bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-tighter rounded border border-amber-100 stock-pulse">
                                Draft
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('stock-opnames.show', $opname) }}"
                           class="text-xs font-bold text-primary hover:underline underline-offset-4 tracking-widest uppercase">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-16 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-5xl block mb-3">inventory</span>
                        <p class="text-slate-400 text-sm">Belum ada stok opname.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 bg-surface-container-low/30 border-t border-slate-100/50 flex items-center justify-between">
            <span class="text-xs text-slate-400">{{ $opnames->total() }} opname</span>
            {{ $opnames->links() }}
        </div>
    </div>
</x-app-layout>
