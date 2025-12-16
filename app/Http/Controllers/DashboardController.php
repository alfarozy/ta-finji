<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\UserBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function getDashboardSummary()
    {
        $userId = Auth::id();

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        // ===== Summary =====
        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        $totalExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        $balance = UserBalance::where('user_id', $userId)->value('balance');

        // ===== Chart (bulan berjalan, per hari) =====
        $daysInMonth = $start->daysInMonth;

        $daily = Transaction::selectRaw('
        EXTRACT(DAY FROM transaction_date) as day,
        SUM(CASE WHEN type = \'income\' THEN amount ELSE 0 END) as income,
        SUM(CASE WHEN type = \'expense\' THEN amount ELSE 0 END) as expense
    ')
            ->where('user_id', $userId)
            ->whereBetween('transaction_date', [$start, $end])
            ->groupBy(DB::raw('EXTRACT(DAY FROM transaction_date)'))
            ->orderBy(DB::raw('EXTRACT(DAY FROM transaction_date)'))
            ->get();

        // siapkan array 1..n hari (default 0)
        $incomeData = array_fill(0, $daysInMonth, 0);
        $expenseData = array_fill(0, $daysInMonth, 0);

        foreach ($daily as $row) {
            $idx = (int)$row->day - 1;
            $incomeData[$idx] = (int)$row->income;
            $expenseData[$idx] = (int)$row->expense;
        }


        //  TOTAL BUDGET
        $totalBudget = DB::table('budgets')
            ->where('user_id', $userId)
            ->sum('amount');

        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $usedBudget = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        $remainingBudget = max($totalBudget - $usedBudget, 0);

        $budgetPercentage = $totalBudget > 0
            ? min(round(($usedBudget / $totalBudget) * 100), 100)
            : 0;

        return view('backoffice.index', compact(
            'totalIncome',
            'totalExpense',
            'balance',
            'totalBudget',
            'usedBudget',
            'remainingBudget',
            'budgetPercentage',
            'incomeData',
            'expenseData',
            'daysInMonth'
        ));
    }
}
