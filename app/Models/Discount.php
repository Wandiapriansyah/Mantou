<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    use HasFactory;

    protected $table = 'discounts';

    protected $fillable = [
        'discount_type',
        'discount_value',
        'base_price',
        'start_date',
        'end_date',
        'product_id',
    ];

    // Relasi: Diskon dimiliki oleh satu Produk
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}