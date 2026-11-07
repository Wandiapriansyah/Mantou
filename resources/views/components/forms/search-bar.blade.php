@props(['id' => 'q', 'buttonId' => 'searchBtn', 'label', 'placeholder' => ''])

<label for="{{ $id }}" class="text-[12px] font-bold">{{ $label }}</label>
<div class="mt-2.5 flex gap-3">
  <div class="flex flex-1 items-center gap-3 rounded-lg border border-line bg-surface px-3.5 focus-within:border-brand">
    <span class="text-muted"><x-ui.icon name="search" size="size-[18px]" /></span>
    <input id="{{ $id }}" type="text" inputmode="search" autocomplete="off" placeholder="{{ $placeholder }}" class="w-full bg-transparent py-3 text-[14px] outline-none">
  </div>
  <button type="button" id="{{ $buttonId }}" class="inline-flex items-center gap-2 rounded-lg bg-brand px-6 text-[13px] font-bold text-white transition hover:bg-brand-dark">
    <x-ui.icon name="search" size="size-[18px]" />Cari
  </button>
</div>
