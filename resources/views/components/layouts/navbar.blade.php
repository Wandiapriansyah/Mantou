@props(['title', 'home' => '/'])

<header class="flex h-[65px] items-center justify-between border-b border-line bg-surface px-5 sm:px-8">
  <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[12px] text-muted">
    <a href="{{ $home }}" class="flex items-center gap-2 hover:text-brand"><x-ui.icon name="store" size="size-4" />Ruang usaha</a>
    <span>/</span>
    <span class="font-bold text-ink">{{ $title }}</span>
  </nav>
  <div class="flex items-center gap-4 text-[12px] text-muted">
    <x-ui.badge class="hidden sm:inline">Data contoh</x-ui.badge>
    <span class="hidden sm:inline">Minggu, 7 Juni 2026</span>
    <button type="button" aria-label="Notifikasi" class="hover:text-brand"><x-ui.icon name="bell" size="size-[18px]" /></button>
  </div>
</header>
