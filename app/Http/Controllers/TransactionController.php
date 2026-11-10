<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Expense;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function catatan()
    {
        $transactions = Transaction::with([
            'user',
            'transactionDetails.product'
        ])->latest('transaction_date')->get();

        $expenses = Expense::with('user')
            ->latest('expense_date')
            ->get();

        $products = Product::with('category')
            ->orderBy('product_name')
            ->get();

        return view('admin.catatan-transaksi', compact(
            'transactions',
            'expenses',
            'products'
        ));
    }

    public function laporanKeuangan()
    {
        $transactions = Transaction::with([
            'user',
            'transactionDetails.product'
        ])->latest('transaction_date')->get();

        $expenses = Expense::with('user')
            ->latest('expense_date')
            ->get();

        $reportTransactions = [];

        foreach ($transactions as $transaction) {
            $items = $transaction->transactionDetails->map(function ($detail) {
                $product = $detail->product;

                return [
                    'name' => $product ? $product->product_name : 'Produk dihapus',
                    'qty' => (int) $detail->purchase_quantity,
                    'cost' => (float) ($product->purchase_price ?? 0),
                    'subtotal' => (float) $detail->subtotal,
                ];
            });

            $revenue = $items->sum('subtotal');

            $cogs = $items->sum(function ($item) {
                return $item['qty'] * $item['cost'];
            });

            $reportTransactions[] = [
                'id' => $transaction->id,
                'type' => 'sale',
                'ts' => $transaction->transaction_date->timestamp * 1000,
                'items' => $items->values(),
                'revenue' => $revenue,
                'cogs' => $cogs,
                'units' => $items->sum('qty'),
            ];
        }

        foreach ($expenses as $expense) {
            $reportTransactions[] = [
                'id' => $expense->id,
                'type' => 'expense',
                'ts' => $expense->expense_date->timestamp * 1000,
                'category' => $expense->category,
                'desc' => $expense->description ?? $expense->category,
                'amount' => (float) $expense->amount,
            ];
        }

        return view('admin.laporan-keuangan', compact(
            'reportTransactions'
        ));
    }
    
    public function storeSale(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date', 'before_or_equal:now'],
            'note' => ['nullable', 'string', 'max:120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.purchase_quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $transaction = DB::transaction(function () use ($validated) {
                $transaction = Transaction::create([
                    'transaction_date' => $validated['transaction_date'],
                    'payment_method' => 'cash',
                    'note' => $validated['note'] ?? null,
                    'user_id' => Auth::id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $product = Product::whereKey($item['product_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $quantity = (int) $item['purchase_quantity'];

                    if ($product->stock < $quantity) {
                        throw new \RuntimeException(
                            'Stok ' . $product->product_name .
                            ' tidak mencukupi. Stok tersedia: ' . $product->stock
                        );
                    }

                    $stockBefore = $product->stock;
                    $stockAfter = $stockBefore - $quantity;

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'purchase_quantity' => $quantity,
                        'unit_price_of_transaction' => $product->selling_price,
                        'subtotal' => $product->selling_price * $quantity,
                    ]);

                    $product->update([
                        'stock' => $stockAfter,
                    ]);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'type' => 'sale',
                        'quantity' => -$quantity,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'note' => 'Penjualan transaksi #' . $transaction->id,
                        'movement_date' => $validated['transaction_date'],
                    ]);
                }

                return $transaction;
            });

            return response()->json([
                'message' => 'Penjualan berhasil disimpan.',
                'transaction_id' => $transaction->id,
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan penjualan.',
            ], 500);
        }
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'expense_date' => ['required', 'date', 'before_or_equal:now'],
            'category' => ['required', 'string', 'max:80'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:120'],
        ]);

        $expense = Expense::create([
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'note' => $validated['note'] ?? null,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Pengeluaran berhasil disimpan.',
            'expense_id' => $expense->id,
        ]);
    }

    public function void(Request $request, string $type, int $id)
    {
        try {
            DB::transaction(function () use ($type, $id) {
                if ($type === 'sale') {
                    $transaction = Transaction::with('transactionDetails.product')
                        ->lockForUpdate()
                        ->findOrFail($id);

                    $movements = StockMovement::where('note', 'Penjualan transaksi #' . $transaction->id)
                        ->where('type', 'sale')
                        ->where('is_voided', false)
                        ->lockForUpdate()
                        ->get();

                    if ($movements->isEmpty()) {
                        throw new \RuntimeException('Transaksi ini sudah dibatalkan atau pergerakan stok tidak ditemukan.');
                    }

                    foreach ($movements as $movement) {
                        $product = Product::whereKey($movement->product_id)
                            ->lockForUpdate()
                            ->firstOrFail();

                        $stockBefore = $product->stock;
                        $stockAfter = $stockBefore - $movement->quantity;

                        $product->update([
                            'stock' => $stockAfter,
                        ]);

                        $movement->update([
                            'is_voided' => true,
                        ]);

                        StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => Auth::id(),
                            'type' => 'adjustment',
                            'quantity' => -$movement->quantity,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'note' => 'Pembatalan penjualan transaksi #' . $transaction->id,
                            'movement_date' => now(),
                        ]);
                    }

                    $transaction->delete();

                } elseif ($type === 'expense') {
                    $expense = Expense::lockForUpdate()->findOrFail($id);
                    $expense->delete();

                } else {
                    throw new \RuntimeException('Jenis catatan tidak valid.');
                }
            });

            return response()->json([
                'message' => 'Catatan berhasil dibatalkan.',
            ]);

        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Terjadi kesalahan saat membatalkan catatan.',
            ], 500);
        }
    }

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
        return redirect()->route('transaksi.index')->with('success', 'Riwayat transaksi berhasil dihapus.');
    }
}