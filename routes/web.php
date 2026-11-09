<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TransactionController;

Route::get('/', function () {
    return view('landing');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dash'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('discounts', DiscountController::class);
        Route::resource('transaksi', TransactionController::class)->only(['index', 'show', 'destroy']);

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

        Route::get('rekomendasi-diskon', function () {
            return view('admin.rekomendasi-diskon');
        });
    });
    Route::middleware('role:cashier')->group(function () {
        Route::get('/kasir', [CashierController::class, 'index'])->name('kasir.index');
        Route::post('/kasir/search-barcode', [CashierController::class,'searchByBarcode']);
        Route::post('/kasir/transaction', [CashierController::class, 'storeTransaction']);
    });

    Route::get('/produk-stok', [ProductController::class, 'index'])->name('produk-stok');
    Route::post('/produk-stok', [ProductController::class, 'store'])->name('produk-stok.store');
    Route::post('/produk-stok/stock-in', [ProductController::class, 'stockIn'])->name('produk-stok.stock-in');
    Route::post('/produk-stok/stock-adjust', [ProductController::class, 'stockAdjust'])->name('produk-stok.stock-adjust');
    Route::post('/produk-stok/void-move', [ProductController::class, 'voidMove'])->name('produk-stok.void-move');
    Route::put('/produk-stok/{product}', [ProductController::class, 'update'])->name('produk-stok.update');
    Route::delete('/produk-stok/{product}', [ProductController::class, 'destroy'])->name('produk-stok.destroy');
});