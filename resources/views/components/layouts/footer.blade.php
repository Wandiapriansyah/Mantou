@props(['menu', 'active'])

@php
    $main = $menu->where('primary', true);
    $more = $menu->where('primary', false);
    $moreActive = $more->contains('key', $active);
    $tab = 'flex min-w-0 flex-1 flex-col items-center gap-1 px-1 pb-1.5 pt-2 text-[11px]';
@endphp

<nav id="bottomNav" class="fixed inset-x-0 bottom-0 z-40 flex border-t border-line bg-surface px-2 pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Menu">
  @foreach ($main as $item)
    @php $current = $item['key'] === $active; @endphp
    <a href="{{ $item['href'] }}" @if ($current) aria-current="page" @endif @class([$tab, 'font-bold text-brand-dark' => $current, 'font-medium text-muted' => ! $current])>
      <span @class(['grid h-7 w-12 place-items-center rounded-full transition', 'bg-tint text-brand-dark' => $current])>
        <x-ui.icon :name="$item['icon']" />
      </span>
      <span class="whitespace-nowrap">{{ $item['short'] }}</span>
    </a>
  @endforeach
  @if ($more->isNotEmpty())
    <button type="button" id="moreToggle" aria-expanded="false" aria-controls="moreMenu" @class([$tab, 'font-bold text-brand-dark' => $moreActive, 'font-medium text-muted' => ! $moreActive])>
      <span @class(['grid h-7 w-12 place-items-center rounded-full transition', 'bg-tint text-brand-dark' => $moreActive])>
        <x-ui.icon name="list" />
      </span>
      <span class="whitespace-nowrap">Lainnya</span>
    </button>
  @endif
</nav>

@if ($more->isNotEmpty())
  <div id="moreMenu" class="fixed inset-x-3 bottom-[76px] z-40 hidden rounded-xl border border-surface bg-surface/95 p-2 shadow-lg backdrop-blur-sm lg:hidden">
    @foreach ($more as $item)
      <a href="{{ $item['href'] }}" @if ($item['key'] === $active) aria-current="page" @endif @class(['flex items-center gap-3 rounded-lg px-3 py-3', 'bg-tint text-brand-dark' => $item['key'] === $active])>
        <x-ui.icon :name="$item['icon']" />
        <span>
          <span class="block text-[13px] font-bold text-ink">{{ $item['label'] }}</span>
          <span class="block text-[12px] text-muted">{{ $item['desc'] }}</span>
        </span>
      </a>
    @endforeach
  </div>
  <script>
    (function () {
      var toggle = document.getElementById('moreToggle');
      var menu = document.getElementById('moreMenu');
      function setOpen(open) {
        menu.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      }
      toggle.addEventListener('click', function (e) { e.stopPropagation(); setOpen(menu.classList.contains('hidden')); });
      document.addEventListener('click', function (e) { if (!menu.contains(e.target)) setOpen(false); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
    })();
  </script>
@endif
