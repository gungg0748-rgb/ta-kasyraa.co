<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SiStok Kasyraa') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface antialiased">

<div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-50 border-r border-slate-200/50 flex flex-col
                  transform transition-transform duration-200 ease-in-out
                  lg:relative lg:translate-x-0 lg:flex">

        {{-- Brand --}}
        <div class="p-8">
            <h1 class="font-manrope font-black text-2xl tracking-tighter text-blue-900">{{ config('app.name') }}</h1>
            <p class="font-sans text-xs tracking-wider uppercase text-slate-500 mt-1">Manajemen Stok</p>
        </div>

        {{-- Nav --}}
        <nav class="flex-grow px-4 space-y-1 overflow-y-auto">
            @include('layouts.sidebar')
        </nav>

        {{-- Bottom --}}
        <div class="p-6 mt-auto border-t border-slate-100/50">
            <div class="space-y-1">
                @if(Auth::user()->isAdmin())
                <p class="px-4 pt-1 pb-1 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Data Master</p>
                <a href="{{ route('categories.index') }}"
                   class="flex items-center gap-3 px-4 py-2 text-slate-500 text-xs font-semibold hover:text-blue-800 transition-colors {{ request()->routeIs('categories.*') ? 'text-blue-800' : '' }}">
                    <span class="material-symbols-outlined text-sm">category</span>Kategori
                </a>
                <a href="{{ route('units.index') }}"
                   class="flex items-center gap-3 px-4 py-2 text-slate-500 text-xs font-semibold hover:text-blue-800 transition-colors {{ request()->routeIs('units.*') ? 'text-blue-800' : '' }}">
                    <span class="material-symbols-outlined text-sm">straighten</span>Satuan
                </a>
                <a href="{{ route('suppliers.index') }}"
                   class="flex items-center gap-3 px-4 py-2 text-slate-500 text-xs font-semibold hover:text-blue-800 transition-colors {{ request()->routeIs('suppliers.*') ? 'text-blue-800' : '' }}">
                    <span class="material-symbols-outlined text-sm">storefront</span>Supplier
                </a>
                <div class="border-t border-slate-100 my-2"></div>
                <a href="{{ route('users.index') }}"
                   class="flex items-center gap-3 px-4 py-2 text-slate-500 text-xs font-semibold hover:text-blue-800 transition-colors {{ request()->routeIs('users.*') ? 'text-blue-800' : '' }}">
                    <span class="material-symbols-outlined text-sm">manage_accounts</span>Akun
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 px-4 py-2 text-slate-500 text-xs font-semibold hover:text-red-600 transition-colors w-full text-left">
                        <span class="material-symbols-outlined text-sm">logout</span>
                        Logout
                    </button>
                </form>
            </div>
            <div class="mt-4 px-4 py-3 bg-white rounded-xl border border-slate-100">
                <p class="text-sm font-bold text-blue-900 leading-none">{{ Auth::user()->name }}</p>
                <p class="text-[10px] uppercase tracking-widest text-slate-400 mt-1">{{ ucfirst(Auth::user()->role) }}</p>
            </div>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    {{-- Main --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- Top bar --}}
        <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm shadow-slate-200/50 flex items-center justify-between px-4 lg:px-8">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden text-slate-500 hover:text-blue-900 transition-colors">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                @isset($header)
                    <h2 class="font-manrope font-extrabold text-blue-900 tracking-tight text-lg">{{ $header }}</h2>
                @endisset
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-blue-900 leading-none">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] uppercase tracking-widest text-slate-400 mt-0.5">{{ ucfirst(Auth::user()->role) }} · Kasyraa</p>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-6 lg:p-10">
            @if(session('success'))
                <div class="mb-6 flex items-center gap-3 px-5 py-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-xl text-sm font-medium">
                    <span class="material-symbols-outlined text-emerald-500 text-base">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 flex items-center gap-3 px-5 py-4 bg-red-50 border border-red-100 text-red-800 rounded-xl text-sm font-medium">
                    <span class="material-symbols-outlined text-red-500 text-base">error</span>
                    {{ session('error') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
