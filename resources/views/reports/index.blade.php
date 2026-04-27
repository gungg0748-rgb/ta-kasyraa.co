<x-app-layout>
    <x-slot name="header">Laporan</x-slot>

    <div class="mb-10">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-primary mb-2">Laporan & Analitik</p>
        <h2 class="text-3xl font-manrope font-extrabold tracking-tight text-blue-900">
            Pusat <span class="text-primary italic">Laporan</span>
        </h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <a href="{{ route('reports.stock') }}"
           class="bg-surface-container-lowest p-8 rounded-2xl editorial-shadow border border-transparent hover:border-primary/20 transition-all group">
            <div class="w-12 h-12 bg-blue-50 text-primary rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-white transition-colors">
                <span class="material-symbols-outlined">inventory_2</span>
            </div>
            <h3 class="font-manrope font-bold text-blue-900 text-lg mb-1">Laporan Stok</h3>
            <p class="text-sm text-slate-400">Rekap stok per produk dan varian, indikator restock.</p>
            <p class="text-xs font-bold text-primary uppercase tracking-widest mt-4">Lihat Laporan →</p>
        </a>

        @if(in_array(Auth::user()->role, ['admin','gudang']))
        <a href="{{ route('reports.purchases') }}"
           class="bg-surface-container-lowest p-8 rounded-2xl editorial-shadow border border-transparent hover:border-primary/20 transition-all group">
            <div class="w-12 h-12 bg-blue-50 text-primary rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-white transition-colors">
                <span class="material-symbols-outlined">shopping_cart</span>
            </div>
            <h3 class="font-manrope font-bold text-blue-900 text-lg mb-1">Laporan Pembelian</h3>
            <p class="text-sm text-slate-400">Detail transaksi pembelian dengan filter tanggal & supplier.</p>
            <p class="text-xs font-bold text-primary uppercase tracking-widest mt-4">Lihat Laporan →</p>
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['admin','kasir']))
        <a href="{{ route('reports.sales') }}"
           class="bg-surface-container-lowest p-8 rounded-2xl editorial-shadow border border-transparent hover:border-primary/20 transition-all group">
            <div class="w-12 h-12 bg-blue-50 text-primary rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-white transition-colors">
                <span class="material-symbols-outlined">sell</span>
            </div>
            <h3 class="font-manrope font-bold text-blue-900 text-lg mb-1">Laporan Penjualan</h3>
            <p class="text-sm text-slate-400">Detail transaksi penjualan dengan filter tanggal.</p>
            <p class="text-xs font-bold text-primary uppercase tracking-widest mt-4">Lihat Laporan →</p>
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['admin','gudang']))
        <a href="{{ route('reports.returns') }}"
           class="bg-surface-container-lowest p-8 rounded-2xl editorial-shadow border border-transparent hover:border-primary/20 transition-all group">
            <div class="w-12 h-12 bg-blue-50 text-primary rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-white transition-colors">
                <span class="material-symbols-outlined">assignment_return</span>
            </div>
            <h3 class="font-manrope font-bold text-blue-900 text-lg mb-1">Laporan Return</h3>
            <p class="text-sm text-slate-400">Rekap seluruh riwayat return pembelian ke supplier.</p>
            <p class="text-xs font-bold text-primary uppercase tracking-widest mt-4">Lihat Laporan →</p>
        </a>

        <a href="{{ route('reports.opnames') }}"
           class="bg-surface-container-lowest p-8 rounded-2xl editorial-shadow border border-transparent hover:border-primary/20 transition-all group">
            <div class="w-12 h-12 bg-blue-50 text-primary rounded-xl flex items-center justify-center mb-5 group-hover:bg-primary group-hover:text-white transition-colors">
                <span class="material-symbols-outlined">inventory</span>
            </div>
            <h3 class="font-manrope font-bold text-blue-900 text-lg mb-1">Laporan Stok Opname</h3>
            <p class="text-sm text-slate-400">Rekap hasil opname beserta selisih stok per periode.</p>
            <p class="text-xs font-bold text-primary uppercase tracking-widest mt-4">Lihat Laporan →</p>
        </a>
        @endif
    </div>
</x-app-layout>
