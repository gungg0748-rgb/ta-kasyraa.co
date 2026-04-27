<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Scanner · Kasyraa</title>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #fff; min-height: 100dvh; display: flex; flex-direction: column; }

        .header { padding: 20px 20px 12px; border-bottom: 1px solid #1e293b; }
        .header h1 { font-size: 18px; font-weight: 900; letter-spacing: -0.5px; color: #f1f5f9; }
        .header-sub { display: flex; align-items: center; gap: 8px; margin-top: 6px; }
        .kasir-badge {
            display: inline-block;
            padding: 3px 10px;
            background: #1e3a5f;
            border: 1px solid #2563eb44;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: #60a5fa;
            letter-spacing: .05em;
        }
        .scan-count {
            font-size: 11px;
            color: #475569;
            font-weight: 600;
        }

        #reader { flex: 1; width: 100%; }
        #reader video { width: 100% !important; }

        .status { padding: 12px 20px 4px; }
        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700;
            transition: all .2s;
        }
        .badge.idle    { background: #1e293b; color: #94a3b8; }
        .badge.success { background: #064e3b; color: #6ee7b7; }
        .badge.error   { background: #450a0a; color: #fca5a5; }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }

        .toast {
            margin: 8px 20px 0;
            padding: 14px 18px;
            border-radius: 14px;
            background: #1e3a5f;
            border: 1px solid #2563eb44;
            transition: opacity .3s;
        }
        .toast.hidden { opacity: 0; pointer-events: none; }
        .toast-label { font-size: 10px; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; color: #60a5fa; margin-bottom: 4px; }
        .toast-name  { font-size: 15px; font-weight: 700; color: #e2e8f0; }
        .toast-code  { font-size: 11px; font-family: monospace; color: #64748b; margin-top: 2px; }

        .footer { padding: 12px 20px 28px; display: flex; justify-content: space-between; align-items: center; }
        .footer-label { font-size: 11px; color: #334155; }
        .footer-code  { font-size: 12px; font-family: monospace; color: #94a3b8; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kasyraa Scanner</h1>
        <div class="header-sub">
            <span class="kasir-badge">{{ $kasir }}</span>
            <span class="scan-count" id="scan-count">0 scan</span>
        </div>
    </div>

    <div id="reader"></div>

    <div class="status">
        <div class="badge idle" id="badge">
            <div class="dot"></div>
            <span id="badge-text">Menunggu scan...</span>
        </div>
    </div>

    <div class="toast hidden" id="toast">
        <div class="toast-label">Produk ditemukan</div>
        <div class="toast-name" id="toast-name">—</div>
        <div class="toast-code" id="toast-code"></div>
    </div>

    <div class="footer">
        <span class="footer-label">Terakhir discan</span>
        <span class="footer-code" id="last-code">—</span>
    </div>

    {{-- Overlay sesi habis --}}
    <div id="session-ended" style="display:none; position:fixed; inset:0; background:#0f172a; z-index:100; flex-direction:column; align-items:center; justify-content:center; padding:32px; text-align:center;">
        <div style="font-size:48px; margin-bottom:16px;">⛔</div>
        <h2 style="font-size:20px; font-weight:900; color:#f1f5f9; margin-bottom:8px;">Sesi Habis</h2>
        <p style="font-size:14px; color:#94a3b8; line-height:1.6;">Kasir telah memutus koneksi.<br>Silakan scan ulang QR Code dari layar kasir untuk terhubung kembali.</p>
    </div>

    <script>
    const PUSH_URL      = "{{ route('scanner.push', ['token' => $token]) }}";
    const PING_URL      = "{{ route('scanner.ping', ['token' => $token]) }}";
    const CONNECTED_URL = "{{ route('scanner.connected', ['token' => $token]) }}";
    const CSRF          = "{{ csrf_token() }}";

    let lastCode  = '';
    let cooldown  = false;
    let scanCount = 0;
    let toastTimer = null;

    const badge      = document.getElementById('badge');
    const badgeText  = document.getElementById('badge-text');
    const lastCodeEl = document.getElementById('last-code');
    const toast      = document.getElementById('toast');
    const toastName  = document.getElementById('toast-name');
    const toastCode  = document.getElementById('toast-code');
    const scanCountEl = document.getElementById('scan-count');

    function beep(success = true) {
        try {
            const ctx  = new (window.AudioContext || window.webkitAudioContext)();
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = success ? 1200 : 400;
            osc.type = 'sine';
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + (success ? 0.12 : 0.3));
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + (success ? 0.12 : 0.3));
        } catch(e) {}
    }

    function setStatus(type, msg) {
        badge.className = 'badge ' + type;
        badgeText.textContent = msg;
    }

    function showToast(name, code) {
        toastName.textContent = name;
        toastCode.textContent = code;
        toast.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    function onScan(code) {
        if (code.startsWith('http://') || code.startsWith('https://')) return;
        if (cooldown || code === lastCode) return;

        cooldown = true;
        lastCode = code;
        lastCodeEl.textContent = code;
        setStatus('success', 'Mengirim...');

        fetch(PUSH_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ barcode: code }) })
        .then(r => r.json())
        .then(data => {
            if (data.found) {
                beep(true);
                scanCount++;
                scanCountEl.textContent = scanCount + ' scan';
                setStatus('success', 'Terkirim ke kasir');
                showToast(data.productName, code);
            } else {
                beep(false);
                setStatus('error', 'Produk tidak ditemukan');
                toast.classList.add('hidden');
            }
            setTimeout(() => {
                setStatus('idle', 'Siap scan berikutnya...');
                lastCode = '';
                cooldown = false;
            }, 2000);
        })
        .catch(() => {
            beep(false);
            setStatus('error', 'Gagal kirim, coba lagi');
            cooldown = false;
        });
    }

    const scanner = new Html5Qrcode('reader');
    scanner.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 280, height: 120 } },
        onScan,
        () => {}
    ).catch(() => {
        setStatus('error', 'Tidak dapat akses kamera');
    });

    // Ping server saat halaman dibuka — tandai HP terhubung
    fetch(PING_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, },
        body: JSON.stringify({}) }).catch(() => {});

    // Heartbeat tiap 5 detik selama halaman terbuka
    setInterval(() => {
        fetch(PING_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, },
            body: JSON.stringify({}) }).catch(() => {});
    }, 5000);

    // Cek status koneksi dari sisi HP tiap 5 detik
    // Kalau PC putus, tampilkan overlay sesi habis
    let wasConnected = false;
    setInterval(() => {
        fetch(CONNECTED_URL, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (wasConnected && !data.connected) {
                // PC putus koneksi
                document.getElementById('session-ended').style.display = 'flex';
                scanner.stop().catch(() => {});
            }
            if (data.connected) wasConnected = true;
        })
        .catch(() => {});
    }, 5000);
    </script>
</body>
</html>
