@php
    $features = [
        ['icon' => 'barcode', 'title' => 'Cek harga lewat barcode', 'text' => 'Pindai kemasan atau cari nama produk. Harga yang tampil selalu harga yang ditetapkan pemilik.'],
        ['icon' => 'box', 'title' => 'Produk & stok', 'text' => 'Simpan harga, biaya perolehan, dan persediaan. Stok berkurang otomatis dari penjualan yang dicatat.'],
        ['icon' => 'document', 'title' => 'Catatan transaksi', 'text' => 'Catat penjualan dan pengeluaran harian dengan formulir singkat yang mudah diisi.'],
        ['icon' => 'chart-line', 'title' => 'Laporan keuangan', 'text' => 'Lihat pendapatan, HPP, beban operasional, dan laba bersih tercatat per hari, minggu, atau bulan.'],
        ['icon' => 'calculator', 'title' => 'Analisis harga', 'text' => 'Susun harga jual dari biaya dan target laba, lengkap dengan pembulatan yang Anda pilih.'],
        ['icon' => 'discount', 'title' => 'Rekomendasi diskon', 'text' => 'Kenali stok yang lambat bergerak dan tinjau diskon sambil menjaga sisa margin.'],
    ];

    $steps = [
        ['title' => 'Daftarkan produk', 'text' => 'Masukkan nama, barcode, harga jual, dan stok awal barang di warung.'],
        ['title' => 'Catat setiap hari', 'text' => 'Isi penjualan dan pengeluaran saat terjadi, atau rapikan di akhir hari.'],
        ['title' => 'Baca hasilnya', 'text' => 'Ringkasan, laporan, dan saran harga tersusun dari catatan Anda sendiri.'],
    ];
@endphp

