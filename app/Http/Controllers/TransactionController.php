<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('transactionCategory')
            ->where('user_id', 1);

        // Filter by type
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        $transactions = $query->latest('transaction_date')->paginate(10);

        return view('backoffice.transactions.index', compact('transactions'));
    }

    public function create()
    {
        $categories = TransactionCategory::where(['is_active' => true])->get();
        return view('backoffice.transactions.create', compact('categories'));
    }

    public function edit($id)
    {
        $categories = TransactionCategory::where(['is_active' => true])->get();
        return view('backoffice.transactions.edit', compact('categories'));
    }
}
