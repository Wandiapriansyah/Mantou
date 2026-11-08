@props(['variant' => 'toast', 'id' => null, 'icon' => null, 'title' => null])

@if ($variant === 'toast')
  <div id="toast" role="status" class="pointer-events-none fixed bottom-24 left-1/2 z-[60] -translate-x-1/2 rounded-lg bg-ink px-4 py-2.5 text-[13px] font-semibold text-surface shadow-lg opacity-0 transition-opacity lg:bottom-8"></div>
@elseif ($variant === 'error')
  <p @if ($id) id="{{ $id }}" @endif role="alert" {{ $attributes->class(['hidden rounded-lg border border-danger bg-danger-bg px-4 py-3 text-[12px] font-semibold text-danger-dark']) }}></p>
@elseif ($variant === 'success')
  <div @if ($id) id="{{ $id }}" @endif role="status" {{ $attributes->class(['hidden rounded-lg border border-success bg-success-bg px-4 py-3 text-[12px] font-semibold text-success']) }}></div>
@elseif ($variant === 'note')
  <div {{ $attributes->class(['flex items-center gap-3 rounded-xl border border-line bg-canvas px-5 py-4 text-[12px] text-muted']) }}>{{ $slot }}</div>
@else
  <div {{ $attributes->class(['flex gap-4 rounded-xl bg-tint px-6 py-5']) }}>
    @if ($icon)
      <span class="mt-0.5 text-brand-dark"><x-ui.icon :name="$icon" /></span>
    @endif
    <div>
      @if ($title)
        <p class="text-[13px] font-bold">{{ $title }}</p>
      @endif
      <p @if ($id) id="{{ $id }}" @endif class="mt-1 text-[12px] text-muted">{{ $slot }}</p>
    </div>
  </div>
@endif
