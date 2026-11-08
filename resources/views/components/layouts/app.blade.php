@props(['title', 'active' => null, 'role' => 'admin'])

@php
    $home = $role === 'worker' ? '/cek-harga' : '/dasboard';

    $menu = collect([
        ['key' => 'index', 'group' => 'Kelola warung', 'href' => '/dasboard', 'label' => 'Ringkasan', 'short' => 'Ringkasan', 'icon' => 'dashboard', 'desc' => 'Kondisi usaha sekilas dari catatan Anda', 'primary' => true, 'roles' => ['admin']],
        ['key' => 'cek-harga', 'group' => 'Kelola warung', 'href' => '/cek-harga', 'label' => 'Cek harga', 'short' => 'Cek harga', 'icon' => 'barcode', 'desc' => 'Lihat harga produk, tanpa mencatat penjualan', 'primary' => true, 'roles' => ['admin', 'worker']],
        ['key' => 'produk-stok', 'group' => 'Kelola warung', 'href' => '/produk-stok', 'label' => 'Produk & stok', 'short' => 'Produk', 'icon' => 'box', 'desc' => 'Harga tersimpan dan persediaan barang', 'primary' => true, 'roles' => ['admin', 'worker']],
        ['key' => 'catatan-transaksi', 'group' => 'Kelola warung', 'href' => '/catatan-transaksi', 'label' => 'Catatan transaksi', 'short' => 'Transaksi', 'icon' => 'document', 'desc' => 'Catat penjualan dan pengeluaran harian', 'primary' => true, 'roles' => ['admin', 'worker']],
        ['key' => 'laporan-keuangan', 'group' => 'Kelola warung', 'href' => '/laporan-keuangan', 'label' => 'Laporan keuangan', 'short' => 'Laporan', 'icon' => 'chart-line', 'desc' => 'Pendapatan, HPP, beban, dan laba bersih', 'primary' => false, 'roles' => ['admin']],
        ['key' => 'analisis-harga', 'group' => 'Wawasan usaha', 'href' => '/analisis-harga', 'label' => 'Analisis harga', 'short' => 'Harga', 'icon' => 'calculator', 'desc' => 'Hitung harga jual dari modal dan target laba', 'primary' => false, 'roles' => ['admin']],
        ['key' => 'analisis-stok-penjualan', 'group' => 'Wawasan usaha', 'href' => '/analisis-stok-penjualan', 'label' => 'Analisis stok & penjualan', 'short' => 'Penjualan', 'icon' => 'bars', 'desc' => 'Produk cepat dan lambat laku', 'primary' => false, 'roles' => ['admin']],
        ['key' => 'rekomendasi-diskon', 'group' => 'Wawasan usaha', 'href' => '/rekomendasi-diskon', 'label' => 'Rekomendasi diskon', 'short' => 'Diskon', 'icon' => 'discount', 'desc' => 'Saran diskon untuk stok yang lambat laku', 'primary' => false, 'roles' => ['admin']],
    ])->filter(fn ($item) => in_array($role, $item['roles']))->values();

    if ($role === 'worker') {
        $workerOrder = ['cek-harga', 'catatan-transaksi', 'produk-stok'];
        $menu = $menu->map(fn ($item) => [...$item, 'primary' => true])->sortBy(fn ($item) => array_search($item['key'], $workerOrder))->values();
    }
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
<x-layouts.head :title="$title" />
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">
<div class="flex min-h-screen">
  <x-layouts.sidebar :menu="$menu" :active="$active" :role="$role" :home="$home" />
  <div class="min-w-0 flex-1">
    <x-layouts.navbar :title="$title" :home="$home" />
    <main class="px-5 pb-28 pt-8 sm:px-8 lg:pb-8">
      {{ $slot }}
    </main>
  </div>
</div>
<x-layouts.footer :menu="$menu" :active="$active" />
<x-ui.alert />
<script src="{{ asset('mantou-store.js') }}"></script>
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
