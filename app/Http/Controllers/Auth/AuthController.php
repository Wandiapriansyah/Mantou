<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function dash()
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
        
        $dashboardTransactions = [];

        foreach ($transactions as $transaction) {
            $items = $transaction->transactionDetails->map(function ($detail) {
                $product = $detail->product;

                return [
                    'pid' => $detail->product_id,
                    'name' => $product ? $product->product_name : 'Produk dihapus',
                    'qty' => (int) $detail->purchase_quantity,
                    'price' => (float) $detail->unit_price_of_transaction,
                    'cost' => (float) ($product->purchase_price ?? 0),
                    'subtotal' => (float) $detail->subtotal,
                ];
            })->values();

            $revenue = $items->sum('subtotal');
            $cogs = $items->sum(function ($item) {
                return $item['qty'] * $item['cost'];
            });

            $dashboardTransactions[] = [
                'id' => $transaction->id,
                'type' => 'sale',
                'ts' => $transaction->transaction_date->timestamp * 1000,
                'by' => $transaction->user->name ?? '',
                'note' => $transaction->note ?? '',
                'items' => $items,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'units' => $items->sum('qty'),
            ];
        }

        foreach ($expenses as $expense) {
            $dashboardTransactions[] = [
                'id' => $expense->id,
                'type' => 'expense',
                'ts' => $expense->expense_date->timestamp * 1000,
                'by' => $expense->user->name ?? '',
                'note' => $expense->note ?? '',
                'category' => $expense->category,
                'desc' => $expense->description ?? $expense->category,
                'amount' => (float) $expense->amount,
            ];
        }

        $dashboardProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->product_name,
                'stock' => (int) $product->stock,
                'min' => (int) $product->minimum_stock,
                'unit' => $product->unit,
                'cost' => (float) $product->purchase_price,
                'price' => (float) $product->selling_price,
                'category' => $product->category->category_name ?? '',
            ];
        })->values();

        return view('admin.index', compact(
            'dashboardTransactions',
            'dashboardProducts'
        ));
    }

    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->roles === 'admin') {
            return redirect()->intended('/dashboard');
        }

        if ($user->roles === 'cashier') {
            return redirect()->intended('/kasir');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([
            'email' => 'Role akun tidak dikenali.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
