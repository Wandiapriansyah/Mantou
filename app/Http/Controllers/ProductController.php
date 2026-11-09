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