@props(['variant' => 'outline', 'size' => 'md', 'icon' => null, 'href' => null, 'type' => 'button'])

@php
    $variants = [
        'primary' => 'bg-brand-dark text-surface hover:bg-ink disabled:cursor-not-allowed disabled:bg-faint',
        'outline' => 'border border-line bg-surface hover:border-brand',
    ];

    $sizes = [
        'md' => 'px-4 py-2.5 text-[13px]',
        'lg' => 'px-5 py-3 text-[13px]',
        'compact' => 'px-4 py-2 text-[13px]',
        'sm' => 'px-3.5 py-2 text-[12px]',
    ];

    $classes = $variant === 'link'
        ? 'text-[13px] font-bold text-brand-dark'
        : 'inline-flex items-center gap-2 rounded-lg font-bold transition '.$variants[$variant].' '.$sizes[$size];
@endphp

@if ($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
@else
  <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
@endif
  @if ($icon)
    <x-ui.icon :name="$icon" size="size-[18px]" />
  @endif
  {{ $slot }}
@if ($href)
  </a>
@else
  </button>
@endif
