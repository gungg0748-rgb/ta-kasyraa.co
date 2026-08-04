<x-app-layout>
    <x-slot name="header">Produk</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Data Master</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
                Katalog <span class="text-primary italic">Produk</span>
            </h2>
        </div>
        <a href="{{ route('products.create') }}"
           class="flex items-center gap-2 px-5 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-base">add</span>
            Tambah Produk
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="mb-6 flex gap-3 flex-wrap">
        <div>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 w-64 shadow-sm">
            <p class="text-[10px] text-slate-400 mt-1">Cari nama / barcode...</p>
        </div>
        <select name="category"
                class="bg-surface-container-lowest border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 shadow-sm">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="px-5 py-2 bg-primary text-on-primary rounded-xl text-sm font-bold hover:opacity-90 transition-opacity">
            Filter
        </button>
        @if(request()->hasAny(['search','category']))
            <a href="{{ route('products.index') }}"
               class="px-5 py-2 bg-surface-container text-slate-500 rounded-xl text-sm font-bold hover:bg-surface-container-high transition-colors">
                Reset
            </a>
        @endif
    </form>

    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/50">
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Produk (Product)</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Barcode</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Kategori (Category)</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Harga Jual</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Harga Beli</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Margin (%)</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Varian (Variants)</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Total Stok (Stock)</th>
                    <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Aksi (Actions)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @forelse($products as $product)
                @php $totalStock = $product->variants->sum('stock'); @endphp
                <tr class="hover:bg-blue-50/30 even:bg-slate-50/40 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-manrope font-bold text-blue-900 text-sm">{{ $product->name }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $product->unit->name }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <button type="button" onclick="showBarcode('{{ $product->barcode }}', '{{ addslashes($product->name) }}')"
                                class="cursor-zoom-in hover:opacity-70 transition-opacity">
                            <svg class="barcode" data-value="{{ $product->barcode }}" style="max-width:120px;height:40px;"></svg>
                            <p class="text-[10px] font-mono text-slate-400 mt-0.5 text-center">{{ $product->barcode }}</p>
                        </button>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-tighter rounded border border-blue-100">
                            {{ $product->category->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-manrope font-bold text-blue-900 text-sm">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 font-manrope font-bold text-sm">
                        @if(isset($lastCost[$product->id]))
                            <span class="text-emerald-700">Rp {{ number_format($lastCost[$product->id], 0, ',', '.') }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-manrope font-bold text-sm">
                        @if(isset($lastCost[$product->id]) && $lastCost[$product->id] > 0)
                            @php $pct = round(($product->price - $lastCost[$product->id]) / $lastCost[$product->id] * 100, 1); @endphp
                            <span class="{{ $pct >= 0 ? 'text-emerald-600' : 'text-rose-500' }}">{{ $pct }}%</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $product->variants->count() }} varian</td>
                    <td class="px-6 py-4">
                        @if($totalStock == 0)
                            <span class="px-2 py-1 bg-rose-100 text-rose-700 text-[10px] font-black uppercase tracking-tighter rounded border border-rose-200 stock-pulse">
                                Habis
                            </span>
                        @elseif($totalStock <= $product->reorder_level)
                            <span class="px-2 py-1 bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-tighter rounded border border-amber-100 stock-pulse">
                                {{ $totalStock }} — Hampir Habis
                            </span>
                        @else
                            <span class="text-sm font-bold text-blue-900">{{ $totalStock }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('products.show', $product) }}"
                               class="text-xs font-bold text-slate-500 hover:underline underline-offset-4 tracking-widest uppercase">Detail</a>
                            <a href="{{ route('products.edit', $product) }}"
                               class="text-xs font-bold text-primary hover:underline underline-offset-4 tracking-widest uppercase">Edit</a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}"
                                  onsubmit="return confirm('Hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-bold text-rose-500 hover:underline underline-offset-4 tracking-widest uppercase">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-8 py-16 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-5xl block mb-3">inventory_2</span>
                        <p class="text-slate-400 text-sm">Belum ada produk.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 bg-surface-container-low/30 border-t border-slate-100/50 flex items-center justify-between">
            <span class="text-xs text-slate-400">{{ $products->total() }} produk</span>
            {{ $products->links() }}
        </div>
    </div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('svg.barcode').forEach(function (el) {
            JsBarcode(el, el.dataset.value, {
                format: 'CODE128',
                width: 1.5,
                height: 35,
                displayValue: false,
                margin: 0,
            });
        });
    });

    function showBarcode(value, name) {
        document.getElementById('modal-barcode-name').textContent = name;
        document.getElementById('modal-barcode-code').textContent = value;

        const svg = document.getElementById('modal-barcode-svg');
        JsBarcode(svg, value, {
            format: 'CODE128',
            width: 3,
            height: 80,
            displayValue: false,
            margin: 10,
        });

        document.getElementById('modal-barcode').classList.remove('hidden');
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.getElementById('modal-barcode').classList.add('hidden');
    });
</script>

{{-- Modal Barcode --}}
<div id="modal-barcode" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
     onclick="this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl p-8 mx-4 text-center" onclick="event.stopPropagation()">
        <p id="modal-barcode-name" class="font-manrope font-bold text-blue-900 text-lg mb-1"></p>
        <svg id="modal-barcode-svg" class="mx-auto my-4"></svg>
        <p id="modal-barcode-code" class="font-mono text-slate-500 text-sm tracking-widest"></p>
        <button onclick="document.getElementById('modal-barcode').classList.add('hidden')"
                class="mt-5 px-6 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-200 transition-colors">
            Tutup
        </button>
    </div>
</div>
@endpush
</x-app-layout>
