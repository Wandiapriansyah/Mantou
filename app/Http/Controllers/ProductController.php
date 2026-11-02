<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->get();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode'      => 'required|string|max:50|unique:products,barcode',
            'product_name'  => 'required|string|max:150',
            'category_id'   => 'required|exists:categories,id',
            'purchase_price'   => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'barcode'      => 'required|string|max:50|unique:products,barcode,' . $product->id,
            'product_name'  => 'required|string|max:150',
            'category_id'   => 'required|exists:categories,id',
            'purchase_price'   => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}