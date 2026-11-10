@props(['menu', 'active', 'role' => null, 'userName' => '', 'userTitle' => ''])

@php
    // ---- Konfigurasi key (identifikasi menu berdasarkan key, bukan urutan array) ----
    $ctaKey = 'cek-harga';
    $adminLeft = ['index', 'produk-stok'];
    $adminRight = ['catatan-transaksi'];
    $guardRoles = ['worker', 'cashier']; // nilai $role penjaga (layout menormalkan ke 'worker')
    $logoutUrl = '/logout'; // endpoint yang sama dengan sidebar desktop

    // Identitas akun (sama seperti sidebar): inisial dari nama, cadangan huruf pertama peran.
    $initials = collect(preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: mb_strtoupper(mb_substr($userTitle, 0, 1));
    $firstName = trim(strtok(trim($userName), ' ') ?: '');

    // ---- Role dikirim dari layout (app.blade.php) lewat prop `role` ----
    $role = $role ?? 'admin';
    $isGuard = in_array($role, $guardRoles, true);

    $cta = $menu->firstWhere('key', $ctaKey);
    $byKeys = fn (array $keys) => $menu->filter(fn ($i) => in_array($i['key'], $keys, true))
        ->sortBy(fn ($i) => array_search($i['key'], $keys, true))->values();

    if ($isGuard) {
        $left = collect(); // sisi kiri penjaga diisi blok Akun (di bawah), bukan item menu
        $right = collect();
        $more = collect();
    } else {
        $left = $byKeys($adminLeft);
        $right = $byKeys($adminRight);
        // Menu tambahan admin (sudah lolos permission di $menu) masuk dropdown Lainnya.
        $placed = array_merge([$ctaKey], $adminLeft, $adminRight);
        $more = $menu->reject(fn ($i) => in_array($i['key'], $placed, true))->values();
    }

    $showMore = ! $isGuard; // admin: Lainnya (berisi Logout); penjaga: tanpa dropdown
    $moreActive = $more->contains('key', $active);
    $ctaCurrent = $cta && $cta['key'] === $active;

    // Ukuran adaptif: 320px lebih ringkas, >=360px normal, tablet sedikit lebih besar.
    $tab = 'flex min-w-0 max-w-[4.75rem] flex-1 flex-col items-center gap-1 px-0.5 pb-1.5 pt-2 text-[10px] leading-tight min-[360px]:text-[11px] sm:max-w-[5.5rem] sm:text-xs';
    $pill = 'grid h-7 w-10 place-items-center rounded-full transition min-[360px]:w-12';
    $zones = [
        'left' => ['items' => $left, 'class' => 'justify-start pl-1'],
        'center' => ['items' => collect(), 'class' => ''],
        'right' => ['items' => $right, 'class' => 'justify-end pr-1'],
    ];
@endphp

<nav id="bottomNav" class="fixed bottom-0 left-0 z-40 w-full border-t border-line bg-surface pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Menu">
  {{-- Isi dibatasi lebarnya dan dipusatkan, supaya di tablet menu tetap dekat dengan Cek harga --}}
  <div class="mx-auto grid max-w-lg grid-cols-3">
  @foreach ($zones as $zone => $cfg)
    @if ($zone === 'center')
      {{-- Area tengah: tombol absolut di 50% viewport, tidak bergantung jumlah menu kiri/kanan --}}
      <div class="relative min-w-0">
        @if ($cta)
          <a href="{{ $cta['href'] }}" @if ($ctaCurrent) aria-current="page" @endif
             class="absolute bottom-2 left-1/2 z-10 flex h-16 min-w-[88px] -translate-x-1/2 flex-col items-center justify-center gap-1 rounded-2xl bg-brand px-4 text-[11px] font-bold leading-tight text-white shadow-lg ring-4 ring-surface transition active:scale-95 {{ $ctaCurrent ? 'brightness-90' : '' }}">
            <x-ui.icon :name="$cta['icon'] ?? 'scan'" />
            <span class="whitespace-nowrap">{{ $cta['short'] ?? $cta['label'] }}</span>
          </a>
        @endif
      </div>
    @else
      <div class="flex min-w-0 {{ $cfg['class'] }}">
        @foreach ($cfg['items'] as $item)
          @php $current = $item['key'] === $active; @endphp
          <a href="{{ $item['href'] }}" @if ($current) aria-current="page" @endif @class([$tab, 'font-bold text-brand-dark' => $current, 'font-medium text-muted' => ! $current])>
            <span @class([$pill, 'bg-tint text-brand-dark' => $current])>
              <x-ui.icon :name="$item['icon']" />
            </span>
            <span class="max-w-full truncate whitespace-nowrap">{{ $item['short'] }}</span>
          </a>
        @endforeach

        @if ($zone === 'left' && $isGuard)
          {{-- Penjaga: Akun. Belum ada halaman akun, jadi hanya menampilkan identitas (bukan link). --}}
          <div @class([$tab, 'font-medium text-muted']) role="group" aria-label="Akun{{ $userName !== '' ? ': '.$userName : '' }}">
            <span class="grid h-7 w-10 place-items-center min-[360px]:w-12">
              <span class="grid size-7 place-items-center rounded-full bg-line text-[11px] font-bold text-ink" aria-hidden="true">{{ $initials }}</span>
            </span>
            <span class="max-w-full truncate whitespace-nowrap">{{ $firstName !== '' ? $firstName : 'Akun' }}</span>
          </div>
        @endif

        @if ($zone === 'right')
          @if ($showMore)
            <button type="button" id="moreToggle" aria-expanded="false" aria-controls="moreMenu" aria-haspopup="true" @class([$tab, 'font-bold text-brand-dark' => $moreActive, 'font-medium text-muted' => ! $moreActive])>
              <span @class([$pill, 'bg-tint text-brand-dark' => $moreActive])>
                <x-ui.icon name="list" />
              </span>
              <span class="max-w-full truncate whitespace-nowrap">Lainnya</span>
            </button>
          @else
            {{-- Penjaga: Logout langsung terlihat, POST + CSRF --}}
            <form method="POST" action="{{ $logoutUrl }}" class="flex min-w-0 max-w-[4.75rem] flex-1 sm:max-w-[5.5rem]">
              @csrf
              <button type="submit" class="flex w-full min-w-0 flex-col items-center gap-1 px-0.5 pb-1.5 pt-2 text-[10px] font-medium leading-tight text-muted min-[360px]:text-[11px] sm:text-xs">
                <span class="{{ $pill }}">
                  <x-ui.icon name="logout" />
                </span>
                <span class="max-w-full truncate whitespace-nowrap">Logout</span>
              </button>
            </form>
          @endif
        @endif
      </div>
    @endif
  @endforeach
  </div>
</nav>

@if ($showMore)
  <div id="moreMenu" class="fixed inset-x-3 bottom-[calc(76px+env(safe-area-inset-bottom))] z-40 mx-auto hidden max-w-lg rounded-xl border border-line bg-surface p-2 shadow-lg lg:hidden">
    @foreach ($more as $item)
      <a href="{{ $item['href'] }}" @if ($item['key'] === $active) aria-current="page" @endif @class(['flex items-center gap-3 rounded-lg px-3 py-3', 'bg-tint text-brand-dark' => $item['key'] === $active])>
        <x-ui.icon :name="$item['icon']" />
        <span>
          <span class="block text-[13px] font-bold text-ink">{{ $item['label'] }}</span>
          <span class="block text-[12px] text-muted">{{ $item['desc'] }}</span>
        </span>
      </a>
    @endforeach

    <form method="POST" action="{{ $logoutUrl }}" @class(['mt-2 border-t border-line pt-2' => $more->isNotEmpty()])>
      @csrf
      <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-left">
        <x-ui.icon name="logout" />
        <span class="block text-[13px] font-bold text-ink">Logout</span>
      </button>
    </form>
  </div>
  <script>
    (function () {
      var toggle = document.getElementById('moreToggle');
      var menu = document.getElementById('moreMenu');
      if (!toggle || !menu) return;
      function setOpen(open) {
        menu.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      }
      toggle.addEventListener('click', function (e) { e.stopPropagation(); setOpen(menu.classList.contains('hidden')); });
      document.addEventListener('click', function (e) { if (!menu.contains(e.target)) setOpen(false); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
    })();
  </script>
@endif