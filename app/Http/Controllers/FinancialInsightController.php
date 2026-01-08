<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Budget;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;


class FinancialInsightController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $now = now();

        // Ambil dari request atau default bulan ini
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : $now->copy()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : $now->copy()->endOfMonth();

        $rangeDays = $startDate->diffInDays($endDate) + 1;

        $startOfPrevPeriod = $startDate->copy()->subDays($rangeDays);
        $endOfPrevPeriod = $endDate->copy()->subDays($rangeDays);
        // 1. SUMMARY BULAN INI
        $summary = $this->getMonthlySummary($user->id, $startDate, $endDate);

        // 2. SUMMARY BULAN SEBELUMNYA
        $prevSummary = $this->getMonthlySummary($user->id, $startOfPrevPeriod, $endOfPrevPeriod);

        // 3. DATA TIME SERIES (30 hari terakhir)
        $timeSeriesData = $this->getTimeSeriesData($user->id, $startDate, $endDate);

        // 4. TOP CATEGORIES (pengeluaran bulan ini)
        $topCategories = $this->getTopCategories($user->id, $startDate, $endDate);

        // 5. BUDGETS USER
        $budgets = $this->getUserBudgets($user->id);

        // 6. HEALTH BREAKDOWN
        $healthBreakdown = $this->calculateHealthBreakdown($summary, $budgets);

        // 7. TOTAL BALANCE (dari semua transaksi)
        $totalBalance = $this->getTotalBalance($user->id);
        $summary['balance'] = $totalBalance;

        // 8. RECENT TRANSACTIONS
        $transactions = Transaction::where('user_id', auth()->id())
            ->with('transactionCategory')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->transaction_date->format('Y-m-d'),
                    'category' => $transaction->transactionCategory->name ?? 'Uncategorized',
                    'type' => $transaction->type,
                    'amount' => 'Rp ' . number_format($transaction->amount, 0, ',', '.'),
                    'description' => $transaction->description
                ];
            });

        $analysis = $this->analyzeFinancial(
            json_encode($transactions),
            $request->filled('start_date'),
            $request->filled('end_date')
        );

        $status = data_get($analysis, 'financial_health.status', 'healthy');

        $statusMetaMap = [
            'healthy' => ['class' => 'success', 'icon' => '✅', 'label' => 'SEHAT'],
            'warning' => ['class' => 'warning', 'icon' => '⚠️', 'label' => 'PERHATIAN'],
            'deficit' => ['class' => 'danger', 'icon' => '❌', 'label' => 'DEFISIT'],
        ];

        $analysisStatus = [
            'status' => $status,
            'score' => data_get($analysis, 'financial_health.score'),
            'reasoning' => data_get($analysis, 'financial_health.reasoning'),
            'meta' => $statusMetaMap[$status] ?? $statusMetaMap['healthy'],
        ];

        return view('backoffice.financial-insight', [
            'analysisStatus' => $analysisStatus,
            'analysis' => $analysis,
            'summary' => $summary,
            'prevSummary' => $prevSummary,
            'labels' => $timeSeriesData['labels'],
            'spending' => $timeSeriesData['spending'],
            'income' => $timeSeriesData['income'],
            'topCategories' => $topCategories,
            'budgets' => $budgets,
            'healthBreakdown' => $healthBreakdown,
            'transactions' => $transactions->take(10),
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $user = auth()->user();
        $user = Auth::user();
        $now = now();

        // Ambil dari request atau default bulan ini
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : $now->copy()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : $now->copy()->endOfMonth();

        $rangeDays = $startDate->diffInDays($endDate) + 1;

        $startOfPrevPeriod = $startDate->copy()->subDays($rangeDays);
        $endOfPrevPeriod = $endDate->copy()->subDays($rangeDays);
        // 1. SUMMARY BULAN INI
        $summary = $this->getMonthlySummary($user->id, $startDate, $endDate);

        // 2. SUMMARY BULAN SEBELUMNYA
        $prevSummary = $this->getMonthlySummary($user->id, $startOfPrevPeriod, $endOfPrevPeriod);

        // 3. DATA TIME SERIES (30 hari terakhir)
        $timeSeriesData = $this->getTimeSeriesData($user->id, $startDate, $endDate);

        // 4. TOP CATEGORIES (pengeluaran bulan ini)
        $topCategories = $this->getTopCategories($user->id, $startDate, $endDate);

        // 5. BUDGETS USER
        $budgets = $this->getUserBudgets($user->id);

        // 6. HEALTH BREAKDOWN
        $healthBreakdown = $this->calculateHealthBreakdown($summary, $budgets);

        // 7. TOTAL BALANCE (dari semua transaksi)
        $totalBalance = $this->getTotalBalance($user->id);
        $summary['balance'] = $totalBalance;

        // 8. RECENT TRANSACTIONS
        $transactions = Transaction::where('user_id', auth()->id())
            ->with('transactionCategory')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->transaction_date->format('Y-m-d'),
                    'category' => $transaction->transactionCategory->name ?? 'Uncategorized',
                    'type' => $transaction->type,
                    'amount' => 'Rp ' . number_format($transaction->amount, 0, ',', '.'),
                    'description' => $transaction->description
                ];
            });

        $analysis = $this->analyzeFinancial(
            json_encode($transactions),
            $request->filled('start_date'),
            $request->filled('end_date')
        );

        $status = data_get($analysis, 'financial_health.status', 'healthy');

        $statusMetaMap = [
            'healthy' => ['class' => 'success', 'icon' => '✅', 'label' => 'SEHAT'],
            'warning' => ['class' => 'warning', 'icon' => '⚠️', 'label' => 'PERHATIAN'],
            'deficit' => ['class' => 'danger', 'icon' => '❌', 'label' => 'DEFISIT'],
        ];

        $analysisStatus = [
            'status' => $status,
            'score' => data_get($analysis, 'financial_health.score'),
            'reasoning' => data_get($analysis, 'financial_health.reasoning'),
            'meta' => $statusMetaMap[$status] ?? $statusMetaMap['healthy'],
        ];

        // ===== Render PDF =====
        $pdf = Pdf::loadView('pdf.financial-insight', [
            'analysisStatus' => $analysisStatus,
            'analysis' => $analysis,
            'summary' => $summary,
            'prevSummary' => $prevSummary,
            'labels' => $timeSeriesData['labels'],
            'spending' => $timeSeriesData['spending'],
            'income' => $timeSeriesData['income'],
            'topCategories' => $topCategories,
            'budgets' => $budgets,
            'healthBreakdown' => $healthBreakdown,
            'transactions' => $transactions->take(10),
        ])->setPaper('A4', 'portrait');

        return $pdf->download(
            'Insight-Keuangan-' . now()->format('Y-m-d') . '.pdf'
        );
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

        // Cari tanggal transaksi terakhir
        $lastTransactionDate = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->max('transaction_date');

        // Hitung jumlah hari sampai transaksi terakhir
        $days = $lastTransactionDate
            ? $startDate->diffInDays(\Carbon\Carbon::parse($lastTransactionDate)) + 1
            : 0;

        return [
            'total_income' => (float) ($result->total_income ?? 0),
            'total_expense' => (float) ($result->total_expense ?? 0),
            'net_flow' => (float) ($result->net_flow ?? 0),
            'days' => $days

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

        // Cari tanggal transaksi terakhir
        $lastTransactionDate = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->max('transaction_date');

        // Hitung jumlah hari sampai transaksi terakhir
        $days = $lastTransactionDate
            ? $startDate->diffInDays(\Carbon\Carbon::parse($lastTransactionDate)) + 1
            : 0;

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'spending' => $expenseData,
            'days' => $days
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

        // 20% savings = score 100
        $savingsScore = min(100, max(0, ($savingsRate / 20) * 100));


        // 2. Expense to Income Ratio Score
        $expenseRatio = $totalIncome > 0 ? ($totalExpense / $totalIncome) * 100 : 100;

        if ($expenseRatio <= 60) {
            $expenseScore = 100;
        } elseif ($expenseRatio <= 80) {
            $expenseScore = 100 - (($expenseRatio - 60) * 2.5);
        } else {
            $expenseScore = max(0, 50 - (($expenseRatio - 80) * 5));
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
        $savingsRate = $totalIncome > 0 ? (($totalIncome - $totalExpense) / $totalIncome) * 100 : 0;
        $expenseRatio = $totalIncome > 0 ? ($totalExpense / $totalIncome) * 100 : 100;

        if ($expenseRatio > 100) {
            // DEFICIT
            $status = 'deficit';
            $healthScore = max(15, 40 - (($expenseRatio - 100) * 1.5));
        } elseif ($savingsRate < 10) {
            // WARNING
            $status = 'warning';
            $healthScore = 45 + ($savingsRate * 2);
        } elseif ($savingsRate < 20) {
            // FAIR
            $status = 'fair';
            $healthScore = 65 + (($savingsRate - 10) * 1.5);
        } else {
            // HEALTHY
            $status = 'healthy';
            $healthScore = min(95, 80 + (($savingsRate - 20) * 0.75));
        }

        $healthScore = round(max(10, min(95, $healthScore)));


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

    public function analyzeFinancial($transactions, $startDate, $endDate)
    {
        $prompt = $this->buildFinancialPrompt(
            $transactions
        );
        try {
            if (empty($startDate) || empty($endDate)) {
                throw new \InvalidArgumentException();
            }
            // API call remains the same
            $apiKey = config('services.google.gemini_api_key');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $response = Http::post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            $data = $response->json();

            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if (!$text) {
                throw new \Exception('No response from AI');
            }

            // Clean and parse JSON
            $clean = preg_replace('/```(json)?|```/', '', $text);
            $clean = trim($clean);
            $parser = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
            return $parser;
        } catch (\Throwable $e) {
            // fallback jika API call gagal
            return [
                'financial_health' => [
                    'score' => 00,
                    'status' => 'warning',
                    'reasoning' => 'Silakan generate analisis terlebih dahulu'
                ],
                'anomalies' => [],
                'advice' => [],
                'message' => 'Silakan generate analisis terlebih dahulu.'
            ];
        }
    }


    private function buildFinancialPrompt($transactions)
    {
        return <<<EOT
Kamu adalah asisten keuangan profesional.
Tugasmu:
1. Deteksi anomali pengeluaran
2. Hitung Financial Health Score (0–100)
3. Buat advice terpadu (insight + rekomendasi)

Data User Transactions
$transactions

A. DETEKSI ANOMALI PENGELUARAN
Identifikasi dan jelaskan anomali berikut:
- Transaksi besar yang jarang terjadi.
- Perubahan pola konsumsi yang tidak konsisten atau impulsif.

B. PENILAIAN KESEHATAN KEUANGAN
Hitung dan nilai kondisi keuangan user:
- Bandingkan pemasukan vs pengeluaran.
- Nilai kemampuan menabung (sisa saldo).
- Evaluasi keseimbangan antara kebutuhan dan keinginan.
Buat skor Financial Health Score (0–100) dengan interpretasi:
- 80–100 → sehat dan stabil (healthy)
- 50–79 → perlu perhatian (warning)
- < 50 → berisiko / defisit (deficit)

C. ADVICE TERPADU
Gabungkan semua temuan menjadi satu kumpulan advice utama, yang mencakup:
- Ringkasan kondisi keuangan user saat ini.
- Dampak dari anomali yang ditemukan.
- Insight penting tentang kebiasaan finansial user.
- Rekomendasi tindakan yang konkret dan realistis:
  - kebiasaan yang perlu dikurangi,
  - langkah kecil untuk perbaikan,
  - peluang hemat tanpa mengorbankan kebutuhan utama.
- Jika ada defisit, berikan peringatan yang lembut, empatik, dan solutif.

GAYA BAHASA
- Gunakan Bahasa Indonesia yang ramah, natural, dan suportif.
- Jangan menghakimi user.
- Sertakan angka (Rp xxx.xxx) atau persentase jika relevan.
- Fokus pada solusi dan perbaikan bertahap.

FORMAT OUTPUT JSON TANPA TEKS:
{
  "financial_health": {
    "score": 0,
    "status": "healthy|warning|deficit",
    "reasoning": ""
  },
  "anomalies": [
    {
      "title": "",
      "description": ""
    }],
  "advice": [
    {
        "icon": "Gunakan emoticon yang relevan",
        "title": "",
        "description": ""
    }],
  "message": ""
}

EOT;
    }
}
