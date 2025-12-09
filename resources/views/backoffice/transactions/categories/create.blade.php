@extends('layouts.backoffice')

@section('title', 'Tambah Kategori Transaksi')
@section('content')
    <div class="content-wrapper">
        <div class="container container-p-y d-flex justify-content-center">

            <div class="col-md-6 col-lg-6 card mb-4">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tambah Kategori Transaksi Baru</h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">

                    <form action="{{ route('transactions.store') }}" method="POST">
                        @csrf

                        {{-- Jenis Kategori --}}
                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select class="form-select" name="type" required>
                                <option value="">-- Pilih Jenis Kategori --</option>
                                <option value="income">Pemasukan</option>
                                <option value="expense">Pengeluaran</option>
                            </select>
                        </div>

                        {{-- Nama Kategori --}}
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" name="category" placeholder="Masukkan kategori..."
                                required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-2">
                            <i class="bx bx-save me-1"></i> Simpan Kategori
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
@endpush
