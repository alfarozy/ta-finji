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
    public function financialInsight()
    {
        $user = auth()->user();
        $now = now();

        // Periode bulan ini
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Periode bulan sebelumnya
        $startOfPrevMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfPrevMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. SUMMARY BULAN INI
        $summary = $this->getMonthlySummary($user->id, $startOfMonth, $endOfMonth);

        // 2. SUMMARY BULAN SEBELUMNYA
        $prevSummary = $this->getMonthlySummary($user->id, $startOfPrevMonth, $endOfPrevMonth);

        // 3. DATA TIME SERIES (30 hari terakhir)
        $timeSeriesData = $this->getTimeSeriesData($user->id, $now->copy()->subDays(30), $endOfMonth);

        // 4. TOP CATEGORIES (pengeluaran bulan ini)
        $topCategories = $this->getTopCategories($user->id, $startOfMonth, $endOfMonth);

        // 5. BUDGETS USER
        $budgets = $this->getUserBudgets($user->id);

        // 6. HEALTH BREAKDOWN
        $healthBreakdown = $this->calculateHealthBreakdown($summary, $budgets);

        // 7. QUICK INSIGHTS
        $quickInsights = $this->generateQuickInsights($summary, $prevSummary, $topCategories);

        return view('backoffice.financial-insight', [
            'summary' => $summary,
            'prevSummary' => $prevSummary,
            'labels' => $timeSeriesData['labels'],
            'spending' => $timeSeriesData['spending'],
            'income' => $timeSeriesData['income'],
            'topCategories' => $topCategories,
            'budgets' => $budgets,
            'healthBreakdown' => $healthBreakdown,
            'quickInsights' => $quickInsights,
        ]);
    }

    /**
     * Get monthly financial summary
     */
    private function getMonthlySummary($userId, $startDate, $endDate)
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense'),
                DB::raw('COALESCE(SUM(CASE WHEN type = "income" THEN amount ELSE -amount END), 0) as net_flow')
            )
            ->first()
            ->toArray();
    }

    /**
     * Get time series data for charts
     */
    private function getTimeSeriesData($userId, $startDate, $endDate)
    {
        $days = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $days[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Get daily aggregates
        $dailyData = Transaction::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income'),
                DB::raw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $incomeData = [];
        $expenseData = [];

        foreach ($days as $day) {
            $date = Carbon::parse($day);
            $labels[] = $date->format('d M');

            if (isset($dailyData[$day])) {
                $incomeData[] = (float) $dailyData[$day]->income;
                $expenseData[] = (float) $dailyData[$day]->expense;
            } else {
                $incomeData[] = 0;
                $expenseData[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'spending' => $expenseData
        ];
    }

    /**
     * Get top expense categories
     */
    private function getTopCategories($userId, $startDate, $endDate)
    {
        return Transaction::with('category:id,name')
            ->where('user_id', $userId)
            ->where('type', Transaction::TYPE_EXPENSE)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get()
            ->groupBy('transaction_category_id')
            ->map(function ($items) {
                $category = $items->first()->category;

                return [
                    'name'   => $category?->name ?? 'Tidak diketahui',
                    'amount' => (float) $items->sum('amount'),
                    'count'  => (int) $items->count(),
                ];
            })
            ->sortByDesc('amount')
            ->take(8)
            ->values()
            ->toArray();
    }

    /**
     * Get user budgets
     */
    private function getUserBudgets($userId)
    {
        return Budget::where('user_id', $userId)
            ->where('month', now()->format('Y-m'))
            ->with('category')
            ->get()
            ->map(function ($budget) {
                return [
                    'category_id' => $budget->category_id,
                    'category_name' => $budget->category->name ?? 'Uncategorized',
                    'amount' => (float) $budget->amount,
                    'period' => $budget->period
                ];
            })
            ->toArray();
    }

    /**
     * Calculate health breakdown
     */
    private function calculateHealthBreakdown($summary, $budgets)
    {
        $totalIncome = $summary['total_income'] ?? 0;
        $totalExpense = $summary['total_expense'] ?? 0;
        $savings = $totalIncome - $totalExpense;

        // 1. Savings Rate Score
        $savingsRate = $totalIncome > 0 ? ($savings / $totalIncome) * 100 : 0;
        $savingsScore = min(100, max(0, $savingsRate * 2)); // Normalize to score

        // 2. Expense to Income Ratio Score
        $expenseRatio = $totalIncome > 0 ? ($totalExpense / $totalIncome) * 100 : 100;
        $expenseScore = $expenseRatio <= 80 ? 100 : max(0, 100 - (($expenseRatio - 80) * 5));

        // 3. Budget Adherence Score (if budgets exist)
        $budgetScore = 75; // Default
        if (!empty($budgets)) {
            $adherenceScores = [];
            foreach ($budgets as $budget) {
                // Logic untuk menghitung adherence per budget
                $adherenceScores[] = 80; // Contoh static
            }
            $budgetScore = !empty($adherenceScores) ? array_sum($adherenceScores) / count($adherenceScores) : 75;
        }

        // 4. Emergency Fund Score (asumsi ada field balance di user)
        $emergencyMonths = $totalExpense > 0 ? (auth()->user()->balance ?? 0) / $totalExpense : 0;
        $emergencyScore = min(100, $emergencyMonths * 33.33); // 3 bulan = 100%

        return [
            [
                'metric' => 'Savings Rate',
                'score' => round($savingsScore),
                'desc' => round($savingsRate, 1) . '% dari pemasukan'
            ],
            [
                'metric' => 'Expense Control',
                'score' => round($expenseScore),
                'desc' => round($expenseRatio, 1) . '% dari pemasukan'
            ],
            [
                'metric' => 'Budget Adherence',
                'score' => round($budgetScore),
                'desc' => !empty($budgets) ? count($budgets) . ' kategori' : 'Tidak ada budget'
            ],
            [
                'metric' => 'Emergency Fund',
                'score' => round($emergencyScore),
                'desc' => round($emergencyMonths, 1) . ' bulan pengeluaran'
            ]
        ];
    }

    /**
     * Generate quick insights
     */
    private function generateQuickInsights($summary, $prevSummary, $topCategories)
    {
        $insights = [];

        // Insight 1: Perbandingan dengan bulan lalu
        $currentIncome = $summary['total_income'] ?? 0;
        $prevIncome = $prevSummary['total_income'] ?? 0;
        $incomeChange = $prevIncome > 0 ? (($currentIncome - $prevIncome) / $prevIncome) * 100 : 0;

        if (abs($incomeChange) > 10) {
            $insights[] = $incomeChange > 0
                ? "Pemasukan meningkat " . round($incomeChange) . "% dari bulan lalu"
                : "Pemasukan turun " . round(abs($incomeChange)) . "% dari bulan lalu";
        }

        // Insight 2: Top spending category
        if (!empty($topCategories)) {
            $topCat = $topCategories[0];
            $insights[] = "Pengeluaran terbanyak di " . $topCat['name'] . " (Rp " . number_format($topCat['amount']) . ")";
        }

        // Insight 3: Savings status
        $savings = ($summary['total_income'] ?? 0) - ($summary['total_expense'] ?? 0);
        if ($savings > 0) {
            $savingsRate = $summary['total_income'] > 0 ? ($savings / $summary['total_income']) * 100 : 0;
            $insights[] = "Tabungan bulan ini: Rp " . number_format($savings) . " (" . round($savingsRate, 1) . "%)";
        } else {
            $insights[] = "⚠️ Defisit bulan ini: Rp " . number_format(abs($savings));
        }

        return $insights;
    }

    /**
     * API endpoint for AI analysis
     */
    public function analyze(Request $request)
    {
        $user = auth()->user();

        // Data yang sama dengan index
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $summary = $this->getMonthlySummary($user->id, $startOfMonth, $endOfMonth);
        $topCategories = $this->getTopCategories($user->id, $startOfMonth, $endOfMonth);
        $budgets = $this->getUserBudgets($user->id);

        // Generate AI analysis
        $analysis = $this->generateAIAnalysis($summary, $topCategories, $budgets);

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'generated_at' => now()->toDateTimeString()
        ]);
    }

    /**
     * Generate AI analysis logic
     */
    private function generateAIAnalysis($summary, $topCategories, $budgets)
    {
        $totalIncome = $summary['total_income'] ?? 0;
        $totalExpense = $summary['total_expense'] ?? 0;
        $savings = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? ($savings / $totalIncome) * 100 : 0;

        // Determine status
        if ($totalExpense > $totalIncome) {
            $status = 'deficit';
            $healthScore = max(10, min(40, 30 + ($savingsRate / 10)));
        } elseif ($savingsRate < 20) {
            $status = 'warning';
            $healthScore = max(40, min(70, 50 + ($savingsRate / 2)));
        } else {
            $status = 'healthy';
            $healthScore = max(70, min(95, 75 + ($savingsRate / 4)));
        }

        // Generate anomalies
        $anomalies = [];
        if ($totalExpense > $totalIncome * 0.8) {
            $anomalies[] = [
                'title' => "Pengeluaran Mendekati Pemasukan",
                'description' => "Pengeluaran mencapai " . round(($totalExpense / $totalIncome) * 100) . "% dari pemasukan bulanan."
            ];
        }

        // Check for unusual spending in categories
        $avgExpense = $totalExpense / max(1, count($topCategories));
        foreach ($topCategories as $cat) {
            if ($cat['amount'] > $avgExpense * 3) { // 3x dari rata-rata
                $anomalies[] = [
                    'title' => "Pengeluaran Tinggi di " . $cat['name'],
                    'description' => "Kategori ini menghabiskan Rp " . number_format($cat['amount']) . " (" . round(($cat['amount'] / $totalExpense) * 100) . "% dari total pengeluaran)."
                ];
            }
        }

        // Generate insights
        $insights = [[
            'title' => "Keseimbangan Keuangan",
            'description' => $savingsRate >= 20
                ? "Bagus! Anda menabung " . round($savingsRate) . "% dari pemasukan bulanan."
                : "Tabungan sebesar " . round($savingsRate) . "% dari pemasukan. Targetkan minimal 20%."
        ]];

        // Generate advice
        $advice = [];
        if ($status === 'deficit') {
            $advice[] = [
                'title' => "Prioritaskan Pengurangan Pengeluaran",
                'description' => "Identifikasi 2-3 kategori pengeluaran terbesar yang bisa dikurangi."
            ];
        } elseif ($status === 'warning') {
            $advice[] = [
                'title' => "Tingkatkan Tabungan",
                'description' => "Alokasikan 5% lebih banyak ke tabungan bulan depan."
            ];
        }

        // Saving opportunities
        $savingOpportunities = [];
        $potentialSavings = round($totalExpense * 0.12);
        if ($potentialSavings > 10000) {
            $savingOpportunities[] = [
                'title' => "Optimasi Pengeluaran Rutin",
                'description' => "Potensi hemat Rp " . number_format($potentialSavings) . " per bulan dengan efisiensi 12%."
            ];
        }

        return [
            'status' => $status,
            'message' => $this->getStatusMessage($status, $savingsRate),
            'health_score' => round($healthScore),
            'anomalies' => $anomalies,
            'insights' => $insights,
            'advice' => $advice,
            'saving_opportunities' => $savingOpportunities,
        ];
    }

    private function getStatusMessage($status, $savingsRate)
    {
        switch ($status) {
            case 'healthy':
                return "Keuangan Anda dalam kondisi baik dengan tabungan " . round($savingsRate) . "% dari pemasukan.";
            case 'warning':
                return "Perhatian: tingkat tabungan rendah (" . round($savingsRate) . "%).";
            case 'deficit':
                return "Defisit terdeteksi. Evaluasi pengeluaran segera.";
            default:
                return "Analisis keuangan selesai.";
        }
    }

    /**
     * Export report
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $data = $this->indexData(); // Reuse index data

        // Generate PDF atau format lain
        // Implementasi sesuai kebutuhan

        return response()->json([
            'success' => true,
            'message' => 'Export feature coming soon'
        ]);
    }
}
