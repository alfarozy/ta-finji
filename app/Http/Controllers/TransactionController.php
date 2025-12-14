<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\UserBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth()->id();
        $query = Transaction::with('transactionCategory')
            ->where('user_id', $userId);

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
            ->latest('created_at')
            ->paginate($perPage)
            ->appends($request->except('page'));

        // Jika butuh daftar kategori di view untuk filter dropdown
        $categories = TransactionCategory::orderBy('name')->get();

        return view('backoffice.transactions.index', compact('transactions', 'categories'));
    }

    public function create()
    {
        $categories = TransactionCategory::get();
        return view('backoffice.transactions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate(
            [
                'type' => ['required', 'in:income,expense'],
                'transaction_category_id' => ['required', 'integer'],
                'amount' => ['required', 'numeric', 'min:1'],
                'transaction_date' => ['required', 'date'],
                'description' => ['nullable', 'string', 'max:255'],
            ],
            [
                'type.required' => 'Jenis transaksi wajib dipilih',
                'type.in' => 'Jenis transaksi tidak valid',
                'transaction_category_id.required' => 'Kategori wajib dipilih',
                'amount.required' => 'Jumlah wajib diisi',
                'amount.numeric' => 'Jumlah harus berupa angka',
                'amount.min' => 'Jumlah minimal 1',
                'transaction_date.required' => 'Tanggal transaksi wajib diisi',
            ]
        );

        $userId = Auth()->id();

        // 2. Validasi kategori sesuai TYPE & USER (ANTI MANIPULASI)
        $category = TransactionCategory::where('id', $validated['transaction_category_id'])
            ->where('type', $validated['type'])
            ->firstOrFail();

        // 3. Simpan transaksi

        DB::transaction(function () use ($validated, $userId, $category) {

            // 1. Simpan transaksi
            Transaction::create([
                'user_id' => $userId,
                'transaction_category_id' => $category->id,
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'transaction_date' => $validated['transaction_date'],
            ]);

            // 2. Ambil / buat saldo user
            $balance = UserBalance::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0]
            );

            // 3. Update saldo
            if ($validated['type'] === Transaction::TYPE_INCOME) {
                $balance->increment('balance', $validated['amount']);
            } else {
                $balance->decrement('balance', $validated['amount']);
            }
        });

        // 4. Redirect sukses
        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $categories = TransactionCategory::get();

        return view('backoffice.transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $userId = Auth()->id();

        $transaction = Transaction::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $validated = $request->validate(
            [
                'type' => ['required', 'in:income,expense'],
                'transaction_category_id' => ['required', 'integer'],
                'amount' => ['required', 'numeric', 'min:1'],
                'transaction_date' => ['required', 'date'],
                'description' => ['nullable', 'string', 'max:255'],
            ]
        );

        // Validasi kategori sesuai type & user
        TransactionCategory::where('id', $validated['transaction_category_id'])
            ->where('type', $validated['type'])
            ->firstOrFail();

        DB::transaction(function () use ($transaction, $validated, $userId) {

            $balance = UserBalance::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0]
            );

            // 1. Rollback saldo lama
            if ($transaction->type === Transaction::TYPE_INCOME) {
                $balance->decrement('balance', $transaction->amount);
            } else {
                $balance->increment('balance', $transaction->amount);
            }

            // 2. Update transaksi
            $transaction->update([
                'type' => $validated['type'],
                'transaction_category_id' => $validated['transaction_category_id'],
                'amount' => $validated['amount'],
                'transaction_date' => $validated['transaction_date'],
                'description' => $validated['description'] ?? null,
            ]);

            // 3. Apply saldo baru
            if ($validated['type'] === Transaction::TYPE_INCOME) {
                $balance->increment('balance', $validated['amount']);
            } else {
                $balance->decrement('balance', $validated['amount']);
            }
        });

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui');
    }

    public function destroy($id)
    {
        $userId = Auth()->id();

        // Ambil transaksi milik user
        $transaction = Transaction::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        DB::transaction(function () use ($transaction, $userId) {

            // Ambil / buat balance
            $balance = UserBalance::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0]
            );

            // Rollback saldo
            if ($transaction->type === Transaction::TYPE_INCOME) {
                // Income dihapus saldo dikurangi
                $balance->decrement('balance', $transaction->amount);
            } else {
                // Expense dihapus saldo dikembalikan
                $balance->increment('balance', $transaction->amount);
            }

            // Hapus transaksi
            $transaction->delete();
        });

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus');
    }
}
