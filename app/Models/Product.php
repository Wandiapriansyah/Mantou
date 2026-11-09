<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'barcode',
        'product_name',
        'unit',
        'selling_price',
        'purchase_price',
        'stock',
        'minimum_stock',
        'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class, 'product_id');
    }

    public function trasaction_details(): HasMany
    {
        return $this->hasMany(TransactionDetails::class, 'product_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }
}