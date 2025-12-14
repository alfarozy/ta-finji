@extends('layouts.backoffice')

@section('title', 'Monthly Budget')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="col-lg-12 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <i class="fa fa-chart-pie fa-2x text-warning"></i>
                            </div>
                        </div>

                        <span class="fw-semibold d-block mb-1">Anggaran Bulan Ini</span>

                        <h5 class="mb-1">
                            Rp{{ number_format($usedBudget, 0, ',', '.') }}
                            <small class="text-muted">
                                / Rp{{ number_format($totalBudget, 0, ',', '.') }}
                            </small>
                        </h5>
                        @php
                            if ($budgetPercentage < 70) {
                                $color = 'bg-success';
                            } elseif ($budgetPercentage < 100) {
                                $color = 'bg-warning';
                            } else {
                                $color = 'bg-danger';
                            }
                        @endphp
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
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header">Anggaran bulanan : {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</h5>
                    <div class="d-flex">

                        <a href="{{ route('budgets.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-plus me-1"></i> Anggaran baru
                        </a>
                    </div>
                </div>
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover mb-0">

                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Anggaran</th>
                                <th>Digunakan</th>
                                <th>Progres</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($budgets as $item)
                                @php
                                    $used = $item->actual ?? 0;
                                    $percent = $item->amount > 0 ? ($used / $item->amount) * 100 : 0;

                                    // Determine progress color
                                    if ($percent < 70) {
                                        $color = 'bg-success';
                                    } elseif ($percent < 100) {
                                        $color = 'bg-warning';
                                    } else {
                                        $color = 'bg-danger';
                                    }
                                @endphp

                                <tr>
                                    <td>{{ $item->category->name }}</td>
                                    <td>Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($used, 0, ',', '.') }}</td>

                                    <td style="width: 40%;">
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar {{ $color }}"
                                                style="width: {{ min($percent, 100) }}%">
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            {{ number_format($percent, 1) }}%
                                        </small>
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('budgets.edit', $item->id) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <a href="{{ route('budgets.show', $item->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-file"></i>
                                        </a>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>
            </div>
        </div>

    </div>
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
