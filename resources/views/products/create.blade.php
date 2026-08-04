<x-app-layout>
    <x-slot name="header">Tambah Produk</x-slot>

    <div class="mb-8">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Data Master · Produk</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Produk <span class="text-primary italic">Baru</span>
        </h2>
    </div>

    <div class="max-w-2xl bg-surface-container-lowest rounded-2xl editorial-shadow p-8">
        <form method="POST" action="{{ route('products.store') }}" class="space-y-5" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-2 gap-5">
                <div class="col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Nama Produk (Name)</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20"
                           required>
                    <p class="text-[10px] text-slate-400 mt-1">Nama produk</p>
                    @error('name') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Kategori (Category)</label>
                    <select name="category_id"
                            class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                        <option value="">— Pilih Kategori —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Satuan (Unit)</label>
                    <select name="unit_id"
                            class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                        <option value="">— Pilih Satuan —</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Harga Jual (Price) (Rp)</label>
                    <input type="number" name="price" value="{{ old('price', 0) }}" min="0"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                    @error('price') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">
                        Harga Beli (Purchase Price) (Rp)
                        <span class="font-normal normal-case tracking-normal">(opsional)</span>
                    </label>
                    <input type="number" name="purchase_price" id="purchase_price" value="{{ old('purchase_price') }}" min="0"
                           oninput="calcSellPrice()"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                    @error('purchase_price') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">
                        Kalkulator Margin / Keuntungan (%)
                        <span class="font-normal normal-case tracking-normal">(alat bantu)</span>
                    </label>
                    <input type="number" id="calc_margin" value="" min="0" max="999.9" step="0.1" placeholder="misal: 20"
                           oninput="calcSellPrice(); document.getElementById('profit_pct').textContent = this.value || '0'"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                    <p class="text-[10px] text-slate-400 mt-1">
                        Harga Jual = Harga Beli + <strong id="profit_pct">0</strong>%
                        <span class="text-primary font-bold" id="profit_hint" style="display:none">→ Rp <span id="profit_result"></span></span>
                    </p>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Level Restock (Reorder Level)</label>
                    <input type="number" name="reorder_level" value="{{ old('reorder_level', 5) }}" min="0"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                    <p class="text-[10px] text-slate-400 mt-1">Notifikasi dikirim saat stok ≤ nilai ini</p>
                    @error('reorder_level') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Deskripsi (Description) <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                    <textarea name="description" rows="3"
                              class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">{{ old('description') }}</textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Deskripsi produk...</p>
                </div>

                <div class="col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Foto Produk (Image) <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20 p-2">
                    <p class="text-[10px] text-slate-400 mt-1">Maks. 2MB. Format: JPG, PNG, WEBP.</p>
                    @error('image') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>
            </div>

            <p class="text-[10px] text-slate-400 bg-surface-container-low rounded-xl px-4 py-3">
                <span class="material-symbols-outlined text-xs align-middle mr-1">info</span>
                Barcode akan di-generate otomatis setelah produk disimpan. Varian (warna & ukuran) dapat ditambahkan setelah produk dibuat.
            </p>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-base">save</span> Simpan Produk
                </button>
                <a href="{{ route('products.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Batal</a>
            </div>
        </form>
    </div>

    <script>
        function calcSellPrice() {
            const beli = parseInt(document.getElementById('purchase_price').value) || 0;
            const margin = parseFloat(document.getElementById('calc_margin').value) || 0;
            const hint = document.getElementById('profit_hint');
            const result = document.getElementById('profit_result');
            if (beli > 0 && margin > 0) {
                const jual = beli + Math.round(beli * margin / 100);
                result.textContent = jual.toLocaleString('id-ID');
                hint.style.display = 'inline';
                document.querySelector('[name=price]').value = jual;
            } else {
                hint.style.display = 'none';
            }
        }
    </script>
</x-app-layout>
