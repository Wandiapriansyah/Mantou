@props(['headers', 'bodyId', 'minWidth' => null])

<div class="overflow-x-auto">
  <table class="w-full text-left" @if ($minWidth) style="min-width:{{ $minWidth }}px" @endif>
    <thead class="bg-canvas text-[12px] text-muted">
      <tr>
        @foreach ($headers as $header)
          @php [$label, $align] = is_array($header) ? $header : [$header, 'left']; @endphp
          <th @class(['py-3 font-semibold', 'px-5' => $loop->first || $loop->last, 'px-3' => ! $loop->first && ! $loop->last, 'text-right' => $align === 'right'])>{{ $label }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody id="{{ $bodyId }}">{{ $slot }}</tbody>
  </table>
</div>
