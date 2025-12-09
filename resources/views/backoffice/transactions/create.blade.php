@extends('layouts.backoffice')

@section('title', 'Tambah Transaksi')
@section('content')
    <div class="content-wrapper">
        <div class="container flex-grow-1 container-p-y d-flex justify-content-center">

            <div class="col-md-6 col-lg-6 card mb-4">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tambah Transaksi Baru</h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">

                    <form action="{{ route('transactions.store') }}" method="POST">
                        @csrf

                        {{-- Transaction Type --}}
                        <div class="mb-3">
                            <label class="form-label d-block mb-2">Jenis Transaksi</label>

                            <div class="row g-2">

                                {{-- INCOME --}}
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-income" value="income"
                                        autocomplete="off" checked>
                                    <label class="btn btn-outline-success w-100 py-2" for="type-income">
                                        <i class="bx bx-trending-up me-1"></i> Pemasukan
                                    </label>
                                </div>

                                {{-- EXPENSE --}}
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-expense" value="expense"
                                        autocomplete="off">
                                    <label class="btn btn-outline-danger w-100 py-2" for="type-expense">
                                        <i class="bx bx-trending-down me-1"></i> Pengeluaran
                                    </label>
                                </div>

                            </div>
                        </div>

                        {{-- Transaction Category --}}
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="transaction_category_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }} ({{ ucfirst($category->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" class="form-control" name="amount" placeholder="Masukkan jumlah..."
                                required>
                        </div>

                        {{-- Transaction Date --}}
                        <div class="mb-3">
                            <label class="form-label">Tanggal Transaksi</label>
                            <input type="date" class="form-control" name="transaction_date" required>
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Tambahkan catatan..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-2">
                            <i class="bx bx-save me-1"></i> Simpan Transaksi
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
@endpush
