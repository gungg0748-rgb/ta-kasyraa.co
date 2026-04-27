<x-app-layout>
    <x-slot name="header">Catat Return</x-slot>

    <div class="mb-8">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Return Pembelian</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Return <span class="text-primary italic">Baru</span>
        </h2>
    </div>

    @php
        $purchasesData = $purchases->map(fn($p) => [
            'id'       => $p->id,
            'label'    => '#' . $p->invoice_number . ' — ' . $p->supplier->name . ' (' . $p->date->format('d M Y') . ')',
            'supplier' => $p->supplier->name,
            'items'    => $p->items->map(fn($i) => [
                'variant_id' => $i->variant_id,
                'label'      => $i->variant->product->name . ' (' .
                                collect([$i->variant->model, $i->variant->color, $i->variant->size])->filter()->implode(', ') . ')',
                'stock'      => $i->variant->stock,
            ]),
        ])->values();
    @endphp

    <div x-data="returnForm({{ $purchasesData->toJson() }})" class="space-y-6">

        {{-- Header --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8">
            <h3 class="font-manrope font-bold text-blue-900 mb-5 text-sm uppercase tracking-widest">Informasi Return</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Referensi Pembelian</label>
                    <select x-model="selectedPurchaseId" @change="onPurchaseChange()"
                            class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                        <option value="">— Pilih Transaksi Pembelian —</option>
                        <template x-for="p in purchases" :key="p.id">
                            <option :value="p.id" x-text="p.label"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Tanggal Return</label>
                    <input type="date" x-model="date" value="{{ date('Y-m-d') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Catatan <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                    <input type="text" x-model="notes"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                    <p class="text-[10px] text-slate-400 mt-1">Catatan return...</p>
                </div>
            </div>

            {{-- Info supplier --}}
            <div x-show="selectedPurchase" class="mt-4 px-4 py-3 bg-amber-50 border border-amber-100 rounded-xl text-sm text-amber-800 font-medium">
                <span class="material-symbols-outlined text-sm align-middle mr-1">storefront</span>
                Supplier: <span class="font-bold" x-text="selectedPurchase?.supplier"></span>
            </div>
        </div>

        {{-- Item return --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-blue-900 text-sm uppercase tracking-widest">Item yang Di-return</h3>
                <button type="button" @click="addItem()"
                        :disabled="!selectedPurchaseId"
                        class="flex items-center gap-1 px-4 py-2 bg-primary/10 text-primary rounded-xl text-xs font-bold hover:bg-primary/20 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">add</span> Tambah Item
                </button>
            </div>

            <div class="divide-y divide-slate-100/50">
                <template x-for="(item, index) in items" :key="index">
                    <div class="px-8 py-5 flex gap-4 items-end flex-wrap">
                        <div class="flex-1 min-w-[220px]">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Varian Produk</label>
                            <select :name="'items[' + index + '][variant_id]'"
                                    x-model="item.variant_id"
                                    @change="onItemVariantChange(item)"
                                    class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                                <option value="">— Pilih Varian —</option>
                                <template x-for="v in availableVariants" :key="v.variant_id">
                                    <option :value="v.variant_id" x-text="v.label + ' (Stok: ' + v.stock + ')'"></option>
                                </template>
                            </select>
                        </div>
                        <div class="w-28">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Qty Return</label>
                            <input type="number" :name="'items[' + index + '][qty]'"
                                   x-model.number="item.qty" min="1" :max="item.maxQty"
                                   class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                            <p x-show="item.maxQty > 0" class="text-[10px] text-slate-400 mt-1" x-text="'Stok: ' + item.maxQty"></p>
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Alasan <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                            <input type="text" :name="'items[' + index + '][reason]'"
                                   x-model="item.reason"
                                   class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                            <p class="text-[10px] text-slate-400 mt-1">Contoh: cacat, salah ukuran...</p>
                        </div>
                        <button type="button" @click="removeItem(index)"
                                class="mb-0.5 text-rose-400 hover:text-rose-600 transition-colors">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </template>
                <div x-show="items.length === 0" class="px-8 py-10 text-center text-slate-400 text-sm">
                    <span x-show="!selectedPurchaseId">Pilih transaksi pembelian terlebih dahulu.</span>
                    <span x-show="selectedPurchaseId">Klik "Tambah Item" untuk menambah item return.</span>
                </div>
            </div>
        </div>

        @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl">
            <ul class="text-rose-600 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Submit --}}
        <form method="POST" action="{{ route('returns.store') }}" id="return-form">
            @csrf
            <div id="return-fields"></div>
            <div class="flex items-center gap-4">
                <button type="button" @click="submitForm()"
                        :disabled="items.length === 0 || !selectedPurchaseId"
                        class="flex items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-base">save</span>
                    Simpan Return
                </button>
                <a href="{{ route('returns.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Batal</a>
            </div>
        </form>
    </div>

    <script>
    function returnForm(purchases) {
        return {
            purchases,
            selectedPurchaseId: '',
            selectedPurchase: null,
            availableVariants: [],
            date: '{{ date('Y-m-d') }}',
            notes: '',
            items: [],

            onPurchaseChange() {
                this.selectedPurchase = this.purchases.find(p => p.id == this.selectedPurchaseId) || null;
                this.availableVariants = this.selectedPurchase ? this.selectedPurchase.items : [];
                this.items = [];
            },

            addItem() {
                this.items.push({ variant_id: '', qty: 1, reason: '', maxQty: 0 });
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            onItemVariantChange(item) {
                const v = this.availableVariants.find(v => v.variant_id == item.variant_id);
                item.maxQty = v ? v.stock : 0;
            },

            submitForm() {
                const fields = document.getElementById('return-fields');
                fields.innerHTML = '';

                const add = (name, val) => {
                    const input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = name;
                    input.value = val;
                    fields.appendChild(input);
                };

                add('purchase_id', this.selectedPurchaseId);
                add('date', this.date);
                add('notes', this.notes);

                this.items.forEach((item, i) => {
                    add(`items[${i}][variant_id]`, item.variant_id);
                    add(`items[${i}][qty]`, item.qty);
                    add(`items[${i}][reason]`, item.reason);
                });

                document.getElementById('return-form').submit();
            }
        }
    }
    </script>
</x-app-layout>
