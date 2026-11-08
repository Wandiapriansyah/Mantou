@props(['title', 'group' => null])

<header class="flex h-14 items-center gap-3 border-b border-line bg-surface px-5 sm:px-8">
  <button type="button" id="sidebarToggle" aria-controls="sidebar" aria-expanded="true" aria-label="Ciutkan sidebar" title="Ciutkan sidebar"
          class="-ml-2 hidden size-9 place-items-center rounded-lg text-muted hover:bg-tint hover:text-brand-dark lg:grid">
    <x-ui.icon name="sidebar" />
  </button>
  <nav aria-label="Breadcrumb" class="flex min-w-0 items-center gap-2 text-[13px] text-muted">
    @if ($group)
      <span class="truncate">{{ $group }}</span>
      <span aria-hidden="true">/</span>
    @endif
    <span class="truncate font-bold text-ink" aria-current="page">{{ $title }}</span>
  </nav>
</header>
