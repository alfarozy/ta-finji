<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('transactionCategory')
            ->where('user_id', 1);

        // Filter by exact category id
        if ($request->filled('category_id')) {
            $query->where('transaction_category_id', $request->category_id);
        }

        // Date range filter (expects YYYY-MM-DD)
        if ($request->filled('date_from')) {
            $from = Carbon::parse($request->date_from)->startOfDay();
            $query->where('transaction_date', '>=', $from);
        }
        if ($request->filled('date_to')) {
            $to = Carbon::parse($request->date_to)->endOfDay();
            $query->where('transaction_date', '<=', $to);
        }

        // Global search (description, amount, category name)
        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                    ->orWhere('amount', 'like', "%{$term}%")
                    ->orWhereHas('transactionCategory', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%")
                            ->orWhere('slug', 'like', "%{$term}%");
                    });
            });
        }

        // Pagination size (default 10)
        $perPage = (int) $request->get('per_page', 10);
        if ($perPage <= 0) $perPage = 10;

        // Ordering + paginate, sertakan query string saat pindah halaman
        $transactions = $query
            ->latest('transaction_date')
            ->paginate($perPage)
            ->appends($request->except('page'));

        // Jika butuh daftar kategori di view untuk filter dropdown
        $categories = \App\Models\TransactionCategory::orderBy('name')->get();

        return view('backoffice.transactions.index', compact('transactions', 'categories'));
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
