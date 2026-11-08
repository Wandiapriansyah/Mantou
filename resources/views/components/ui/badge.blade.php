@props(['tone' => 'muted', 'size' => 'sm'])

@php
    $tones = [
        'muted' => 'bg-line text-muted',
        'brand' => 'bg-tint text-brand-dark',
        'warn' => 'border border-warning bg-warn-bg text-warn',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1',
        'md' => 'px-3 py-1.5',
    ];
@endphp

<span {{ $attributes->class(['rounded-md text-[12px] font-bold', $sizes[$size], $tones[$tone]]) }}>{{ $slot }}</span>
