<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Transaction;

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

    public function rekomendasi()
    {
        $products = Product::with([
            'category',
            'discounts' => function ($query) {
                $query->whereDate('start_date', '<=', today())
                    ->whereDate('end_date', '>=', today())
                    ->latest();
            }
        ])->orderBy('product_name')->get();

        $transactions = Transaction::with('transactionDetails')
            ->latest('transaction_date')
            ->get();

        $recommendationProducts = $products->map(function ($product) {
            $discount = $product->discounts->first();

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
                'promo' => $discount ? [
                    'type' => $discount->discount_type,
                    'pct' => (float) $discount->discount_value,
                    'base' => (float) $discount->base_price,
                    'ts' => $discount->created_at->timestamp * 1000,
                ] : null,
            ];
        })->values();

        $recommendationTransactions = $transactions->map(function ($transaction) {
            $items = $transaction->transactionDetails->map(function ($detail) {
                return [
                    'pid' => $detail->product_id,
                    'qty' => (int) $detail->purchase_quantity,
                ];
            })->values();

            return [
                'type' => 'sale',
                'ts' => $transaction->transaction_date->timestamp * 1000,
                'units' => $items->sum('qty'),
                'items' => $items,
            ];
        })->values();

        return view('admin.rekomendasi-diskon', compact(
            'recommendationProducts',
            'recommendationTransactions'
        ));
    }

    public function apply(Request $request, Product $product)
    {
        $validated = $request->validate([
            'discount_value' => 'required|numeric|min:1|max:50',
        ]);

        $discountValue = (float) $validated['discount_value'];

        $activeDiscount = $product->discounts()
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->latest()
            ->first();

        $basePrice = $activeDiscount && $activeDiscount->base_price !== null
            ? (float) $activeDiscount->base_price
            : (float) $product->selling_price;

        $newPrice = round(
            ($basePrice * (1 - $discountValue / 100)) / 100
        ) * 100;

        if ($newPrice <= $product->purchase_price) {
            return response()->json([
                'message' => 'Harga setelah diskon tidak boleh sama dengan atau di bawah biaya perolehan.',
            ], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($product, $discountValue, $basePrice, $newPrice, $activeDiscount) {
            if ($activeDiscount) {
                $activeDiscount->delete();
            }

            $product->discounts()->create([
                'discount_type' => 'percent',
                'discount_value' => $discountValue,
                'base_price' => $basePrice,
                'start_date' => today(),
                'end_date' => '2099-12-31',
            ]);

            $product->update([
                'selling_price' => $newPrice,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Diskon berhasil diterapkan.',
            'price' => (float) $newPrice,
            'base_price' => (float) $basePrice,
        ]);
    }

    public function revert(Product $product)
    {
        $discount = $product->discounts()
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->latest()
            ->first();

        if (!$discount) {
            return response()->json([
                'message' => 'Tidak ada diskon aktif pada produk ini.',
            ], 404);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($product, $discount) {
            $product->update([
                'selling_price' => $discount->base_price,
            ]);

            $discount->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Harga produk berhasil dikembalikan.',
            'price' => (float) $product->selling_price,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'discount_type' => 'required|in:percent,nominal',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Discount::create($validated);

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil ditambahkan.');
    }

    public function edit(Discount $discount)
    {
        $products = Product::all();
        return view('admin.discounts.edit', compact('discount', 'products'));
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'discount_type' => 'required|in:percent,nominal',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $discount->update($validated);

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil diperbarui.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil dihapus.');
    }
}