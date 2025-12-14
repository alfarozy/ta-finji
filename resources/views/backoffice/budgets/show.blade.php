@extends('layouts.backoffice')

@section('title', 'detail anggaran')
@push('styles')
    <style>
        /* Pastikan SweetAlert2 selalu di atas */
        .swal2-container {
            z-index: 99999 !important;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="col-lg-12 mb-4">

                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex justify-content-between align-items-center">
                            <div class="col-6">
                                <div class="avatar flex-shrink-0">
                                    <i class="fa fa-chart-pie fa-2x text-warning"></i>
                                </div>
                            </div>

                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger m-1"
                                    onclick="confirmDeleteBudget({{ $budget->id }})">
                                    <i class="bx bx-trash me-1"></i> Hapus Anggaran
                                </button>
                                <a href="{{ route('budgets.edit', $budget->id) }}"
                                    class="m-1 btn btn-sm btn-outline-primary">
                                    <i class="bx bx-edit me-1"></i> Edit Anggaran
                                </a>
                            </div>
                        </div>


                        <span class="fw-semibold d-block mb-1">Anggaran : {{ $budget->category->name }}</span>
                        <small class="text-muted">{{ $budget->description }}</small>
                        <h5 class="my-1">
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

                    <h5 class="card-header">Riwayat pengeluaran</h5>
                    <a href="{{ route('budgets.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>

                <div class="table-responsive text-nowrap">
                    <table id="table" class="table table-striped">
                        <thead>
                            <tr>
                                <th width="55%">Transaksi</th>
                                <th width="20%">Tanggal</th>
                                <th width="25%" class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td>
                                        <span class="d-block">{{ $transaction->description ?? '—' }}</span>
                                    </td>

                                    <td>
                                        @if ($transaction->transaction_date)
                                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span
                                            class="{{ $transaction->type == \App\Models\Transaction::TYPE_INCOME ? 'text-success' : 'text-danger' }}">
                                            {{ $transaction->type == \App\Models\Transaction::TYPE_INCOME ? '+' : '-' }}
                                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        Belum ada transaksi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            Menampilkan {{ $transactions->firstItem() ? $transactions->firstItem() : 0 }}
                            - {{ $transactions->lastItem() ? $transactions->lastItem() : 0 }}
                            dari {{ $transactions->total() }} transaksi
                        </div>

                        <div>
                            {{ $transactions->links() }}
                        </div>
                    </div>
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
    <form id="delete-budget-form-{{ $budget->id }}" action="{{ route('budgets.destroy', $budget->id) }}" method="POST"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <div class="content-backdrop fade"></div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmDeleteBudget(budgetId) {
            Swal.fire({
                title: 'Hapus anggaran?',
                text: 'Anggaran yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document
                        .getElementById('delete-budget-form-' + budgetId)
                        .submit();
                }
            });
        }
    </script>
@endpush
