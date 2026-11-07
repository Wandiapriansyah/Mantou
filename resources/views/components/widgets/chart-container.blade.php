@props(['height' => 200, 'compact' => false, 'alignEnd' => true, 'emptyText'])

<div class="mt-6 flex gap-3">
  <div id="yAxis" @class(['flex flex-col justify-between text-muted', 'text-right text-[10px]' => $compact, 'w-12 shrink-0 text-[11px]' => ! $compact]) style="height:{{ $height }}px"></div>
  <div class="min-w-0 flex-1">
    <div class="relative" style="height:{{ $height }}px">
      <div class="absolute inset-0 flex flex-col justify-between" aria-hidden="true">
        <span class="border-t border-line"></span><span class="border-t border-line"></span><span class="border-t border-line"></span><span class="border-t border-line"></span>
      </div>
      <div id="bars" @class(['relative flex h-full px-2', 'items-end' => $alignEnd])></div>
      <p id="chartEmpty" class="absolute inset-0 hidden place-items-center text-[12px] text-muted">{{ $emptyText }}</p>
    </div>
    <div id="xAxis" @class(['flex px-2 text-muted', 'mt-2 text-[10px]' => $compact, 'mt-3 text-[11px]' => ! $compact])></div>
  </div>
</div>
