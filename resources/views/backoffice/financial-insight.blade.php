@extends('layouts.backoffice')

@section('title', 'Financial Insight')
@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card p-3">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-header p-0 border-0 mb-1">Financial Insight</h5>
                        <p class="text-muted mb-0 small">Analisis keuangan pribadi oleh Finji AI Assistant — periode
                            {{ date('F Y') }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="btnRunAI" class="btn btn-outline-primary btn-sm" onclick="runAIAnalysis()">
                            <i class="bx bx-refresh me-1"></i> Generate Analysis
                        </button>
                        <button id="btnDownload" class="btn btn-success btn-sm" onclick="downloadAIAnalysis()">
                            <i class="bx bx-download me-1"></i> Export Laporan
                        </button>
                    </div>
                </div>

                <!-- AI Analysis Status -->
                <div id="aiAnalysisStatus" class="mb-3" aria-live="polite"></div>

                <!-- Financial Summary + Chart -->
                <div class="row mb-4 align-items-stretch">
                    <div class="col-lg-8 col-md-12 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <small class="text-muted">Saldo Saat Ini</small>
                                </div>
                                <h3 id="balanceValue" class="mb-2">Rp 0</h3>
                                <div class="d-flex justify-content-center gap-2">
                                    <small class="text-success">Pemasukan: <span id="incomeValue">Rp 0</span></small>
                                    <small class="text-danger">Pengeluaran: <span id="expenseValue">Rp 0</span></small>
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

                <!-- Extra Insights: Category Breakdown, Recurring, MoM, Cashflow -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h6 class="card-title mb-0">🧾 Breakdown Kategori</h6>
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
                                    <h6 class="card-title mb-0">📉 Perbandingan Bulan-ke-Bulan</h6>
                                </div>
                                <div class="card-body" id="momComparison">
                                    <!-- populated by JS -->
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">🔮 Perkiraan Arus Kas (30 hari)</h6>
                                </div>
                                <div class="card-body" id="cashflowForecast">
                                    <!-- populated by JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Actionable Recommendations -->
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">📌 Rekomendasi Tindakan</h6>
                            </div>
                            <div class="card-body" id="actionableRecommendations">
                                <!-- populated by JS -->
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">🚨 Deteksi Anomali Pengeluaran</h6>
                            </div>
                            <div class="card-body" id="anomaliesSection">
                                <!-- populated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Analysis Modal -->
    <div class="modal fade" id="aiAnalysisModal" tabindex="-1" aria-hidden="true">
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

    </div>

@endsection

@push('styles')
    <style>
        /* Circle chart (SVG) styling */
        .circular-chart {
            width: 120px;
            height: 120px;
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
        }

        .insight-card {
            border-left: 4px solid #0dcaf0;
        }

        .advice-card {
            border-left: 4px solid #198754;
        }

        .saving-card {
            border-left: 4px solid #6f42c1;
        }

        /* Responsive tweaks */
        @media (max-width: 767px) {
            .circular-chart {
                width: 90px;
                height: 90px;
            }
        }
    </style>
@endpush

@push('scripts')
    <!-- ApexCharts CDN (or your local build) -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        // Default data (from backend)
        const financialData = {
            summary: {
                balance: {{ $summary['balance'] ?? 0 }},
                total_income: {{ $summary['total_income'] ?? 0 }},
                total_expense: {{ $summary['total_expense'] ?? 0 }}
            },
            topCategories: {!! json_encode($topCategories ?? []) !!},
            spending: {!! json_encode($spending ?? []) !!},
            income: {!! json_encode($income ?? []) !!},
            prev_summary: {!! json_encode($prevSummary ?? null) !!},
            budgets: {!! json_encode($budgets ?? null) !!},
            // tambahan untuk Health & Quick Insights
            health_breakdown: {!! json_encode($healthBreakdown ?? []) !!},
            quick_insights: {!! json_encode($quickInsights ?? []) !!},
            trendLabels: {!! json_encode($labels ?? []) !!},

        };


        let currentAIResult = null;
        let isAnalyzing = false;

        function setButtonsState(disabled = false) {
            document.getElementById('btnRunAI').disabled = disabled;
            document.getElementById('btnDownload').disabled = disabled || !currentAIResult;
        }

        function runAIAnalysis() {
            if (isAnalyzing) return;
            isAnalyzing = true;
            setButtonsState(true);

            const modal = new bootstrap.Modal(document.getElementById('aiAnalysisModal'));
            modal.show();

            // Example: replace setTimeout with real fetch to backend e.g.
            // fetch('/api/insight/analyze', { method: 'POST', body: JSON.stringify({...}) })
            setTimeout(async () => {
                modal.hide();

                // If you have real AI endpoint, call it here.
                // const aiResult = await fetchAIAnalysisFromServer();

                const aiResult = generateAIAnalysis(financialData); // mock
                currentAIResult = aiResult;
                updateUIWithAIResults(aiResult);

                showNotification('success', '🎉 Finji telah menganalisis keuangan Anda!');
                isAnalyzing = false;
                setButtonsState(false);
            }, 1500);
        }

        // Small helper to illustrate how to call backend AI (uncomment when used)
        /*
        async function fetchAIAnalysisFromServer() {
            const res = await fetch('/api/insight/analyze', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ start: null, end: null })
            });
            return res.json();
        }
        */

        // Mock AI generator (kept from your previous logic but simplified)
        function generateAIAnalysis(data) {
            const balance = data.summary.balance;
            const totalIncome = data.summary.total_income || 1;
            const totalExpense = data.summary.total_expense || 0;
            const savings = totalIncome - totalExpense;
            const savingsRate = Math.round((savings / totalIncome) * 100);

            let status = 'healthy';
            let healthScore = 78;
            if (totalExpense > totalIncome) {
                status = 'deficit';
                healthScore = 32;
            } else if (savingsRate < 20) {
                status = 'warning';
                healthScore = 62;
            }

            const anomalies = [];
            if (totalExpense > totalIncome * 0.8) {
                anomalies.push({
                    title: "Pengeluaran Mendekati Pemasukan",
                    description: `Pengeluaran bulan ini Rp ${formatRupiah(totalExpense)} (${Math.round(totalExpense/totalIncome*100)}% dari pemasukan). Sisa Rp ${formatRupiah(savings)}.`
                });
            }
            const insights = [{
                title: "Keseimbangan Keuangan",
                description: savingsRate >= 20 ? `Anda menabung ${savingsRate}% dari pemasukan.` :
                    `Tabungan rendah: ${savingsRate}% dari pemasukan.`
            }];
            const advice = [{
                title: "Strategi Budgeting",
                description: "Pertimbangkan metode 50-30-20 untuk alokasi pengeluaran."
            }];
            const savingOpportunities = [];
            const potentialSavings = Math.round(totalExpense * 0.12);
            if (potentialSavings > 0) {
                savingOpportunities.push({
                    title: "Optimasi Pengeluaran Rutin",
                    description: `Potensi hemat sekitar Rp ${formatRupiah(potentialSavings)} per bulan dengan efisiensi 12%.`
                });
            }

            return {
                anomalies,
                insights,
                advice,
                saving_opportunities: savingOpportunities,
                status,
                message: status === 'healthy' ? 'Keuangan Anda dalam kondisi baik.' : status === 'warning' ?
                    'Perhatian: tingkat tabungan rendah.' : 'Defisit: evaluasi pengeluaran segera.',
                health_score: healthScore
            };
        }

        function updateUIWithAIResults(result) {
            updateAnalysisStatus(result.status, result.message);
            updateSummaryCards(financialData.summary);
            renderSummaryChart(financialData);
            updateAnomaliesSection(result.anomalies);
            updateHealthMetrics(result.insights, result.health_score);
            updateAdviceSection(result.advice);
            updateSavingOpportunities(result.saving_opportunities);
            updateQuickInsights(result);
            // render extended insights (categories, recurring, MoM, cashflow, budgets, recommendations)
            renderExtraInsights();
            setButtonsState(false);
        }

        // UI update helpers
        function updateAnalysisStatus(status, message) {
            const map = {
                healthy: {
                    cls: 'success',
                    icon: '✅'
                },
                warning: {
                    cls: 'warning',
                    icon: '⚠️'
                },
                deficit: {
                    cls: 'danger',
                    icon: '❌'
                }
            };
            const meta = map[status] || map.healthy;
            const el = document.getElementById('aiAnalysisStatus');
            el.innerHTML = `
                <div class="alert alert-${meta.cls} d-flex align-items-center">
                    <div class="me-3 fs-4">${meta.icon}</div>
                    <div>
                        <div class="fw-bold">Status: ${status.toUpperCase()}</div>
                        <div class="small text-muted">${message}</div>
                    </div>
                </div>
            `;
        }

        function updateSummaryCards(summary) {
            document.getElementById('balanceValue').textContent = `Rp ${formatRupiah(summary.balance)}`;
            document.getElementById('incomeValue').textContent = `Rp ${formatRupiah(summary.total_income)}`;
            document.getElementById('expenseValue').textContent = `Rp ${formatRupiah(summary.total_expense)}`;
        }

        function renderSummaryChart(data) {
            const el = document.querySelector('#summaryChart');
            const labels = Array.isArray(data.trendLabels) ? data.trendLabels : [];

            // --- helper ---
            const shortDateLabel = (full) => full.split(" ").slice(0, 1).join(" ");
            const fullDateLabel = (full) => {
                const d = new Date(full);
                return d.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });
            };

            const series = [{
                    name: 'Pemasukan',
                    data: data.income ?? [0]
                },
                {
                    name: 'Pengeluaran',
                    data: data.spending ?? [0]
                }
            ];

            const options = {
                chart: {
                    type: 'area',
                    height: 270,
                    toolbar: {
                        show: false
                    }
                },
                series,
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                colors: ['#198754', '#dc3545'],
                fill: {
                    opacity: 0.2
                },
                dataLabels: {
                    enabled: false
                },

                // ⭐ X-Axis tanggal singkat
                xaxis: {
                    categories: labels.map(l => shortDateLabel(l)),
                    labels: {
                        show: false
                    }
                },

                // ⭐ Y-Axis compact format (rb / jt)
                yaxis: {
                    labels: {
                        formatter: (value) => formatShortRupiah(value)
                    }
                },

                // ⭐ Tooltip tanggal lengkap
                tooltip: {
                    x: {
                        formatter: function(_, opts) {
                            const fullDate = labels[opts.dataPointIndex];
                            return fullDateLabel(fullDate);
                        }
                    },
                    y: {
                        formatter: (val) => formatCurrency(val)
                    }
                },

                legend: {
                    show: true,
                    position: 'top'
                }
            };

            el.innerHTML = '';
            new ApexCharts(el, options).render();
        }

        function updateAnomaliesSection(anomalies) {
            const el = document.getElementById('anomaliesSection');
            if (!anomalies || anomalies.length === 0) {
                el.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="bx bx-check-circle fs-1 mb-2 text-success"></i>
                        <div>Tidak Ada Anomali Terdeteksi</div>
                    </div>`;
                return;
            }
            let html = '<div class="row">';
            anomalies.forEach(a => {
                html += `<div class="col-md-6 mb-2">
                    <div class="card anomaly-card">
                        <div class="card-body">
                            <h6 class="mb-1 text-warning">${a.title}</h6>
                            <p class="mb-0 small text-muted">${a.description}</p>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
            el.innerHTML = html;
        }

        function formatShortRupiah(value) {
            if (value >= 1000000) {
                return "Rp " + (value / 1000000).toFixed(value >= 10000000 ? 0 : 1) + "jt";
            }
            if (value >= 1000) {
                return "Rp " + Math.round(value / 1000) + "rb";
            }
            if (value == 0) {
                return '';
            }
            return "Rp " + value;
        }

        function updateHealthMetrics(insights, healthScore) {
            // circle
            const svg = document.querySelector('#healthScoreCircle .circle');
            const percText = document.querySelector('#healthScoreCircle .percentage');
            const dash = (healthScore / 100) * 100;
            svg.setAttribute('stroke-dasharray', `${dash}, 100`);
            percText.textContent = `${healthScore}%`;

            // title & badge
            const title = document.getElementById('healthScoreTitle');
            const badge = document.getElementById('healthScoreBadge');
            let statusText = 'Baik',
                badgeClass = 'bg-success';
            if (healthScore < 60) {
                statusText = 'Perlu Perhatian';
                badgeClass = 'bg-warning';
            }
            if (healthScore < 40) {
                statusText = 'Kritis';
                badgeClass = 'bg-danger';
            }
            title.textContent = statusText;
            badge.className = `badge ${badgeClass}`;
            badge.textContent = statusText;

            // insights list
            const container = document.getElementById('healthMetricsSection');
            let html = '';
            insights.forEach(i => {
                html += `<div class="mb-3">
                    <div class="d-flex align-items-start">
                        <i class="bx bx-info-circle text-info me-2 mt-1"></i>
                        <div>
                            <h6 class="mb-1">${i.title}</h6>
                            <p class="mb-0 small text-muted">${i.description}</p>
                        </div>
                    </div>
                </div>`;
            });
            container.innerHTML = html;
        }

        function updateAdviceSection(advice) {
            const el = document.getElementById('adviceSection');
            let html = '<div class="row">';
            advice.forEach(a => {
                html += `<div class="col-md-12 mb-2">
                    <div class="card advice-card">
                        <div class="card-body">
                            <h6 class="mb-1 text-success">${a.title}</h6>
                            <p class="mb-0 small text-muted">${a.description}</p>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
            el.innerHTML = html;
        }

        function updateSavingOpportunities(list) {

            let html = '<div class="row">';
            list.forEach(i => {
                html += `<div class="col-md-6 mb-2">
                    <div class="card saving-card h-100">
                        <div class="card-body">
                            <h6 class="mb-1 text-primary">${i.title}</h6>
                            <p class="mb-0 small text-muted">${i.description}</p>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div>';
            el.innerHTML = html;
        }

        function updateQuickInsights(result) {
            const el = document.getElementById('quickInsightsList');
            const html = `<div class="small text-muted mb-2">Ringkasan:</div>
                <div><strong>${result.health_score}%</strong> Financial Health Score</div>
                <div class="mt-2 small text-muted">Pesan: ${result.message}</div>`;
            el.innerHTML = html;
        }

        // Download - if server returns binary, adapt accordingly
        function downloadAIAnalysis() {
            if (!currentAIResult) {
                showNotification('warning', 'Silakan jalankan analisis AI terlebih dahulu!');
                return;
            }
            setButtonsState(true);
            showNotification('info', 'Membuat laporan analisis Finji...');

            // Example: Prefer server endpoint that returns file
            // fetch('/api/insight/export', { method: 'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({data: currentAIResult}) })
            //  .then(r => r.blob()).then(blob => { ... download ... })

            setTimeout(() => {
                // fallback: download JSON
                const reportData = {
                    ...currentAIResult,
                    generated_at: new Date().toISOString(),
                    financial_summary: financialData.summary
                };

                const blob = new Blob([JSON.stringify(reportData, null, 2)], {
                    type: 'application/json'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `laporan-finji-${new Date().toISOString().split('T')[0]}.json`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(url);

                showNotification('success', 'Laporan Finji AI berhasil diunduh!');
                setButtonsState(false);
            }, 900);
        }

        // Utils
        function formatRupiah(amount) {
            if (amount === null || amount === undefined) return '0';
            return new Intl.NumberFormat('id-ID').format(Number(amount));
        }

        function formatCurrency(val) {
            return 'Rp ' + formatRupiah(val);
        }

        function showNotification(type, message) {
            const wrapper = document.createElement('div');
            wrapper.className =
                `toast align-items-center text-bg-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} border-0`;
            wrapper.style.position = 'fixed';
            wrapper.style.right = '20px';
            wrapper.style.top = '20px';
            wrapper.style.zIndex = 9999;
            wrapper.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body small text-white">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            document.body.appendChild(wrapper);
            const bsToast = new bootstrap.Toast(wrapper, {
                delay: 3500
            });
            bsToast.show();
            wrapper.addEventListener('hidden.bs.toast', () => wrapper.remove());
        }

        // ------------------------------
        // Extra insights functions
        // ------------------------------
        function safeArraySum(arr) {
            if (!Array.isArray(arr)) return 0;
            return arr.reduce((s, v) => s + (Number(v) || 0), 0);
        }

        // Category breakdown (pie + list)
        function renderCategoryBreakdown(data) {
            const categories = data.topCategories || [];
            const elList = document.getElementById('categoryBreakdownList');
            const chartEl = document.getElementById('categoryBreakdownChart');

            if (!categories.length) {
                chartEl.innerHTML = '<div class="text-center text-muted py-4">Tidak ada data kategori.</div>';
                elList.innerHTML = '';
                return;
            }

            const series = categories.map(c => c.amount || 0);
            const labels = categories.map(c => c.name || 'Unknown');

            const options = {
                chart: {
                    type: 'donut',
                    height: 240
                },
                series: series,
                labels: labels,
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    y: {
                        formatter: (val) => formatCurrency(val)
                    }
                }
            };
            chartEl.innerHTML = '';
            new ApexCharts(chartEl, options).render();

            let html = '<div class="list-group list-group-flush">';
            categories.forEach(c => {
                const pct = data.summary.total_expense ? Math.round((c.amount / data.summary.total_expense) *
                        100) :
                    0;
                html += `<div class="d-flex justify-content-between align-items-center py-2">
                            <div><strong>${c.name}</strong><div class="small text-muted">${c.count || 0} transaksi</div></div>
                            <div class="text-end"><div>${formatCurrency(c.amount)}</div><small class="text-muted">${pct}%</small></div>
                         </div>`;
            });
            html += '</div>';
            elList.innerHTML = html;
        }

        // Detect recurring expenses (heuristic)
        function detectRecurringExpenses(raw) {
            const repeating = [];
            const cats = raw.topCategories || [];
            cats.forEach(cat => {
                const count = cat.count || 0;
                const avg = cat.amount ? (cat.amount / Math.max(1, count)) : 0;
                if (count >= 3 && avg > 0) {
                    repeating.push({
                        name: cat.name,
                        monthly_count: count,
                        avg_amount: Math.round(avg),
                        total: cat.amount
                    });
                }
            });
            return repeating;
        }


        // MoM comparison
        function renderMoMComparison(data) {
            const el = document.getElementById('momComparison');
            const cur = data.summary || {};
            const prev = data.prev_summary || null;
            if (!prev) {
                el.innerHTML = '<div class="text-muted small">Data perbandingan bulan sebelumnya tidak tersedia.</div>';
                return;
            }
            const incomeDelta = cur.total_income - prev.total_income;
            const expenseDelta = cur.total_expense - prev.total_expense;
            const incomePct = prev.total_income ? Math.round((incomeDelta / prev.total_income) * 100) : 0;
            const expensePct = prev.total_expense ? Math.round((expenseDelta / prev.total_expense) * 100) : 0;

            el.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <div><small class="text-muted">Pemasukan (Bulan Ini)</small><div class="fw-bold">${formatCurrency(cur.total_income)}</div></div>
                    <div class="${incomeDelta >=0 ? 'text-success' : 'text-danger'}">${incomeDelta >=0 ? '↑' : '↓'} ${formatCurrency(Math.abs(incomeDelta))} (${incomePct}%)</div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <div><small class="text-muted">Pengeluaran (Bulan Ini)</small><div class="fw-bold">${formatCurrency(cur.total_expense)}</div></div>
                    <div class="${expenseDelta <=0 ? 'text-success' : 'text-danger'}">${expenseDelta <=0 ? '↓' : '↑'} ${formatCurrency(Math.abs(expenseDelta))} (${Math.abs(expensePct)}%)</div>
                </div>
                <div><small class="text-muted">Perubahan Saldo</small><div class="fw-bold">${formatCurrency(cur.balance)} (sekarang)</div></div>
            `;
        }

        // Cashflow forecast (30 days)
        function renderCashflowForecast(data) {
            const el = document.getElementById('cashflowForecast');
            const dailyIncomeArr = Array.isArray(data.income) && data.income.length ? data.income : [];
            const dailyExpenseArr = Array.isArray(data.spending) && data.spending.length ? data.spending : [];
            const days = Math.max(dailyIncomeArr.length, dailyExpenseArr.length, 1);
            const sumIncome = safeArraySum(dailyIncomeArr);
            const sumExpense = safeArraySum(dailyExpenseArr);
            const avgDailyNet = (sumIncome - sumExpense) / days;
            const daysForecast = 30;
            const projectedNet = Math.round(avgDailyNet * daysForecast);
            const projectedBalance = Math.round((data.summary.balance || 0) + projectedNet);

            el.innerHTML = `
                <div class="mb-2 small text-muted">Rata-rata harian (net): <strong>${formatCurrency(Math.round(avgDailyNet))}</strong></div>
                <div class="mb-2">Perkiraan perubahan dalam ${daysForecast} hari: <strong>${projectedNet >=0 ? '+' : '-'} ${formatCurrency(Math.abs(projectedNet))}</strong></div>
                <div>Perkiraan saldo di ${daysForecast} hari: <strong>${formatCurrency(projectedBalance)}</strong></div>
                <div class="mt-2 small text-muted">Proyeksi sederhana berdasarkan rata-rata historis. Untuk akurasi lebih baik, gunakan data transaksi lebih panjang.</div>
            `;
        }

        // Budget variance (if budgets provided)
        function renderBudgetVariance(data) {
            const budgets = data.budgets || null;
            if (!budgets || !budgets.length) return;

            const catMap = {};
            (data.topCategories || []).forEach(c => {
                catMap[c.name] = c.amount;
            });

            let html = '<div class="row">';
            budgets.forEach(b => {
                const spent = catMap[b.category_name] || 0;
                const diff = b.amount - spent;
                const status = diff >= 0 ? 'Under' : 'Over';
                html += `<div class="col-md-6 mb-2">
                            <div class="card ${status === 'Over' ? 'anomaly-card' : 'insight-card'}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div><strong>${b.category_name}</strong><div class="small text-muted">Budget Rp ${formatRupiah(b.amount)}</div></div>
                                        <div class="text-end">
                                            <div class="${status==='Over'?'text-danger':'text-success'} fw-bold">${formatCurrency(spent)}</div>
                                            <small class="text-muted">${status === 'Over' ? 'Melebihi' : 'Tersisa'} ${formatCurrency(Math.abs(diff))}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         </div>`;
            });
            html += '</div>';
            el.insertAdjacentHTML('beforeend',
                `<hr class="my-3"><div><h6 class="mb-2">Budget Variance</h6>${html}</div>`);
        }

        // Actionable recommendations
        function renderActionableRecommendations(data, aiResult) {
            const el = document.getElementById('actionableRecommendations');
            const recs = [];

            if (aiResult && aiResult.advice) {
                aiResult.advice.forEach(a => recs.push({
                    title: a.title,
                    desc: a.description
                }));
            }

            const monthlyExpense = data.summary.total_expense || 0;
            const balance = data.summary.balance || 0;
            const monthsCovered = monthlyExpense ? (balance / monthlyExpense) : 0;
            if (monthsCovered < 1) {
                recs.push({
                    title: "Tambahkan Dana Darurat",
                    desc: `Saldo hanya mencukupi ${monthsCovered.toFixed(1)} bulan pengeluaran. Targetkan dana darurat minimal 3 bulan.`
                });
            } else if (monthsCovered < 3) {
                recs.push({
                    title: "Perkuat Dana Darurat",
                    desc: `Saldo mencukupi ~${monthsCovered.toFixed(1)} bulan. Tambahkan tabungan hingga mencapai 3 bulan.`
                });
            }

            const recurring = detectRecurringExpenses(data);
            if (recurring.length) {
                recs.push({
                    title: "Tinjau Langganan",
                    desc: `Teridentifikasi ${recurring.length} pengeluaran berulang. Pertimbangkan untuk membatalkan langganan yang tidak digunakan.`
                });
            }

            const avgDailyNet = ((safeArraySum(data.income) - safeArraySum(data.spending)) / Math.max(1, Math.max(data
                .income.length, data.spending.length)));
            if (avgDailyNet < 0) {
                recs.push({
                    title: "Perbaiki Arus Kas",
                    desc: "Rata-rata arus kas harian negatif — kurangi pengeluaran jangka pendek atau tingkatkan pemasukan."
                });
            }

            if (!recs.length) {
                el.innerHTML =
                    '<div class="text-muted small">Tidak ada rekomendasi tambahan — kondisi Anda stabil.</div>';
                return;
            }

            let html = '<div class="row">';
            recs.forEach(r => {
                html += `<div class="col-md-6 mb-2">
                            <div class="card insight-card h-100">
                                <div class="card-body">
                                    <h6 class="mb-1">${r.title}</h6>
                                    <p class="mb-0 small text-muted">${r.desc}</p>
                                </div>
                            </div>
                         </div>`;
            });
            html += '</div>';
            el.innerHTML = html;
        }

        // Compose extra insights
        function renderExtraInsights() {
            try {
                renderCategoryBreakdown(financialData);
                renderMoMComparison(financialData);
                renderCashflowForecast(financialData);
                renderBudgetVariance(financialData);
                renderActionableRecommendations(financialData, currentAIResult);
                renderHealthBreakdown(financialData.health_breakdown);
                renderQuickInsights(financialData.quick_insights);
            } catch (e) {
                console.error('Error rendering extra insights', e);
            }
        }

        // Init
        document.addEventListener('DOMContentLoaded', function() {
            updateSummaryCards(financialData.summary);
            renderSummaryChart(financialData);
            renderCategoryBreakdown(financialData);
            renderMoMComparison(financialData);
            renderCashflowForecast(financialData);
            renderHealthBreakdown(financialData.health_breakdown);
            renderQuickInsights(financialData.quick_insights);

            // run AI automatically (optional)
            runAIAnalysis();
        });


        function renderHealthBreakdown(data) {
            const container = document.getElementById('healthBreakdown');
            if (!data || !data.length) {
                container.innerHTML = '<div class="text-muted small">Tidak ada data kesehatan keuangan.</div>';
                return;
            }
            let html = '<div class="row g-2">';
            data.forEach(item => {
                // small bar indicator berdasarkan score
                const pct = Math.max(0, Math.min(100, item.score));
                let color = 'bg-success';
                if (pct < 40) color = 'bg-danger';
                else if (pct < 60) color = 'bg-warning';

                html += `<div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-muted">${item.metric}</div>
                    <div class="fw-bold">${item.score}%</div>
                    <div class="small text-muted">${item.desc}</div>
                </div>
                <div style="width:140px">
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar ${color}" role="progressbar" style="width:${pct}%" aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
        }
    </script>
@endpush
