@extends('layouts.backoffice')

@section('title', 'Transactions')
@push('styles')
    <style>
        .swal2-container {
            z-index: 99999 !important;
        }
    </style>
@endpush
@section('content')
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-header mb-0">Transaksi</h5>
                    <div class="">
                        <a href="{{ route('transactions.sync') }}" class="btn btn-outline-info btn-sm mr-2">
                            <i class="bx bx-sync me-1"></i> Singkronkan mutasi bank
                        </a>
                        <a href="{{ route('transactions.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-plus me-1"></i> Transaksi baru
                        </a>
                    </div>
                </div>

                {{-- Filter / Search Form --}}
                <form method="GET" class="mb-4">

                    {{-- Row 1 --}}
                    <div class="row g-3 align-items-center">

                        <div class="col-md-5    ">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control"
                                    placeholder="Cari: deskripsi, nominal, kategori" value="{{ request('q') }}">
                                <button class="btn btn-outline-secondary" type="submit">Cari</button>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <select name="type" class="form-select">
                                <option value="">Semua Tipe</option>
                                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>

                    </div>

                    {{-- Row 2 --}}
                    <div class="row g-3 mt-1 align-items-center">

                        <div class="col-md-2">
                            <select name="per_page" class="form-select">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / halaman
                                </option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / halaman
                                </option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / halaman
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="category_id" class="form-select">
                                <option value="">Semua Kategori</option>
                                @if (isset($categories))
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="col-md-7 d-flex justify-content-end gap-2">
                            <button class="btn btn-primary" type="submit">
                                <i class="bx bx-search me-1"></i> Terapkan
                            </button>

                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-reset me-1"></i> Reset
                            </a>
                        </div>

                    </div>
                </form>
                {{-- /Filter --}}
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
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td>
                                        <span class="d-block">{{ $transaction->description ?? '—' }}</span>
                                        @if ($transaction->source === \App\Models\Transaction::SOURCE_MUTATION)
                                            <small class="mt-1 text-info">Mutasi Bank</small>
                                        @elseif ($transaction->source === \App\Models\Transaction::SOURCE_WHATSAPP)
                                            <small class="mt-1 text-success">Whatsapp</small>
                                        @else
                                            <small class="mt-1 text-secondary">Input manual</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $cat = $transaction->transactionCategory ?? null;
                                            $badgeClass =
                                                $transaction->type === \App\Models\Transaction::TYPE_INCOME
                                                    ? 'bg-label-success'
                                                    : 'bg-label-danger';
                                        @endphp

                                        @if ($cat)
                                            <small class="badge {{ $badgeClass }}">{{ $cat->name }}</small>
                                        @else
                                            <small class="badge bg-secondary">Kategori hilang</small>
                                        @endif


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

                                                <button type="button" class="dropdown-item text-danger"
                                                    data-id="{{ $transaction->id }}"
                                                    onclick="confirmDeleteTransaction(this)">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </button>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        Tidak ada transaksi. Coba ubah kata kunci atau filter.
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
    <form id="delete-transaction-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <div class="content-backdrop fade"></div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmDeleteTransaction(el) {
            const transactionId = el.dataset.id;
            const form = document.getElementById('delete-transaction-form');

            form.action = `/transactions/${transactionId}`;

            Swal.fire({
                title: 'Hapus transaksi?',
                text: 'Transaksi yang dihapus akan mempengaruhi saldo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
@endpush
