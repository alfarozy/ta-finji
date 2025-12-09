<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{

    protected $fillable = [
        'user_id',
        'balance',
    ];

    protected $hidden = [
        'user_id',
    ];

    /**
     * Relasi: transaksi dimiliki oleh user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