<x-layouts.guest title="Usaha warung yang lebih tertata">
  <header class="sticky top-0 z-30 border-b border-line bg-surface">
    <div class="mx-auto flex h-[68px] max-w-[1120px] items-center justify-between px-5 sm:px-8">
      <a href="/" class="flex items-center gap-2.5">
        <span class="grid size-9 place-items-center rounded-lg bg-brand-dark text-surface"><x-ui.icon name="store" /></span>
        <span class="text-[22px] font-bold tracking-tight">Mantou</span>
      </a>
      <nav class="hidden items-center gap-8 text-[13px] font-semibold text-muted md:flex" aria-label="Navigasi">
        <a href="#fitur" class="hover:text-brand-dark">Fitur</a>
        <a href="#cara-kerja" class="hover:text-brand-dark">Cara kerja</a>
        <a href="#peran" class="hover:text-brand-dark">Pemilik &amp; penjaga</a>
      </nav>
      <x-forms.button href="/login" variant="primary">Masuk</x-forms.button>
    </div>
  </header>

  <main>
    <section class="mx-auto grid max-w-[1120px] items-center gap-12 px-5 pb-16 pt-14 sm:px-8 lg:grid-cols-[1.05fr_1fr] lg:pb-24 lg:pt-20">
      <div>
        <x-ui.badge tone="brand" size="md">Untuk warung dan usaha kecil</x-ui.badge>
        <h1 class="mt-5 text-[40px] font-semibold leading-[1.1] tracking-tight sm:text-[52px]">Usaha kecil, lebih tertata.</h1>
        <p class="mt-5 max-w-[480px] text-[15px] leading-relaxed text-muted">Mantou membantu Anda mengecek harga, mengelola stok, dan mencatat transaksi warung dalam satu tempat, lalu mengubahnya menjadi laporan yang mudah dibaca.</p>
        <div class="mt-8 flex flex-wrap items-center gap-3">
          <x-forms.button href="/login" variant="primary" size="lg" icon="store">Mulai kelola warung</x-forms.button>
          <x-forms.button href="#fitur" size="lg">Lihat fitur</x-forms.button>
        </div>
        <p class="mt-5 flex items-center gap-2 text-[12px] text-muted"><span class="text-brand-dark"><x-ui.icon name="shield-check" size="size-4" /></span>Harga hanya diubah oleh pemilik atau pengguna berizin.</p>
      </div>

      <div class="relative">
        <div class="rounded-2xl border border-line bg-surface p-5">
          <div class="flex items-center justify-between">
            <p class="text-[13px] font-bold">Ringkasan minggu ini</p>
            <x-ui.badge>Contoh tampilan</x-ui.badge>
          </div>
          <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-tint p-4">
              <p class="text-[12px] text-muted">Pendapatan</p>
              <p class="mt-2 text-[22px] font-bold leading-none">Rp2,4 jt</p>
            </div>
            <div class="rounded-xl border border-line p-4">
              <p class="text-[12px] text-muted">Laba kotor</p>
              <p class="mt-2 text-[22px] font-bold leading-none">Rp610 rb</p>
            </div>
          </div>
          <div class="mt-4 flex h-[120px] items-end gap-3 rounded-xl border border-line bg-surface px-4 pb-4 pt-6">
            @foreach ([38, 55, 44, 70, 52, 86, 64] as $height)
              <span class="flex-1 rounded-t-sm {{ $loop->last ? 'bg-brand' : 'bg-brand-soft' }}" style="height:{{ $height }}%"></span>
            @endforeach
          </div>
          <div class="mt-4 flex items-center gap-3 rounded-xl border border-warning bg-warn-bg px-4 py-3">
            <span class="grid size-8 place-items-center rounded-lg bg-surface text-[12px] font-bold text-warn">3</span>
            <p class="text-[12px] font-semibold text-warn">Produk di bawah batas stok minimum</p>
          </div>
        </div>
      </div>
    </section>

    <section id="fitur" class="border-y border-line bg-surface">
      <div class="mx-auto max-w-[1120px] px-5 py-16 sm:px-8 lg:py-20">
        <div class="max-w-[560px]">
          <h2 class="text-[30px] font-semibold tracking-tight">Semua kebutuhan harian warung</h2>
          <p class="mt-3 text-[14px] leading-relaxed text-muted">Dari kasir sampai laporan, tersedia dalam satu aplikasi yang sederhana.</p>
        </div>
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          @foreach ($features as $feature)
            <x-ui.card>
              <span class="grid size-11 place-items-center rounded-lg bg-tint text-brand-dark"><x-ui.icon :name="$feature['icon']" /></span>
              <h3 class="mt-5 text-[16px] font-bold">{{ $feature['title'] }}</h3>
              <p class="mt-2 text-[13px] leading-relaxed text-muted">{{ $feature['text'] }}</p>
            </x-ui.card>
          @endforeach
        </div>
      </div>
    </section>

    <section id="cara-kerja" class="mx-auto max-w-[1120px] px-5 py-16 sm:px-8 lg:py-20">
      <h2 class="text-[30px] font-semibold tracking-tight">Cara kerjanya</h2>
      <div class="mt-10 grid gap-8 md:grid-cols-3">
        @foreach ($steps as $step)
          <div>
            <span class="grid size-10 place-items-center rounded-full bg-brand-dark text-[15px] font-bold text-surface">{{ $loop->iteration }}</span>
            <h3 class="mt-4 text-[16px] font-bold">{{ $step['title'] }}</h3>
            <p class="mt-2 text-[13px] leading-relaxed text-muted">{{ $step['text'] }}</p>
          </div>
        @endforeach
      </div>
    </section>

    <section id="peran" class="mx-auto max-w-[1120px] px-5 pb-16 sm:px-8 lg:pb-20">
      <div class="grid gap-5 md:grid-cols-2">
        <x-ui.card radius="2xl" tone="tint">
          <h3 class="text-[20px] font-bold">Pemilik warung</h3>
          <p class="mt-2 text-[13px] leading-relaxed text-muted">Akses penuh: menetapkan harga, mengelola stok, membaca laporan, dan meninjau analisis serta diskon.</p>
        </x-ui.card>
        <x-ui.card radius="2xl">
          <h3 class="text-[20px] font-bold">Penjaga warung</h3>
          <p class="mt-2 text-[13px] leading-relaxed text-muted">Akses harian: mengecek harga, melihat produk, dan mencatat transaksi tanpa dapat mengubah harga.</p>
        </x-ui.card>
      </div>
    </section>

    <section class="mx-auto max-w-[1120px] px-5 pb-20 sm:px-8">
      <div class="rounded-2xl bg-brand-dark px-8 py-12 text-center text-surface sm:px-12">
        <h2 class="text-[30px] font-semibold tracking-tight">Siap merapikan catatan warung?</h2>
        <p class="mx-auto mt-3 max-w-[460px] text-[14px] leading-relaxed text-surface">Masuk dan mulai dari satu hal kecil: catat penjualan pertama hari ini.</p>
        <x-forms.button href="/login" size="lg" class="mt-7 !border-0 text-brand-dark">Masuk ke Mantou</x-forms.button>
      </div>
    </section>
  </main>

  <footer class="border-t border-line bg-surface">
    <div class="mx-auto flex max-w-[1120px] flex-wrap items-center justify-between gap-3 px-5 py-6 text-[12px] text-muted sm:px-8">
      <span class="flex items-center gap-2 font-bold text-ink"><x-ui.icon name="store" size="size-4" />Mantou</span>
      <span>Pencatatan manual, bukan layanan pembayaran atau akuntansi profesional.</span>
    </div>
  </footer>
</x-layouts.guest>
