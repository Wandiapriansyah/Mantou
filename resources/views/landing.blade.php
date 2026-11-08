@php
    $questions = [
        ['question' => 'Berapa harga produk ini?', 'answer' => 'Pindai barcode atau cari nama produk. Harga yang tampil adalah harga yang ditetapkan pemilik.'],
        ['question' => 'Stok apa yang perlu ditambah?', 'answer' => 'Stok berkurang dari penjualan yang dicatat, dan produk di bawah batas minimum ditandai.'],
        ['question' => 'Berapa laba yang tercatat?', 'answer' => 'Pendapatan, modal barang terjual, dan biaya operasional dirangkum menjadi laba bersih tercatat.'],
    ];

    $features = [
        ['title' => 'Cek harga', 'text' => 'Cari produk lewat barcode kamera atau nama. Cek harga hanya menampilkan harga dan tidak mencatat penjualan.'],
        ['title' => 'Produk & stok', 'text' => 'Simpan harga jual, biaya perolehan, dan persediaan. Catat barang masuk dan penyesuaian stok.'],
        ['title' => 'Catatan transaksi', 'text' => 'Catat penjualan dan pengeluaran harian. Total dan dampak ke stok terlihat sebelum disimpan.'],
        ['title' => 'Laporan keuangan', 'text' => 'Lihat pendapatan, harga pokok terjual, beban operasional, dan laba bersih tercatat per periode.'],
        ['title' => 'Analisis harga', 'text' => 'Hitung harga jual dari biaya dan target laba, lengkap dengan pembulatan yang Anda pilih.'],
        ['title' => 'Analisis stok & rekomendasi diskon', 'text' => 'Kenali produk yang cepat dan lambat terjual, lalu tinjau diskon sambil memeriksa sisa margin.'],
    ];
@endphp

