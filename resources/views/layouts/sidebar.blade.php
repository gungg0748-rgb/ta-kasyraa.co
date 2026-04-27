@php
    $role = Auth::user()->role;
    $items = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon'  => 'dashboard',
            'roles' => ['admin', 'kasir', 'gudang'],
        ],
        [
            'label' => 'Produk',
            'route' => 'products.index',
            'icon'  => 'inventory_2',
            'roles' => ['admin', 'gudang'],
        ],
        [
            'label' => 'Pembelian',
            'route' => 'purchases.index',
            'icon'  => 'shopping_cart',
            'roles' => ['admin', 'gudang'],
        ],
        [
            'label' => 'Penjualan',
            'route' => 'sales.index',
            'icon'  => 'sell',
            'roles' => ['admin', 'kasir'],
        ],
        [
            'label' => 'Return',
            'route' => 'returns.index',
            'icon'  => 'assignment_return',
            'roles' => ['admin', 'gudang'],
        ],
        [
            'label' => 'Stok Opname',
            'route' => 'stock-opnames.index',
            'icon'  => 'inventory',
            'roles' => ['admin', 'gudang'],
        ],
        [
            'label' => 'Laporan',
            'route' => 'reports.index',
            'icon'  => 'analytics',
            'roles' => ['admin', 'kasir', 'gudang'],
        ],
    ];
@endphp

@foreach($items as $item)
    @if(in_array($role, $item['roles']))
        @php
            $isActive = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route']));
        @endphp
        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm
                  {{ $isActive
                      ? 'text-blue-900 font-bold bg-white shadow-sm scale-[0.99]'
                      : 'text-slate-500 font-medium hover:text-blue-800 hover:bg-blue-50/50' }}">
            <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
            <span class="font-sans">{{ $item['label'] }}</span>
        </a>
    @endif
@endforeach
