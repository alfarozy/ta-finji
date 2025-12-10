<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\TransactionCategory;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::with('category')
            ->get()
            ->map(function ($item) {
                $item->actual = \App\Models\Transaction::where('transaction_category_id', $item->transaction_category_id)
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum('amount');

                return $item;
            });
        return view('backoffice.budgets.index', compact('budgets'));
    }
    public function create()
    {
        $categories = TransactionCategory::get();
        return view('backoffice.budgets.create', compact('categories'));
    }
    public function edit($id)
    {
        $categories = TransactionCategory::get();
        return view('backoffice.budgets.edit', compact('categories'));
    }
}
