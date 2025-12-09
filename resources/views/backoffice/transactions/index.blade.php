@extends('layouts.backoffice')

@section('title', 'Transactions')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.5/css/dataTables.dataTables.css">
@endpush
@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header">Transaksi</h5>
                    <div class="d-flex">
                        <a href="{{ route('transactions.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-plus me-1"></i> Transaksi baru
                        </a>
                    </div>
                </div>

                <div class="table-responsive text-nowrap">
                    <table id="table" class="table table-striped">
                        <thead>
                            <tr>
                                <th width="35%">Transaksi</th>
                                <th width="20%">Kategori</th>
                                <th width="15%">Tanggal</th>
                                <th width="15%" class="text-end">Nominal</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td>
                                        <span class="d-block">{{ $transaction->description ?? 'Pengeluaran' }}</span>
                                    </td>
                                    <td><small
                                            class="badge {{ $transaction->type == 'income' ? 'bg-label-success' : 'bg-label-danger' }}">
                                            {{ $transaction->transactionCategory->name }}</small></td>
                                    <td>{{ now()->format('d M Y') }}</td>
                                    <td class="text-end">
                                        <span class="{{ $transaction->type == 'income' ? 'text-success' : 'text-danger' }}">
                                            {{ $transaction->type == 'income' ? '+' : '-' }}
                                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                    href="{{ route('transactions.edit', $transaction->id) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                <form action="{{ route('transactions.destroy', $transaction->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item"
                                                        onclick="return confirm('Are you sure?')">
                                                        <i class="bx bx-trash me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="card-body">
                        {{ $transactions->links() }}
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
    <script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>

    <script>
        new DataTable('#table');
    </script>
@endpush
