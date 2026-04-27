<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SiStok Kasyraa') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen bg-surface-container-low flex items-center justify-center p-4"
      style="background-image: radial-gradient(circle at 20% 50%, rgba(0,35,111,0.06) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(0,35,111,0.04) 0%, transparent 40%);">

    <div class="w-full max-w-md">

        {{-- Brand --}}
        <div class="text-center mb-8">
            <h1 class="font-manrope font-black text-3xl tracking-tighter text-blue-900">{{ config('app.name') }}</h1>
            <p class="text-xs tracking-[0.25em] uppercase text-slate-400 mt-1">Manajemen Stok · Kasyraa</p>
        </div>

        {{-- Card --}}
        <div class="bg-surface-container-lowest rounded-2xl editorial-shadow p-8">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        <p class="text-center text-[10px] text-slate-300 font-medium tracking-widest uppercase mt-8">
            © {{ date('Y') }} Kasyraa.co — All rights reserved
        </p>
    </div>

</body>
</html>
