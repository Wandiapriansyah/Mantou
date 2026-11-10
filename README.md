# Mantou

**Aplikasi web pengelolaan UMKM: cek harga barcode, produk & stok, catatan transaksi, laporan keuangan, dan analisis bisnis dalam satu sistem.**

Karya Tim **NaWaR as RTRw** untuk **Garda Tech National Innovation Competition (GTNIC) 2026**, kategori Web Development.
Sub-tema: *Accelerating Digital Transformation for Business and SMEs*.

- Website live: mantou.rtrwstudio.online
- Repositori: https://github.com/Wandiapriansyah/Mantou

---

## Latar Belakang

Di banyak warung, penjaga sering tidak tahu harga produk saat pemilik tidak di tempat, sehingga harus menelepon pemilik atau memperkirakan harga. Pencatatan transaksi, stok, dan keuangan juga kerap terpisah, sehingga pemilik sulit mengetahui barang yang laku, sisa stok, dan keuntungan usaha.

Mantou menjawab masalah tersebut dengan sistem yang menyatukan pencarian harga, pengelolaan stok, pencatatan transaksi, dan analisis usaha.

> Pencatatan transaksi pada Mantou berfungsi sebagai dokumentasi usaha, bukan sistem kasir atau pemrosesan pembayaran.

## Fitur Utama

| Fitur | Deskripsi |
|---|---|
| Cek Harga (Barcode) | Pindai barcode dengan kamera atau cari nama produk untuk melihat harga jual. |
| Produk & Stok | Tambah, ubah, dan hapus produk; catat barang masuk; penyesuaian stok; batalkan riwayat stok. |
| Catatan Transaksi | Catat penjualan dan pengeluaran; stok berkurang otomatis saat penjualan; transaksi dapat dibatalkan (void). |
| Laporan Keuangan | Ringkasan pemasukan, pengeluaran, dan keuntungan per periode. |
| Analisis Harga | Rekomendasi harga jual dari harga modal dan margin keuntungan. |
| Analisis Stok & Penjualan | Mengidentifikasi produk cepat dan lambat terjual. |
| Rekomendasi Diskon | Usulan promo untuk produk berpenjualan rendah dengan tetap memperhatikan margin. |
| Hak Akses Berbasis Peran | `admin` (pemilik) mengakses seluruh fitur; `cashier` (penjaga) menggunakan halaman cek harga. |

## Teknologi

- **Backend:** PHP 8.3+, Laravel 13
- **Basis data:** MySQL
- **Frontend:** Blade, Tailwind CSS 4, JavaScript
- **Build tool:** Vite
- **Pemindaian barcode:** ZXing (dimuat di browser)

## Arsitektur Singkat

```
Browser ──► Route (routes/web.php) ──► Middleware (auth, role)
                                         │
                                         ▼
                               Controller ──► Model (Eloquent) ──► MySQL
                                         │
                                         ▼
                                  View (Blade) ──► Respons ke browser
```

Entitas basis data: `users`, `categories`, `products`, `transactions`, `transaction_details`, `stock_movements`, `expenses`, `discounts`. Skema lengkap ada di folder `database/migrations`.

## Struktur Folder

```
app/Http/Controllers/     Logika aplikasi (Auth, Product, Transaction, Cashier, dll.)
app/Http/Middleware/      RoleMiddleware (pembatasan akses admin / cashier)
app/Models/               Model Eloquent
database/migrations/      Skema basis data
database/seeders/         Data awal (akun demo dan kategori)
resources/views/          Tampilan Blade (admin, worker, auth, components)
routes/web.php            Definisi rute
public/                   Aset publik dan entry point
```

## Cara Menjalankan Secara Lokal

### Prasyarat

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- MySQL

### Langkah

```bash
# 1. Kloning repositori
git clone https://github.com/Wandiapriansyah/Mantou.git
cd Mantou

# 2. Pasang dependensi
composer install
npm install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate
```

Buka file `.env`, lalu sesuaikan koneksi basis data:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mantou
DB_USERNAME=root
DB_PASSWORD=
```

Buat basis data kosong bernama `mantou` di MySQL, lalu lanjutkan:

```bash
# 4. Migrasi dan data awal
php artisan migrate --seed

# 5. Build aset dan jalankan
npm run build
php artisan serve
```

Aplikasi dapat dibuka di `http://127.0.0.1:8000`.

## Akun Demo

Akun berikut dibuat oleh seeder untuk keperluan penjurian dan pengujian:

| Peran | Email | Kata sandi |
|---|---|---|
| Admin (pemilik) | `admin@mantou.com` | `admin123` |
| Penjaga (cashier) | `Udydaya@mantou.com` | `jaga123` |

> Akun ini hanya untuk demo. Ganti kata sandi sebelum digunakan pada lingkungan produksi.

## Rute Utama

| Rute | Akses | Fungsi |
|---|---|---|
| `/` | Publik | Halaman landing |
| `/login` | Tamu | Halaman masuk |
| `/dashboard` | Admin | Ringkasan usaha |
| `/produk-stok` | Login | Produk dan stok |
| `/catatan-transaksi` | Admin | Catatan penjualan dan pengeluaran |
| `/cek-harga` | Admin | Cek harga (admin) |
| `/kasir` | Penjaga | Cek harga (penjaga) |
| `/laporan-keuangan` | Admin | Laporan keuangan |
| `/analisis-harga` | Admin | Analisis harga |
| `/analisis-stok-penjualan` | Admin | Analisis stok dan penjualan |
| `/rekomendasi-diskon` | Admin | Rekomendasi diskon |

## Status Pengembangan

- **Terhubung ke basis data:** login dan peran, Produk & Stok, Catatan Transaksi, dan Cek Harga barcode.
- **Menggunakan data contoh di browser:** Dashboard, Laporan Keuangan, Analisis Harga, Analisis Stok & Penjualan, dan Rekomendasi Diskon saat ini membaca data contoh dari `public/mantou-store.js` (tersimpan di localStorage), belum dari basis data.
- **Belum selesai:** modul pengelolaan pengguna, kategori, dan diskon (rute sudah ada, tampilan belum tersedia).

## Rencana Pengembangan

1. Menghubungkan seluruh laporan dan analisis ke basis data.
2. Dukungan multi-cabang dan multi-usaha dengan dashboard terpusat.
3. Integrasi pembayaran digital (QRIS) untuk menyelaraskan data pembayaran dengan catatan keuangan.
4. Rekomendasi diskon yang lebih adaptif berdasarkan sisa stok, lama penyimpanan, dan kecepatan penjualan.

## Tim

**Tim NaWaR as RTRw**

1. Wandi Apriansyah
2. Nurkalam Abdurrahman
3. Muhammad Rafli

## Lisensi

Dibuat untuk keperluan kompetisi GTNIC 2026. Kerangka dasar menggunakan [Laravel](https://laravel.com), berlisensi MIT.