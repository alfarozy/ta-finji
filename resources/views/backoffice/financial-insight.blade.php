@extends('layouts.backoffice')

@section('title', 'Insight Keuangan')

@push('styles')
    <style>
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
            transition: stroke-dasharray 0.7s ease;
        }

        .percentage {
            font-size: 0.7rem;
            text-anchor: middle;
            fill: #7367f0;
            transform: rotate(90deg);
        }

        .health-breakdown small {
            font-size: 0.85rem;
        }

        .anomaly-card {
            border-left: 4px solid #ffc107;
            background: linear-gradient(to right, rgba(255, 193, 7, 0.05), transparent);
        }

        .insight-card {
            border-left: 4px solid #0dcaf0;
            background: linear-gradient(to right, rgba(13, 202, 240, 0.05), transparent);
        }

        .advice-card {
            border-left: 4px solid #198754;
            background: linear-gradient(to right, rgba(25, 135, 84, 0.05), transparent);
        }

        .saving-card {
            border-left: 4px solid #6f42c1;
            background: linear-gradient(to right, rgba(111, 66, 193, 0.05), transparent);
        }

        .bullet-point {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .transaction-income {
            color: #198754;
        }

        .transaction-expense {
            color: #dc3545;
        }

        @media (max-width: 767px) {
            .circular-chart {
                width: 90px;
                height: 90px;
            }

            .insight-card .card-body {
                padding: 0.5rem;
            }
        }
    </style>
    <style>
        .circular-chart {
            width: 120px;
            height: 120px;
            transform: rotate(-90deg);
        }

        .percentage {
            font-size: 6px;
            fill: #7367f0;
            font-weight: 600;
            transform: rotate(90deg);
            transform-origin: center;
        }
    </style>
    <!-- Chart.js sebagai fallback -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Fallback jika ApexCharts error
        window.apexchartsLoaded = false;

        // Cek jika ApexCharts gagal load
        setTimeout(() => {
            if (!window.apexchartsLoaded && typeof ApexCharts === 'undefined') {
                console.warn('ApexCharts failed to load, using Chart.js fallback');
                loadChartJsFallback();
            }
        }, 2000);

        function loadChartJsFallback() {
            // Implement Chart.js fallback di sini
        }
    </script>
@endpush
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card p-3">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-header p-0 border-0 mb-1">Insight Keuangan</h5>
                        <p class="text-muted mb-0 small">Analisis keuangan pribadi oleh Finji AI Assistant — periode
                            {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="btnRunAI" class="btn btn-outline-primary btn-sm" onclick="runAIAnalysis()">
                            <i class="bx bx-refresh me-1"></i> Generate Analysis
                        </button>
                        <button id="btnDownload" class="btn btn-success btn-sm" onclick="downloadAIAnalysis()">
                            <i class="bx bx-download me-1"></i> Export JSON
                        </button>
                    </div>
                </div>

                <!-- AI Analysis Status -->
                <div id="aiAnalysisStatus" class="mb-3" aria-live="polite">
                    <!-- Will be populated by JavaScript -->
                </div>

                <!-- Financial Summary + Chart -->
                <div class="row mb-4 align-items-stretch">
                    <div class="col-lg-8 col-md-12 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <small class="text-muted">Total Saldo Akun</small>
                                </div>
                                <h3 id="balanceValue" class="mb-2">Rp {{ number_format($summary['balance'] ?? 0) }}</h3>
                                <div class="d-flex justify-content-center gap-2">
                                    <small class="text-success">Pemasukan: <span id="incomeValue">Rp
                                            {{ number_format($summary['total_income'] ?? 0) }}</span></small>
                                    <small class="text-danger">Pengeluaran: <span id="expenseValue">Rp
                                            {{ number_format($summary['total_expense'] ?? 0) }}</span></small>
                                </div>
                                <div id="summaryChart" class="mt-3" style="height:270px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Health Score -->
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <div class="text-center mb-3">
                                    <div class="circular-progress mx-auto" id="healthScoreCircle" data-percentage="50">
                                        <svg viewBox="0 0 36 36" class="circular-chart">
                                            <path class="circle-bg"
                                                d="M18 2.0845
                                                                                                                                                                                                                                                                                                                                                                    a 15.9155 15.9155 0 0 1 0 31.831
                                                                                                                                                                                                                                                                                                                                                                    a 15.9155 15.9155 0 0 1 0 -31.831" />
                                            <path class="circle" stroke-dasharray="0, 100"
                                                d="M18 2.0845
                                                                                                                                                                                                                                                                                                                                                                    a 15.9155 15.9155 0 0 1 0 31.831
                                                                                                                                                                                                                                                                                                                                                                    a 15.9155 15.9155 0 0 1 0 -31.831" />
                                            <text x="18" y="20.35" class="percentage">50%</text>
                                        </svg>
                                    </div>
                                    <h5 class="mt-2 mb-1" id="healthScoreTitle">Loading...</h5>
                                    <span id="healthScoreBadge" class="badge bg-secondary">-</span>
                                </div>
                                <div class="health-breakdown w-100 px-2" id="healthBreakdown">
                                    <!-- populated by JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Extra Insights: Category Breakdown, MoM, Cashflow -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title mb-0">🧾 Breakdown Kategori Pengeluaran</h6>
                            </div>
                            <div class="card-body">
                                <div id="categoryBreakdownChart" style="height:240px;"></div>
                                <div id="categoryBreakdownList" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="col-md-12 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">📉 Perbandingan Bulan sebelumya</h6>
                                </div>
                                <div class="card-body" id="momComparison">
                                    <!-- populated by JS -->
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">🚨 Deteksi Anomali & Peringatan</h6>
                                </div>
                                <div class="card-body" id="anomaliesSection">
                                    <!-- populated by JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actionable Recommendations & Anomalies -->
                <div class="row mb-4">

                    <div class="col-md-12">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title mb-0">📌 Rekomendasi Tindakan</h6>
                            </div>
                            <div class="card-body" id="actionableRecommendations">
                                <!-- populated by JS -->
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Budget Overview (if any) -->
                @if (!empty($budgets))
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">📊 Budget Overview</h6>
                                    <a href="{{ route('budgets.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-plus"></i> Kelola Budget
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach ($budgets as $budget)
                                            @php
                                                $progress =
                                                    $budget['budget_amount'] > 0
                                                        ? min(
                                                            100,
                                                            ($budget['actual_spent'] / $budget['budget_amount']) * 100,
                                                        )
                                                        : ($budget['actual_spent'] > 0
                                                            ? 100
                                                            : 0);

                                                $statusClass = 'bg-secondary';
                                                $statusText = 'No Budget';

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
                                            <div class="col-md-6 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <strong>{{ $budget['category_name'] }}</strong>
                                                            <span
                                                                class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                                        </div>

                                                        @if ($budget['budget_amount'] > 0)
                                                            <div class="progress mb-2" style="height: 8px;">
                                                                <div class="progress-bar {{ $statusClass }}"
                                                                    role="progressbar"
                                                                    style="width: {{ $progress }}%">
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="d-flex justify-content-between small text-muted mb-1">
                                                                <span>Spent: Rp
                                                                    {{ number_format($budget['actual_spent']) }}</span>
                                                                <span>Budget: Rp
                                                                    {{ number_format($budget['budget_amount']) }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                @if ($budget['remaining'] >= 0)
                                                                    <span class="text-success small">
                                                                        <i class="bx bx-check-circle"></i> Sisa: Rp
                                                                        {{ number_format($budget['remaining']) }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-danger small">
                                                                        <i class="bx bx-error-circle"></i> Lebih: Rp
                                                                        {{ number_format(abs($budget['remaining'])) }}
                                                                    </span>
                                                                @endif
                                                                <span class="text-muted small">
                                                                    {{ round($progress) }}%
                                                                </span>
                                                            </div>
                                                        @else
                                                            <div class="alert alert-warning py-1 mb-2">
                                                                <small><i class="bx bx-info-circle"></i> Belum ada
                                                                    budget</small>
                                                            </div>
                                                            <div class="text-center">
                                                                <small class="text-muted">Pengeluaran: Rp
                                                                    {{ number_format($budget['actual_spent']) }}</small>
                                                            </div>
                                                            <div class="text-center mt-1">
                                                                <a href="{{ route('budgets.create', ['category' => $budget['category_id']]) }}"
                                                                    class="btn btn-sm btn-outline-primary">
                                                                    <i class="bx bx-plus"></i> Buat Budget
                                                                </a>
                                                            </div>
                                                        @endif

                                                        @if (!empty($budget['description']))
                                                            <div class="mt-2 small text-muted">
                                                                <i class="bx bx-note"></i>
                                                                {{ Str::limit($budget['description'], 50) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if (count($budgets) == 0)
                                        <div class="text-center py-4">
                                            <i class="bx bx-pie-chart-alt fs-1 text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada budget yang ditetapkan</p>
                                            <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                                                <i class="bx bx-plus"></i> Buat Budget Pertama
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Recent Transactions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">📋 Transaksi Terbaru</h6>
                                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-list-ul"></i> Lihat Semua
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Kategori</th>
                                                <th>Deskripsi</th>
                                                <th class="text-end">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentTransactions">
                                            @foreach ($transactions as $transaction)
                                                <tr>
                                                    <td>{{ $transaction['date'] }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            {{ $transaction['category'] }}
                                                        </div>
                                                    </td>
                                                    <td>{{ $transaction['description'] ?? '-' }}</td>
                                                    <td
                                                        class="text-end {{ $transaction['type'] === 'income' ? 'text-success' : 'text-danger' }} fw-bold">
                                                        {{ $transaction['type'] === 'income' ? '+' : '-' }}
                                                        {{ $transaction['amount'] }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Analysis Modal -->
    {{-- <div class="modal fade" id="aiAnalysisModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
                    <p class="mb-2">Finji sedang menganalisis transaksi Anda...</p>
                    <div class="progress" style="height:6px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated w-100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
@endsection

@push('scripts')
    <!-- Gunakan versi ApexCharts yang stabil -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0"></script>

    <script>
        // Data dari backend Laravel
        const financialData = {
            summary: @json($summary),
            topCategories: @json($topCategories ?? []),
            spending: @json($spending ?? []),
            income: @json($income ?? []),
            trendLabels: @json($labels ?? []),
            prev_summary: @json($prevSummary ?? null),
            budgets: {!! json_encode($budgets ?? []) !!},
            health_breakdown: {!! json_encode($healthBreakdown ?? []) !!},
        };

        let currentAIResult = null;
        let isAnalyzing = false;
        let summaryChartInstance = null;
        let categoryChartInstance = null;

        // API endpoint URLs
        const ANALYZE_URL = '{{ route('financial.insight.analyze') }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';
        // Initialize UI setelah DOM siap
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan element ada sebelum render chart
            if (document.querySelector('#summaryChart')) {
                renderSummaryChart(financialData);
            }

            if (document.querySelector('#categoryBreakdownChart')) {
                renderCategoryBreakdown(financialData);
            }

            renderMoMComparison(financialData);
            renderHealthBreakdown(financialData.health_breakdown);
            // Auto-run AI analysis setelah semua chart selesai
            setTimeout(() => {
                runAIAnalysis();
            }, 500);
        });

        // Run AI Analysis
        async function runAIAnalysis() {
            if (isAnalyzing) return;

            isAnalyzing = true;
            setButtonsState(true);

            try {
                // Cek jika modal element ada
                const modalElement = document.getElementById('aiAnalysisModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }

                const response = await fetch(ANALYZE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    currentAIResult = result.analysis;
                    updateUIWithAIResults(result.analysis);
                    // showNotification('success', '🎉 Finji telah menganalisis keuangan Anda!');
                } else {
                    throw new Error('Analysis failed');
                }
            } catch (error) {
                console.error('AI Analysis error:', error);
                // showNotification('warning', 'Terjadi kesalahan saat analisis.');

                // Fallback to basic analysis
                currentAIResult = generateBasicAnalysis();
                updateUIWithAIResults(currentAIResult);
            } finally {
                // Tutup modal jika ada
                const modalElement = document.getElementById('aiAnalysisModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();
                }

                isAnalyzing = false;
                setButtonsState(false);
            }
        }

        // Update UI with AI results
        function updateUIWithAIResults(result) {
            if (!result) return;

            updateAnalysisStatus(result.status, result.message);
            updateHealthScore(result.health_score);
            updateAnomaliesSection(result.anomalies);
            updateRecommendations(result.advice, result.saving_opportunities);
        }

        // Update analysis status
        function updateAnalysisStatus(status, message) {
            const map = {
                healthy: {
                    cls: 'success',
                    icon: '✅',
                    text: 'SEHAT'
                },
                warning: {
                    cls: 'warning',
                    icon: '⚠️',
                    text: 'PERHATIAN'
                },
                deficit: {
                    cls: 'danger',
                    icon: '❌',
                    text: 'DEFISIT'
                }
            };

            const meta = map[status] || map.healthy;
            const el = document.getElementById('aiAnalysisStatus');
            if (el) {
                el.innerHTML = `
                    <div class="alert alert-${meta.cls} d-flex align-items-center">
                        <div class="me-3 fs-4">${meta.icon}</div>
                        <div>
                            <div class="fw-bold">Status: ${meta.text}</div>
                            <div class="small text-muted">${message}</div>
                        </div>
                    </div>
                `;
            }
        }

        // Update health score circle
        function updateHealthScore(score) {
            const circle = document.querySelector('#healthScoreCircle .circle');
            const percentageText = document.querySelector('#healthScoreCircle .percentage');
            const title = document.getElementById('healthScoreTitle');
            const badge = document.getElementById('healthScoreBadge');

            if (!circle || !percentageText || !title || !badge) return;
            score = Math.max(0, Math.min(100, Math.round(score)));

            const dash = score;
            circle.setAttribute('stroke-dasharray', `${dash}, 100`);
            percentageText.textContent = `${score}%`;

            // Status mapping
            let statusText, badgeClass;

            if (score < 40) {
                statusText = 'Kritis';
                badgeClass = 'bg-danger';
            } else if (score < 55) {
                statusText = 'Perlu Perhatian';
                badgeClass = 'bg-warning';
            } else if (score < 70) {
                statusText = 'Cukup';
                badgeClass = 'bg-info';
            } else {
                statusText = 'Sehat';
                badgeClass = 'bg-success';
            }


            title.textContent = statusText;
            badge.className = `badge ${badgeClass}`;
            badge.textContent = statusText;
        }

        // Update anomalies section
        function updateAnomaliesSection(anomalies) {
            const el = document.getElementById('anomaliesSection');
            if (!el) return;

            if (!anomalies || anomalies.length === 0) {
                el.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="bx bx-check-circle fs-1 mb-2 text-success"></i>
                        <div>Tidak Ada Anomali Terdeteksi</div>
                        <small class="text-muted">Pengeluaran Anda normal bulan ini</small>
                    </div>`;
                return;
            }

            let html = '<div class="row">';
            anomalies.slice(0, 4).forEach(a => {
                html += `<div class="col-md-12 mb-2">
                    <div class="card anomaly-card h-100">
                        <div class="card-body">
                            <h6 class="mb-1 text-warning">${a.title}</h6>
                            <p class="mb-0 small text-muted">${a.description}</p>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';

            if (anomalies.length > 4) {
                html += `<div class="text-center mt-2">
                    <small class="text-muted">+ ${anomalies.length - 4} anomali lainnya</small>
                </div>`;
            }

            el.innerHTML = html;
        }

        // Update recommendations
        function updateRecommendations(advice = [], savingOpportunities = []) {
            const el = document.getElementById('actionableRecommendations');
            if (!el) return;

            const allRecs = [...advice, ...savingOpportunities];

            if (!allRecs || allRecs.length === 0) {
                el.innerHTML = '<div class="text-muted small">Tidak ada rekomendasi saat ini.</div>';
                return;
            }

            let html = '<div class="row">';
            allRecs.slice(0, 4).forEach((rec, index) => {
                const cardClass = index < advice.length ? 'advice-card' : 'saving-card';
                const icon = index < advice.length ? '📋' : '💰';
                html += `<div class="col-md-6 mb-2">
                    <div class="card ${cardClass} h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <span class="me-2">${icon}</span>
                                <div>
                                    <h6 class="mb-1">${rec.title}</h6>
                                    <p class="mb-0 small text-muted">${rec.description}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
            el.innerHTML = html;
        }

        // Chart rendering functions dengan error handling
        function renderSummaryChart(data) {
            const el = document.querySelector('#summaryChart');
            if (!el) {
                console.warn('Summary chart element not found');
                return;
            }

            // Clear previous chart jika ada
            if (summaryChartInstance) {
                summaryChartInstance.destroy();
            }

            const labels = Array.isArray(data.trendLabels) ? data.trendLabels : [];
            const incomeData = Array.isArray(data.income) ? data.income : [];
            const expenseData = Array.isArray(data.spending) ? data.spending : [];

            // Jika tidak ada data, tampilkan pesan
            if (incomeData.length === 0 && expenseData.length === 0) {
                el.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="bx bx-line-chart fs-1 mb-2"></i>
                        <div>Belum ada data transaksi</div>
                        <small class="text-muted">Mulai tambahkan transaksi untuk melihat chart</small>
                    </div>
                `;
                return;
            }

            // Buat default labels jika kosong
            const finalLabels = labels.length > 0 ? labels :
                Array.from({
                    length: Math.max(incomeData.length, expenseData.length)
                }, (_, i) => `Day ${i + 1}`);

            // Pastikan data arrays memiliki panjang yang sama
            const maxLength = Math.max(finalLabels.length, incomeData.length, expenseData.length);
            const paddedIncome = [...incomeData];
            const paddedExpense = [...expenseData];

            while (paddedIncome.length < maxLength) paddedIncome.push(0);
            while (paddedExpense.length < maxLength) paddedExpense.push(0);

            try {
                const options = {
                    chart: {
                        type: 'line',
                        height: 370,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    series: [{
                            name: 'Pemasukan',
                            data: paddedIncome
                        },
                        {
                            name: 'Pengeluaran',
                            data: paddedExpense
                        }
                    ],
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#71dd37', '#ff3e1d'],
                    markers: {
                        size: 2,
                        hover: {
                            size: 3
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: finalLabels,
                        labels: {
                            show: true,
                            rotate: -45,
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value) {
                                return formatShortRupiah(value);
                            }
                        },
                        min: 0
                    },
                    tooltip: {
                        y: {
                            formatter: (val) => formatCurrency(val)
                        }
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        horizontalAlign: 'center'
                    },
                    grid: {
                        borderColor: '#f1f1f1',
                        strokeDashArray: 2,
                        padding: {
                            top: 10,
                            right: 10,
                            bottom: 0,
                            left: 10
                        }
                    }
                };

                // Render chart dengan setTimeout untuk memastikan DOM ready
                setTimeout(() => {
                    try {
                        summaryChartInstance = new ApexCharts(el, options);
                        summaryChartInstance.render();
                    } catch (chartError) {
                        console.error('Error rendering summary chart:', chartError);
                        el.innerHTML = `
                            <div class="text-center text-danger py-4">
                                <i class="bx bx-error-circle fs-1 mb-2"></i>
                                <div>Gagal memuat chart</div>
                                <small class="text-muted">Silakan refresh halaman</small>
                            </div>
                        `;
                    }
                }, 100);

            } catch (error) {
                console.error('Error creating summary chart options:', error);
                el.innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="bx bx-error-circle fs-1 mb-2"></i>
                        <div>Gagal membuat chart</div>
                        <small class="text-muted">${error.message}</small>
                    </div>
                `;
            }
        }

        function renderCategoryBreakdown(data) {
            const categories = data.topCategories || [];
            const elList = document.getElementById('categoryBreakdownList');
            const chartEl = document.getElementById('categoryBreakdownChart');

            if (!chartEl) {
                console.warn('Category chart element not found');
                return;
            }

            // Clear previous chart jika ada
            if (categoryChartInstance) {
                categoryChartInstance.destroy();
            }

            if (!categories.length) {
                chartEl.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="bx bx-pie-chart-alt fs-1 mb-2"></i>
                        <div>Belum ada pengeluaran</div>
                        <small class="text-muted">Tambah transaksi pengeluaran untuk melihat breakdown</small>
                    </div>`;

                if (elList) {
                    elList.innerHTML = '<div class="text-center text-muted py-3">Tidak ada pengeluaran bulan ini.</div>';
                }
                return;
            }

            // Prepare data for chart
            const series = categories.map(c => c.amount || 0);
            const labels = categories.map(c => c.name || 'Unknown');

            try {
                const options = {
                    chart: {
                        type: 'pie',
                        height: 240
                    },
                    series: series,
                    labels: labels,
                    colors: ['#7367f0', '#28c76f', '#ea5455', '#ff9f43', '#9c8df9', '#00cfe8', '#ff85a1', '#a8aaae'],
                    legend: {
                        position: 'bottom',
                        fontSize: '12px',
                        itemMargin: {
                            horizontal: 10,
                            vertical: 5
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: (val) => formatCurrency(val)
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };

                // Render chart
                setTimeout(() => {
                    try {
                        categoryChartInstance = new ApexCharts(chartEl, options);
                        categoryChartInstance.render();
                    } catch (chartError) {
                        console.error('Error rendering category chart:', chartError);
                        chartEl.innerHTML = `
                            <div class="text-center text-danger py-4">
                                <i class="bx bx-error-circle fs-1 mb-2"></i>
                                <div>Gagal memuat chart kategori</div>
                            </div>
                        `;
                    }
                }, 100);

            } catch (error) {
                console.error('Error creating category chart options:', error);
                chartEl.innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="bx bx-error-circle fs-1 mb-2"></i>
                        <div>Gagal membuat chart kategori</div>
                    </div>
                `;
            }

            // List breakdown
            if (elList) {
                let html = '<div class="list-group list-group-flush">';
                const totalExpense = data.summary?.total_expense || 0;

                categories.forEach((c, index) => {
                    const percentage = totalExpense > 0 ?
                        Math.round((c.amount / totalExpense) * 100) : 0;
                    const colors = ['#7367f0', '#28c76f', '#ea5455', '#ff9f43', '#9c8df9', '#00cfe8', '#ff85a1',
                        '#a8aaae'
                    ];
                    const color = colors[index % colors.length];

                    html += `
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bullet-point me-2" style="width:10px;height:10px;background:${color};border-radius:50%"></div>
                            <div>
                                <strong class="d-block">${c.name}</strong>
                                <small class="text-muted">${c.count || 0} transaksi</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">${formatCurrency(c.amount)}</div>
                            <small class="text-muted">${percentage}%</small>
                        </div>
                    </div>`;
                });
                html += '</div>';
                elList.innerHTML = html;
            }
        }

        function renderMoMComparison(data) {
            const el = document.getElementById('momComparison');
            if (!el) return;

            const cur = data.summary || {};
            const prev = data.prev_summary || null;
            const curDays = cur.days || 30;
            const prevDays = prev.days || 30;

            if (!prev || prev.total_income === undefined) {
                el.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="bx bx-line-chart fs-1 mb-2"></i>
                        <div>Data bulan sebelumnya tidak tersedia</div>
                        <small class="text-muted">Mulai tracking keuangan untuk melihat perbandingan</small>
                    </div>`;
                return;
            }

            const incomeDelta = cur.total_income - prev.total_income;
            const expenseDelta = cur.total_expense - prev.total_expense;
            const incomePct = prev.total_income > 0 ? Math.round((incomeDelta / prev.total_income) * 100) : 0;
            const expensePct = prev.total_expense > 0 ? Math.round((expenseDelta / prev.total_expense) * 100) : 0;
            const savingsDelta = (cur.total_income - cur.total_expense) - (prev.total_income - prev.total_expense);

            const avgDailyExpenseCur = cur.total_expense / curDays;
            const avgDailyExpensePrev = prev.total_expense / prevDays;
            const avgExpenseDelta = avgDailyExpenseCur - avgDailyExpensePrev;

            const avgExpensePct = avgDailyExpensePrev > 0 ?
                Math.round((avgExpenseDelta / avgDailyExpensePrev) * 100) :
                0;

            el.innerHTML = `
                <div class="row text-center">
                    <div class="col-4">
                        <div class="mb-2">
                            <small class="text-muted d-block">Pemasukan</small>
                            <div class="fw-bold fs-5">${formatShortRupiah(cur.total_income)}</div>
                            <small class="${incomeDelta >=0 ? 'text-success' : 'text-danger'}">
                                ${incomeDelta >=0 ? '↑' : '↓'} ${Math.abs(incomePct)}%
                            </small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-2">
                            <small class="text-muted d-block">Pengeluaran</small>
                            <div class="fw-bold fs-5">${formatShortRupiah(cur.total_expense)}</div>
                            <small class="${expenseDelta <=0 ? 'text-success' : 'text-danger'}">
                                ${expenseDelta <=0 ? '↓' : '↑'} ${Math.abs(expensePct)}%
                            </small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-2">
                            <small class="text-muted d-block">Tabungan</small>
                            <div class="fw-bold fs-5">${formatShortRupiah(cur.total_income - cur.total_expense)}</div>
                            <small class="${savingsDelta >=0 ? 'text-success' : 'text-danger'}">
                                ${savingsDelta >=0 ? '↑' : '↓'} ${formatShortRupiah(Math.abs(savingsDelta))}
                            </small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <small class="text-muted d-block">Rata-rata Pengeluaran Harian</small>
                    <div class="fw-semibold">
                        ${formatShortRupiah(avgDailyExpenseCur)}
                        <small class="text-muted">/ hari</small>
                    </div>
                    <small class="${avgExpenseDelta <= 0 ? 'text-success' : 'text-danger'}">
                        ${avgExpenseDelta <= 0 ? '↓' : '↑'} ${Math.abs(avgExpensePct)}%
                        dibanding bulan lalu <b>${formatShortRupiah(avgDailyExpensePrev)}</b>
                    </small>
                </div>
            `;
        }


        function renderHealthBreakdown(data) {
            const container = document.getElementById('healthBreakdown');
            if (!container) return;

            if (!data || !data.length) {
                container.innerHTML = '<div class="text-muted small">Tidak ada data kesehatan keuangan.</div>';
                return;
            }

            let html = '<div class="row g-2">';
            data.forEach(item => {
                const pct = Math.max(0, Math.min(100, item.score));
                let color = 'bg-success',
                    icon = '✅';
                if (pct < 40) {
                    color = 'bg-danger';
                    icon = '❌';
                } else if (pct < 60) {
                    color = 'bg-warning';
                    icon = '⚠️';
                }

                html += `
               <div class="col-12">
                    <div class="p-3 border rounded">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-5">${icon}</span>
                                <div class="fw-semibold">${item.metric}</div>
                            </div>
                            <div class="fw-bold">${item.score}%</div>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div
                                class="progress-bar ${color}"
                                role="progressbar"
                                style="width: ${pct}%"
                                aria-valuenow="${pct}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                        <div class="small text-muted mt-1">
                            ${item.desc}
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
        }

        // Helper functions
        function formatRupiah(amount) {
            if (amount === null || amount === undefined || isNaN(amount)) return '0';
            return new Intl.NumberFormat('id-ID').format(Math.round(Number(amount)));
        }

        function formatCurrency(val) {
            return 'Rp ' + formatRupiah(val);
        }

        function formatShortRupiah(value) {
            const num = Number(value);
            if (isNaN(num)) return "Rp 0";

            if (Math.abs(num) >= 1000000000) {
                return "Rp " + (num / 1000000000).toFixed(1) + "M";
            }
            if (Math.abs(num) >= 1000000) {
                return "Rp " + (num / 1000000).toFixed(num >= 10000000 ? 0 : 1) + "jt";
            }
            if (Math.abs(num) >= 1000) {
                return "Rp " + Math.round(num / 1000) + "rb";
            }
            if (num == 0) {
                return "Rp 0";
            }
            return "Rp " + Math.round(num);
        }

        function setButtonsState(disabled = false) {
            const btnRun = document.getElementById('btnRunAI');
            const btnDownload = document.getElementById('btnDownload');

            if (btnRun) btnRun.disabled = disabled;
            if (btnDownload) btnDownload.disabled = disabled || !currentAIResult;
        }

        function showNotification(type, message) {
            // Simple toast notification
            const toastId = 'toast-' + Date.now();
            const toast = `
                <div id="${toastId}" class="toast show align-items-center text-white bg-${type} border-0"
                     style="position:fixed; top:20px; right:20px; z-index:9999;">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                                data-bs-dismiss="toast" onclick="document.getElementById('${toastId}').remove()"></button>
                    </div>
                </div>`;

            // Add new toast
            document.body.insertAdjacentHTML('beforeend', toast);

            // Auto remove after 3 seconds
            setTimeout(() => {
                const toastEl = document.getElementById(toastId);
                if (toastEl) toastEl.remove();
            }, 3000);
        }

        // Basic analysis for fallback
        function generateBasicAnalysis() {
            const totalIncome = financialData.summary?.total_income || 0;
            const totalExpense = financialData.summary?.total_expense || 0;
            const savings = totalIncome - totalExpense;
            const savingsRate = totalIncome > 0 ? (savings / totalIncome) * 100 : 0;

            let status = 'healthy',
                healthScore = 75;
            if (totalExpense > totalIncome) {
                status = 'deficit';
                healthScore = 35;
            } else if (savingsRate < 20) {
                status = 'warning';
                healthScore = 55;
            }

            const anomalies = [];
            if (totalIncome > 0 && (totalExpense / totalIncome) > 0.8) {
                anomalies.push({
                    title: "Pengeluaran Mendekati Pemasukan",
                    description: `Pengeluaran mencapai ${Math.round((totalExpense/totalIncome)*100)}% dari pemasukan.`
                });
            }

            return {
                status,
                message: status === 'healthy' ? 'Keuangan Anda dalam kondisi baik.' : status === 'warning' ?
                    'Perhatian: tingkat tabungan rendah.' : 'Defisit: evaluasi pengeluaran segera.',
                health_score: healthScore,
                anomalies: anomalies,
                insights: [{
                    title: 'Analisis Dasar',
                    description: 'Analisis menggunakan data transaksi bulan ini.'
                }],
                advice: [{
                    title: 'Pantau Rutin',
                    description: 'Lacak pengeluaran Anda secara berkala untuk menjaga kesehatan keuangan.'
                }],
                saving_opportunities: []
            };
        }


        // Clean up charts saat page unload
        window.addEventListener('beforeunload', function() {
            if (summaryChartInstance) {
                summaryChartInstance.destroy();
            }
            if (categoryChartInstance) {
                categoryChartInstance.destroy();
            }
        });

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }
    </script>
@endpush
