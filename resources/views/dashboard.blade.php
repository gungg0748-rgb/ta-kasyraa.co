<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    {{-- Editorial Header --}}
    <div class="mb-10 flex justify-between items-end">
        <div>
            <p class="font-sans text-xs font-bold tracking-[0.2em] uppercase text-primary mb-3">
                Overview — {{ now()->translatedFormat('d M Y') }}
            </p>
            <h2 class="text-4xl font-manrope font-extrabold tracking-tight text-on-surface">
                Ringkasan <span class="text-primary italic">Hari Ini</span>
            </h2>
        </div>
    </div>

    {{-- Bento Stats --}}
    <div class="grid grid-cols-12 gap-6 mb-10">
        {{-- Hero: Penjualan hari ini --}}
        <div class="col-span-12 md:col-span-5 bg-gradient-to-br from-primary to-primary-container p-8 rounded-2xl editorial-shadow relative overflow-hidden flex flex-col justify-between h-56">
            <div class="relative z-10">
                <p class="text-on-primary-container font-sans text-xs font-bold tracking-widest uppercase mb-1">Penjualan Hari Ini</p>
                <h3 class="text-3xl font-manrope font-black text-white">
                    Rp {{ number_format($salesToday, 0, ',', '.') }}
                </h3>
                @if($purchasesToday > 0)
                <div class="mt-3 flex items-center gap-2 bg-white/10 w-fit px-3 py-1 rounded-full border border-white/10">
                    <span class="material-symbols-outlined text-white" style="font-size:14px">shopping_cart</span>
                    <span class="text-white text-xs font-medium">Pembelian: Rp {{ number_format($purchasesToday, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
            <div class="relative z-10">
                <span class="text-white/40 text-[10px] tracking-[0.3em] uppercase">{{ Auth::user()->name }} · {{ ucfirst(Auth::user()->role) }}</span>
            </div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-white/10 via-transparent to-transparent"></div>
        </div>

        {{-- Total Produk --}}
        <div class="col-span-12 md:col-span-4 bg-surface-container-lowest p-8 rounded-2xl flex flex-col justify-between border border-transparent hover:border-primary/10 transition-colors editorial-shadow">
            <div>
                <div class="w-10 h-10 bg-blue-50 text-primary rounded-xl flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
                <p class="text-slate-400 font-sans text-xs font-bold tracking-widest uppercase mb-1">Total Produk</p>
                <h3 class="text-3xl font-manrope font-bold text-blue-900">{{ number_format($totalSkus) }}</h3>
                <p class="text-[10px] text-slate-400 mt-1">{{ \App\Models\Product::count() }} jenis produk</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-[10px] text-primary font-bold uppercase tracking-widest hover:underline">Lihat Katalog →</a>
        </div>

        {{-- Perlu Restock ringkas --}}
        <div class="col-span-12 md:col-span-3 bg-surface-container-lowest p-8 rounded-2xl flex flex-col justify-between border border-transparent editorial-shadow {{ $criticalCount > 0 ? 'border-rose-100' : '' }}">
            <div>
                <div class="w-10 h-10 {{ $criticalCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} rounded-xl flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">{{ $criticalCount > 0 ? 'warning' : 'check_circle' }}</span>
                </div>
                <p class="{{ $criticalCount > 0 ? 'text-rose-600' : 'text-emerald-600' }} font-sans text-xs font-bold tracking-widest uppercase mb-1">Perlu Restock</p>
                <h3 class="text-3xl font-manrope font-bold {{ $criticalCount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $criticalCount }}</h3>
                <p class="text-[10px] text-slate-400 mt-1">{{ $criticalCount > 0 ? 'produk hampir habis' : 'semua stok aman' }}</p>
            </div>
            @if($criticalCount > 0)
            <a href="{{ route('reports.stock') }}" class="text-[10px] text-rose-600 font-bold uppercase tracking-widest hover:underline">Lihat Detail →</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-12 gap-8">
        {{-- Tabel stok hampir habis --}}
        @if($criticalCount > 0)
        <div class="col-span-12 lg:col-span-7">
            <div class="flex items-center justify-between mb-5">
                <h4 class="font-manrope text-xl font-extrabold text-blue-900 tracking-tight">
                    Produk Hampir Habis
                </h4>
                <a href="{{ route('reports.stock') }}" class="text-xs font-bold text-primary hover:underline underline-offset-4 tracking-widest uppercase">Lihat Semua</a>
            </div>
            <div class="bg-surface-container-lowest rounded-2xl overflow-hidden editorial-shadow">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-surface-container-low/50">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Produk</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center">Stok</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center">Min.</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/50">
                        @foreach($criticalStock->take(6) as $product)
                        @php $stock = $product->variants->sum('stock'); @endphp
                        <tr class="hover:bg-rose-50/20 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-manrope font-bold text-blue-900 text-sm">{{ $product->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $product->category->name }}</p>
                            </td>
                            <td class="px-6 py-4 text-center font-manrope font-black text-rose-600 text-lg">{{ $stock }}</td>
                            <td class="px-6 py-4 text-center text-sm text-slate-400">{{ $product->reorder_level }}</td>
                            <td class="px-6 py-4">
                                @if($stock == 0)
                                    <span class="px-2 py-1 bg-rose-100 text-rose-700 text-[9px] font-black uppercase tracking-tighter rounded border border-rose-200">Habis</span>
                                @else
                                    <span class="px-2 py-1 bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-tighter rounded border border-amber-100">Hampir Habis</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Aktivitas terbaru --}}
        <div class="col-span-12 {{ $criticalCount > 0 ? 'lg:col-span-5' : 'lg:col-span-6' }}">
            <h4 class="font-manrope text-xl font-extrabold text-blue-900 tracking-tight mb-5">
                Aktivitas Terbaru
            </h4>
            <div class="space-y-3">
                @forelse($recentSales as $sale)
                <a href="{{ route('sales.show', $sale) }}"
                   class="flex items-center gap-4 p-4 bg-surface-container-lowest rounded-xl border border-transparent hover:border-blue-100 hover:editorial-shadow transition-all">
                    <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-sm">sell</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-blue-900 truncate">Penjualan #{{ $sale->invoice_number }}</p>
                        <p class="text-xs text-slate-400">{{ $sale->user->name }} · {{ $sale->date->format('d M Y') }} <svg class="inline w-3 h-3 mr-0.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>{{ $sale->created_at->format('H:i') }}</p>
                    </div>
                    <span class="text-sm font-manrope font-bold text-blue-900 shrink-0">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                </a>
                @empty
                <p class="text-slate-400 text-sm text-center py-6">Belum ada penjualan.</p>
                @endforelse

                @foreach($recentPurchases->take(3) as $purchase)
                <a href="{{ route('purchases.show', $purchase) }}"
                   class="flex items-center gap-4 p-4 bg-surface-container-lowest rounded-xl border border-transparent hover:border-blue-100 hover:editorial-shadow transition-all">
                    <div class="w-9 h-9 rounded-full bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-sm">shopping_cart</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-blue-900 truncate">Pembelian #{{ $purchase->invoice_number }}</p>
                        <p class="text-xs text-slate-400">{{ $purchase->supplier->name }} · {{ $purchase->date->format('d M Y') }} <svg class="inline w-3 h-3 mr-0.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>{{ $purchase->created_at->format('H:i') }}</p>
                    </div>
                    <span class="text-sm font-manrope font-bold text-blue-900 shrink-0">Rp {{ number_format($purchase->total, 0, ',', '.') }}</span>
                </a>
                @endforeach
            </div>
        </div>

    </div>
</x-app-layout>
