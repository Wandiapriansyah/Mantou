@props([
    'title' => null,
    'subtitle' => null,
    'subtitleId' => null,
    'titleSize' => 'text-[18px]',
    'tone' => 'surface',
    'radius' => 'xl',
    'padding' => 'p-6',
    'flush' => false,
])

@php
    $tones = [
        'surface' => 'border-line bg-surface',
        'tint' => 'border-brand bg-tint',
    ];

    $radii = [
        'xl' => 'rounded-xl',
        '2xl' => 'rounded-2xl',
    ];
@endphp

<section {{ $attributes->class(['border', $radii[$radius], $tones[$tone], 'overflow-hidden' => $flush, $padding => ! $flush]) }}>
  @if ($title)
    <div @class(['flex flex-wrap items-start justify-between gap-3', 'p-6 pb-5' => $flush])>
      <div>
        <h2 class="{{ $titleSize }} font-semibold">{{ $title }}</h2>
        @if ($subtitle || $subtitleId)
          <p @if ($subtitleId) id="{{ $subtitleId }}" @endif class="mt-1 text-[12px] text-muted">{{ $subtitle }}</p>
        @endif
      </div>
      {{ $aside ?? '' }}
    </div>
  @endif
  {{ $slot }}
</section>
