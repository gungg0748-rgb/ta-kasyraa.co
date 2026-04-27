<x-app-layout>
    <x-slot name="header">{{ isset($stockOpname) ? 'Lanjutkan Draft Opname' : 'Mulai Stok Opname' }}</x-slot>

    <div class="mb-8">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Rekonsiliasi Stok</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Input <span class="text-primary italic">Stok Fisik</span>
        </h2>
        <p class="text-slate-400 text-sm mt-2">Masukkan jumlah stok fisik hasil penghitungan di gudang. Tersimpan otomatis setiap perubahan.</p>
    </div>

    @if(session('info'))
    <div class="mb-4 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-center gap-3">
        <span class="material-symbols-outlined text-blue-500 text-sm">info</span>
        <p class="text-sm text-blue-700 font-medium">{{ session('info') }}</p>
    </div>
    @endif

    {{-- Status indicator --}}
    <div id="save-status" class="fixed bottom-4 right-4 z-50 hidden items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold shadow-lg transition-all">
        <span id="save-icon" class="material-symbols-outlined text-sm"></span>
        <span id="save-text"></span>
    </div>

    {{-- Header --}}
    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Tanggal Opname</label>
                <input type="date" id="opname-date"
                       value="{{ isset($stockOpname) ? $stockOpname->date->format('Y-m-d') : date('Y-m-d') }}"
                       class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Catatan <span class="font-normal normal-case tracking-normal">(opsional)</span></label>
                <input type="text" id="opname-notes"
                       value="{{ isset($stockOpname) ? $stockOpname->notes : '' }}"
                       class="w-full bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                <p class="text-[10px] text-slate-400 mt-1">Periode opname, keterangan...</p>
            </div>
        </div>
    </div>

    {{-- Scan Barcode untuk highlight varian --}}
    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-6 mb-6">
        <div class="flex items-center gap-4">
            <span class="material-symbols-outlined text-primary text-3xl">qr_code_scanner</span>
            <div class="flex-1">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">Scan Barcode — Langsung ke Baris Varian</label>
                <div class="flex gap-3 flex-wrap">
                    <input type="text" id="opname-barcode-input"
                           class="flex-1 bg-surface-container-low border-0 rounded-xl text-sm text-blue-900 font-medium focus:ring-2 focus:ring-primary/20">
                    <div id="opname-hp-btn">
                        <button type="button" onclick="openOpnameScannerModal()"
                                class="px-4 py-2.5 bg-blue-900 text-white rounded-xl text-sm font-bold hover:bg-blue-800 transition-colors">
                            Hubungkan HP
                        </button>
                    </div>
                    <div id="opname-hp-connected" class="hidden flex items-center gap-2">
                        <div class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-xs font-bold text-emerald-700">HP Terhubung</span>
                        </div>
                        <button type="button" onclick="disconnectOpnameHP()"
                                class="px-3 py-2.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-bold hover:bg-slate-200 transition-colors">
                            Putus
                        </button>
                    </div>
                    <button type="button" onclick="opnameScanBarcode()"
                            class="px-4 py-2.5 bg-primary text-on-primary rounded-xl text-sm font-bold hover:opacity-90 transition-opacity">
                        Cari
                    </button>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Scan barcode varian atau ketik manual, lalu tekan Enter.</p>
                <p id="opname-scan-error" class="hidden text-rose-500 text-xs mt-1.5 font-medium"></p>
                <p id="opname-scan-success" class="hidden text-emerald-600 text-xs mt-1.5 font-medium"></p>
            </div>
        </div>
    </div>

    {{-- Modal HP Scanner Opname --}}
    <div id="modal-opname-scanner" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <p class="font-manrope font-bold text-blue-900 text-sm">Hubungkan HP sebagai Scanner</p>
                    <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-widest">{{ Auth::user()->name }}</p>
                </div>
                <button type="button" onclick="closeOpnameScannerModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <div class="p-6 text-center">
                <div id="opname-scanner-loading">
                    <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p class="text-sm text-slate-500 mt-3">Membuat sesi scanner...</p>
                </div>
                <div id="opname-scanner-qr" class="hidden">
                    <p class="text-xs text-slate-500 mb-3">Scan QR ini dengan HP, lalu arahkan kamera ke barcode barang</p>
                    <div class="mx-auto w-48 h-48 bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center">
                        <img id="opname-scanner-qr-img" src="" class="w-48 h-48 rounded-xl hidden">
                        <span id="opname-scanner-qr-loading" class="text-slate-300 text-xs">Loading QR...</span>
                    </div>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <div id="opname-scanner-dot" class="w-2 h-2 rounded-full animate-pulse bg-amber-400"></div>
                        <span id="opname-scanner-status" class="text-xs font-bold text-amber-600">Menunggu HP...</span>
                    </div>
                </div>
            </div>
            <div class="px-6 pb-5">
                <button type="button" onclick="closeOpnameScannerModal()"
                        class="w-full py-2.5 bg-surface-container text-slate-600 rounded-xl text-sm font-bold">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Tabel input stok per produk --}}
    @foreach($products as $product)
    <div class="bg-surface-container-lowest rounded-2xl editorial-shadow overflow-hidden mb-4">
        <div class="px-6 py-4 bg-surface-container-low/50 border-b border-slate-100 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-sm">inventory_2</span>
            <div>
                <p class="font-manrope font-bold text-blue-900 text-sm">{{ $product->name }}</p>
                <p class="text-[10px] text-slate-400">{{ $product->category->name }} · {{ $product->unit->name }}</p>
            </div>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low/30">
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Varian</th>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Barcode</th>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center">Stok Sistem</th>
                    <th class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center w-40">Stok Fisik</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/50">
                @foreach($product->variants as $variant)
                @php
                    $savedVal = isset($savedItems) && isset($savedItems[$variant->id])
                        ? $savedItems[$variant->id]->physical_stock
                        : $variant->stock;
                    $hasDiff = $savedVal != $variant->stock;
                @endphp
                <tr class="hover:bg-blue-50/20 transition-colors {{ $hasDiff ? 'bg-amber-50/20' : '' }}"
                    data-barcode="{{ $variant->barcode }}"
                    id="opname-row-{{ $variant->id }}">
                    <td class="px-6 py-3 text-sm text-blue-900 font-medium">
                        {{ collect([$variant->model, $variant->color, $variant->size])->filter()->implode(' · ') ?: 'Default' }}
                    </td>
                    <td class="px-6 py-3 text-xs font-mono text-slate-400">{{ $variant->barcode }}</td>
                    <td class="px-6 py-3 text-center">
                        <span class="font-manrope font-bold text-blue-900">{{ $variant->stock }}</span>
                    </td>
                    <td class="px-6 py-3">
                        <input type="number"
                               data-variant="{{ $variant->id }}"
                               value="{{ $savedVal }}"
                               min="0"
                               class="opname-input w-full bg-surface-container-low border-0 rounded-xl text-sm text-center text-blue-900 font-bold focus:ring-2 focus:ring-primary/20">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="flex items-center gap-4 mt-6">
        <a id="btn-finish" href="{{ isset($stockOpname) ? route('stock-opnames.show', $stockOpname) : route('stock-opnames.index') }}"
           class="flex items-center gap-2 px-6 py-3 bg-primary text-on-primary rounded-xl font-manrope font-bold text-sm shadow-lg hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-base">check_circle</span>
            Selesai
        </a>
        @isset($stockOpname)
        <a href="{{ route('stock-opnames.show', $stockOpname) }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Lihat Draft</a>
        @endisset
        <a href="{{ route('stock-opnames.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest">Batal</a>
    </div>

