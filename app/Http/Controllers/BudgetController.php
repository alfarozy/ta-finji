<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index()
    {
        $userId = Auth()->id();
        $budgets = Budget::with('category')
            ->get()
            ->map(function ($item) {
                $item->actual = \App\Models\Transaction::where('transaction_category_id', $item->transaction_category_id)
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum('amount');

                return $item;
            });

        //  TOTAL BUDGET
        $totalBudget = Budget::where('user_id', $userId)
            ->sum('amount');

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $usedBudget = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        $remainingBudget = max($totalBudget - $usedBudget, 0);

        $budgetPercentage = $totalBudget > 0
            ? min(round(($usedBudget / $totalBudget) * 100), 100)
            : 0;
        return view('backoffice.budgets.index', compact('budgets', 'totalBudget', 'usedBudget', 'remainingBudget', 'budgetPercentage'));
    }
    public function create()
    {
        $categories = TransactionCategory::whereType('expense')->get();
        return view('backoffice.budgets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate(
            [
                'transaction_category_id' => ['required', 'exists:transaction_categories,id'],
                'amount' => ['required', 'numeric', 'min:1'],
                'description' => ['nullable', 'string', 'max:255'],
            ],
            [
                'transaction_category_id.required' => 'Kategori wajib dipilih',
                'amount.required' => 'Jumlah anggaran wajib diisi',
                'amount.numeric' => 'Jumlah anggaran harus berupa angka',
                'amount.min' => 'Jumlah anggaran minimal 1',
            ]
        );

        $userId = Auth()->id();
        // 2. Cegah duplikasi kategori saat update
        $duplicate = Budget::where('user_id', Auth()->id())
            ->where('transaction_category_id', $validated['transaction_category_id'])
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors([
                    'transaction_category_id' => 'Anggaran untuk kategori ini sudah tersedia',
                ])
                ->withInput();
        }

        // 3. Simpan anggaran
        Budget::create([
            'user_id' => $userId,
            'transaction_category_id' => $validated['transaction_category_id'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
        ]);

        // 4. Redirect sukses
        return redirect()
            ->route('budgets.index')
            ->with('success', 'Anggaran berhasil disimpan');
    }

    public function edit($id)
    {
        $budget = Budget::where('user_id', Auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $categories = TransactionCategory::whereType('expense')->get();
        return view('backoffice.budgets.edit', compact('categories', 'budget'));
    }
    public function update(Request $request, $id)
    {
        // 1. Ambil data anggaran
        $budget = Budget::where('user_id', Auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // 2. Validasi input
        $validated = $request->validate(
            [
                'transaction_category_id' => ['required', 'exists:transaction_categories,id'],
                'amount' => ['required', 'numeric', 'min:1'],
                'description' => ['nullable', 'string', 'max:255'],
            ],
            [
                'transaction_category_id.required' => 'Kategori wajib dipilih',
                'transaction_category_id.exists' => 'Kategori tidak valid',
                'amount.required' => 'Jumlah anggaran wajib diisi',
                'amount.numeric' => 'Jumlah anggaran harus berupa angka',
                'amount.min' => 'Jumlah anggaran minimal 1',
            ]
        );

        // 3. Cegah duplikasi kategori saat update
        $duplicate = Budget::where('user_id', Auth()->id())
            ->where('transaction_category_id', $validated['transaction_category_id'])
            ->where('id', '!=', $budget->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors([
                    'transaction_category_id' => 'Anggaran untuk kategori ini sudah tersedia',
                ])
                ->withInput();
        }

        // 4. Update data
        $budget->update([
            'transaction_category_id' => $validated['transaction_category_id'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
        ]);

        // 5. Redirect sukses
        return redirect()
            ->route('budgets.index')
            ->with('success', 'Anggaran berhasil diperbarui');
    }

    public function destroy($id)
    {
        // 1. Ambil data budget milik user login
        $budget = Budget::where('user_id', Auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // 2. Hapus data
        $budget->delete();

        // 3. Redirect dengan pesan sukses
        return redirect()
            ->route('budgets.index')
            ->with('success', 'Anggaran berhasil dihapus');
    }
    public function show($id)
    {
        $userId =  Auth()->id();
        $budget = Budget::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();
        //  TOTAL BUDGET
        $totalBudget = $budget->amount;

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $transactions = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('transaction_category_id', $budget->transaction_category_id)
            ->whereBetween('transaction_date', [$start, $end]);

        $usedBudget = $transactions->sum('amount');

        $remainingBudget = max($totalBudget - $usedBudget, 0);

        $budgetPercentage = $totalBudget > 0
            ? min(round(($usedBudget / $totalBudget) * 100), 100)
            : 0;

        $transactions = $transactions->latest('transaction_date')
            ->paginate(10);

        return view('backoffice.budgets.show', compact('budget', 'transactions', 'totalBudget', 'usedBudget', 'remainingBudget', 'budgetPercentage'));
    }
}
