<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses Ditolak · Kasyraa</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 4px 40px rgba(30,58,138,.08);
            padding: 56px 48px;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .code-badge {
            display: inline-block;
            background: #fef2f2;
            color: #dc2626;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .2em;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid #fecaca;
            margin-bottom: 24px;
        }
        .number {
            font-size: 96px;
            font-weight: 900;
            color: #1e3a8a;
            line-height: 1;
            letter-spacing: -4px;
            margin-bottom: 8px;
        }
        .number span { color: #3b82f6; }
        h1 {
            font-size: 22px;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 12px;
            letter-spacing: -.5px;
        }
        p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .message {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 13px;
            color: #475569;
            font-style: italic;
            margin-bottom: 32px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: #1e3a8a;
            color: #fff;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: opacity .2s;
        }
        .btn:hover { opacity: .85; }
        .brand {
            margin-top: 40px;
            font-size: 12px;
            color: #cbd5e1;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="code-badge">Error 403</div>
        <div class="number">4<span>0</span>3</div>
        <h1>Akses Ditolak</h1>
        <p>Kamu tidak punya izin untuk mengakses halaman ini. Hubungi admin jika kamu merasa ini adalah kesalahan.</p>

        @if(!empty($exception->getMessage()))
        <div class="message">"{{ $exception->getMessage() }}"</div>
        @endif

        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}" class="btn">
            &larr; Kembali
        </a>

        <div class="brand">Kasyraa · Manajemen Stok</div>
    </div>
</body>
</html>
