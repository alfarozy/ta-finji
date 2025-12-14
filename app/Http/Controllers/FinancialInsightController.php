<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Budget;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FinancialInsightController extends Controller
{
    public function index()
    {
        $user = Auth::user();
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
        $timeSeriesData = $this->getTimeSeriesData($user->id, $startOfMonth, $endOfMonth);

        // 4. TOP CATEGORIES (pengeluaran bulan ini)
        $topCategories = $this->getTopCategories($user->id, $startOfMonth, $endOfMonth);

        // 5. BUDGETS USER
        $budgets = $this->getUserBudgets($user->id);

        // 6. HEALTH BREAKDOWN
        $healthBreakdown = $this->calculateHealthBreakdown($summary, $budgets);

        // 7. TOTAL BALANCE (dari semua transaksi)
        $totalBalance = $this->getTotalBalance($user->id);
        $summary['balance'] = $totalBalance;

        return view('backoffice.financial-insight', [
            'summary' => $summary,
            'prevSummary' => $prevSummary,
            'labels' => $timeSeriesData['labels'],
            'spending' => $timeSeriesData['spending'],
            'income' => $timeSeriesData['income'],
            'topCategories' => $topCategories,
            'budgets' => $budgets,
            'healthBreakdown' => $healthBreakdown,
        ]);
    }

    public function recentTransaction()
    {
        $transactions = Transaction::where('user_id', auth()->id())
            ->with('transactionCategory')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->transaction_date->format('Y-m-d'),
                    'category' => $transaction->transactionCategory->name ?? 'Uncategorized',
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description
                ];
            });

        return response()->json($transactions);
    }
    /**
     * Get monthly financial summary - disesuaikan dengan field model Anda
     */
    private function getMonthlySummary($userId, $startDate, $endDate)
    {
        $result = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('
                COALESCE(SUM(CASE WHEN type = \'income\' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = \'expense\' THEN amount ELSE 0 END), 0) as total_expense,
                COALESCE(SUM(CASE WHEN type = \'income\' THEN amount ELSE -amount END), 0) as net_flow
            ')
            ->first();

        return [
            'total_income' => (float) ($result->total_income ?? 0),
            'total_expense' => (float) ($result->total_expense ?? 0),
            'net_flow' => (float) ($result->net_flow ?? 0)
        ];
    }

    /**
     * Get total balance from all transactions
     */
    private function getTotalBalance($userId)
    {
        $result = Transaction::where('user_id', $userId)
            ->selectRaw('
                COALESCE(SUM(CASE WHEN type = \'income\' THEN amount ELSE -amount END), 0) as balance
            ')
            ->first();

        return (float) ($result->balance ?? 0);
    }

    /**
     * Get time series data for charts - disesuaikan dengan field transaction_date
     */
    private function getTimeSeriesData($userId, $startDate, $endDate)
    {
        // Generate date range
        $days = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $days[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Get daily aggregates - menggunakan transaction_date
        $dailyData = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw("
                DATE(transaction_date) as day,
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
            ")
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->orderBy('day', 'asc')
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
     * Get top expense categories - disesuaikan dengan transaction_category_id
     */
    private function getTopCategories($userId, $startDate, $endDate)
    {
        return Transaction::where('user_id', $userId)
            ->where('transactions.type', Transaction::TYPE_EXPENSE)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->join('transaction_categories', 'transactions.transaction_category_id', '=', 'transaction_categories.id')
            ->selectRaw("
                transaction_categories.name,
                COALESCE(SUM(transactions.amount), 0) as amount,
                COUNT(transactions.id) as count
            ")
            ->groupBy('transaction_categories.id', 'transaction_categories.name')
            ->orderByDesc('amount')
            ->limit(8)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'amount' => (float) $item->amount,
                    'count' => (int) $item->count
                ];
            })
            ->toArray();
    }

    /**
     * Get user budgets - disesuaikan dengan transaction_category_id
     */
    private function getUserBudgets($userId)
    {
        $currentMonth = now()->format('Y-m');

        $budgets = Budget::where('user_id', $userId)
            ->with(['category' => function ($query) {
                $query->where('type', TransactionCategory::TYPE_EXPENSE);
            }])
            ->get()
            ->filter(function ($budget) {
                return $budget->category !== null; // Hanya budget untuk kategori expense
            })
            ->map(function ($budget) use ($userId, $currentMonth) {
                // Hitung actual spending untuk budget ini bulan ini
                $actualSpent = Transaction::where('user_id', $userId)
                    ->where('transaction_category_id', $budget->transaction_category_id)
                    ->where('type', Transaction::TYPE_EXPENSE)
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum('amount');

                return [
                    'category_id' => $budget->transaction_category_id,
                    'category_name' => $budget->category->name ?? 'Uncategorized',
                    'budget_amount' => (float) $budget->amount,
                    'actual_spent' => (float) $actualSpent,
                    'remaining' => (float) ($budget->amount - $actualSpent),
                    'description' => $budget->description
                ];
            })
            ->toArray();

        // Jika tidak ada budget bulan ini, ambil semua kategori expense
        if (empty($budgets)) {
            $expenseCategories = TransactionCategory::where('type', TransactionCategory::TYPE_EXPENSE)
                ->get();

            foreach ($expenseCategories as $category) {
                $actualSpent = Transaction::where('user_id', $userId)
                    ->where('transaction_category_id', $category->id)
                    ->where('type', Transaction::TYPE_EXPENSE)
                    ->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year)
                    ->sum('amount');

                if ($actualSpent > 0) {
                    $budgets[] = [
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                        'budget_amount' => 0, // Tidak ada budget
                        'actual_spent' => (float) $actualSpent,
                        'remaining' => (float) (0 - $actualSpent), // Negatif karena over tanpa budget
                        'description' => 'No budget set'
                    ];
                }
            }
        }

        return $budgets;
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
        $savingsScore = min(100, max(0, $savingsRate * 2));

        // 2. Expense to Income Ratio Score
        $expenseRatio = $totalIncome > 0 ? ($totalExpense / $totalIncome) * 100 : 100;
        $expenseScore = $expenseRatio <= 80 ? 100 : max(0, 100 - (($expenseRatio - 80) * 5));

        // 3. Budget Adherence Score
        $budgetScore = 75;
        if (!empty($budgets)) {
            $adherenceScores = [];
            foreach ($budgets as $budget) {
                if ($budget['budget_amount'] > 0) {
                    $usagePercent = ($budget['actual_spent'] / $budget['budget_amount']) * 100;
                    // Score: 100% jika usage 0-100%, menurun jika over budget
                    $score = $usagePercent <= 100 ?
                        100 - ($usagePercent * 0.5) :
                        max(0, 100 - (($usagePercent - 100) * 2));
                    $adherenceScores[] = $score;
                }
            }
            $budgetScore = !empty($adherenceScores) ?
                round(array_sum($adherenceScores) / count($adherenceScores)) : 75;
        }

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
                'desc' => !empty($budgets) ? count($budgets) . ' kategori' : 'Belum ada budget'
            ]
        ];
    }

    /**
     * API endpoint for AI analysis
     */
    public function analyze(Request $request)
    {
        $user = Auth::user();

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

        // Check expense ratio
        if ($totalIncome > 0 && ($totalExpense / $totalIncome) > 0.8) {
            $ratio = round(($totalExpense / $totalIncome) * 100);
            $anomalies[] = [
                'title' => "Pengeluaran Mendekati Pemasukan",
                'description' => "Pengeluaran mencapai {$ratio}% dari pemasukan. Idealnya di bawah 80%."
            ];
        }

        // Check for unusual spending in categories
        $avgExpense = count($topCategories) > 0 ? $totalExpense / count($topCategories) : 0;
        foreach ($topCategories as $cat) {
            if ($avgExpense > 0 && $cat['amount'] > $avgExpense * 3) {
                $percentage = $totalExpense > 0 ? round(($cat['amount'] / $totalExpense) * 100) : 0;
                $anomalies[] = [
                    'title' => "Pengeluaran Tinggi di " . $cat['name'],
                    'description' => "Rp " . number_format($cat['amount']) . " ({$percentage}% dari total)."
                ];
            }
        }

        // Check budget overruns
        foreach ($budgets as $budget) {
            if ($budget['actual_spent'] > $budget['budget_amount'] && $budget['budget_amount'] > 0) {
                $overrun = $budget['actual_spent'] - $budget['budget_amount'];
                $overrunPercent = round(($overrun / $budget['budget_amount']) * 100);
                $anomalies[] = [
                    'title' => "Melebihi Budget: " . $budget['category_name'],
                    'description' => "Melebihi Rp " . number_format($overrun) . " ({$overrunPercent}% dari budget)."
                ];
            }
        }

        // Check for categories without budget but high spending
        foreach ($budgets as $budget) {
            if ($budget['budget_amount'] == 0 && $budget['actual_spent'] > 100000) {
                $anomalies[] = [
                    'title' => "Pengeluaran Signifikan Tanpa Budget",
                    'description' => "{$budget['category_name']}: Rp " . number_format($budget['actual_spent']) . " tanpa budget yang ditetapkan."
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

        // Income diversity insight
        $incomeCategories = Transaction::where('user_id', Auth::id())
            ->where('type', Transaction::TYPE_INCOME)
            ->whereMonth('transaction_date', now()->month)
            ->with('transactionCategory')
            ->get()
            ->groupBy('transaction_category_id')
            ->count();

        if ($incomeCategories > 1) {
            $insights[] = [
                'title' => "Sumber Pemasukan Beragam",
                'description' => "Anda memiliki " . $incomeCategories . " sumber pemasukan berbeda."
            ];
        }

        // Generate advice based on status
        $advice = [];
        if ($status === 'deficit') {
            $advice[] = [
                'title' => "Prioritaskan Pengurangan Pengeluaran",
                'description' => "Identifikasi 2-3 kategori pengeluaran terbesar yang bisa dikurangi."
            ];
            $advice[] = [
                'title' => "Tinjau Pengeluaran Rutin",
                'description' => "Periksa langganan dan pengeluaran otomatis yang mungkin tidak diperlukan."
            ];
        } elseif ($status === 'warning') {
            $advice[] = [
                'title' => "Tingkatkan Tabungan",
                'description' => "Alokasikan 5% lebih banyak ke tabungan bulan depan."
            ];
            $advice[] = [
                'title' => "Buat Budget Realistis",
                'description' => "Tetapkan budget untuk setiap kategori pengeluaran utama."
            ];
        } else {
            $advice[] = [
                'title' => "Pertahankan Kebiasaan Baik",
                'description' => "Lanjutkan pengelolaan keuangan yang baik dengan tracking rutin."
            ];
            if ($savingsRate < 30) {
                $advice[] = [
                    'title' => "Tingkatkan Tabungan",
                    'description' => "Coba tingkatkan tabungan menjadi 30% untuk mencapai financial freedom lebih cepat."
                ];
            }
        }

        // Budget-related advice
        if (empty($budgets)) {
            $advice[] = [
                'title' => "Buat Budget",
                'description' => "Mulai dengan membuat budget untuk 3 kategori pengeluaran terbesar Anda."
            ];
        }

        // Saving opportunities
        $savingOpportunities = [];
        $potentialSavings = round($totalExpense * 0.10); // 10% potensi penghematan
        if ($potentialSavings > 50000) {
            $savingOpportunities[] = [
                'title' => "Optimasi Pengeluaran",
                'description' => "Potensi hemat Rp " . number_format($potentialSavings) . " per bulan dengan evaluasi pengeluaran."
            ];
        }

        // Add investment advice if savings are good
        if ($savingsRate > 25 && $savings > 1000000) {
            $savingOpportunities[] = [
                'title' => "Peluang Investasi",
                'description' => "Dengan tabungan Rp " . number_format($savings) . "/bulan, pertimbangkan investasi jangka panjang."
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
        $user = Auth::user();
        $type = $request->get('type', 'json');

        // Get all data
        $data = $this->getExportData($user->id);

        if ($type === 'json') {
            return response()->json([
                'success' => true,
                'data' => $data,
                'exported_at' => now()->toDateTimeString()
            ]);
        }

        // For PDF or other formats, you can implement here
        return response()->json([
            'success' => false,
            'message' => 'Format export belum tersedia'
        ]);
    }

    private function getExportData($userId)
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $summary = $this->getMonthlySummary($userId, $startOfMonth, $endOfMonth);
        $topCategories = $this->getTopCategories($userId, $startOfMonth, $endOfMonth);
        $budgets = $this->getUserBudgets($userId);
        $analysis = $this->generateAIAnalysis($summary, $topCategories, $budgets);

        // Get recent transactions
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('transactionCategory')
            ->orderBy('transaction_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->transaction_date->format('Y-m-d'),
                    'category' => $transaction->transactionCategory->name ?? 'Uncategorized',
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description
                ];
            });

        return [
            'period' => $now->format('F Y'),
            'summary' => $summary,
            'top_categories' => $topCategories,
            'budgets' => $budgets,
            'analysis' => $analysis,
            'recent_transactions' => $recentTransactions,
            'generated_at' => now()->toDateTimeString()
        ];
    }
}
