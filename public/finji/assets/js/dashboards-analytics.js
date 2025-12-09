/**
 * Dashboard Analytics
 */

'use strict';

(function () {
    let cardColor, headingColor, axisColor, shadeColor, borderColor;

    cardColor = config.colors.white;
    headingColor = config.colors.headingColor;
    axisColor = config.colors.axisColor;
    borderColor = config.colors.borderColor;

    // Total Revenue Report Chart - Bar Chart
    // --------------------------------------------------------------------
    const totalRevenueChartEl = document.querySelector('#totalRevenueChart');

    if (typeof totalRevenueChartEl !== 'undefined' && totalRevenueChartEl !== null) {
        // --- contoh data awal (ganti dengan @json($incomeData) atau sumber API) ---
        // rawIncome/rawExpense adalah array nilai per-hari (index 0 => tanggal 01)
        const rawIncome = [1800000, 700000, 1500000, 2900000, 1800000, 1200000, 900000];
        const rawExpense = [1300000, 1800000, 900000, 1400000, 500000, 1700000, 1500000];

        // Jika dari Blade:
        // const rawIncome = @json($incomeData);
        // const rawExpense = @json($expenseData);

        // --- konfigurasi jumlah hari (ubah sesuai bulan jika dinamis) ---
        const daysInMonth = 31;

        // --- generate kategori tanggal 01..31 ---
        const dayCategories = Array.from({ length: daysInMonth }, (_, i) =>
            String(i + 1).padStart(2, '0')
        );

        // --- helper: pad atau truncate series agar panjang = daysInMonth ---
        function normalizeSeries(arr, length) {
            const out = new Array(length).fill(0);
            for (let i = 0; i < Math.min(arr.length, length); i++) {
                const v = arr[i];
                out[i] = (typeof v === 'number' && !isNaN(v)) ? v : 0;
            }
            return out;
        }

        const incomeSeries = normalizeSeries(rawIncome, daysInMonth);
        const expenseSeries = normalizeSeries(rawExpense, daysInMonth);

        // --- opsi area chart ---
        const totalRevenueChartOptions = {
            series: [
                { name: 'Pemasukan', data: incomeSeries },
                { name: 'Pengeluaran', data: expenseSeries }
            ],
            chart: {
                height: 340,
                type: 'area',
                stacked: false,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            stroke: {
                curve: 'smooth',
                width: 2,
                lineCap: 'round'
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    inverseColors: false,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            markers: {
                size: 3,
                strokeWidth: 2,
                hover: { size: 5 }
            },
            colors: [config.colors.success, config.colors.danger],
            dataLabels: { enabled: false },
            legend: {
                show: true,
                horizontalAlign: 'left',
                position: 'top',
                labels: { colors: axisColor },
                itemMargin: { horizontal: 10 }
            },
            grid: {
                borderColor: borderColor,
                padding: { top: 6, bottom: -8, left: 20, right: 20 }
            },

            // X axis: tanggal 01..31
            xaxis: {
                categories: dayCategories,
                labels: {
                    rotate: -20,
                    style: { fontSize: '12px', colors: axisColor }
                },
                axisTicks: { show: false },
                axisBorder: { show: false }
            },

            // Y axis: singkatan untuk readability
            yaxis: {
                labels: {
                    style: { fontSize: '13px', colors: axisColor },
                    formatter: function (val) {
                        if (Math.abs(val) >= 1000000) {
                            return (val / 1000000).toFixed(1).replace(/\.0$/, '') + 'jt';
                        }
                        if (Math.abs(val) >= 1000) {
                            return (val / 1000).toFixed(1).replace(/\.0$/, '') + 'rb';
                        }
                        return val;
                    }
                }
            },

            tooltip: {
                x: {
                    formatter: function (val) {
                        return 'Tanggal ' + val;
                    }
                },
                y: {
                    formatter: function (val) {
                        try {
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                maximumFractionDigits: 0
                            }).format(val);
                        } catch (e) {
                            return 'Rp ' + Number(val).toLocaleString();
                        }
                    }
                }
            },

            // responsive: atur tinggi & label rotation untuk layar kecil
            responsive: [
                {
                    breakpoint: 1024,
                    options: {
                        chart: { height: 300 },
                        xaxis: { labels: { rotate: -25, style: { fontSize: '11px' } } },
                        markers: { size: 3 }
                    }
                },
                {
                    breakpoint: 640,
                    options: {
                        chart: { height: 240 },
                        xaxis: { labels: { rotate: -45, style: { fontSize: '10px' } } },
                        markers: { size: 2 }
                    }
                }
            ],

            states: {
                hover: { filter: { type: 'none' } },
                active: { filter: { type: 'none' } }
            }
        };

        // render
        const totalRevenueChart = new ApexCharts(totalRevenueChartEl, totalRevenueChartOptions);
        totalRevenueChart.render();
    }


    // if (typeof totalRevenueChartEl !== undefined && totalRevenueChartEl !== null) {
    //     const totalRevenueChart = new ApexCharts(totalRevenueChartEl, totalRevenueChartOptions);
    //     totalRevenueChart.render();
    // }

    // Growth Chart - Radial Bar Chart
    // --------------------------------------------------------------------
    const growthChartEl = document.querySelector('#growthChart'),
        growthChartOptions = {
            series: [78],
            labels: ['Growth'],
            chart: {
                height: 240,
                type: 'radialBar'
            },
            plotOptions: {
                radialBar: {
                    size: 150,
                    offsetY: 10,
                    startAngle: -150,
                    endAngle: 150,
                    hollow: {
                        size: '55%'
                    },
                    track: {
                        background: cardColor,
                        strokeWidth: '100%'
                    },
                    dataLabels: {
                        name: {
                            offsetY: 15,
                            color: headingColor,
                            fontSize: '15px',
                            fontWeight: '600',
                            fontFamily: 'Public Sans'
                        },
                        value: {
                            offsetY: -25,
                            color: headingColor,
                            fontSize: '22px',
                            fontWeight: '500',
                            fontFamily: 'Public Sans'
                        }
                    }
                }
            },
            colors: [config.colors.primary],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    shadeIntensity: 0.5,
                    gradientToColors: [config.colors.primary],
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 0.6,
                    stops: [30, 70, 100]
                }
            },
            stroke: {
                dashArray: 5
            },
            grid: {
                padding: {
                    top: -35,
                    bottom: -10
                }
            },
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
    if (typeof growthChartEl !== undefined && growthChartEl !== null) {
        const growthChart = new ApexCharts(growthChartEl, growthChartOptions);
        growthChart.render();
    }

    // Profit Report Line Chart
    // --------------------------------------------------------------------
    const profileReportChartEl = document.querySelector('#profileReportChart'),
        profileReportChartConfig = {
            chart: {
                height: 80,
                // width: 175,
                type: 'line',
                toolbar: {
                    show: false
                },
                dropShadow: {
                    enabled: true,
                    top: 10,
                    left: 5,
                    blur: 3,
                    color: config.colors.warning,
                    opacity: 0.15
                },
                sparkline: {
                    enabled: true
                }
            },
            grid: {
                show: false,
                padding: {
                    right: 8
                }
            },
            colors: [config.colors.warning],
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 5,
                curve: 'smooth'
            },
            series: [
                {
                    data: [110, 270, 145, 245, 205, 285]
                }
            ],
            xaxis: {
                show: false,
                lines: {
                    show: false
                },
                labels: {
                    show: false
                },
                axisBorder: {
                    show: false
                }
            },
            yaxis: {
                show: false
            }
        };
    if (typeof profileReportChartEl !== undefined && profileReportChartEl !== null) {
        const profileReportChart = new ApexCharts(profileReportChartEl, profileReportChartConfig);
        profileReportChart.render();
    }

    // Order Statistics Chart
    // --------------------------------------------------------------------
    const chartOrderStatistics = document.querySelector('#orderStatisticsChart'),
        orderChartConfig = {
            chart: {
                height: 165,
                width: 130,
                type: 'donut'
            },
            labels: ['Electronic', 'Sports', 'Decor', 'Fashion'],
            series: [85, 15, 50, 50],
            colors: [config.colors.primary, config.colors.secondary, config.colors.info, config.colors.success],
            stroke: {
                width: 5,
                colors: cardColor
            },
            dataLabels: {
                enabled: false,
                formatter: function (val, opt) {
                    return parseInt(val) + '%';
                }
            },
            legend: {
                show: false
            },
            grid: {
                padding: {
                    top: 0,
                    bottom: 0,
                    right: 15
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            value: {
                                fontSize: '1.5rem',
                                fontFamily: 'Public Sans',
                                color: headingColor,
                                offsetY: -15,
                                formatter: function (val) {
                                    return parseInt(val) + '%';
                                }
                            },
                            name: {
                                offsetY: 20,
                                fontFamily: 'Public Sans'
                            },
                            total: {
                                show: true,
                                fontSize: '0.8125rem',
                                color: axisColor,
                                label: 'Weekly',
                                formatter: function (w) {
                                    return '38%';
                                }
                            }
                        }
                    }
                }
            }
        };
    if (typeof chartOrderStatistics !== undefined && chartOrderStatistics !== null) {
        const statisticsChart = new ApexCharts(chartOrderStatistics, orderChartConfig);
        statisticsChart.render();
    }

    // Income Chart - Area chart
    // --------------------------------------------------------------------
    const incomeChartEl = document.querySelector('#incomeChart'),
        incomeChartConfig = {
            series: [
                {
                    data: [24, 21, 30, 22, 42, 26, 35, 29]
                }
            ],
            chart: {
                height: 215,
                parentHeightOffset: 0,
                parentWidthOffset: 0,
                toolbar: {
                    show: false
                },
                type: 'area'
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 2,
                curve: 'smooth'
            },
            legend: {
                show: false
            },
            markers: {
                size: 6,
                colors: 'transparent',
                strokeColors: 'transparent',
                strokeWidth: 4,
                discrete: [
                    {
                        fillColor: config.colors.white,
                        seriesIndex: 0,
                        dataPointIndex: 7,
                        strokeColor: config.colors.primary,
                        strokeWidth: 2,
                        size: 6,
                        radius: 8
                    }
                ],
                hover: {
                    size: 7
                }
            },
            colors: [config.colors.primary],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: shadeColor,
                    shadeIntensity: 0.6,
                    opacityFrom: 0.5,
                    opacityTo: 0.25,
                    stops: [0, 95, 100]
                }
            },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 3,
                padding: {
                    top: -20,
                    bottom: -8,
                    left: -10,
                    right: 8
                }
            },
            xaxis: {
                categories: ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    show: true,
                    style: {
                        fontSize: '13px',
                        colors: axisColor
                    }
                }
            },
            yaxis: {
                labels: {
                    show: false
                },
                min: 10,
                max: 50,
                tickAmount: 4
            }
        };
    if (typeof incomeChartEl !== undefined && incomeChartEl !== null) {
        const incomeChart = new ApexCharts(incomeChartEl, incomeChartConfig);
        incomeChart.render();
    }

    // Expenses Mini Chart - Radial Chart
    // --------------------------------------------------------------------
    const weeklyExpensesEl = document.querySelector('#expensesOfWeek'),
        weeklyExpensesConfig = {
            series: [65],
            chart: {
                width: 60,
                height: 60,
                type: 'radialBar'
            },
            plotOptions: {
                radialBar: {
                    startAngle: 0,
                    endAngle: 360,
                    strokeWidth: '8',
                    hollow: {
                        margin: 2,
                        size: '45%'
                    },
                    track: {
                        strokeWidth: '50%',
                        background: borderColor
                    },
                    dataLabels: {
                        show: true,
                        name: {
                            show: false
                        },
                        value: {
                            formatter: function (val) {
                                return '$' + parseInt(val);
                            },
                            offsetY: 5,
                            color: '#697a8d',
                            fontSize: '13px',
                            show: true
                        }
                    }
                }
            },
            fill: {
                type: 'solid',
                colors: config.colors.primary
            },
            stroke: {
                lineCap: 'round'
            },
            grid: {
                padding: {
                    top: -10,
                    bottom: -15,
                    left: -10,
                    right: -10
                }
            },
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
    if (typeof weeklyExpensesEl !== undefined && weeklyExpensesEl !== null) {
        const weeklyExpenses = new ApexCharts(weeklyExpensesEl, weeklyExpensesConfig);
        weeklyExpenses.render();
    }
})();
