<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    // Menampilkan daftar seluruh riwayat Transaction (Read All)
    public function index()
    {
        $transaction = Transaction::with(['user', 'transactionDetails.product'])->latest()->get();
        return view('admin.transaction.index', compact('transaction'));
    }

    // Menampilkan detail item dari satu Transaction tertentu (Read Detail)
    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'transactionDetails.product']);
        return view('admin.transaction.show', compact('transaction'));
    }

    // Menghapus riwayat Transaction (jika ada pembatalan / penyesuaian)
    public function destroy(Transaction $transaction)
    {
        $transaction->delete(); // Karena onDelete('cascade'), Transaction_detail otomatis terhapus
        return redirect()->route('transaction.index')->with('success', 'Riwayat transaksi berhasil dihapus.');
    }
}