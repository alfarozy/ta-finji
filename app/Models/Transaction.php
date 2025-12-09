<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $fillable = [
        'user_id',
        'transaction_category_id',
        'amount',
        'description',
        'type',
        'transaction_date',
    ];

    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    protected $casts = [
        'transaction_date' => 'date',
    ];

    /**
     * Relasi: transaksi dimiliki oleh user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: transaksi punya kategori
     */
    public function transactionCategory()
    {
        return $this->belongsTo(TransactionCategory::class, 'transaction_category_id');
    }
}
