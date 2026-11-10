@props(['title', 'active' => null, 'role' => 'admin'])

@php
    // 'cashier' diperlakukan sama dengan 'worker' (penjaga) agar nilai role tidak saling bertentangan
    $role = $role === 'cashier' ? 'worker' : $role;
    $home = $role === 'worker' ? '/cek-harga' : '/dashboard';

    // identitas dari akun yang sedang masuk; tanpa nilai bawaan jika data tidak tersedia
    $authUser = auth()->user();
    $userName = trim((string) data_get($authUser, 'name', ''));
    $warungName = trim((string) (data_get($authUser, 'warung_name') ?? data_get($authUser, 'store_name') ?? ''));
    $userTitle = $role === 'worker' ? 'Penjaga warung' : 'Pemilik warung';

    $menu = collect([
        ['key' => 'index', 'group' => 'Kelola warung', 'href' => '/dashboard', 'label' => 'Ringkasan', 'short' => 'Ringkasan', 'icon' => 'dashboard', 'desc' => 'Kondisi usaha sekilas dari catatan Anda', 'primary' => true, 'roles' => ['admin']],
        ['key' => 'cek-harga', 'group' => 'Kelola warung', 'href' => '/cek-harga', 'label' => 'Cek harga', 'short' => 'Cek harga', 'icon' => 'barcode', 'desc' => 'Lihat harga produk, tanpa mencatat penjualan', 'primary' => true, 'roles' => ['admin', 'worker']],
        ['key' => 'produk-stok', 'group' => 'Kelola warung', 'href' => '/produk-stok', 'label' => 'Produk & stok', 'short' => 'Produk', 'icon' => 'box', 'desc' => 'Harga tersimpan dan persediaan barang', 'primary' => true, 'roles' => ['admin']],
        ['key' => 'catatan-transaksi', 'group' => 'Kelola warung', 'href' => '/catatan-transaksi', 'label' => 'Catatan transaksi', 'short' => 'Transaksi', 'icon' => 'document', 'desc' => 'Catat penjualan dan pengeluaran harian', 'primary' => true, 'roles' => ['admin']],
        ['key' => 'laporan-keuangan', 'group' => 'Kelola warung', 'href' => '/laporan-keuangan', 'label' => 'Laporan keuangan', 'short' => 'Laporan', 'icon' => 'chart-line', 'desc' => 'Pendapatan, HPP, beban, dan laba bersih', 'primary' => false, 'roles' => ['admin']],
        ['key' => 'analisis-harga', 'group' => 'Wawasan usaha', 'href' => '/analisis-harga', 'label' => 'Analisis harga', 'short' => 'Harga', 'icon' => 'calculator', 'desc' => 'Hitung harga jual dari modal dan target laba', 'primary' => false, 'roles' => ['admin']],
        ['key' => 'analisis-stok-penjualan', 'group' => 'Wawasan usaha', 'href' => '/analisis-stok-penjualan', 'label' => 'Analisis stok & penjualan', 'short' => 'Penjualan', 'icon' => 'bars', 'desc' => 'Produk cepat dan lambat laku', 'primary' => false, 'roles' => ['admin']],
        ['key' => 'rekomendasi-diskon', 'group' => 'Wawasan usaha', 'href' => '/rekomendasi-diskon', 'label' => 'Rekomendasi diskon', 'short' => 'Diskon', 'icon' => 'discount', 'desc' => 'Saran diskon untuk stok yang lambat laku', 'primary' => false, 'roles' => ['admin']],
    ])->filter(fn ($item) => in_array($role, $item['roles']))->values();

    // penjaga hanya melihat menu berperan 'worker' (saat ini: Cek harga); susunan bottom nav diatur di footer
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
<x-layouts.head :title="$title" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
  // pulihkan preferensi sidebar sebelum render pertama agar tidak berkedip
  (function () {
    var v = 'expanded';
    try { v = localStorage.getItem('mantou.sidebar') === 'collapsed' ? 'collapsed' : 'expanded'; } catch (e) {}
    document.documentElement.setAttribute('data-sidebar', v);
  })();
