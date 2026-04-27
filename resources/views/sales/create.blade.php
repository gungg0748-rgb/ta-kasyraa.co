<x-app-layout>
    <x-slot name="header">Catat Penjualan</x-slot>

    <div class="mb-8">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Barang Keluar</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Transaksi <span class="text-primary italic">Penjualan Baru</span>
        </h2>
    </div>

    @php
        $variantsData = [];
        foreach($products as $product) {
            foreach($product->variants as $variant) {
                $parts = array_filter([$variant->model, $variant->color, $variant->size]);
                $label = $product->name . ($parts ? ' (' . implode(', ', $parts) . ')' : '');
                $variantsData[] = [
                    'id'             => $variant->id,
                    'label'          => $label,
                    'price'          => $product->price,
                    'stock'          => $variant->stock,
                    'barcode'        => $variant->barcode,        // barcode varian
                ];
            }
        }
    @endphp

    <div x-data="saleForm({{ json_encode($variantsData) }})" class="space-y-6">

        {{-- Header --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8">
            <h3 class="font-manrope font-bold text-blue-900 mb-5 text-sm uppercase tracking-widest">Informasi Penjualan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Tanggal</label>
                    <input type="date" x-model="date" name="date" value="{{ date('Y-m-d') }}"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Catatan <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                    <input type="text" name="notes"
                           class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                    <p class="text-[10px] text-slate-400 mt-1">Catatan transaksi...</p>
                </div>
            </div>
        </div>

        {{-- Scan barcode --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6">
            <div class="flex items-center gap-4">
                <span class="material-symbols-outlined text-primary text-3xl">qr_code_scanner</span>
                <div class="flex-1">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Scan / Input Barcode</label>
                    <div class="flex gap-3">
                        <input type="text" x-model="barcodeInput" id="barcode-input"
                               @keydown.enter.prevent="scanBarcode()"
                               class="flex-1 bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                        {{-- Tombol hubungkan HP --}}
                        <template x-if="!mobileConnected">
                            <button type="button" onclick="openScannerModal()"
                                    class="relative flex items-center gap-1.5 px-4 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-bold hover:bg-blue-800 transition-colors">
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

        {{-- Modal HP Scanner (Vanilla JS) --}}
        <div id="modal-scanner" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <p class="font-manrope font-bold text-blue-900 text-sm">Hubungkan HP sebagai Scanner</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-widest">Kasir: {{ Auth::user()->name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div id="scanner-active-badge" class="hidden flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-100 rounded-lg">
                            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span id="scanner-active-text" class="text-[10px] font-bold text-emerald-700"></span>
                        </div>
                        <button type="button" onclick="closeScannerModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
                    </div>
                </div>
                <div class="p-6 text-center">
                    <div id="scanner-loading">
                        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p class="text-sm text-slate-500 mt-3">Membuat sesi scanner...</p>
                    </div>
                    <div id="scanner-qr-section" class="hidden">
                        <p class="text-xs text-slate-500 mb-3">Scan QR ini dengan HP, lalu arahkan kamera ke barcode barang</p>
                        <div class="mx-auto w-48 h-48 bg-slate-100 rounded-xl flex items-center justify-center overflow-hidden">
                            <img id="scanner-qr-img" src="" alt="QR Scanner" class="w-48 h-48 rounded-xl hidden">
                            <span id="scanner-qr-loading" class="text-slate-300 text-xs">Loading QR...</span>
                        </div>
                        <div class="mt-4 flex items-center justify-center gap-2">
                            <div id="scanner-status-dot" class="w-2 h-2 rounded-full animate-pulse bg-amber-400"></div>
                            <span id="scanner-status-text" class="text-xs font-bold text-amber-600">Menunggu HP...</span>
                        </div>
                        <div id="scanner-last-scan" class="hidden mt-3 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-left">
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1">Scan Terakhir</p>
                            <p id="scanner-last-name" class="text-sm font-bold text-blue-900"></p>
                            <p id="scanner-last-code" class="text-[10px] font-mono text-slate-400 mt-0.5"></p>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-5">
                    <button type="button" onclick="closeScannerModal()"
                            class="w-full py-2.5 bg-surface-container text-slate-600 rounded-xl text-sm font-bold hover:bg-surface-container-high transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Item penjualan --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-manrope font-bold text-blue-900 text-sm uppercase tracking-widest">Item Penjualan</h3>
                <button type="button" @click="addItem()"
                        class="flex items-center gap-1 px-4 py-2 bg-primary/10 text-primary rounded-xl text-xs font-bold hover:bg-primary/20 transition-colors">
                    <span class="material-symbols-outlined text-sm">add</span> Tambah Manual
                </button>
            </div>

            <div class="divide-y divide-slate-100/50">
                <template x-for="(item, index) in items" :key="index">
                    <div class="px-8 py-5 flex gap-4 items-end flex-wrap">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Varian Produk</label>
                            {{-- Dari scan: tampil nama, tidak perlu pilih --}}
                            <template x-if="item.fromScan">
                                <div class="w-full bg-emerald-50 border border-emerald-100 rounded-xl px-4 py-2.5">
                                    <p class="text-sm font-bold text-blue-900" x-text="variants.find(v => String(v.id) === String(item.variant_id))?.label || '—'"></p>
                                    <p class="text-[10px] text-emerald-600 font-bold mt-0.5">Dari scan barcode</p>
                                    <input type="hidden" :name="'items[' + index + '][variant_id]'" :value="item.variant_id">
                                </div>
                            </template>
                            {{-- Manual: tampil dropdown --}}
                            <template x-if="!item.fromScan">
                                <select :name="'items[' + index + '][variant_id]'"
                                        x-model="item.variant_id"
                                        @change="onVariantChange(item)"
                                        class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                                    <option value="">— Pilih Varian —</option>
                                    <template x-for="v in variants" :key="v.id">
                                        <option :value="v.id" x-text="v.label + ' (Stok: ' + v.stock + ')'"></option>
                                    </template>
                                </select>
                            </template>
                        </div>
                        <div class="w-28">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Qty</label>
                            <input type="number" :name="'items[' + index + '][qty]'"
                                   x-model.number="item.qty" min="1" :max="item.stock"
                                   @input="calcTotal()"
                                   class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20" required>
                            <p x-show="item.stock > 0" class="text-[10px] text-slate-400 mt-1" x-text="'Stok: ' + item.stock"></p>
                        </div>
                        <div class="w-40">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Harga Jual (Rp)</label>
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
                                class="mb-0.5 text-rose-400 hover:text-rose-600 transition-colors">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </template>
                <div x-show="items.length === 0" class="px-8 py-10 text-center text-slate-400 text-sm">
                    Scan barcode atau klik "Tambah Manual" untuk menambah item.
                </div>
            </div>

            <div class="px-8 py-5 bg-surface-container-low/30 border-t border-slate-100 flex justify-between items-center">
                <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Total Penjualan</span>
                <span class="text-2xl font-manrope font-black text-blue-900" x-text="'Rp ' + formatNum(total)"></span>
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
        <form method="POST" action="{{ route('sales.store') }}" id="sale-form">
            @csrf
            <div id="sale-fields"></div>
            <div class="flex items-center gap-4">
                <button type="button" @click="submitForm()"
                        :disabled="items.length === 0"
                        class="flex items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-base">save</span>
                    Simpan Penjualan
                </button>
                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Batal</a>
            </div>
        </form>
    </div>

    <script>
    function saleForm(variants) {
        return {
            variants,
            date: '{{ date('Y-m-d') }}',
            items: [],
            total: 0,
            barcodeInput: '',
            barcodeError: '',

            // Mobile scanner state (diupdate dari vanilla JS)
            mobileConnected: false,
            mobileLastScan: '',
            mobileLastProductName: '',

            addItem(variantId = '', price = 0, stock = 0, fromScan = false) {
                this.items.push({ variant_id: variantId, qty: 1, price, stock, fromScan });
                this.calcTotal();
            },

            removeItem(index) {
                this.items.splice(index, 1);
                this.calcTotal();
            },

            onVariantChange(item) {
                const v = this.variants.find(v => v.id == item.variant_id);
                if (v) { item.price = v.price; item.stock = v.stock; }
                this.calcTotal();
            },

            scanBarcode(code = null) {
                this.barcodeError = '';
                const raw = code ?? this.barcodeInput.trim();
                if (!raw) return;
                if (raw.startsWith('http://') || raw.startsWith('https://')) return;

                // Hanya terima barcode varian (exact match)
                let v = this.variants.find(v => v.barcode === raw);

                if (!v) { this.barcodeError = 'Barcode varian tidak ditemukan.'; return; }
                if (v.stock <= 0) { this.barcodeError = 'Stok varian ini habis.'; return; }

                const existing = this.items.find(i => i.variant_id == v.id);
                if (existing) {
                    existing.qty++;
                } else {
                    this.addItem(v.id, v.price, v.stock, true);
                }
                this.calcTotal();
                this.barcodeInput = '';
            },

            // ── Mobile HP Scanner ──────────────────────────────────────
            openMobileScanner() { openScannerModal(); },

            init() {
                // Expose ke vanilla JS
                window._alpineScanBarcode  = (code) => this.scanBarcode(code);
                window._alpineSetConnected = (val)  => {
                    this.mobileConnected = val;
                    if (val) {
                        localStorage.setItem('scanner_connected', '1');
                        localStorage.setItem('scanner_token', _currentToken || '');
                        localStorage.setItem('scanner_date', new Date().toDateString());
                    } else {
                        localStorage.removeItem('scanner_connected');
                        localStorage.removeItem('scanner_token');
                    }
                };
                window._alpineIsConnected  = ()     => this.mobileConnected;
                window._alpineSetLastScan  = (code, name) => {
                    this.mobileLastScan = code;
                    this.mobileLastProductName = name || code;
                };

                // Restore state dari localStorage kalau masih hari yang sama
                const savedDate  = localStorage.getItem('scanner_date');
                const savedConn  = localStorage.getItem('scanner_connected');
                const savedToken = localStorage.getItem('scanner_token');
                if (savedConn && savedDate === new Date().toDateString() && savedToken && savedToken !== 'undefined') {
                    fetch(BASE_URL + `/scanner/connected/${savedToken}`, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.connected) {
                            this.mobileConnected = true;
                            _currentToken = savedToken;
                            startScannerPolling(savedToken);
                        } else {
                            localStorage.removeItem('scanner_connected');
                            localStorage.removeItem('scanner_token');
                            localStorage.removeItem('scanner_date');
                        }
                    }).catch(() => {});
                } else if (savedToken === 'undefined') {
                    // Bersihkan localStorage yang corrupt
                    localStorage.removeItem('scanner_connected');
                    localStorage.removeItem('scanner_token');
                    localStorage.removeItem('scanner_date');
                }
            },

            closeMobileScanner() {
                closeScannerModal();
            },

            disconnectMobile() {
                if (_currentToken) {
                    fetch(BASE_URL + `/scanner/disconnect/${_currentToken}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }).catch(() => {});
                }
                if (typeof _pollInterval !== 'undefined') clearInterval(_pollInterval);
                this.mobileConnected = false;
                this.mobileLastScan = '';
                this.mobileLastProductName = '';
                localStorage.removeItem('scanner_connected');
                localStorage.removeItem('scanner_token');
                localStorage.removeItem('scanner_date');
            },
            // ──────────────────────────────────────────────────────────

            calcTotal() {
                this.total = this.items.reduce((sum, i) => sum + (i.qty * i.price), 0);
            },

            formatNum(n) {
                return Number(n).toLocaleString('id-ID');
            },

            submitForm() {
                const form   = document.getElementById('sale-form');
                const fields = document.getElementById('sale-fields');
                fields.innerHTML = '';
                const add = (name, val) => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = name; input.value = val;
                    fields.appendChild(input);
                };
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
// ── Vanilla JS Modal Controllers ──────────────────────────────────────────────
const BASE_URL = '{{ rtrim(config("app.url"), "/") }}';
const CSRF     = '{{ csrf_token() }}';
let _pollInterval  = null;
let _currentToken  = null;

// ── Scanner Modal ─────────────────────────────────────────────────────────────
function openScannerModal() {
    const modal = document.getElementById('modal-scanner');
    modal.classList.remove('hidden');
    document.getElementById('scanner-loading').classList.remove('hidden');
    document.getElementById('scanner-qr-section').classList.add('hidden');

    // Fetch active devices
    fetch(BASE_URL + '/scanner/devices', {
        headers: { 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => {
        if (d.devices.length > 0) {
            document.getElementById('scanner-active-badge').classList.remove('hidden');
            document.getElementById('scanner-active-text').textContent = d.devices.length + ' scanner aktif';
        }
    }).catch(() => {});

    // Generate token
    fetch(BASE_URL + '/scanner/token', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        _currentToken = data.token;
        const encoded = encodeURIComponent(data.url);
        const qrImg   = document.getElementById('scanner-qr-img');
        qrImg.src     = `https://api.qrserver.com/v1/create-qr-code/?size=192x192&data=${encoded}`;
        qrImg.classList.remove('hidden');
        document.getElementById('scanner-qr-loading').classList.add('hidden');
        document.getElementById('scanner-loading').classList.add('hidden');
        document.getElementById('scanner-qr-section').classList.remove('hidden');
        startScannerPolling(data.token);
    });
}

function closeScannerModal() {
    document.getElementById('modal-scanner').classList.add('hidden');
    // Polling tetap jalan di background
}

function startScannerPolling(token) {
    if (_pollInterval) clearInterval(_pollInterval);

    const connectedUrl = BASE_URL + `/scanner/connected/${token}`;
    const pollUrl      = BASE_URL + `/scanner/poll/${token}`;

    _pollInterval = setInterval(() => {
        const isConnected = window._alpineIsConnected && window._alpineIsConnected();

        if (!isConnected) {
            fetch(connectedUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.connected) {
                    // Update UI modal
                    const dot  = document.getElementById('scanner-status-dot');
                    const text = document.getElementById('scanner-status-text');
                    dot.className  = 'w-2 h-2 rounded-full animate-pulse bg-emerald-500';
                    text.className = 'text-xs font-bold text-emerald-600';
                    text.textContent = 'HP Terhubung — Siap Scan';
                    // Tutup modal & update Alpine state
                    document.getElementById('modal-scanner').classList.add('hidden');
                    window._alpineSetConnected && window._alpineSetConnected(true);
                } else if (window._alpineIsConnected && window._alpineIsConnected()) {
                    // HP sebelumnya terhubung tapi sekarang tidak — disconnect
                    window._alpineSetConnected && window._alpineSetConnected(false);
                }
            }).catch(() => {});
            return;
        }

        // Poll barcode
        fetch(pollUrl, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.expired) { clearInterval(_pollInterval); return; }
            if (data.barcode) {
                // Update last scan di modal
                const lastDiv  = document.getElementById('scanner-last-scan');
                const lastName = document.getElementById('scanner-last-name');
                const lastCode = document.getElementById('scanner-last-code');
                lastDiv.classList.remove('hidden');
                lastName.textContent = data.productName || data.barcode;
                lastCode.textContent = data.barcode;
                // Kirim ke Alpine
                window._alpineScanBarcode && window._alpineScanBarcode(data.barcode);
                window._alpineSetLastScan && window._alpineSetLastScan(data.barcode, data.productName);
            }
        }).catch(() => {});
    }, 1000);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeScannerModal();
    }
});
</script>

</x-app-layout>
