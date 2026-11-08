@props(['title', 'subtitle' => null, 'subtitleId' => null])

<div class="flex flex-wrap items-start justify-between gap-4">
  <div>
    <h1 class="text-[28px] font-semibold tracking-tight">{{ $title }}</h1>
    @if ($subtitle)
      <p @if ($subtitleId) id="{{ $subtitleId }}" @endif class="mt-2 text-[13px] text-muted">{{ $subtitle }}</p>
    @endif
  </div>
  @if ($slot->isNotEmpty())
    <div class="flex flex-wrap items-center gap-2.5">{{ $slot }}</div>
  @endif
</div>
