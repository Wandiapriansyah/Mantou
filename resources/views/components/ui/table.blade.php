@props(['headers', 'bodyId', 'minWidth' => null, 'mdMinWidth' => null])

<div class="overflow-x-auto">
  <table @class(['w-full text-left', 'min-w-[560px]' => $mdMinWidth, 'md:min-w-[980px]' => $mdMinWidth]) @if ($minWidth && ! $mdMinWidth) style="min-width:{{ $minWidth }}px" @endif>
    <thead class="bg-canvas text-[12px] text-muted">
      <tr>
        @foreach ($headers as $header)
          @php [$label, $align, $extra] = is_array($header) ? array_pad($header, 3, null) : [$header, 'left', null]; @endphp
          <th @class(['py-3 font-semibold', 'px-5' => $loop->first || $loop->last, 'px-3' => ! $loop->first && ! $loop->last, 'text-right' => $align === 'right', $extra => $extra])>{{ $label }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody id="{{ $bodyId }}">{{ $slot }}</tbody>
  </table>
</div>
