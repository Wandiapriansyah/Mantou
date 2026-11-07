@props(['menu', 'active'])

<nav id="bottomNav" class="fixed inset-x-0 bottom-0 z-40 flex overflow-x-auto border-t border-line bg-surface/95 px-2 pb-[env(safe-area-inset-bottom)] backdrop-blur [scrollbar-width:none] lg:hidden" aria-label="Menu">
  @foreach ($menu as $item)
    @php $current = $item['key'] === $active; @endphp
    <a href="{{ $item['href'] }}" @if ($current) aria-current="page" @endif
       @class([
           'flex min-w-[68px] shrink-0 flex-col items-center gap-1 px-1 pb-1 pt-2 text-[10px]',
           'font-bold text-brand' => $current,
           'font-medium text-muted' => ! $current,
       ])>
      <span @class(['grid h-7 w-12 place-items-center rounded-full transition', 'bg-tint text-brand' => $current, 'text-muted' => ! $current])>
        <x-ui.icon :name="$item['icon']" />
      </span>
      <span class="whitespace-nowrap">{{ $item['short'] }}</span>
    </a>
  @endforeach
</nav>
<script>
  (function () {
    var nav = document.getElementById('bottomNav');
    var current = nav.querySelector('[aria-current]');
    if (current) nav.scrollLeft = current.offsetLeft - (nav.clientWidth - current.offsetWidth) / 2;
  })();
</script>
