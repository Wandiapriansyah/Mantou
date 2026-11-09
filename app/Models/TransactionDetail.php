<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'transaction_details';

    protected $fillable = [
        'purchase_quantity',
        'unit_price_of_transaction',
        'subtotal',
        'transaction_id',
        'product_id',
    ];

    // Relasi: Detail mengarah ke satu Transaksi utama
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    // Relasi: Detail merujuk ke satu Produk yang dibeli
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}