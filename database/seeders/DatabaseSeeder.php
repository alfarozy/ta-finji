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
        ];


        foreach ($categories as $category) {
            TransactionCategory::create($category);
        }

        $budgets = [
            [
                'user_id' => 1,
                'transaction_category_id' => 1,
                'amount' => 1500000,
                'description' => 'Monthly food and groceries budget',
            ],
            [
                'user_id' => 1,
                'transaction_category_id' => 2,
                'amount' => 500000,
                'description' => 'Transport & fuel budget',
            ],
            [
                'user_id' => 1,
                'transaction_category_id' => 4,
                'amount' => 800000,
                'description' => 'Utility bills: electricity, water, internet',
            ]
        ];

        foreach ($budgets as $budget) {
            Budget::create($budget);
        }

        $transactions = [
            [
                'user_id' => 1,
                'transaction_category_id' => 3,
                'amount' => 3000000,
                'description' => 'Uang saku',
                'type' => 'income',
                'transaction_date' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => 1,
                'transaction_category_id' => 1,
                'amount' => 25000,
                'description' => 'Beli makanan',
                'type' => 'expense',
                'transaction_date' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => 1,
                'transaction_category_id' => 2,
                'amount' => 100000,
                'description' => 'Isi bensin',
                'type' => 'expense',
                'transaction_date' => Carbon::now()->subDay(),
            ],
        ];

        foreach ($transactions as $trx) {
            Transaction::create($trx);
        }
    }
}
