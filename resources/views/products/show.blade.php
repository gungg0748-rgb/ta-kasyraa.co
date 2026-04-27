<x-app-layout>
    <x-slot name="header">Detail Produk</x-slot>

    <div class="mb-8 flex items-end justify-between">
        <div>
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Data Master · Produk</p>
            <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">{{ $product->name }}</h2>
            <p class="text-slate-400 text-sm mt-1">{{ $product->category->name }} · {{ $product->unit->name }}</p>
        </div>
        <a href="{{ route('products.edit', $product) }}"
           class="flex items-center gap-2 px-5 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-base">edit</span> Edit Produk
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Info produk --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8 space-y-5">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Barcode</p>
                <p class="text-sm font-mono font-bold text-blue-900">{{ $product->barcode }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Harga Jual</p>
                <p class="text-2xl font-manrope font-black text-blue-900">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Reorder Level</p>
                <p class="text-sm font-bold text-blue-900">{{ $product->reorder_level }} {{ $product->unit->name }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Total Stok</p>
                @php $totalStock = $product->variants->sum('stock'); @endphp
                <p class="text-2xl font-manrope font-black {{ $totalStock <= $product->reorder_level ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $totalStock }}
                </p>
            </div>
            @if($product->description)
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Deskripsi</p>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $product->description }}</p>
            </div>
            @endif
            @if($product->image)
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Foto Produk</p>
                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                     class="w-full max-w-xs rounded-xl object-cover">
            </div>
            @endif
        </div>

        {{-- Varian --}}
        <div class="lg:col-span-2 bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-blue-900 tracking-tight">Varian Produk</h3>
                <span class="text-xs text-slate-400">{{ $product->variants->count() }} varian</span>
            </div>

            {{-- Tabel varian --}}
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-low/50">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Model</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Warna</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Ukuran</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Barcode</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Stok</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50" id="variant-list">
                    @forelse($product->variants as $variant)
                    <tr class="hover:bg-blue-50/30 transition-colors" id="row-{{ $variant->id }}">
                        <td class="px-6 py-3 text-sm text-blue-900 font-medium">{{ $variant->model ?: '—' }}</td>
                        <td class="px-6 py-3 text-sm text-slate-600">{{ $variant->color ?: '—' }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 bg-surface-container text-slate-600 text-[10px] font-black uppercase tracking-tighter rounded">
                                {{ $variant->size ?: '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-xs font-mono text-slate-400">{{ $variant->barcode }}</td>
                        <td class="px-6 py-3">
                            <span class="font-manrope font-bold text-sm {{ $variant->stock <= $product->reorder_level ? 'text-rose-600' : 'text-blue-900' }}">
                                {{ $variant->stock }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <button onclick="openEditVariant({{ $variant->id }}, '{{ $variant->model }}', '{{ $variant->color }}', '{{ $variant->size }}')"
                                        class="text-xs font-bold text-primary hover:underline underline-offset-4 tracking-widest uppercase">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('products.variants.destroy', [$product, $variant]) }}"
                                      onsubmit="return confirm('Hapus varian ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-bold text-rose-500 hover:underline underline-offset-4 tracking-widest uppercase">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-10 text-center text-slate-400 text-sm">Belum ada varian. Tambahkan di bawah.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Form tambah varian --}}
            <div class="px-8 py-6 bg-surface-container-low/30 border-t border-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4">Tambah Varian Baru</p>
                <form method="POST" action="{{ route('products.variants.store', $product) }}">
                    @csrf
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Model <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input type="text" name="model"
                                   class="w-full bg-white border border-slate-200 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 px-3 py-2.5">
                            <p class="text-[10px] text-slate-400 mt-1">Contoh: Slim Fit, Oversize</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Warna <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input type="text" name="color"
                                   class="w-full bg-white border border-slate-200 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 px-3 py-2.5">
                            <p class="text-[10px] text-slate-400 mt-1">Contoh: Hitam, Putih, Navy</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Ukuran <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input type="text" name="size"
                                   class="w-full bg-white border border-slate-200 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 px-3 py-2.5">
                            <p class="text-[10px] text-slate-400 mt-1">Contoh: S, M, L, XL, XXL</p>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mb-3">Isi minimal satu field. Barcode varian akan dibuat otomatis. Stok awal = 0.</p>
                    <button type="submit"
                            class="flex items-center gap-1 px-5 py-2.5 bg-primary text-on-primary rounded-xl font-bold text-sm shadow hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-sm">add</span> Tambah Varian
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal edit varian --}}
    <div id="edit-variant-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl editorial-shadow p-8 w-full max-w-sm">
            <h3 class="font-manrope font-bold text-blue-900 mb-1">Edit Varian</h3>
            <p class="text-xs text-slate-400 mb-5">Kosongkan field yang tidak ingin diubah.</p>
            <form id="edit-variant-form" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Model <span class="font-normal text-slate-400">(opsional)</span></label>
                    <input type="text" name="model" id="edit-model"
                           class="w-full bg-surface-container-low border border-slate-200 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 px-3 py-2.5">
                    <p class="text-[10px] text-slate-400 mt-1">Contoh: Slim Fit, Oversize</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Warna <span class="font-normal text-slate-400">(opsional)</span></label>
                    <input type="text" name="color" id="edit-color"
                           class="w-full bg-surface-container-low border border-slate-200 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 px-3 py-2.5">
                    <p class="text-[10px] text-slate-400 mt-1">Contoh: Hitam, Putih, Navy</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Ukuran <span class="font-normal text-slate-400">(opsional)</span></label>
                    <input type="text" name="size" id="edit-size"
                           class="w-full bg-surface-container-low border border-slate-200 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 px-3 py-2.5">
                    <p class="text-[10px] text-slate-400 mt-1">Contoh: S, M, L, XL, XXL</p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm hover:opacity-90 transition-opacity">
                        Simpan Perubahan
                    </button>
                    <button type="button" onclick="closeEditVariant()"
                            class="flex-1 py-3 bg-surface-container text-slate-600 rounded-xl font-bold text-sm hover:bg-surface-container-high transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('products.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">
            ← Kembali ke katalog
        </a>
    </div>

    <script>
        const baseUrl = '{{ route('products.variants.update', [$product, '__ID__']) }}';

        function openEditVariant(id, model, color, size) {
            document.getElementById('edit-model').value = model === 'null' ? '' : model;
            document.getElementById('edit-color').value = color === 'null' ? '' : color;
            document.getElementById('edit-size').value  = size === 'null' ? '' : size;
            document.getElementById('edit-variant-form').action = baseUrl.replace('__ID__', id);
            document.getElementById('edit-variant-modal').classList.remove('hidden');
        }

        function closeEditVariant() {
            document.getElementById('edit-variant-modal').classList.add('hidden');
        }
    </script>
</x-app-layout>
