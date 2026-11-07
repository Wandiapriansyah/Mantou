@props(['name', 'size' => 'size-5'])

@php
    $icons = [
        'store' => '<path d="M4 9l1.5-5h13L20 9"/><path d="M4 9v11h16V9"/><path d="M4 9c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3"/><path d="M10 20v-5h4v5"/>',
        'chevrons' => '<path d="M8 10l4-4 4 4M8 14l4 4 4-4"/>',
        'dashboard' => '<rect x="4" y="4" width="6.5" height="6.5" rx="1"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1"/>',
        'barcode' => '<path d="M3 7V5a1 1 0 011-1h2M21 7V5a1 1 0 00-1-1h-2M3 17v2a1 1 0 001 1h2M21 17v2a1 1 0 01-1 1h-2"/><path d="M8 8v8M11 8v8M14 8v8M17 8v8"/>',
        'box' => '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z"/><path d="M4 7.5l8 4.5 8-4.5M12 12v9"/>',
        'document' => '<path d="M5 6a2 2 0 012-2h8l4 4v11a1 1 0 01-1 1H7a2 2 0 01-2-2V6z"/><path d="M9 13h6M9 17h4"/>',
        'chart-line' => '<path d="M4 20h16"/><path d="M5 16l4-4 3 3 6-7"/>',
        'calculator' => '<rect x="5" y="3.5" width="14" height="17" rx="2"/><path d="M8.5 8h7M8.5 12h2M13.5 12h2M8.5 16h2M13.5 16h2"/>',
        'bars' => '<path d="M4 20h16M8 20v-8M12 20V6M16 20v-5"/>',
        'discount' => '<circle cx="12" cy="12" r="8.5"/><path d="M9 15l6-6"/><circle cx="9.5" cy="9.5" r=".8"/><circle cx="14.5" cy="14.5" r=".8"/>',
        'sprout' => '<path d="M12 21v-8"/><path d="M12 13c0-3.5-2.5-5.5-6-5.5 0 3.5 2.5 5.5 6 5.5z"/><path d="M12 15c0-3 2-5 5.5-5 0 3-2 5-5.5 5z"/>',
        'external-link' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V7a1 1 0 011-1h5"/>',
        'logout' => '<path d="M9 4H5a1 1 0 00-1 1v14a1 1 0 001 1h4M15 8l4 4-4 4M19 12H9"/>',
        'bell' => '<path d="M6 16V11a6 6 0 1112 0v5l1.5 2h-15L6 16z"/><path d="M10 21h4"/>',
        'list' => '<path d="M4 7h16M7 12h10M10 17h4"/>',
        'arrow-up-right' => '<path d="M7 17L17 7M9 7h8v8"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8v.5"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'clipboard-check' => '<rect x="6" y="5" width="12" height="16" rx="2"/><path d="M9.5 5V3.5h5V5M9 13l2.2 2.2L15.5 11"/>',
        'scan' => '<path d="M4 8V5a1 1 0 011-1h3M16 4h3a1 1 0 011 1v3M20 16v3a1 1 0 01-1 1h-3M8 20H5a1 1 0 01-1-1v-3"/><path d="M8 12h8"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4 4"/>',
        'shield-check' => '<path d="M12 3l7 3v5c0 5-3 8.5-7 10-4-1.5-7-5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
        'calendar' => '<rect x="4" y="5.5" width="16" height="14.5" rx="2"/><path d="M4 10h16M8 3.5v4M16 3.5v4"/>',
        'wallet' => '<path d="M4 8a2 2 0 012-2h11v3"/><path d="M4 8v9a2 2 0 002 2h13a1 1 0 001-1V9a1 1 0 00-1-1H6"/><circle cx="16.5" cy="13.5" r="1"/>',
        'sun' => '<circle cx="12" cy="12" r="3.5"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M5.6 18.4L7 17M17 7l1.4-1.4"/>',
        'download' => '<path d="M12 4v11M7.5 11l4.5 4.5 4.5-4.5M5 20h14"/>',
        'arrow-down-left' => '<path d="M17 7L7 17M15 17H7V9"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => $size.' shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$name] !!}</svg>