<script>
(function () {
    const csrfToken = '{{ csrf_token() }}';
    const initUrl   = '{{ route('stock-opnames.init-draft') }}';
    const saveBase  = '{{ url('stock-opnames') }}';

    // Kalau sudah ada draft (mode edit), langsung pakai ID-nya
    let opnameId  = {{ isset($stockOpname) ? $stockOpname->id : 'null' }};
    let saveTimer = null;
    let pendingQueue = [];

    const statusEl = document.getElementById('save-status');
    const iconEl   = document.getElementById('save-icon');
    const textEl   = document.getElementById('save-text');

    function showStatus(state) {
        statusEl.classList.remove('hidden');
        statusEl.classList.add('flex');
        statusEl.classList.remove('bg-amber-50','text-amber-700','border-amber-200','bg-emerald-50','text-emerald-700','border-emerald-200','bg-rose-50','text-rose-700','border-rose-200');
        if (state === 'saving') {
            statusEl.classList.add('bg-amber-50', 'text-amber-700', 'border', 'border-amber-200');
            iconEl.textContent = 'sync';
            textEl.textContent = 'Menyimpan...';
        } else if (state === 'saved') {
            statusEl.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
            iconEl.textContent = 'check_circle';
            textEl.textContent = 'Tersimpan';
            setTimeout(() => statusEl.classList.add('hidden'), 2000);
        } else if (state === 'error') {
            statusEl.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
            iconEl.textContent = 'error';
            textEl.textContent = 'Gagal menyimpan';
        }
    }

    async function initDraft() {
        const date  = document.getElementById('opname-date').value;
        const notes = document.getElementById('opname-notes').value;
        const res = await fetch(initUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ date, notes }) });
        const data = await res.json();
        opnameId = data.id;
        document.getElementById('btn-finish').href = `${saveBase}/${opnameId}`;
        pendingQueue.forEach(variantId => saveItem(variantId));
        pendingQueue = [];
    }

    async function saveItem(variantId) {
        const input = document.querySelector(`[data-variant="${variantId}"]`);
        if (!input || opnameId === null) return;
        showStatus('saving');
        try {
            const res = await fetch(`${saveBase}/${opnameId}/save-item`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ variant_id: variantId, physical_stock: input.value }) });
            if (!res.ok) throw new Error();
            showStatus('saved');
        } catch {
            showStatus('error');
        }
    }

    function onInputChange(e) {
        const variantId = parseInt(e.target.dataset.variant);
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            if (opnameId === null) {
                pendingQueue.push(variantId);
                initDraft();
            } else {
                saveItem(variantId);
            }
        }, 600);
    }

    document.querySelectorAll('.opname-input').forEach(el => {
        el.addEventListener('change', onInputChange);
    });

    document.getElementById('opname-date').addEventListener('change', () => {
        if (opnameId) initDraft();
    });
    document.getElementById('opname-notes').addEventListener('change', () => {
        if (opnameId) initDraft();
    });
})();
</script>

