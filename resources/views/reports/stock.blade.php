<x-app-layout>
    <x-slot name="header">Laporan Stok</x-slot>

    <div class="mb-6 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Laporan</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Stok <span class="text-primary italic">Barang</span>
            </h2>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.stock.export', request()->query()) }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-sm">download</span>
                Export Excel
            </a>
            <a href="{{ route('reports.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">← Laporan</a>
        </div>
    </div>

    <form method="GET" class="mb-6 flex gap-3 flex-wrap items-end">
        <div>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm w-52">
            <p class="text-[10px] text-slate-400 mt-1">Cari produk...</p>
        </div>
        <select name="category" class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:opacity-90">Filter</button>
        @if(request()->hasAny(['search','category']))
            <a href="{{ route('reports.stock') }}" class="px-5 py-2.5 bg-surface-container text-slate-500 rounded-xl text-sm font-bold">Reset</a>
        @endif
    </form>

    {{-- Summary --}}
    @php
        $totalVariants  = $products->sum(fn($p) => $p->variants->count());
        $totalStock     = $products->sum(fn($p) => $p->variants->sum('stock'));
        $criticalProds  = $products->filter(fn($p) => $p->variants->count() > 0 && $p->variants->sum('stock') <= $p->reorder_level)->count();
    @endphp
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Total Produk</p>
            <p class="text-3xl font-manrope font-black text-blue-900">{{ $products->count() }}</p>
        </div>
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Total Stok</p>
            <p class="text-3xl font-manrope font-black text-blue-900">{{ number_format($totalStock) }}</p>
        </div>
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Perlu Restock</p>
            <p class="text-3xl font-manrope font-black {{ $criticalProds > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $criticalProds }}</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($products as $product)
        @php $productStock = $product->variants->sum('stock'); @endphp
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
            {{-- Header produk --}}
            <div class="px-6 py-4 bg-surface-container-low/40 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <p class="font-manrope font-bold text-blue-900 text-sm">{{ $product->name }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $product->category->name }} · {{ $product->variants->count() }} varian</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400">Total stok:</span>
                    <span class="font-manrope font-black text-blue-900 text-lg">{{ $productStock }}</span>
                    @if($productStock == 0)
                        <span class="px-2 py-1 bg-rose-100 text-rose-700 text-[9px] font-black uppercase tracking-tighter rounded border border-rose-200">Habis</span>
                    @elseif($productStock <= $product->reorder_level)
                        <span class="px-2 py-1 bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-tighter rounded border border-amber-100">Hampir Habis</span>
                    @else
                        <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-tighter rounded border border-emerald-100">Aman</span>
                    @endif
                </div>
            </div>
            {{-- Tabel varian --}}
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low/20">
                        <th class="px-6 py-2.5 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Varian</th>
                        <th class="px-6 py-2.5 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Warna</th>
                        <th class="px-6 py-2.5 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Ukuran</th>
                        <th class="px-6 py-2.5 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Stok</th>
                        <th class="px-6 py-2.5 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Min.</th>
                        <th class="px-6 py-2.5 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50">
                    @foreach($product->variants as $variant)
                    <tr class="hover:bg-blue-50/20 transition-colors {{ $variant->stock <= $product->reorder_level ? 'bg-rose-50/20' : '' }}">
                        <td class="px-6 py-2.5 text-sm text-slate-600">{{ $variant->model ?: '—' }}</td>
                        <td class="px-6 py-2.5 text-sm text-slate-600">{{ $variant->color ?: '—' }}</td>
                        <td class="px-6 py-2.5">
                            <span class="px-2 py-0.5 bg-surface-container text-slate-600 text-[10px] font-bold rounded">{{ $variant->size ?: '—' }}</span>
                        </td>
                        <td class="px-6 py-2.5 text-center font-manrope font-bold {{ $variant->stock <= $product->reorder_level ? 'text-rose-600' : 'text-blue-900' }}">
                            {{ $variant->stock }}
                        </td>
                        <td class="px-6 py-2.5 text-center text-sm text-slate-400">{{ $product->reorder_level }}</td>
                        <td class="px-6 py-2.5">
                            @if($variant->stock == 0)
                                <span class="px-2 py-1 bg-rose-100 text-rose-700 text-[9px] font-black uppercase tracking-tighter rounded border border-rose-200">Habis</span>
                            @elseif($variant->stock <= $product->reorder_level)
                                <span class="px-2 py-1 bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-tighter rounded border border-amber-100">Hampir Habis</span>
                            @else
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-tighter rounded border border-emerald-100">Aman</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @empty
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow px-8 py-16 text-center text-slate-400 text-sm">
            Tidak ada data.
        </div>
        @endforelse
    </div>
</x-app-layout>
