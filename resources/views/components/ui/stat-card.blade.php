@props(['id', 'label', 'icon', 'caption' => '', 'value' => 'Rp0', 'highlight' => false])

<div @class(['rounded-xl border p-5', 'border-brand-soft/60 bg-tint' => $highlight, 'border-line bg-surface' => ! $highlight])>
  <div class="flex items-center justify-between text-[12px] text-muted">
    <span>{{ $label }}</span>
    <span class="text-brand"><x-ui.icon :name="$icon" size="size-[18px]" /></span>
  </div>
  <p id="{{ $id }}" class="mt-4 text-[28px] font-extrabold leading-none tracking-tight text-ink">{{ $value }}</p>
  <p id="{{ $id }}Cap" class="mt-4 text-[11px] text-muted">{{ $caption }}</p>
</div>
