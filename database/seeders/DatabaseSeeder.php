<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Alfarozy',
            'email' => 'alfarozy@etafilia.com',
            'password' => Hash::make('password'),
            'whatsapp' => '081234567890'
        ]);

        $categories = [
            [
                'name' => 'Makanan',
                'slug' => 'makanan',
                'type' => TransactionCategory::TYPE_EXPENSE, // Pengeluaran
            ],
            [
                'name' => 'Minuman',
                'slug' => 'minuman',
                'type' => TransactionCategory::TYPE_EXPENSE, // Pengeluaran
            ],
            [
                'name' => 'Transportasi',
                'slug' => 'transportasi',
                'type' => TransactionCategory::TYPE_EXPENSE,
            ],
            [
                'name' => 'Uang Bulanan',
                'slug' => 'uang-bulanan',
                'type' => TransactionCategory::TYPE_INCOME, // Pemasukan
            ],
            [
                'name' => 'Gaji',
                'slug' => 'gaji',
                'type' => TransactionCategory::TYPE_INCOME, // Pemasukan
            ],
            [
                'name' => 'Tagihan & Langganan',
                'slug' => 'tagihan-langganan',
                'type' => TransactionCategory::TYPE_EXPENSE,
            ],
            [
                'name' => 'Hiburan',
                'slug' => 'hiburan',
                'type' => TransactionCategory::TYPE_EXPENSE,
            ],
        ];


        foreach ($categories as $category) {
            TransactionCategory::create($category);
        }
    }
}
