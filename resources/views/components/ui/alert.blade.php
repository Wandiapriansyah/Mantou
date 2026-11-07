@props(['variant' => 'toast', 'id' => null, 'icon' => null, 'title' => null])

@if ($variant === 'toast')
  <div id="toast" role="status" class="pointer-events-none fixed bottom-24 left-1/2 z-[60] -translate-x-1/2 rounded-lg bg-ink px-4 py-2.5 text-[13px] font-semibold text-white opacity-0 transition-opacity lg:bottom-8"></div>
@elseif ($variant === 'error')
  <p @if ($id) id="{{ $id }}" @endif role="alert" {{ $attributes->class(['hidden rounded-lg bg-warn-bg px-4 py-3 text-[12px] font-semibold text-warn']) }}></p>
@elseif ($variant === 'note')
  <div {{ $attributes->class(['flex items-center gap-3 rounded-xl bg-line/50 px-5 py-4 text-[12px] text-muted']) }}>{{ $slot }}</div>
@else
  <div {{ $attributes->class(['flex gap-4 rounded-xl bg-tint px-6 py-5']) }}>
    @if ($icon)
      <span class="mt-0.5 text-brand"><x-ui.icon :name="$icon" /></span>
    @endif
    <div>
      @if ($title)
        <p class="text-[13px] font-bold">{{ $title }}</p>
      @endif
      <p @if ($id) id="{{ $id }}" @endif class="mt-1 text-[12px] text-muted">{{ $slot }}</p>
    </div>
  </div>
@endif