<x-layouts.guest title="Kelola harga, stok, dan catatan warung">
  <header class="border-b border-line bg-surface">
    <div class="mx-auto flex h-16 max-w-[1200px] items-center justify-between px-5 md:px-10">
      <a href="/" class="flex items-center gap-2.5">
        <span class="grid size-8 place-items-center rounded-lg bg-brand-dark text-surface"><x-ui.icon name="store" size="size-[18px]" /></span>
        <span class="text-[20px] font-semibold tracking-tight">Mantou</span>
      </a>
      <nav class="flex items-center gap-8" aria-label="Navigasi utama">
        <a href="#fitur" class="hidden text-[14px] font-medium text-muted hover:text-ink md:inline">Fitur</a>
        <a href="#peran" class="hidden text-[14px] font-medium text-muted hover:text-ink md:inline">Pemilik &amp; penjaga</a>
        <x-forms.button href="/login" variant="primary">Masuk</x-forms.button>
      </nav>
    </div>
  </header>

  <main>
    <section class="mx-auto max-w-[1200px] px-5 pb-20 pt-16 md:px-10 md:pb-28 md:pt-28" aria-labelledby="judul-utama">
      <p class="text-[14px] font-medium text-brand-dark">Aplikasi pencatatan untuk warung</p>
      <h1 id="judul-utama" class="mt-5 max-w-[760px] text-[40px] font-semibold leading-[1.08] tracking-tight md:text-[64px]">Kelola harga, stok, dan catatan warung dalam satu tempat.</h1>
      <p class="mt-6 max-w-[560px] text-[17px] leading-relaxed text-muted md:mt-8 md:text-[19px]">Mantou membantu pemilik dan penjaga warung mengecek harga, mencatat penjualan dan pengeluaran, memantau stok, dan membaca laba dari catatan harian.</p>
      <div class="mt-9 flex flex-wrap items-center gap-x-8 gap-y-4 md:mt-11">
        <x-forms.button href="/login" variant="primary" size="lg">Masuk ke Mantou</x-forms.button>
        <a href="#fitur" class="text-[15px] font-semibold text-brand-dark hover:underline">Lihat fitur</a>
      </div>
    </section>

    <section class="border-t border-line" aria-labelledby="judul-nilai">
      <div class="mx-auto max-w-[1200px] px-5 py-16 md:px-10 md:py-28">
        <h2 id="judul-nilai" class="max-w-[640px] text-[30px] font-semibold leading-tight tracking-tight md:text-[44px]">Tiga pertanyaan harian, dijawab dari catatan Anda.</h2>
        <div class="mt-12 grid gap-10 md:mt-16 md:grid-cols-3 md:gap-12">
          @foreach ($questions as $item)
            <div>
              <h3 class="text-[20px] font-semibold leading-snug md:text-[22px]">{{ $item['question'] }}</h3>
              <p class="mt-3 text-[16px] leading-relaxed text-muted">{{ $item['answer'] }}</p>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section id="fitur" class="border-t border-line" aria-labelledby="judul-fitur">
      <div class="mx-auto grid max-w-[1200px] gap-10 px-5 py-16 md:grid-cols-[4fr_8fr] md:gap-16 md:px-10 md:py-28">
        <div>
          <h2 id="judul-fitur" class="text-[30px] font-semibold leading-tight tracking-tight md:text-[44px]">Fitur yang tersedia</h2>
          <p class="mt-4 max-w-[320px] text-[16px] leading-relaxed text-muted">Setiap fitur mengikuti pekerjaan harian di warung.</p>
        </div>
        <ul class="border-t border-line">
          @foreach ($features as $feature)
            <li class="grid gap-2 border-b border-line py-6 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)] lg:gap-10 lg:py-8">
              <h3 class="text-[19px] font-semibold md:text-[21px]">{{ $feature['title'] }}</h3>
              <p class="text-[16px] leading-relaxed text-muted">{{ $feature['text'] }}</p>
            </li>
          @endforeach
        </ul>
      </div>
    </section>

    <section id="peran" class="border-t border-line" aria-labelledby="judul-peran">
      <div class="mx-auto max-w-[1200px] px-5 py-16 md:px-10 md:py-28">
        <h2 id="judul-peran" class="max-w-[640px] text-[30px] font-semibold leading-tight tracking-tight md:text-[44px]">Satu aplikasi, dua peran.</h2>
        <div class="mt-12 grid gap-10 md:mt-16 md:grid-cols-2 md:gap-16">
          <div>
            <h3 class="text-[20px] font-semibold md:text-[22px]">Pemilik warung</h3>
            <p class="mt-3 max-w-[460px] text-[16px] leading-relaxed text-muted">Menetapkan harga, mengelola produk dan stok, membaca laporan keuangan, serta meninjau analisis harga dan rekomendasi diskon.</p>
          </div>
          <div>
            <h3 class="text-[20px] font-semibold md:text-[22px]">Penjaga warung</h3>
            <p class="mt-3 max-w-[460px] text-[16px] leading-relaxed text-muted">Mengecek harga dan mencatat transaksi harian. Perubahan harga hanya dapat dilakukan pemilik atau pengguna yang diberi izin.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="border-t border-line" aria-labelledby="judul-akhir">
      <div class="mx-auto max-w-[1200px] px-5 py-16 md:px-10 md:py-28">
        <h2 id="judul-akhir" class="max-w-[640px] text-[30px] font-semibold leading-tight tracking-tight md:text-[44px]">Mulai dari catatan pertama hari ini.</h2>
        <x-forms.button href="/login" variant="primary" size="lg" class="mt-8">Masuk ke Mantou</x-forms.button>
      </div>
    </section>
  </main>

  <footer class="border-t border-line">
    <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-5 py-8 text-[13px] text-muted md:px-10">
      <span class="font-semibold text-ink">Mantou</span>
      <span>Pencatatan manual, bukan layanan pembayaran atau akuntansi profesional.</span>
    </div>
  </footer>
</x-layouts.guest>
