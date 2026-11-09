<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // Menampilkan halaman Produk & Stok
    
    public function index()
    {
        $products = Product::with('category')
            ->latest()
            ->get();

        $categories = Category::orderBy('category_name')->get();

        $stockMovements = StockMovement::with(['product', 'user'])
            ->latest('movement_date')
            ->get();

        // Ubah format database menjadi format yang digunakan JavaScript
        $jsProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->product_name,
                'barcode' => $product->barcode,
                'unit' => $product->unit,
                'category' => $product->category->category_name ?? '',
                'cost' => (float) $product->purchase_price,
                'price' => (float) $product->selling_price,
                'stock' => (int) $product->stock,
                'min' => (int) $product->minimum_stock,
                'promo' => null,
            ];
        });

        $jsMoves = $stockMovements->map(function ($move) {
            return [
                'id' => $move->id,
                'type' => $move->type,
                'qty' => $move->quantity,
                'ts' => $move->movement_date->timestamp * 1000,
                'name' => $move->product->product_name ?? 'Produk tidak tersedia',
                'before' => $move->stock_before,
                'after' => $move->stock_after,
                'note' => $move->note,
            ];
        });

        $initialState = [
            'products' => $jsProducts,
            'moves' => $jsMoves,
        ];

        return view('admin.produk-stok', compact(
            'products',
            'categories',
            'stockMovements',
            'initialState'
        ));
    }

    

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'unit' => 'required|string|max:100',
            'category' => 'required|string|exists:categories,category_name',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min' => 'required|integer|min:0',
        ]);

        $category = Category::where(
            'category_name',
            $data['category']
        )->firstOrFail();

        $result = DB::transaction(function () use ($data, $category) {
            $product = Product::create([
                'barcode' => $data['barcode'] ?: null,
                'product_name' => $data['name'],
                'unit' => $data['unit'],
                'category_id' => $category->id,
                'purchase_price' => $data['cost'],
                'selling_price' => $data['price'],
                'stock' => $data['stock'],
                'minimum_stock' => $data['min'],
            ]);

            $movement = null;

            if ($product->stock > 0) {
                $movement = StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'adjustment',
                    'quantity' => $product->stock,
                    'stock_before' => 0,
                    'stock_after' => $product->stock,
                    'note' => 'Stok awal produk',
                    'movement_date' => now(),
                ]);
            }

            return [
                'product' => $product,
                'movement' => $movement,
            ];
        });

        $product = $result['product'];
        $movement = $result['movement'];

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'product' => [
                'id' => $product->id,
                'name' => $product->product_name,
                'barcode' => $product->barcode,
                'unit' => $product->unit,
                'category' => $category->category_name,
                'cost' => (float) $product->purchase_price,
                'price' => (float) $product->selling_price,
                'stock' => (int) $product->stock,
                'min' => (int) $product->minimum_stock,
                'promo' => null,
            ],
            'move' => $movement ? [
                'id' => $movement->id,
                'type' => $movement->type,
                'qty' => $movement->quantity,
                'ts' => $movement->movement_date->timestamp * 1000,
                'name' => $product->product_name,
                'before' => $movement->stock_before,
                'after' => $movement->stock_after,
                'note' => $movement->note,
            ] : null,
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'unit' => 'required|string|max:100',
            'category' => 'required|string|exists:categories,category_name',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'min' => 'required|integer|min:0',
        ]);

        $category = Category::where(
            'category_name',
            $data['category']
        )->firstOrFail();

        $product->update([
            'barcode' => $data['barcode'] ?: null,
            'product_name' => $data['name'],
            'unit' => $data['unit'],
            'category_id' => $category->id,
            'purchase_price' => $data['cost'],
            'selling_price' => $data['price'],
            'minimum_stock' => $data['min'],
        ]);

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'product' => [
                'id' => $product->id,
                'name' => $product->product_name,
                'barcode' => $product->barcode,
                'unit' => $product->unit,
                'category' => $category->category_name,
                'cost' => (float) $product->purchase_price,
                'price' => (float) $product->selling_price,
                'stock' => (int) $product->stock,
                'min' => (int) $product->minimum_stock,
                'promo' => null,
            ],
        ]);
    }

    
    public function stockIn(Request $request)
    {
        $data = $request->validate([
            'pid' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'cost' => 'nullable|numeric|min:0',
            'ts' => 'required|numeric|gt:0',
            'note' => 'nullable|string|max:255',
        ]);

        $movementDate = \Carbon\Carbon::createFromTimestampMs(
            (int) $data['ts']
        );

        if ($movementDate->gt(now()->addMinute())) {
            return response()->json([
                'message' => 'Waktu tidak boleh di masa depan.',
            ], 422);
        }

        $result = DB::transaction(function () use ($data, $movementDate) {
            $product = Product::with('category')
                ->lockForUpdate()
                ->findOrFail($data['pid']);

            $before = (int) $product->stock;
            $qty = (int) $data['qty'];

            $product->stock = $before + $qty;

            if (isset($data['cost']) && $data['cost'] !== '') {
                $product->purchase_price = $data['cost'];
            }

            $product->save();

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'in',
                'quantity' => $qty,
                'stock_before' => $before,
                'stock_after' => $product->stock,
                'note' => $data['note'] ?? '',
                'movement_date' => $movementDate,
            ]);

            return [
                'product' => $product,
                'movement' => $movement,
            ];
        });

        $product = $result['product'];
        $movement = $result['movement'];

        return response()->json([
            'message' => 'Barang masuk berhasil dicatat.',
            'product' => [
                'id' => $product->id,
                'name' => $product->product_name,
                'barcode' => $product->barcode,
                'unit' => $product->unit,
                'category' => $product->category->category_name ?? '',
                'cost' => (float) $product->purchase_price,
                'price' => (float) $product->selling_price,
                'stock' => (int) $product->stock,
                'min' => (int) $product->minimum_stock,
                'promo' => null,
            ],
            'move' => [
                'id' => $movement->id,
                'type' => $movement->type,
                'qty' => (int) $movement->quantity,
                'ts' => $movement->movement_date->timestamp * 1000,
                'name' => $product->product_name,
                'before' => (int) $movement->stock_before,
                'after' => (int) $movement->stock_after,
                'note' => $movement->note,
            ],
        ]);
    }

    public function stockAdjust(Request $request)
    {
        $data = $request->validate([
            'pid' => 'required|exists:products,id',
            'counted' => 'required|integer|min:0',
            'ts' => 'required|numeric|gt:0',
            'note' => 'required|string|max:255',
        ]);

        $movementDate = \Carbon\Carbon::createFromTimestampMs(
            (int) $data['ts']
        );

        if ($movementDate->gt(now()->addMinute())) {
            return response()->json([
                'message' => 'Waktu tidak boleh di masa depan.',
            ], 422);
        }

        $result = DB::transaction(function () use ($data, $movementDate) {
            $product = Product::with('category')
                ->lockForUpdate()
                ->findOrFail($data['pid']);

            $before = (int) $product->stock;
            $after = (int) $data['counted'];

            if ($before === $after) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'counted' => 'Stok fisik sama dengan stok tercatat; tidak ada yang perlu disesuaikan.',
                ]);
            }

            $quantity = $after - $before;

            $product->stock = $after;
            $product->save();

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'adjustment',
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => trim($data['note']),
                'movement_date' => $movementDate,
            ]);

            return [
                'product' => $product,
                'movement' => $movement,
            ];
        });

        $product = $result['product'];
        $movement = $result['movement'];

        return response()->json([
            'message' => 'Penyesuaian stok berhasil dicatat.',
            'product' => [
                'id' => $product->id,
                'name' => $product->product_name,
                'barcode' => $product->barcode,
                'unit' => $product->unit,
                'category' => $product->category->category_name ?? '',
                'cost' => (float) $product->purchase_price,
                'price' => (float) $product->selling_price,
                'stock' => (int) $product->stock,
                'min' => (int) $product->minimum_stock,
                'promo' => null,
            ],
            'move' => [
                'id' => $movement->id,
                'type' => $movement->type,
                'qty' => (int) $movement->quantity,
                'ts' => $movement->movement_date->timestamp * 1000,
                'name' => $product->product_name,
                'before' => (int) $movement->stock_before,
                'after' => (int) $movement->stock_after,
                'note' => $movement->note,
            ],
        ]);
    }

    public function voidMove(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:stock_movements,id',
        ]);

        $result = DB::transaction(function () use ($data) {
            $movement = StockMovement::lockForUpdate()
                ->findOrFail($data['id']);

            if ($movement->is_voided) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'id' => 'Catatan stok ini sudah pernah dibatalkan.',
                ]);
            }

            if (!in_array($movement->type, ['in', 'adjustment'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'id' => 'Jenis catatan stok ini tidak dapat dibatalkan melalui fitur ini.',
                ]);
            }

            $product = Product::lockForUpdate()
                ->findOrFail($movement->product_id);

            $before = (int) $product->stock;

            // Pastikan stok saat ini masih sesuai dengan kondisi
            // setelah transaksi yang akan dibatalkan.
            if ($before !== (int) $movement->stock_after) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'id' => 'Stok produk sudah berubah sejak transaksi ini. Pembatalan tidak dapat dilakukan.',
                ]);
            }

            $after = (int) $movement->stock_before;

            if ($after < 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'id' => 'Pembatalan akan menyebabkan stok negatif.',
                ]);
            }

            $product->stock = $after;
            $product->save();

            $movement->is_voided = true;
            $movement->save();

            $reversal = StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'adjustment',
                'quantity' => $after - $before,
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => 'Pembatalan catatan stok #' . $movement->id,
                'movement_date' => now(),
            ]);

            return [
                'product' => $product,
                'movement' => $movement,
                'reversal' => $reversal,
            ];
        });

        return response()->json([
            'message' => 'Catatan stok berhasil dibatalkan.',
            'product' => [
                'id' => $result['product']->id,
                'stock' => (int) $result['product']->stock,
            ],
            'movement_id' => $result['movement']->id,
            'reversal_id' => $result['reversal']->id,
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->stockMovements()->exists()) {
            return response()->json([
                'message' => 'Produk memiliki riwayat stok sehingga tidak dapat dihapus.',
            ], 422);
        }

        if ($product->trasaction_details()->exists()) {
            return response()->json([
                'message' => 'Produk memiliki riwayat transaksi sehingga tidak dapat dihapus.',
            ], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus.',
        ]);
    }
}