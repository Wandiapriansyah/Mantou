@props(['id', 'label' => null, 'options' => [], 'selected' => null, 'size' => 'sm'])

@php
    $sizes = [
        'sm' => 'px-3.5 py-2.5 text-[13px]',
        'md' => 'px-3.5 py-3 text-[14px]',
    ];
@endphp

@if ($label)
  <label for="{{ $id }}" class="text-[12px] font-bold">{{ $label }}</label>
@endif
<select id="{{ $id }}" {{ $attributes->class(['rounded-lg border border-line bg-surface outline-none focus:border-brand', $sizes[$size], 'mt-2' => $label]) }}>
  @foreach ($options as $value => $text)
    <option value="{{ $value }}" @selected((string) $value === (string) $selected)>{{ $text }}</option>
  @endforeach
</select>
