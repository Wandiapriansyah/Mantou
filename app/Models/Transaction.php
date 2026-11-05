<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'transaction_date',
        'payment_method',
        'user_id',
    ];

    // Relasi: Transaksi dilayani oleh satu User (Kasir/Admin)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Satu Transaksi memiliki banyak Transaksi Detail
    public function transactionDetail(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaksi_id');
    }
}