<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\StockMovement;
use App\Models\TransactionDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index()
    {
        return view('worker.cek-harga');
    }

    public function searchByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::where('barcode', $request->barcode)->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product,
        ]);
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,qris,transfer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.purchase_quantity' => 'required|integer|min:1',
        ]);

        try {
            $transaction = DB::transaction(function () use ($validated) {

                // Membuat transaksi utama
                $transaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'transaction_date' => now(),
                    'payment_method' => $validated['payment_method'],
                ]);

                foreach ($validated['items'] as $item) {

                    // Mengunci data produk selama transaksi berlangsung
                    $product = Product::where('id', $item['product_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $quantity = $item['purchase_quantity'];

                    // Memastikan stok mencukupi
                    if ($product->stock < $quantity) {
                        throw new \RuntimeException(
                            "Stok produk {$product->product_name} tidak mencukupi!"
                        );
                    }

                    $stockBefore = $product->stock;
                    $stockAfter = $stockBefore - $quantity;

                    $price = $product->selling_price;
                    $subtotal = $price * $quantity;

                    // Menyimpan detail transaksi
                    TransactionDetails::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'purchase_quantity' => $quantity,
                        'unit_price_of_transaction' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    // Memperbarui stok produk
                    $product->stock = $stockAfter;
                    $product->save();

                    // Mencatat pergerakan stok karena penjualan
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'type' => 'sale',
                        'quantity' => -$quantity,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'note' => 'Penjualan transaksi #' . $transaction->id,
                        'movement_date' => now(),
                    ]);
                }

                return $transaction;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil disimpan dan stok diperbarui!',
                'transaction_id' => $transaction->id,
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi gagal disimpan. Silakan coba kembali.',
            ], 500);
        }
    }
}