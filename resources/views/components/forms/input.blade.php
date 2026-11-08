@props(['id', 'label' => null, 'type' => 'text', 'prefix' => null, 'suffix' => null, 'hint' => null, 'hintId' => null, 'size' => 'md', 'wrapper' => '', 'container' => '', 'error' => null])

@php
    $sizes = [
        'md' => 'px-3.5 py-3 text-[14px]',
        'sm' => 'px-3.5 py-2.5 text-[13px]',
    ];
    $border = $error ? 'border-danger' : 'border-line';
    $described = $error ? $id.'-error' : null;
@endphp

<div @class([$container])>
  @if ($label)
    <label for="{{ $id }}" class="text-[12px] font-bold">{{ $label }}</label>
  @endif

  @if ($prefix || $suffix)
    <div @class(['mt-2 flex items-center gap-3 rounded-lg border bg-surface focus-within:border-brand', $border, $sizes[$size], $wrapper])>
      @if ($prefix)
        <span class="text-[13px] text-muted">{{ $prefix }}</span>
      @endif
      <input id="{{ $id }}" type="{{ $type }}" {{ $attributes->class(['min-w-0 flex-1 bg-transparent outline-none']) }}>
      @if ($suffix)
        <span class="text-[13px] text-muted">{{ $suffix }}</span>
      @endif
    </div>
  @else
    <input id="{{ $id }}" type="{{ $type }}" {{ $attributes->class(['w-full rounded-lg border bg-surface outline-none focus:border-brand', $border, $sizes[$size], 'mt-2' => $label])->merge(['aria-invalid' => $error ? 'true' : null, 'aria-describedby' => $described]) }}>
  @endif

  @if ($error)
    <p id="{{ $id }}-error" class="mt-2 text-[13px] font-semibold text-danger-dark">{{ $error }}</p>
  @endif

  @if ($hint || $hintId)
    <p @if ($hintId) id="{{ $hintId }}" @endif class="mt-2 text-[12px] text-muted">{{ $hint }}</p>
  @endif
</div>
