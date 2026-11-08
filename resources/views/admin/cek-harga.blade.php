<x-layouts.app title="Cek harga" active="cek-harga" role="admin">
  <x-layouts.page-header title="Cek harga" subtitle="Temukan harga yang ditetapkan pemilik, cukup dengan barcode produk." />

  <x-ui.alert variant="info" class="mt-6" icon="barcode" title="Cek harga bukan pencatatan penjualan">Memindai produk hanya menampilkan harga. Untuk mencatat penjualan dan mengurangi stok, buka <a href="/catatan-transaksi" class="font-semibold text-brand-dark underline">Catatan transaksi</a>.</x-ui.alert>

  <div class="mt-5 grid items-start gap-5 xl:grid-cols-[1.45fr_1fr]">
    <x-widgets.barcode-scanner />
    <x-widgets.price-check-result />
  </div>

  <p class="mt-6 flex items-center gap-2 text-[12px] text-muted">
    <span><x-ui.icon name="shield-check" size="size-4" /></span>Penjaga warung dapat mengecek harga. Perubahan harga hanya untuk pemilik atau pengguna yang diberi izin.
  </p>
</x-layouts.app>
