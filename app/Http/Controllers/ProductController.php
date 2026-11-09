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

    // Menambahkan produk
    public function store(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'product_name' => 'required|string|max:150',
            'unit' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::create($data);

            // Catat stok awal jika produk langsung memiliki stok
            if ($product->stock > 0) {
                StockMovement::create([
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
        });

        return redirect()->route('produk-stok')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    // Mengubah produk
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'product_name' => 'required|string|max:150',
            'unit' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        // Stok tidak diubah melalui form edit produk.
        // Perubahan stok harus melalui barang masuk atau penyesuaian stok.
        $product->update($data);

        return redirect()->route('produk-stok')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    // Menghapus produk
    public function destroy(Product $product)
    {
        if ($product->stockMovements()->exists()) {
            return redirect()->route('produk-stok')
                ->with('error', 'Produk memiliki riwayat stok sehingga tidak dapat dihapus.');
        }

        if ($product->trasaction_details()->exists()) {
            return redirect()->route('produk-stok')
                ->with('error', 'Produk memiliki riwayat transaksi sehingga tidak dapat dihapus.');
        }

        $product->delete();

        return redirect()->route('produk-stok')
            ->with('success', 'Produk berhasil dihapus.');
    }
}