</script>
<style>
  :root { --sidebar-open: 272px; --sidebar-rail: 72px; }
  #sidebar { width: var(--sidebar-open); transition: width .15s ease; }
  html[data-sidebar="collapsed"] #sidebar { width: var(--sidebar-rail); }
  /* label disembunyikan secara visual saja, tetap terbaca pembaca layar */
  html[data-sidebar="collapsed"] #sidebar .sb-label { position: absolute; width: 1px; height: 1px; margin: -1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
  html[data-sidebar="collapsed"] #sidebar .sb-hide { display: none; }
  html[data-sidebar="collapsed"] #sidebar .sb-center { justify-content: center; padding-left: 0; padding-right: 0; }
  html[data-sidebar="collapsed"] #sidebar .sb-group { margin: .75rem .5rem .25rem; padding: 0; border-top: 1px solid var(--color-line); }
  html[data-sidebar="collapsed"] #sidebar .sb-group:first-child { display: none; }
  html[data-sidebar="collapsed"] #sidebar .sb-stack { flex-direction: column; gap: .75rem; }
  html[data-sidebar="collapsed"] #sidebar .sb-active-bar { right: 4px; }
  @media (prefers-reduced-motion: reduce) { #sidebar { transition: none; } }
</style>
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
<div class="flex min-h-screen">
  <x-layouts.sidebar :menu="$menu" :active="$active" :role="$role" :home="$home" :user-name="$userName" :user-title="$userTitle" :warung-name="$warungName" />
  <div class="min-w-0 flex-1">
    <x-layouts.navbar :title="$title" :group="$menu->firstWhere('key', $active)['group'] ?? null" />
    <main class="px-5 pb-28 pt-8 sm:px-8 lg:pb-8">
      {{ $slot }}
    </main>
  </div>
</div>
<x-layouts.footer :menu="$menu" :active="$active" :role="$role" :user-name="$userName" :user-title="$userTitle" />
<x-ui.alert />
<script src="{{ asset('mantou-store.js') }}"></script>
<script>
  try {
    if (window.Mantou && typeof window.Mantou.setUser === 'function') window.Mantou.setUser(@json(['name' => $userName, 'title' => $userTitle, 'warung' => $warungName]));
  } catch (e) { if (window.console) console.error(e); }
</script>
<script>
  // toggle sidebar desktop (collapse/expand), preferensi tersimpan di browser
  (function () {
    var root = document.documentElement, btn = document.getElementById('sidebarToggle');
    if (!btn) return;
    function sync() {
      var open = root.getAttribute('data-sidebar') !== 'collapsed';
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.setAttribute('aria-label', open ? 'Ciutkan sidebar' : 'Buka sidebar');
      btn.setAttribute('title', open ? 'Ciutkan sidebar' : 'Buka sidebar');
    }
    btn.addEventListener('click', function () {
      var next = root.getAttribute('data-sidebar') === 'collapsed' ? 'expanded' : 'collapsed';
      root.setAttribute('data-sidebar', next);
      try { localStorage.setItem('mantou.sidebar', next); } catch (e) {}
      sync();
    });
    window.addEventListener('storage', function (e) {
      if (e.key === 'mantou.sidebar') { root.setAttribute('data-sidebar', e.newValue === 'collapsed' ? 'collapsed' : 'expanded'); sync(); }
    });
    sync();
  })();
</script>
<script>
  (function () {
    var timer;
    window.toast = function (message) {
      var el = document.getElementById('toast');
      el.textContent = message;
      el.style.opacity = 1;
      clearTimeout(timer);
      timer = setTimeout(function () { el.style.opacity = 0; }, 3000);
    };
  })();
</script>
@stack('scripts')
</body>
</html>