<x-app-layout>
    <x-slot name="header">Catat Pembelian</x-slot>

    <div class="mb-8">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Barang Masuk</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Transaksi <span class="text-primary italic">Pembelian Baru</span>
        </h2>
    </div>

    {{-- Data produk & varian untuk Alpine --}}
    @php
        $variantsData = [];
        foreach($products as $product) {
            foreach($product->variants as $variant) {
                $label = $product->name;
                $parts = array_filter([$variant->model, $variant->color, $variant->size]);
                if ($parts) $label .= ' (' . implode(', ', $parts) . ')';
                $variantsData[] = [
                    'id'             => $variant->id,
                    'label'          => $label,
                    'price'          => $variantCosts[$variant->id] ?? 0,
                    'barcode'        => $variant->barcode,
                    'product_barcode'=> $product->barcode,        // barcode produk (fallback)
                ];
            }
        }
    @endphp

    <div x-data="purchaseForm({{ json_encode($variantsData) }})" class="space-y-6">

        {{-- Header form --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8">
            <h3 class="font-manrope font-bold text-blue-900 mb-5 text-sm uppercase tracking-widest">Informasi Pembelian</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Supplier (Supplier)</label>
                    <select x-model="supplierId" name="supplier_id"
                            class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                        <option value="">— Pilih Supplier —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Tanggal (Date)</label>
                    <input type="date" x-model="date" name="date" value="{{ date('Y-m-d') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                    @error('date') <p class="text-rose-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Catatan (Notes) <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                    <input type="text" name="notes"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                    <p class="text-[10px] text-slate-400 mt-1">Catatan tambahan...</p>
                </div>
            </div>
        </div>

        {{-- Scan Barcode --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <div class="flex items-center gap-4">
                <span class="material-symbols-outlined text-primary text-3xl">qr_code_scanner</span>
                <div class="flex-1">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Scan / Input Barcode</label>
                    <div class="flex gap-3">
                        <input type="text" x-model="barcodeInput" id="barcode-input"
                               @keydown.enter.prevent="scanBarcode()"
                               class="flex-1 bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                        <template x-if="!mobileConnected">
                            <button type="button" onclick="openPurchaseScannerModal()"
                                    class="px-4 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-bold hover:bg-blue-800 transition-colors">
                                Hubungkan HP
                            </button>
                        </template>
                        <template x-if="mobileConnected">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-xl">
                                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                    <span class="text-xs font-bold text-emerald-700">HP Terhubung</span>
                                </div>
                                <button type="button" @click="disconnectMobile()"
                                        class="px-3 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                                    Putus
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="scanBarcode()"
                                class="px-4 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:opacity-90 transition-opacity">
                            Tambah
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Scan barcode varian atau ketik manual, lalu tekan Enter.</p>
                    <p x-show="barcodeError" x-text="barcodeError" class="text-rose-500 text-xs mt-1.5 font-medium"></p>
                </div>
            </div>
        </div>

        {{-- Modal HP Scanner Pembelian --}}
        <div id="modal-purchase-scanner" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <p class="font-manrope font-bold text-blue-900 text-sm">Hubungkan HP sebagai Scanner</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-widest">{{ Auth::user()->name }}</p>
                    </div>
                    <button type="button" onclick="closePurchaseScannerModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>
                <div class="p-6 text-center">
                    <div id="purchase-scanner-loading">
                        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p class="text-sm text-slate-500 mt-3">Membuat sesi scanner...</p>
                    </div>
                    <div id="purchase-scanner-qr" class="hidden">
                        <p class="text-xs text-slate-500 mb-3">Scan QR ini dengan HP, lalu arahkan kamera ke barcode barang</p>
                        <div class="mx-auto w-48 h-48 bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center">
                            <img id="purchase-scanner-qr-img" src="" class="w-48 h-48 rounded-xl hidden">
                            <span id="purchase-scanner-qr-loading" class="text-slate-300 text-xs">Loading QR...</span>
                        </div>
                        <div class="mt-4 flex items-center justify-center gap-2">
                            <div id="purchase-scanner-dot" class="w-2 h-2 rounded-full animate-pulse bg-amber-400"></div>
                            <span id="purchase-scanner-status" class="text-xs font-bold text-amber-600">Menunggu HP...</span>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-5">
                    <button type="button" onclick="closePurchaseScannerModal()"
                            class="w-full py-2.5 bg-surface-container text-slate-600 rounded-xl text-sm font-bold hover:bg-surface-container-high transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Item pembelian --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-blue-900 text-sm uppercase tracking-widest">Item Pembelian</h3>
                <button type="button" @click="addItem()"
                        class="flex items-center gap-1 px-4 py-2 bg-primary/10 text-primary rounded-xl text-xs font-bold hover:bg-primary/20 transition-colors">
                    <span class="material-symbols-outlined text-sm">add</span> Tambah Item
                </button>
            </div>

            <div class="divide-y divide-slate-100/50">
                <template x-for="(item, index) in items" :key="index">
                    <div class="px-8 py-5 flex gap-4 items-end flex-wrap">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Varian Produk</label>
                            <template x-if="item.fromScan">
                                <div class="w-full bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-2.5">
                                    <p class="text-sm font-bold text-blue-900" x-text="variants.find(v => String(v.id) === String(item.variant_id))?.label || '—'"></p>
                                    <p class="text-[10px] text-emerald-600 font-bold mt-0.5">Dari scan barcode</p>
                                    <input type="hidden" :name="'items[' + index + '][variant_id]'" :value="item.variant_id">
                                </div>
                            </template>
                            <template x-if="!item.fromScan">
                                <select :name="'items[' + index + '][variant_id]'"
                                        x-model="item.variant_id"
                                        @change="setPrice(item)"
                                        class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                                    <option value="">— Pilih Varian —</option>
                                    <template x-for="v in variants" :key="v.id">
                                        <option :value="v.id" x-text="v.label"></option>
                                    </template>
                                </select>
                            </template>
                        </div>
                        <div class="w-28">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Qty</label>
                            <input type="number" :name="'items[' + index + '][qty]'"
                                   x-model.number="item.qty" min="1"
                                   @input="calcTotal()"
                                   class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div class="w-40">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Harga Beli (Rp)</label>
                            <input type="number" :name="'items[' + index + '][price]'"
                                   x-model.number="item.price" min="0" step="100"
                                   @input="calcTotal()"
                                   class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                        </div>
                        <div class="w-36">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Subtotal</label>
                            <p class="font-manrope font-bold text-blue-900 text-sm py-2.5" x-text="'Rp ' + formatNum(item.qty * item.price)"></p>
                        </div>
                        <button type="button" @click="removeItem(index)"
                                class="mb-0.5 text-rose-400 hover:text-rose-600 transition-colors" title="Hapus item">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </template>

                <div x-show="items.length === 0" class="px-8 py-10 text-center text-slate-400 text-sm">
                    Belum ada item. Klik "Tambah Item" untuk mulai.
                </div>
            </div>

            {{-- Total --}}
            <div class="px-8 py-5 bg-surface-container-low/30 border-t border-slate-100 flex justify-between items-center">
                <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Total Pembelian</span>
                <span class="text-2xl font-manrope font-black text-blue-900" x-text="'Rp ' + formatNum(total)"></span>
            </div>
        </div>

        {{-- Submit --}}
        <form method="POST" action="{{ route('purchases.store') }}" id="purchase-form">
            @csrf
            <div id="form-fields"></div>
            <div class="flex items-center gap-4">
                <button type="button" @click="submitForm()"
                        :disabled="items.length === 0 || !supplierId"
                        class="flex items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-base">save</span>
                    Simpan Pembelian
                </button>
                <a href="{{ route('purchases.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Batal</a>
            </div>
        </form>
    </div>

    @if($errors->any())
    <div class="mt-4 p-4 bg-rose-50 border border-rose-100 rounded-xl">
        <ul class="text-rose-600 text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <script>
    function purchaseForm(variants) {
        return {
            variants,
            supplierId: '',
            date: '{{ date('Y-m-d') }}',
            items: [],
            total: 0,
            barcodeInput: '',
            barcodeError: '',
            mobileConnected: false,

            addItem(variantId = '', price = 0, fromScan = false) {
                this.items.push({ variant_id: variantId, qty: 1, price: Number(price) || 0, fromScan });
                this.calcTotal();
            },

            removeItem(index) {
                this.items.splice(index, 1);
                this.calcTotal();
            },

            setPrice(item) {
                const v = this.variants.find(v => v.id == item.variant_id);
                if (v) item.price = v.price;
                this.calcTotal();
            },

            scanBarcode(code = null) {
                this.barcodeError = '';
                const raw = code ?? this.barcodeInput.trim();
                if (!raw || raw.startsWith('http')) return;

                // Cocokkan barcode varian dulu, lalu fallback ke barcode produk
                let v = this.variants.find(v => v.barcode === raw);
                if (!v) {
                    v = this.variants.find(v => v.product_barcode && v.product_barcode === raw) || null;
                }

                if (!v) { this.barcodeError = 'Barcode tidak ditemukan.'; return; }

                const existing = this.items.find(i => i.variant_id == v.id);
                if (existing) { existing.qty++; this.calcTotal(); }
                else { this.addItem(v.id, v.price ?? 0, true); }
                this.barcodeInput = '';
            },

            disconnectMobile() {
                if (window._purchaseToken) {
                    fetch(PURCHASE_BASE + `/scanner/disconnect/${window._purchaseToken}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': PURCHASE_CSRF, 'Accept': 'application/json' } }).catch(() => {});
                }
                clearInterval(window._purchasePollInterval);
                this.mobileConnected = false;
                localStorage.removeItem('scanner_connected');
                localStorage.removeItem('scanner_token');
            },

            init() {
                window._purchaseAlpineScan      = (code) => this.scanBarcode(code);
                window._purchaseSetConnected     = (val)  => { this.mobileConnected = val; };
                window._purchaseIsConnected      = ()     => this.mobileConnected;

                // Restore session
                const saved = localStorage.getItem('scanner_connected');
                const token = localStorage.getItem('scanner_token');
                const date  = localStorage.getItem('scanner_date');
                if (saved && token && token !== 'undefined' && date === new Date().toDateString()) {
                    fetch(PURCHASE_BASE + `/scanner/connected/${token}`, {
                        headers: { 'Accept': 'application/json' }
                    }).then(r => r.json()).then(d => {
                        if (d.connected) {
                            this.mobileConnected = true;
                            window._purchaseToken = token;
                            startPurchasePolling(token);
                        } else {
                            localStorage.removeItem('scanner_connected');
                            localStorage.removeItem('scanner_token');
                            localStorage.removeItem('scanner_date');
                        }
                    }).catch(() => {});
                }
            },

            calcTotal() {
                this.total = this.items.reduce((sum, i) => sum + (i.qty * i.price), 0);
            },

            formatNum(n) {
                return Number(n).toLocaleString('id-ID');
            },

            submitForm() {
                const form   = document.getElementById('purchase-form');
                const fields = document.getElementById('form-fields');
                fields.innerHTML = '';
                const add = (name, val) => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = name; input.value = val;
                    fields.appendChild(input);
                };
                add('supplier_id', this.supplierId);
                add('date', document.querySelector('[name=date]').value);
                add('notes', document.querySelector('[name=notes]').value);
                this.items.forEach((item, i) => {
                    add(`items[${i}][variant_id]`, item.variant_id);
                    add(`items[${i}][qty]`, item.qty);
                    add(`items[${i}][price]`, item.price);
                });
                form.submit();
            }
        }
    }
    </script>

<script>
const PURCHASE_BASE = '{{ rtrim(config("app.url"), "/") }}';
const PURCHASE_CSRF = '{{ csrf_token() }}';

function openPurchaseScannerModal() {
    document.getElementById('modal-purchase-scanner').classList.remove('hidden');
    document.getElementById('purchase-scanner-loading').classList.remove('hidden');
    document.getElementById('purchase-scanner-qr').classList.add('hidden');

    fetch(PURCHASE_BASE + '/scanner/token', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': PURCHASE_CSRF, 'Accept': 'application/json' } }).then(r => r.json()).then(data => {
        window._purchaseToken = data.token;
        const img = document.getElementById('purchase-scanner-qr-img');
        img.src = `https://api.qrserver.com/v1/create-qr-code/?size=192x192&data=${encodeURIComponent(data.url)}`;
        img.classList.remove('hidden');
        document.getElementById('purchase-scanner-qr-loading').classList.add('hidden');
        document.getElementById('purchase-scanner-loading').classList.add('hidden');
        document.getElementById('purchase-scanner-qr').classList.remove('hidden');
        startPurchasePolling(data.token);
    });
}

function closePurchaseScannerModal() {
    document.getElementById('modal-purchase-scanner').classList.add('hidden');
}

function startPurchasePolling(token) {
    clearInterval(window._purchasePollInterval);
    const connUrl = PURCHASE_BASE + `/scanner/connected/${token}`;
    const pollUrl = PURCHASE_BASE + `/scanner/poll/${token}`;

    window._purchasePollInterval = setInterval(() => {
        if (!window._purchaseIsConnected || !window._purchaseIsConnected()) {
            fetch(connUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json()).then(d => {
                if (d.connected) {
                    document.getElementById('purchase-scanner-dot').className = 'w-2 h-2 rounded-full animate-pulse bg-emerald-500';
                    document.getElementById('purchase-scanner-status').className = 'text-xs font-bold text-emerald-600';
                    document.getElementById('purchase-scanner-status').textContent = 'HP Terhubung — Siap Scan';
                    document.getElementById('modal-purchase-scanner').classList.add('hidden');
                    window._purchaseSetConnected && window._purchaseSetConnected(true);
                    localStorage.setItem('scanner_connected', '1');
                    localStorage.setItem('scanner_token', token);
                    localStorage.setItem('scanner_date', new Date().toDateString());
                } else if (window._purchaseIsConnected && window._purchaseIsConnected()) {
                    window._purchaseSetConnected && window._purchaseSetConnected(false);
                    localStorage.removeItem('scanner_connected');
                }
            }).catch(() => {});
            return;
        }
        fetch(pollUrl, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json()).then(d => {
            if (d.barcode) window._purchaseAlpineScan && window._purchaseAlpineScan(d.barcode);
        }).catch(() => {});
    }, 1000);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closePurchaseScannerModal(); }
});
</script>

</x-app-layout>