<script>
// ── Opname Barcode Scanner ────────────────────────────────────────────────────

function opnameScanBarcode(code) {
    const raw = code || document.getElementById('opname-barcode-input').value.trim();
    const errEl = document.getElementById('opname-scan-error');
    const okEl  = document.getElementById('opname-scan-success');
    errEl.classList.add('hidden');
    okEl.classList.add('hidden');
    if (!raw || raw.startsWith('http')) return;

    // Cari baris yang punya barcode cocok
    const row = document.querySelector(`tr[data-barcode="${raw}"]`);

    if (!row) {
        errEl.textContent = 'Barcode tidak ditemukan di daftar opname.';
        errEl.classList.remove('hidden');
        return;
    }

    // Highlight dan scroll ke baris
    document.querySelectorAll('tr.scan-highlight').forEach(r => r.classList.remove('scan-highlight', 'ring-2', 'ring-primary'));
    row.classList.add('scan-highlight', 'ring-2', 'ring-primary');
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Increment stok fisik +1 dan trigger auto-save
    const input = row.querySelector('.opname-input');
    if (input) {
        input.value = parseInt(input.value || 0) + 1;
        input.dispatchEvent(new Event('change')); // trigger auto-save
        input.focus();

        const variantName = row.querySelector('td')?.textContent?.trim();
        okEl.textContent = `+1 → ${variantName} (${input.value})`;
        okEl.classList.remove('hidden');
        setTimeout(() => okEl.classList.add('hidden'), 2000);
    }

    document.getElementById('opname-barcode-input').value = '';
}

document.getElementById('opname-barcode-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); opnameScanBarcode(); }
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeOpnameScannerModal();
});
</script>

<script>
// ── Opname HP Scanner ─────────────────────────────────────────────────────────
const OPNAME_BASE = '{{ rtrim(config("app.url"), "/") }}';
const OPNAME_CSRF = '{{ csrf_token() }}';
let _opnameHPToken    = null;
let _opnamePollInterval = null;
let _opnameConnected  = false;

