<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function financialInsight()
    {

        $summary = [
            'balance' => 5200000,        // saldo saat ini (Rp)
            'total_income' => 12000000,  // total pemasukan bulan ini (Rp)
            'total_expense' => 6800000   // total pengeluaran bulan ini (Rp)
        ];

        // Top categories (nama, jumlah transaksi, total amount)
        $topCategories = [
            ['name' => 'Makanan & Minuman', 'count' => 22, 'amount' => 1800000],
            ['name' => 'Transportasi', 'count' => 10, 'amount' => 650000],
            ['name' => 'Tagihan & Langganan', 'count' => 3, 'amount' => 900000],
            ['name' => 'Belanja & Retail', 'count' => 8, 'amount' => 1200000],
        ];

        // Daily spending (contoh 30 hari) — angka bulat Rp
        $spending = [
            750000,
            120000,
            50000,
            0,
            90000,
            60000,
            150000,
            80000,
            0,
            95000,
            700000,
            110000,
            45000,
            30000,
            125000,
            0,
            60000,
            1400000,
            80000,
            50000,
            70000,
            90000,
            0,
            120000,
            750000,
            65000,
            85000,
            40000,
            100000,
            95000
        ];

        // Daily income (contoh 30 hari) — bisa sebagian hari 0 (freelance/gaji berkala)
        $income = [
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            2000000,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            2000000,
            0,
            0,
            0,
            0,
            0
        ];

        // Optional: labels (dipakai chart xaxis)
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();

        $labels = [];
        $current = $start->copy();

        while ($current <= $end) {
            $labels[] = $current->format('d M Y'); // contoh: 01 Jan, 02 Jan, ...
            $current->addDay();
        }

        // Previous month summary (untuk MoM)
        $prevSummary = [
            'balance' => 3200000,
            'total_income' => 10000000,
            'total_expense' => 6800000
        ];

        // Example budgets (untuk budget variance)
        $budgets = [
            ['category_name' => 'Makanan & Minuman', 'amount' => 2000000],
            ['category_name' => 'Transportasi', 'amount' => 700000],
            ['category_name' => 'Tagihan & Langganan', 'amount' => 1000000],
            ['category_name' => 'Belanja & Retail', 'amount' => 1500000]
        ];
        $healthBreakdown = [
            ['metric' => 'Expense to Income', 'score' => 72, 'desc' => 'Rasio pengeluaran terhadap pemasukan — semakin rendah semakin baik.'],
            ['metric' => 'Expense Volatility', 'score' => 60, 'desc' => 'Fluktuasi pengeluaran harian — stabilitas sedang.'],
        ];

        $quickInsights = [
            ['title' => 'Pengeluaran Tertinggi', 'value' => 'Makanan & Minuman', 'meta' => 'Rp 1.800.000 • 22 transaksi'],
            ['title' => 'Rata-Rata Harian (net)', 'value' => 'Rp -5.000', 'meta' => 'Arus kas harian sedikit negatif'],
            ['title' => 'Potensi Hemat', 'value' => 'Rp 816.000 / bln', 'meta' => 'Optimasi 12% pada pengeluaran rutin']
        ];
        return view('backoffice.financial-insight', compact('summary', 'topCategories', 'spending', 'income', 'labels', 'prevSummary', 'budgets', 'healthBreakdown', 'quickInsights'));
    }
    public function getDashboardSummary()
    {
        $userId = Auth::id();

        // ===== Summary =====
        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        $balance = $totalIncome - $totalExpense;

        // ===== Chart (bulan berjalan, per hari) =====
        $start = Carbon::now()->startOfMonth();
        $end   = Carbon::now()->endOfMonth();
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
    public function categories()
    {
        return view('backoffice.transactions.categories.index');
    }

    public function analyze(Request $request)
    {
        $userId = 1;

        $data = [
            'summary' => $this->getFinancialSummary($userId),
            'topCategories' => $this->getTopCategories($userId),
            'spending' => $this->getSpendingTransactions($userId),
            'income' => $this->getIncomeTransactions($userId)
        ];

        // Call AI analysis API (you would replace this with your actual AI service)
        $aiAnalysis = $this->callAIAnalysis($data);

        return response()->json($aiAnalysis);
    }

    private function getFinancialSummary($userId)
    {
        // Example implementation - replace with your actual data
        return [
            'balance' => 2500000,
            'total_income' => 3500000,
            'total_expense' => 2800000
        ];
    }

    private function getTopCategories($userId)
    {
        // Example implementation
        return [
            ['name' => 'Makan & Minum', 'amount' => 850000],
            ['name' => 'Transportasi', 'amount' => 450000],
            ['name' => 'Hiburan', 'amount' => 350000],
            ['name' => 'Belanja', 'amount' => 420000],
            ['name' => 'Pendidikan', 'amount' => 300000]
        ];
    }

    private function getSpendingTransactions($userId)
    {
        // Example implementation
        return [
            ['date' => '2024-12-15', 'description' => 'GoFood', 'amount' => 75000, 'category' => 'Makan & Minum'],
            ['date' => '2024-12-14', 'description' => 'Token Listrik', 'amount' => 150000, 'category' => 'Kebutuhan'],
            ['date' => '2024-12-13', 'description' => 'Buku Kuliah', 'amount' => 120000, 'category' => 'Pendidikan']
        ];
    }

    private function getIncomeTransactions($userId)
    {
        // Example implementation
        return [
            ['date' => '2024-12-01', 'description' => 'Transfer Orang Tua', 'amount' => 2000000],
            ['date' => '2024-12-15', 'description' => 'Part-time Job', 'amount' => 1500000]
        ];
    }

    private function callAIAnalysis($data)
    {
        // This is where you would call your actual AI API
        // For now, return mock analysis based on the data

        $balance = $data['summary']['balance'];
        $totalIncome = $data['summary']['total_income'];
        $totalExpense = $data['summary']['total_expense'];

        // Simple analysis logic
        $savings = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? ($savings / $totalIncome) * 100 : 0;

        $status = 'healthy';
        if ($savings < 0) {
            $status = 'deficit';
        } elseif ($savingsRate < 20) {
            $status = 'warning';
        }

        return [
            'anomalies' => $this->generateAnomalies($data),
            'insights' => $this->generateInsights($data),
            'advice' => $this->generateAdvice($data),
            'saving_opportunities' => $this->generateSavingOpportunities($data),
            'status' => $status,
            'message' => $this->generateMessage($status, $data),
            'health_score' => $this->calculateHealthScore($data)
        ];
    }

    private function generateAnomalies($data)
    {
        $anomalies = [];
        $totalIncome = $data['summary']['total_income'];

        // Check if any category spending is too high
        foreach ($data['topCategories'] as $category) {
            if ($category['amount'] > $totalIncome * 0.3) {
                $anomalies[] = [
                    'title' => "Pengeluaran {$category['name']} Terlalu Tinggi",
                    'description' => "Pengeluaran untuk {$category['name']} mencapai " . number_format($category['amount']) . " (" . round(($category['amount'] / $totalIncome) * 100) . "% dari pemasukan)"
                ];
            }
        }

        return $anomalies;
    }

    private function generateInsights($data)
    {
        // Implementation for insights generation
        return [];
    }

    private function generateAdvice($data)
    {
        // Implementation for advice generation
        return [];
    }

    private function generateSavingOpportunities($data)
    {
        // Implementation for saving opportunities
        return [];
    }

    private function generateMessage($status, $data)
    {
        // Implementation for message generation
        return "Analisis keuangan Anda selesai.";
    }

    private function calculateHealthScore($data)
    {
        // Implementation for health score calculation
        return 75;
    }
}
