<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
    ];

    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    /**
     * Relasi: satu kategori punya banyak transaksi
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
