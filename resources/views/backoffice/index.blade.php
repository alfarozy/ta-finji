@extends('layouts.backoffice')

@section('title', 'Dashboard')
@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12 order-1">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-title d-flex align-items-start justify-content-between">
                                        <div class="avatar flex-shrink-0">
                                            <i class="fas fa-arrow-trend-up fa-2x text-success" aria-hidden="true"></i>
                                        </div>

                                    </div>
                                    <span class="fw-semibold d-block mb-1">Pemasukan</span>
                                    <h3 class="card-title text-nowrap mb-1">
                                        Rp{{ number_format($totalIncome, 0, ',', '.') }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-title d-flex align-items-start justify-content-between">
                                        <div class="avatar flex-shrink-0">
                                            <i class="fa fa-arrow-trend-down fa-2x text-danger" aria-hidden="true"></i>
                                        </div>

                                    </div>
                                    <span class="fw-semibold d-block mb-1">Pengeluaran</span>
                                    <h3 class="card-title text-nowrap mb-1">
                                        Rp{{ number_format($totalExpense, 0, ',', '.') }}</h3>

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-title d-flex align-items-start justify-content-between">
                                        <div class="avatar flex-shrink-0">
                                            <i class="fa fa-wallet fa-2x text-primary" aria-hidden="true"></i>
                                        </div>

                                    </div>
                                    <span class="fw-semibold d-block mb-1">Saldo saat ini</span>
                                    <h3 class="card-title text-nowrap mb-1">Rp{{ number_format($balance, 0, ',', '.') }}
                                    </h3>

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="card-title d-flex align-items-start justify-content-between">
                                        <div class="avatar flex-shrink-0">
                                            <i class="fa fa-chart-pie fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                    @php
                                        if ($budgetPercentage < 70) {
                                            $color = 'bg-success';
                                        } elseif ($budgetPercentage < 100) {
                                            $color = 'bg-warning';
                                        } else {
                                            $color = 'bg-danger';
                                        }
                                    @endphp
                                    <span class="fw-semibold d-block mb-1">Anggaran Bulan Ini</span>

                                    <h5 class="mb-1">
                                        Rp{{ number_format($usedBudget, 0, ',', '.') }}
                                        <small class="text-muted">
                                            / Rp{{ number_format($totalBudget, 0, ',', '.') }}
                                        </small>
                                    </h5>

                                    <div class="progress mb-1" style="height: 8px;">
                                        <div class="progress-bar {{ $color }}" role="progressbar"
                                            style="width: {{ $budgetPercentage }}%;" aria-valuenow="{{ $budgetPercentage }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>

                                    <small class="text-muted">
                                        {{ $budgetPercentage }}% terpakai •
                                        Sisa Rp{{ number_format($remainingBudget, 0, ',', '.') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Total Revenue -->
                <div class="col-12 order-2 order-md-3 order-lg-2 mb-4">
                    <div class="card">
                        <div class="row row-bordered g-0">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center mb-3 p-4">
                                    <h5 class="card-header m-0 p-0">
                                        Ringkasan keuangan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}
                                    </h5>

                                    <a href="{{ route('dashboard.financial-insight') }}" class="btn btn-sm btn-primary">
                                        Lihat insight
                                    </a>
                                </div>
                                <div id="financialChart" class="px-2"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- / Content -->

    <!-- Footer -->
    <footer class="content-footer footer bg-footer-theme">
        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
            <div class="mb-2 mb-md-0">
                ©
                <script>
                    document.write(new Date().getFullYear());
                </script>

                <a href="https://finji.app" target="_blank" class="footer-link fw-bolder">Hak Cipta dilindungi</a>
            </div>
            <div>


                <a href="https://github.com/themeselection/sneat-html-admin-template-free/issues" target="_blank"
                    class="footer-link me-4">Dev by Alfarozy</a>
            </div>
        </div>
    </footer>
    <!-- / Footer -->

    <div class="content-backdrop fade"></div>
    </div>
@endsection
@push('scripts')
    <script>
        let cardColor, headingColor, axisColor, borderColor;

        cardColor = config.colors.white;
        headingColor = config.colors.headingColor;
        axisColor = config.colors.axisColor;
        borderColor = config.colors.borderColor;

        const financialChart = document.querySelector('#financialChart');

        if (financialChart) {

            // =========================
            // DATA DARI BACKEND
            // =========================
            const incomeSeries = @json($incomeData);
            const expenseSeries = @json($expenseData);
            const daysInMonth = {{ $daysInMonth }};

            // kategori tanggal: 01..n
            const dayCategories = Array.from({
                    length: daysInMonth
                }, (_, i) =>
                String(i + 1).padStart(2, '0')
            );

            const totalRevenueChartOptions = {
                series: [{
                        name: 'Pemasukan',
                        data: incomeSeries
                    },
                    {
                        name: 'Pengeluaran',
                        data: expenseSeries
                    }
                ],
                chart: {
                    height: 340,
                    type: 'area',
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    }
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                markers: {
                    size: 3,
                    strokeWidth: 2,
                    hover: {
                        size: 5
                    }
                },
                colors: [config.colors.success, config.colors.danger],
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: true,
                    horizontalAlign: 'left',
                    position: 'top',
                    labels: {
                        colors: axisColor
                    }
                },
                grid: {
                    borderColor: borderColor,
                    padding: {
                        top: 6,
                        bottom: -8,
                        left: 20,
                        right: 20
                    }
                },
                xaxis: {
                    categories: dayCategories,
                    labels: {
                        rotate: -20,
                        style: {
                            fontSize: '12px',
                            colors: axisColor
                        }
                    },
                    axisTicks: {
                        show: false
                    },
                    axisBorder: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '13px',
                            colors: axisColor
                        },
                        formatter: function(val) {
                            if (Math.abs(val) >= 1000000) {
                                return (val / 1000000).toFixed(1).replace(/\.0$/, '') + ' jt';
                            }
                            if (Math.abs(val) >= 1000) {
                                return (val / 1000).toFixed(1).replace(/\.0$/, '') + ' rb';
                            }
                            return val;
                        }
                    }
                },
                tooltip: {
                    x: {
                        formatter: function(val) {
                            return 'Tanggal ' + val;
                        }
                    },
                    y: {
                        formatter: function(val) {
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                maximumFractionDigits: 0
                            }).format(val);
                        }
                    }
                },
                responsive: [{
                        breakpoint: 1024,
                        options: {
                            chart: {
                                height: 300
                            },
                            xaxis: {
                                labels: {
                                    rotate: -25,
                                    style: {
                                        fontSize: '11px'
                                    }
                                }
                            }
                        }
                    },
                    {
                        breakpoint: 640,
                        options: {
                            chart: {
                                height: 240
                            },
                            xaxis: {
                                labels: {
                                    rotate: -45,
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            }
                        }
                    }
                ],
                states: {
                    hover: {
                        filter: {
                            type: 'none'
                        }
                    },
                    active: {
                        filter: {
                            type: 'none'
                        }
                    }
                }
            };

            new ApexCharts(financialChart, totalRevenueChartOptions).render();
        }
    </script>
@endpush
