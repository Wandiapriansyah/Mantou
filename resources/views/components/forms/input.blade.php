@props(['id', 'label' => null, 'type' => 'text', 'prefix' => null, 'suffix' => null, 'hint' => null, 'hintId' => null, 'size' => 'md', 'wrapper' => '', 'container' => ''])

@php
    $sizes = [
        'md' => 'px-3.5 py-3 text-[14px]',
        'sm' => 'px-3.5 py-2.5 text-[13px]',
    ];
@endphp

<div @class([$container])>
  @if ($label)
    <label for="{{ $id }}" class="text-[12px] font-bold">{{ $label }}</label>
  @endif

  @if ($prefix || $suffix)
    <div @class(['mt-2 flex items-center gap-3 rounded-lg border border-line bg-surface focus-within:border-brand', $sizes[$size], $wrapper])>
      @if ($prefix)
        <span class="text-[13px] text-muted">{{ $prefix }}</span>
      @endif
      <input id="{{ $id }}" type="{{ $type }}" {{ $attributes->class(['min-w-0 flex-1 bg-transparent outline-none']) }}>
      @if ($suffix)
        <span class="text-[13px] text-muted">{{ $suffix }}</span>
      @endif
    </div>
  @else
    <input id="{{ $id }}" type="{{ $type }}" {{ $attributes->class(['w-full rounded-lg border border-line bg-surface outline-none focus:border-brand', $sizes[$size], 'mt-2' => $label]) }}>
  @endif

  @if ($hint || $hintId)
    <p @if ($hintId) id="{{ $hintId }}" @endif class="mt-2 text-[11px] text-muted">{{ $hint }}</p>
  @endif
</div>
