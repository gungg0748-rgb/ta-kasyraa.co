<x-app-layout>
    <x-slot name="header">Detail Stok Opname</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Rekonsiliasi Stok</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Opname <span class="text-primary italic">{{ $stockOpname->date->format('d M Y') }}</span>
                <span class="text-slate-400 text-lg font-normal ml-2"><svg class="inline w-3 h-3 mr-0.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>{{ $stockOpname->created_at->format('H:i') }}</span>
            </h2>
        </div>
        <div class="flex items-center gap-3">
            @if($stockOpname->status === 'confirmed')
                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-tighter rounded-full border border-emerald-100">
                    Dikonfirmasi
                </span>
            @else
                <span class="px-3 py-1.5 bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-tighter rounded-full border border-amber-100">
                    Draft
                </span>
                <form method="POST" action="{{ route('stock-opnames.confirm', $stockOpname) }}"
                      onsubmit="return confirm('Konfirmasi opname ini? Stok sistem akan disesuaikan dengan stok fisik.')">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 px-5 py-2.5 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        Konfirmasi & Sesuaikan Stok
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Summary cards --}}
    @php
        $totalItems   = $stockOpname->items->count();
        $withDiff     = $stockOpname->items->filter(fn($i) => $i->difference != 0)->count();
        $totalDiff    = $stockOpname->items->sum('difference');
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Total Varian</p>
            <p class="text-3xl font-manrope font-black text-blue-900">{{ $totalItems }}</p>
        </div>
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Ada Selisih</p>
            <p class="text-3xl font-manrope font-black {{ $withDiff > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $withDiff }}</p>
        </div>
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Total Selisih</p>
            <p class="text-3xl font-manrope font-black {{ $totalDiff < 0 ? 'text-rose-600' : ($totalDiff > 0 ? 'text-emerald-600' : 'text-blue-900') }}">
                {{ $totalDiff > 0 ? '+' : '' }}{{ $totalDiff }}
            </p>
        </div>
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Dicatat oleh</p>
            <p class="text-sm font-manrope font-bold text-blue-900 mt-2">{{ $stockOpname->user->name }}</p>
            @if($stockOpname->notes)
                <p class="text-xs text-slate-400 mt-1 truncate">{{ $stockOpname->notes }}</p>
            @endif
        </div>
    </div>

    {{-- Tabel detail --}}
    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
        <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-manrope font-bold text-blue-900 text-sm uppercase tracking-widest">Detail Selisih Stok</h3>
            <div class="flex gap-3 text-xs font-bold">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span> Lebih</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span> Kurang</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-slate-300 inline-block"></span> Sesuai</span>
            </div>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Produk</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Varian</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center">Stok Sistem</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center">Stok Fisik</th>
                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center">Selisih</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @foreach($stockOpname->items as $item)
                @php $diff = $item->difference; @endphp
                <tr class="hover:bg-blue-50/20 transition-colors {{ $diff != 0 ? 'bg-amber-50/30' : '' }}">
                    <td class="px-6 py-4 font-manrope font-bold text-blue-900 text-sm">
                        {{ $item->variant->product->name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">
                        {{ collect([$item->variant->model, $item->variant->color, $item->variant->size])->filter()->implode(' · ') ?: 'Default' }}
                        <span class="block text-[10px] font-mono text-slate-300">{{ $item->variant->barcode }}</span>
                    </td>
                    <td class="px-6 py-4 text-center font-bold text-blue-900">{{ $item->system_stock }}</td>
                    <td class="px-6 py-4 text-center font-bold text-blue-900">{{ $item->physical_stock }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($diff > 0)
                            <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-xs font-black rounded border border-emerald-100">+{{ $diff }}</span>
                        @elseif($diff < 0)
                            <span class="px-2 py-1 bg-rose-50 text-rose-600 text-xs font-black rounded border border-rose-100">{{ $diff }}</span>
                        @else
                            <span class="text-slate-300 text-xs font-bold">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($stockOpname->status === 'draft')
    <div class="mt-6 p-5 bg-amber-50 border border-amber-100 rounded-xl flex items-start gap-3">
        <span class="material-symbols-outlined text-amber-600 mt-0.5">info</span>
        <div>
            <p class="text-sm font-bold text-amber-800">Opname masih berstatus Draft</p>
            <p class="text-xs text-amber-700 mt-1">Klik "Konfirmasi & Sesuaikan Stok" di atas untuk menerapkan hasil opname ke stok sistem. Tindakan ini tidak dapat dibatalkan.</p>
        </div>
    </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('stock-opnames.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">
            ← Kembali ke riwayat opname
        </a>
    </div>
</x-app-layout>
