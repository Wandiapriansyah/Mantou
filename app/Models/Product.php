<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'barcode',
        'product_name',
        'selling_price',
        'purchase_price',
        'stock',
        'minimum_stock',
        'category_id',
    ];

    public function category(): Belongsto {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function discounts(): HasMany {
        return $this->hasMany(Discout::class, 'product_id');
    }

    public function trasaction_details(): HasMany {
        return $this->hasMany(TransactionDetail::class, 'product_id');
    }
}
