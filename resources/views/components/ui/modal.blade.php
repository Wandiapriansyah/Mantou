@props(['id' => 'modal', 'boxId' => 'modalBox'])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-end justify-center bg-ink/40 p-4 backdrop-blur-sm sm:items-center" role="dialog" aria-modal="true">
  <div id="{{ $boxId }}" class="max-h-[92vh] w-full max-w-[520px] overflow-y-auto rounded-2xl border border-line bg-surface p-6 shadow-xl"></div>
</div>
