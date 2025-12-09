@extends('layouts.backoffice')

@section('title', 'Monthly Budget')

@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header">Anggaran bulanan : {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</h5>
                    <div class="d-flex">

                        <a href="{{ route('budgets.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-plus me-1"></i> Anggaran baru
                        </a>
                    </div>
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover mb-0">

                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Anggaran</th>
                                <th>Digunakan</th>
                                <th>Progres</th>
                                <th>Aksi</th>
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

                                    <td>
                                        <a href="{{ route('budgets.edit', $item->id) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="bx bx-edit"></i>
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
