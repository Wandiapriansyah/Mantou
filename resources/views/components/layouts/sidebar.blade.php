@props(['menu', 'active', 'role', 'home', 'userName' => '', 'userTitle' => '', 'warungName' => ''])

@php
    // Inisial dari nama akun; tanpa nama, pakai huruf pertama peran.
    $initials = collect(preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: mb_strtoupper(mb_substr($userTitle, 0, 1));
@endphp

<aside id="sidebar" aria-label="Sidebar" class="sticky top-0 hidden h-screen shrink-0 flex-col overflow-hidden border-r border-line bg-surface px-4 pb-5 pt-6 lg:flex">
  <a href="{{ $home }}" title="Mantou" class="sb-center flex items-center gap-2.5 px-1">
    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-brand-dark text-surface"><x-ui.icon name="store" /></span>
    <span class="sb-label text-[22px] font-bold tracking-tight text-ink">Mantou</span>
  </a>

  @if ($warungName !== '')
    <div class="sb-hide mt-6 rounded-xl border border-line px-3.5 py-3">
      <span class="block truncate text-[13px] font-bold text-ink" title="{{ $warungName }}">{{ $warungName }}</span>
    </div>
  @endif

  <nav class="mt-6 flex flex-1 flex-col gap-1 overflow-y-auto overflow-x-hidden" aria-label="Menu utama">
    @foreach ($menu->groupBy('group') as $group => $items)
      <p @class(['sb-group mb-1 px-1 text-[12px] font-semibold text-faint', 'mt-5' => ! $loop->first])><span class="sb-label">{{ $group }}</span></p>
      @foreach ($items as $item)
        @php $current = $item['key'] === $active; @endphp
        <a href="{{ $item['href'] }}" title="{{ $item['label'] }} — {{ $item['desc'] }}" @if ($current) aria-current="page" @endif
           @class([
               'sb-center relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px]',
               'bg-tint font-bold text-brand-dark' => $current,
               'font-medium text-muted transition hover:bg-tint hover:text-brand-dark' => ! $current,
           ])>
          <x-ui.icon :name="$item['icon']" />
          <span class="sb-label">{{ $item['label'] }}</span>
          @if ($current)
            <span class="sb-active-bar absolute right-2.5 h-4 w-[3px] rounded-full bg-brand" aria-hidden="true"></span>
          @endif
        </a>
      @endforeach
    @endforeach
  </nav>

  <div class="sb-stack sb-center mt-4 flex items-center gap-3 border-t border-line px-1 pt-4">
    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-line text-[12px] font-bold text-ink" aria-hidden="true">{{ $initials }}</span>
    <span class="sb-hide min-w-0 flex-1">
      @if ($userName !== '')
        <span class="block truncate text-[13px] font-bold text-ink" title="{{ $userName }}">{{ $userName }}</span>
      @endif
      <span @class(['block truncate text-[12px] text-muted', 'text-[13px] font-bold text-ink' => $userName === ''])>{{ $userTitle }}</span>
    </span>
    <form method="POST" action="/logout">
      @csrf
      <button type="submit" aria-label="Keluar" title="Keluar" class="grid size-9 place-items-center rounded-lg text-muted hover:bg-tint hover:text-brand-dark"><x-ui.icon name="logout" /></button>
    </form>
  </div>
</aside>
