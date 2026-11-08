@props(['id', 'label', 'icon', 'caption' => '', 'hint' => null, 'value' => 'Rp0', 'highlight' => false, 'size' => 'md'])

@php
    $sizes = [
        'lg' => 'mt-4 text-[40px]',
        'md' => 'mt-4 text-[28px]',
        'sm' => 'mt-3 text-[22px]',
    ];
@endphp

<div {{ $attributes->class(['rounded-xl border', 'p-6' => $size === 'lg', 'p-5' => $size !== 'lg', 'border-brand bg-tint' => $highlight, 'border-line bg-surface' => ! $highlight]) }}>
  <div class="flex items-center justify-between text-[12px] text-muted">
    <span @class(['font-bold text-ink' => $size === 'lg'])>{{ $label }}</span>
    <span class="text-brand-dark"><x-ui.icon :name="$icon" size="size-[18px]" /></span>
  </div>
  <p id="{{ $id }}" class="{{ $sizes[$size] }} font-bold leading-none tracking-tight text-ink">{{ $value }}</p>
  <p id="{{ $id }}Cap" class="mt-3 text-[12px] text-muted">{{ $caption }}</p>
  @if ($hint)
    <p class="mt-1 text-[12px] text-faint">{{ $hint }}</p>
  @endif
</div>
