<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = ['user_id', 'bank_name', 'moota_bank_id', 'account_number', 'account_name', 'last_synced_at'];
}
