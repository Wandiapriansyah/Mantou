@props(['menu', 'active', 'role', 'home'])

@php
    $profile = [
        'name' => 'Bu Ratna',
        'initials' => 'BR',
        'title' => $role === 'worker' ? 'Penjaga warung' : 'Pemilik warung',
    ];
@endphp

<aside class="sticky top-0 hidden h-screen w-[236px] shrink-0 flex-col border-r border-line bg-surface px-4 pb-5 pt-7 lg:flex">
  <a href="{{ $home }}" class="flex items-center gap-2.5 px-1">
    <span class="grid size-9 place-items-center rounded-lg bg-brand-dark text-surface"><x-ui.icon name="store" /></span>
    <span class="text-[22px] font-bold tracking-tight text-ink">Mantou</span>
  </a>

  <button type="button" class="mt-6 flex w-full items-start justify-between rounded-xl border border-line bg-surface px-3.5 py-3 text-left">
    <span>
      <span class="block text-[13px] font-bold text-ink">Warung Bu Ratna</span>
      <span class="mt-1.5 block text-[12px] text-muted">Sukamaju, Bandung</span>
    </span>
    <span class="mt-0.5 text-muted"><x-ui.icon name="chevrons" size="size-4" /></span>
  </button>

  <nav class="mt-7 flex flex-1 flex-col gap-1 overflow-y-auto" aria-label="Menu utama">
    @foreach ($menu->groupBy('group') as $group => $items)
      <p @class(['mb-1 px-1 text-[12px] font-semibold text-faint', 'mt-5' => ! $loop->first])>{{ $group }}</p>
      @foreach ($items as $item)
        @php $current = $item['key'] === $active; @endphp
        <a href="{{ $item['href'] }}" title="{{ $item['desc'] }}" @if ($current) aria-current="page" @endif
           @class([
               'flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px]',
               'relative bg-tint font-bold text-brand-dark' => $current,
               'font-medium text-muted transition hover:bg-tint hover:text-brand-dark' => ! $current,
           ])>
          <x-ui.icon :name="$item['icon']" />
          <span>{{ $item['label'] }}</span>
          @if ($current)
            <span class="absolute right-2.5 h-4 w-[3px] rounded-full bg-brand"></span>
          @endif
        </a>
      @endforeach
    @endforeach
  </nav>

  <a href="#" class="mb-4 inline-flex items-center gap-1.5 px-1 text-[12px] font-semibold text-brand-dark hover:underline">Panduan penggunaan <x-ui.icon name="external-link" size="size-3.5" /></a>

  <div class="flex items-center gap-3 px-1">
    <span class="grid size-9 place-items-center rounded-full bg-line text-[12px] font-bold text-ink">{{ $profile['initials'] }}</span>
    <span class="flex-1">
      <span class="block text-[13px] font-bold text-ink">{{ $profile['name'] }}</span>
      <span class="block text-[12px] text-muted">{{ $profile['title'] }}</span>
    </span>
    <a href="#" aria-label="Keluar" class="text-muted hover:text-brand-dark"><x-ui.icon name="logout" /></a>
  </div>
</aside>
