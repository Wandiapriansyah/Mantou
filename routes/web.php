<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\CashierController;

Route::get('/', function () {
    return view('admin.index');
});
Route::get('cek-harga', function () {
    return view('admin.cek-harga');
});
Route::get('analisis-harga', function () {
    return view('admin.analisis-harga');
});
Route::get('analisis-stok-penjualan', function () {
    return view('admin.analisis-stok-penjualan');
});
Route::get('catatan-transaksi', function () {
    return view('admin.catatan-transaksi');
});
Route::get('laporan-keuangan', function () {
    return view('admin.laporan-keuangan');
});
Route::get('produk-stok', function () {
    return view('admin.produk-stok');
});
Route::get('rekomendasi-diskon', function () {
    return view('admin.rekomendasi-diskon');
});



Route::middleware(['auth'])->group(function () {
    
    // CRUD Otomatis via Resource Route untuk Admin
    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('discounts', DiscountController::class);
    Route::resource('transaksi', TransaksiController::class)->only(['index', 'show', 'destroy']);

    // Route Operasional Kasir
    Route::get('/kasir', [CashierController::class, 'index'])->name('kasir.index');
    Route::post('/kasir/search-barcode', [CashierController::class, 'searchByBarcode']);
    Route::post('/kasir/transaction', [CashierController::class, 'storeTransaction']);

});
