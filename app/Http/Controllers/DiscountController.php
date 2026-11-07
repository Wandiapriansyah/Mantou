<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discount;
use App\Models\Product;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::with('product')->latest()->get();
        return view('admin.discounts.index', compact('discounts'));
    }

    public function create()
    {
        $products = Product::all();
        return view('admin.discounts.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'dicount_type'     => 'required|in:persen,nominal',
            'discount_value'    => 'required|numeric|min:0',
            'start_date'   => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Discount::create($request->all());

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil ditambahkan.');
    }

    public function edit(Discount $discount)
    {
        $products = Product::all();
        return view('admin.discounts.edit', compact('discount', 'products'));
    }

    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'dicount_type'     => 'required|in:persen,nominal',
            'discount_value'    => 'required|numeric|min:0',
            'start_date'   => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $discount->update($request->all());

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil diperbarui.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil dihapus.');
    }
}