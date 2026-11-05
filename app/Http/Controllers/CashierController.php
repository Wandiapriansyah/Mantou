<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    // Menampilkan halaman utama kasir (tampilan scan barcode di smartphone)
    public function index()
    {
        return view('cashier.index');
    }

    // Mencari produk berdasarkan barcode (untuk AJAX/kamera scan)
    public function searchByBarcode(Request $request)
    {
        $product = Product::where('barcode', $request->barcode)->first();

        if (!$product) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Produk tidak ditemukan!'
            ], 404);
        }

        return response()->json([
            'status' => 'success', 
            'data' => $product
        ]);
    }

    // Menyimpan transaksi penjualan kasir dan mengurangi stok otomatis
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:tunai,qris,transfer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.purchase_quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Simpan ke tabel transaksi (header)
            $transaksi = Transaksi::create([
                'user_id' => auth()->id(),
                'transaction_date' => now(),
                'payment_method' => $request->payment_method,
            ]);

            // Simpan rincian item dan kurangi stok
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stok < $item['purchase_quantity']) {
                    throw new \Exception("Stok produk {$product->nama_produk} tidak mencukupi!");
                }

                $hargaSatuan = $product->harga_jual;
                $subtotal = $hargaSatuan * $item['purchase_quantity'];

                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'product_id' => $product->id,
                    'purchase_quantity' => $item['purchase_quantity'],
                    'unit_price_of_transaction' => $hargaSatuan,
                    'subtotal' => $subtotal,
                ]);

                // Kurangi stok produk secara otomatis
                $product->stok -= $item['purchase_quantity'];
                $product->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success', 
                'message' => 'Transaksi berhasil disimpan dan stok diperbarui!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage()
            ], 400);
        }
    }
}