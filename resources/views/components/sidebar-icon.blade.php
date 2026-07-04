@props(['name'])

@php
    $iconClass = $attributes->get('class', 'h-6 w-6');
@endphp

@switch($name)
    @case('dashboard')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <rect x="3" y="3" width="7" height="8" rx="2" />
            <rect x="14" y="3" width="7" height="5" rx="2" />
            <rect x="14" y="12" width="7" height="9" rx="2" />
            <rect x="3" y="15" width="7" height="6" rx="2" />
        </svg>
        @break
    @case('inventory_2')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <path d="M4 7.5 12 3l8 4.5-8 4.5-8-4.5Z" />
            <path d="M4 7.5v9L12 21l8-4.5v-9" />
            <path d="M12 12v9" />
        </svg>
        @break
    @case('shopping_cart')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <path d="M5 5h2l1.4 9.2a2 2 0 0 0 2 1.7h6.7a2 2 0 0 0 1.9-1.4L21 8H8" />
            <circle cx="10" cy="20" r="1.2" />
            <circle cx="18" cy="20" r="1.2" />
        </svg>
        @break
    @case('sell')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <path d="M20 13.5 13.5 20a2 2 0 0 1-2.8 0L4 13.3V4h9.3L20 10.7a2 2 0 0 1 0 2.8Z" />
            <circle cx="8.5" cy="8.5" r="1.2" />
        </svg>
        @break
    @case('assignment_return')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <path d="M9 5H6.8A2.8 2.8 0 0 0 4 7.8v8.4A2.8 2.8 0 0 0 6.8 19h10.4a2.8 2.8 0 0 0 2.8-2.8V7.8A2.8 2.8 0 0 0 17.2 5H15" />
            <path d="M9 5a3 3 0 0 1 6 0" />
            <path d="M10 12h7" />
            <path d="m13 9-3 3 3 3" />
        </svg>
        @break
    @case('inventory')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <rect x="5" y="4" width="14" height="17" rx="2.5" />
            <path d="M9 4.5V3h6v1.5" />
            <path d="M8.5 10h7" />
            <path d="M8.5 14h7" />
            <path d="M8.5 18h4" />
        </svg>
        @break
    @case('analytics')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <path d="M4 19V5" />
            <path d="M4 19h16" />
            <rect x="7" y="11" width="3" height="5" rx="1" />
            <rect x="12" y="7" width="3" height="9" rx="1" />
            <rect x="17" y="9" width="3" height="7" rx="1" />
        </svg>
        @break
    @case('category')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <rect x="4" y="4" width="6" height="6" rx="1.5" />
            <rect x="14" y="4" width="6" height="6" rx="1.5" />
            <rect x="4" y="14" width="6" height="6" rx="1.5" />
            <rect x="14" y="14" width="6" height="6" rx="1.5" />
        </svg>
        @break
    @case('straighten')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <rect x="4" y="8" width="16" height="8" rx="2" />
            <path d="M8 8v3" />
            <path d="M12 8v4" />
            <path d="M16 8v3" />
        </svg>
        @break
    @case('storefront')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <path d="M5 10h14l-1.2-5.2A1 1 0 0 0 16.8 4H7.2a1 1 0 0 0-1 .8L5 10Z" />
            <path d="M6 10v9h12v-9" />
            <path d="M9 19v-5h6v5" />
            <path d="M5 10c0 1.3 1 2.3 2.3 2.3S9.6 11.3 9.6 10c0 1.3 1.1 2.3 2.4 2.3s2.4-1 2.4-2.3c0 1.3 1 2.3 2.3 2.3S19 11.3 19 10" />
        </svg>
        @break
    @case('manage_accounts')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <circle cx="9" cy="8" r="3" />
            <path d="M4 19a5 5 0 0 1 10 0" />
            <circle cx="17.5" cy="15.5" r="2" />
            <path d="M17.5 11.5v1" />
            <path d="M17.5 18.5v1" />
            <path d="M13.5 15.5h1" />
            <path d="M20.5 15.5h1" />
        </svg>
        @break
    @case('logout')
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <path d="M10 5H6.5A2.5 2.5 0 0 0 4 7.5v9A2.5 2.5 0 0 0 6.5 19H10" />
            <path d="M13 8l4 4-4 4" />
            <path d="M17 12H9" />
        </svg>
        @break
    @default
        <svg {{ $attributes->merge(['class' => $iconClass.' shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
            <circle cx="12" cy="12" r="8" />
            <path d="M12 8v4l3 2" />
        </svg>
@endswitch
