@props(['tone' => 'muted', 'size' => 'sm'])

@php
    $tones = [
        'muted' => 'bg-line/70 text-muted',
        'brand' => 'bg-tint text-brand',
        'warn' => 'bg-warn-bg text-warn',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1',
        'md' => 'px-3 py-1.5',
    ];
@endphp

<span {{ $attributes->class(['rounded-md text-[11px] font-bold', $sizes[$size], $tones[$tone]]) }}>{{ $slot }}</span>
