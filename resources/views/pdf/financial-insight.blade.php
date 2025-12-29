<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Insight Keuangan - {{ date('F Y') }}</title>
    <style>
        /* Reset dan Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background-color: #fff;
            padding: 20px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #7367f0;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #7367f0;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header .subtitle {
            color: #666;
            font-size: 14px;
        }

        .header .period {
            color: #888;
            font-size: 12px;
            margin-top: 5px;
        }

        /* User Info */
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #7367f0;
        }

        .user-info .row {
            display: flex;
            justify-content: space-between;
        }

        .user-info .col {
            flex: 1;
        }

        .user-info strong {
            color: #555;
        }

        /* Cards */
        .card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%);
            color: white;
            padding: 12px 15px;
            font-weight: 600;
        }

        .card-body {
            padding: 15px;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .summary-card {
            flex: 2;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
        }

        .health-score-card {
            flex: 1;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .balance-amount {
            font-size: 28px;
            font-weight: bold;
            color: #7367f0;
            margin: 10px 0;
            text-align: center;
        }

        .income-expense {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 15px;
        }

        .income {
            color: #28a745;
        }

        .expense {
            color: #dc3545;
        }

        /* Circular Progress */
        .circular-progress {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 15px;
        }

        .circular-chart {
            transform: rotate(-90deg);
        }

        .circle-bg {
            fill: none;
            stroke: #eee;
            stroke-width: 3.8;
        }

        .circle {
            fill: none;
            stroke-width: 3.8;
            stroke-linecap: round;
            stroke: #7367f0;
        }

        .percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 20px;
            font-weight: bold;
            color: #7367f0;
        }

        .score-label {
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .score-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .bg-success {
            background: linear-gradient(90deg, #eaf7f0, #f6fffb);
            color: #1e7f4f;
            border-left: 4px solid #2ecc71;
        }

        .bg-warning {
            background: linear-gradient(90deg, #fff8e5, #fffdf5);
            color: #8a6d1d;
            border-left: 4px solid #ffc107;
        }

        .bg-danger {
            background: linear-gradient(90deg, #fdecea, #fff5f5);
            color: #a4282f;
            border-left: 4px solid #dc3545;
        }

        /* Grid Layout */
        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .col-6 {
            flex: 0 0 calc(50% - 10px);
        }

        .col-12 {
            flex: 0 0 100%;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }

        .table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Anomalies and Recommendations */
        .anomaly-card {
            border-left: 4px solid #ffc107;
            background: linear-gradient(to right, rgba(255, 193, 7, 0.05), transparent);
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .recommendation-card {
            border-left: 4px solid #198754;
            background: linear-gradient(to right, rgba(25, 135, 84, 0.05), transparent);
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        /* Progress Bars */
        .progress {
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 5px 0;
        }

        .progress-bar {
            height: 100%;
            background-color: #7367f0;
        }

        /* Budget Items */
        .budget-item {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .budget-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .budget-amounts {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #666;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 11px;
        }

        .footer .date {
            margin-top: 5px;
            color: #888;
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .mt-1 {
            margin-top: 5px;
        }

        .mt-2 {
            margin-top: 10px;
        }

        .mt-3 {
            margin-top: 15px;
        }

        .d-flex {
            display: flex;
        }

        .justify-between {
            justify-content: space-between;
        }

        .align-center {
            align-items: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-muted {
            color: #6c757d;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-warning {
            color: #ffc107;
        }

        /* --- Summary Grid --- */
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .summary-grid td {
            width: 33.3%;
            text-align: center;
            padding: 15px 10px;
            border: 1px solid #e9ecef;
            background-color: var(--finji-light);
        }

        .summary-grid td:first-child {
            background-color: rgba(46, 204, 113, 0.1);
        }

        .summary-grid td:nth-child(2) {
            background-color: rgba(231, 76, 60, 0.1);
        }

        .summary-grid td:last-child {
            background-color: rgba(52, 152, 219, 0.1);
        }

        .muted {
            font-size: 13px;
            color: #7f8c8d;
            margin-bottom: 5px;
            display: block;
        }

        .amount {
            font-size: 18px;
            font-weight: bold;
        }

        .amount.income {
            color: #2ecc71;
        }

        .amount.expense {
            color: #e74c3c;
        }

        .amount.balance {
            color: #3498db;
        }


        /* Print Specific */
        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .card {
                break-inside: avoid;
            }

            .summary-section {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>Laporan Insight Keuangan</h1>
        <div class="subtitle">Analisis Keuangan Pribadi</div>
        <div class="period">Periode: {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
    </div>

    <!-- User Information -->
    <div class="user-info">
        <div class="row">
            <div class="col">
                <strong>Nama:</strong> {{ auth()->user()->name ?? 'Pengguna' }}
            </div>
            <div class="col">
                <strong>Email:</strong> {{ auth()->user()->email ?? '-' }}
            </div>
            <div class="col">
                <strong>Tanggal Laporan:</strong> {{ date('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="summary-section">
        <div class="summary-card">
            <h3 class="mb-2">Ringkasan Keuangan</h3>
            <div class="balance-amount">Rp {{ number_format($summary['balance'] ?? 0) }}</div>
            <div class="text-center text-muted mb-3">Total Saldo Akun</div>

            <table class="summary-grid">
                <tr>
                    <td>
                        <span class="muted">Total Pemasukan</span>
                        <div class="amount income">Rp{{ number_format($summary['total_income'], 0, ',', '.') }}
                        </div>
                    </td>
                    <td>
                        <span class="muted">Total Pengeluaran</span>
                        <div class="amount expense">
                            Rp{{ number_format($summary['total_expense'], 0, ',', '.') }}</div>
                    </td>
                    <td>
                        <span class="muted">Pengeluaran Harian</span>
                        <div class="amount balance">
                            Rp{{ number_format($summary['total_expense'] / $summary['days'], 0, ',', '.') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Health Score -->
        <div class="health-score-card">
            <h4 class="mb-3">Kesehatan Keuangan</h4>

            @php
                $score = $analysisStatus['score'] ?? 0;
                $meta = $analysisStatus['meta'] ?? ['label' => 'Tidak Tersedia', 'class' => 'secondary'];
            @endphp

            <div class="circular-progress">
                <svg viewBox="0 0 36 36" class="circular-chart">
                    <path class="circle-bg" d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="circle" stroke-dasharray="{{ $score }}, 100" d="M18 2.0845
                        a 15.9155 15.9155 0 0 1 0 31.831
                        a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <div class="percentage">{{ $score }}%</div>
            </div>
            <span class="score-status bg-{{ $meta['class'] }}">{{ $meta['label'] }}</span>

            @if (!empty($analysisStatus['reasoning']))
                <div class="mt-3 text-muted" style="font-size: 11px;">
                    {{ $analysisStatus['reasoning'] }}
                </div>
            @endif
        </div>
    </div>

    <!-- Breakdown Kategori Pengeluaran -->
    <div class="card">
        <div class="card-header"
            style="background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%); color: white; padding: 12px 15px; font-weight: 600;">

            Rincian Kategori Pengeluaran
        </div>
        <div class="card-body">
            @if (!empty($topCategories) && count($topCategories) > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Pengeluaran</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalExpense = $summary['total_expense'] ?? 1;
                        @endphp
                        @foreach ($topCategories as $category)
                            @php
                                $percentage = ($category['amount'] / $totalExpense) * 100;
                            @endphp
                            <tr>
                                <td>{{ $category['name'] ?? 'Unknown' }}</td>
                                <td>{{ $category['count'] ?? 0 }}</td>
                                <td>Rp {{ number_format($category['amount'] ?? 0) }}</td>
                                <td>
                                    <div class="d-flex align-center">
                                        <div class="progress" style="flex: 1; margin-right: 10px;">
                                            <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span>{{ round($percentage, 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center text-muted">
                    Tidak ada data pengeluaran untuk ditampilkan
                </div>
            @endif
        </div>
    </div>

    <!-- Rekomendasi Tindakan -->
    <div class="card">
        <div class="card-header"
            style="background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%); color: white; padding: 12px 15px; font-weight: 600;">

            Rekomendasi Tindakan
        </div>
        <div class="card-body">
            @if (!empty($analysis['advice']) && count($analysis['advice']) > 0)
                <div class="row">
                    @foreach ($analysis['advice'] as $rec)
                        <div class="col-6 mb-2">
                            <div class="recommendation-card">
                                <div class="fw-bold text-success mb-1">{{ $rec['title'] }}</div>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $rec['description'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted">
                    Tidak ada rekomendasi tindakan saat ini
                </div>
            @endif
        </div>
    </div>

    <!-- Budget Overview -->
    @if (!empty($budgets) && count($budgets) > 0)
        <div class="card">
            <div class="card-header"
                style="background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%); color: white; padding: 12px 15px; font-weight: 600;">

                Ikhtisar Budget
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($budgets as $budget)
                        @php
                            $progress =
                                $budget['budget_amount'] > 0
                                    ? min(100, ($budget['actual_spent'] / $budget['budget_amount']) * 100)
                                    : 0;

                            $statusClass = 'bg-secondary';
                            $statusText = 'Tanpa Budget';

                            if ($budget['budget_amount'] > 0) {
                                if ($progress <= 80) {
                                    $statusClass = 'bg-success';
                                    $statusText = 'On Track';
                                } elseif ($progress <= 90) {
                                    $statusClass = 'bg-warning';
                                    $statusText = 'Near Limit';
                                } else {
                                    $statusClass = 'bg-danger';
                                    $statusText = 'Over Budget';
                                }
                            }
                        @endphp

                        <div class="col-6 mb-3">
                            <div class="budget-item">
                                <div class="budget-header">
                                    <strong>{{ $budget['category_name'] }}</strong>
                                    <span class="{{ $statusClass }}"
                                        style="padding: 2px 8px; border-radius: 12px; font-size: 10px;">
                                        {{ $statusText }}
                                    </span>
                                </div>

                                @if ($budget['budget_amount'] > 0)
                                    <div class="progress">
                                        <div class="progress-bar {{ $statusClass }}"
                                            style="width: {{ $progress }}%">
                                        </div>
                                    </div>

                                    <div class="budget-amounts">
                                        <span>Terpakai: Rp {{ number_format($budget['actual_spent']) }}</span>
                                        <span>Budget: Rp {{ number_format($budget['budget_amount']) }}</span>
                                    </div>

                                    <div class="text-center mt-1">
                                        <small
                                            class="{{ $budget['remaining'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            @if ($budget['remaining'] >= 0)
                                                Sisa: Rp {{ number_format($budget['remaining']) }}
                                            @else
                                                Lebih: Rp {{ number_format(abs($budget['remaining'])) }}
                                            @endif
                                            ({{ round($progress) }}%)
                                        </small>
                                    </div>
                                @else
                                    <div class="text-center text-muted" style="font-size: 11px;">
                                        Pengeluaran: Rp {{ number_format($budget['actual_spent']) }}
                                    </div>
                                    <div class="text-center text-warning mt-1" style="font-size: 10px;">
                                        Belum ada budget yang ditetapkan
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Transaksi Terbaru -->
    <div class="card">
        <div class="card-header"
            style="background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%); color: white; padding: 12px 15px; font-weight: 600;">

            Transaksi Terbaru
        </div>
        <div class="card-body">
            @if (!empty($transactions) && count($transactions) > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction['date'] }}</td>
                                <td>{{ $transaction['category'] }}</td>
                                <td>{{ $transaction['description'] ?? '-' }}</td>
                                <td class="{{ $transaction['type'] === 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction['type'] === 'income' ? '+' : '-' }}
                                    {{ $transaction['amount'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center text-muted">
                    Tidak ada transaksi untuk ditampilkan
                </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>Laporan ini dibuat secara otomatis oleh sistem Finji</div>
        <div class="date">Dicetak pada: {{ date('d/m/Y H:i:s') }}</div>
        <div class="mt-1" style="color: #7367f0; font-size: 10px;">
            © {{ date('Y') }} Finji - Smart Financial Assistant
        </div>
    </div>

    <!-- JavaScript untuk langsung print jika diperlukan -->
    <script>
        // Auto print jika parameter print=1
        if (window.location.search.includes('print=1')) {
            window.print();
        }
    </script>
</body>

</html>
