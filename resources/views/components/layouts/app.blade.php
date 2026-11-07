@props(['title', 'active' => null, 'role' => 'admin'])

@php
    $home = $role === 'worker' ? '/cek-harga' : '/';

    $menu = collect([
        ['key' => 'index', 'group' => 'Kelola warung', 'href' => '/', 'label' => 'Ringkasan', 'short' => 'Ringkasan', 'icon' => 'dashboard', 'roles' => ['admin']],
        ['key' => 'cek-harga', 'group' => 'Kelola warung', 'href' => '/cek-harga', 'label' => 'Cek harga', 'short' => 'Cek harga', 'icon' => 'barcode', 'roles' => ['admin', 'worker']],
        ['key' => 'produk-stok', 'group' => 'Kelola warung', 'href' => '/produk-stok', 'label' => 'Produk & stok', 'short' => 'Produk', 'icon' => 'box', 'roles' => ['admin', 'worker']],
        ['key' => 'catatan-transaksi', 'group' => 'Kelola warung', 'href' => '/catatan-transaksi', 'label' => 'Catatan transaksi', 'short' => 'Transaksi', 'icon' => 'document', 'roles' => ['admin', 'worker']],
        ['key' => 'laporan-keuangan', 'group' => 'Kelola warung', 'href' => '/laporan-keuangan', 'label' => 'Laporan keuangan', 'short' => 'Laporan', 'icon' => 'chart-line', 'roles' => ['admin']],
        ['key' => 'analisis-harga', 'group' => 'Wawasan usaha', 'href' => '/analisis-harga', 'label' => 'Analisis harga', 'short' => 'Harga', 'icon' => 'calculator', 'roles' => ['admin']],
        ['key' => 'analisis-stok-penjualan', 'group' => 'Wawasan usaha', 'href' => '/analisis-stok-penjualan', 'label' => 'Analisis stok & penjualan', 'short' => 'Penjualan', 'icon' => 'bars', 'roles' => ['admin']],
        ['key' => 'rekomendasi-diskon', 'group' => 'Wawasan usaha', 'href' => '/rekomendasi-diskon', 'label' => 'Rekomendasi diskon', 'short' => 'Diskon', 'icon' => 'discount', 'roles' => ['admin']],
    ])->filter(fn ($item) => in_array($role, $item['roles']))->values();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $title }} · Mantou</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.1.4"></script>
<style type="text/tailwindcss">
  @theme {
    --font-sans: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
    --color-canvas: #f6f5ef;
    --color-surface: #fffefa;
    --color-line: #e1e5dc;
    --color-tint: #e8f0e8;
    --color-brand: #245c45;
    --color-brand-dark: #203f32;
    --color-brand-soft: #7fa68a;
    --color-ink: #1f3a2d;
    --color-muted: #6b7a70;
    --color-warn-bg: #fbf0d9;
    --color-warn: #9a6a1f;
  }
</style>
@stack('styles')
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