function openOpnameScannerModal() {
    document.getElementById('modal-opname-scanner').classList.remove('hidden');
    document.getElementById('opname-scanner-loading').classList.remove('hidden');
    document.getElementById('opname-scanner-qr').classList.add('hidden');

    // Restore session
    const saved = localStorage.getItem('scanner_connected');
    const token = localStorage.getItem('scanner_token');
    const date  = localStorage.getItem('scanner_date');
    if (saved && token && token !== 'undefined' && date === new Date().toDateString()) {
        fetch(OPNAME_BASE + `/scanner/connected/${token}`, {
            headers: { 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => {
            if (d.connected) {
                _opnameHPToken = token;
                _opnameConnected = true;
                setOpnameConnected(true);
                document.getElementById('modal-opname-scanner').classList.add('hidden');
                startOpnamePolling(token);
                return;
            }
            generateOpnameToken();
        }).catch(() => generateOpnameToken());
    } else {
        generateOpnameToken();
    }
}

function generateOpnameToken() {
    fetch(OPNAME_BASE + '/scanner/token', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': OPNAME_CSRF, 'Accept': 'application/json' } }).then(r => r.json()).then(data => {
        _opnameHPToken = data.token;
        const img = document.getElementById('opname-scanner-qr-img');
        img.src = `https://api.qrserver.com/v1/create-qr-code/?size=192x192&data=${encodeURIComponent(data.url)}`;
        img.classList.remove('hidden');
        document.getElementById('opname-scanner-qr-loading').classList.add('hidden');
        document.getElementById('opname-scanner-loading').classList.add('hidden');
        document.getElementById('opname-scanner-qr').classList.remove('hidden');
        startOpnamePolling(data.token);
    });
}

function closeOpnameScannerModal() {
    document.getElementById('modal-opname-scanner').classList.add('hidden');
}

function setOpnameConnected(val) {
    _opnameConnected = val;
    if (val) {
        document.getElementById('opname-hp-btn').classList.add('hidden');
        document.getElementById('opname-hp-connected').classList.remove('hidden');
        localStorage.setItem('scanner_connected', '1');
        localStorage.setItem('scanner_token', _opnameHPToken);
        localStorage.setItem('scanner_date', new Date().toDateString());
    } else {
        document.getElementById('opname-hp-btn').classList.remove('hidden');
        document.getElementById('opname-hp-connected').classList.add('hidden');
        localStorage.removeItem('scanner_connected');
        localStorage.removeItem('scanner_token');
        localStorage.removeItem('scanner_date');
    }
}

function disconnectOpnameHP() {
    if (_opnameHPToken) {
        fetch(OPNAME_BASE + `/scanner/disconnect/${_opnameHPToken}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': OPNAME_CSRF, 'Accept': 'application/json' } }).catch(() => {});
    }
    clearInterval(_opnamePollInterval);
    setOpnameConnected(false);
    _opnameHPToken = null;
}

function startOpnamePolling(token) {
    clearInterval(_opnamePollInterval);
    const connUrl = OPNAME_BASE + `/scanner/connected/${token}`;
    const pollUrl = OPNAME_BASE + `/scanner/poll/${token}`;

    _opnamePollInterval = setInterval(() => {
        if (!_opnameConnected) {
            fetch(connUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json()).then(d => {
                if (d.connected) {
                    document.getElementById('opname-scanner-dot').className = 'w-2 h-2 rounded-full animate-pulse bg-emerald-500';
                    document.getElementById('opname-scanner-status').className = 'text-xs font-bold text-emerald-600';
                    document.getElementById('opname-scanner-status').textContent = 'HP Terhubung — Siap Scan';
                    document.getElementById('modal-opname-scanner').classList.add('hidden');
                    setOpnameConnected(true);
                } else if (_opnameConnected) {
                    setOpnameConnected(false);
                }
            }).catch(() => {});
            return;
        }
        fetch(pollUrl, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json()).then(d => {
            if (d.barcode) opnameScanBarcode(d.barcode);
        }).catch(() => {});
    }, 1000);
}

// Restore session saat halaman load
(function() {
    const saved = localStorage.getItem('scanner_connected');
    const token = localStorage.getItem('scanner_token');
    const date  = localStorage.getItem('scanner_date');
    if (saved && token && token !== 'undefined' && date === new Date().toDateString()) {
        fetch(OPNAME_BASE + `/scanner/connected/${token}`, {
            headers: { 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => {
            if (d.connected) {
                _opnameHPToken = token;
                setOpnameConnected(true);
                startOpnamePolling(token);
            }
        }).catch(() => {});
    }
})();
</script>

</x-app-layout>
