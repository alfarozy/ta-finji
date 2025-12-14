@extends('layouts.backoffice')

@section('title', 'Tambah Kategori Transaksi')
@section('content')
    <div class="content-wrapper">
        <div class="container container-p-y d-flex justify-content-center">

            <div class="col-md-6 col-lg-6 card mb-4">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tambah Kategori Transaksi Baru</h5>
                    <a href="{{ route('transactions-categories.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">

                    <form action="{{ route('transactions-categories.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis Kategori --</option>
                                <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>
                                    Pemasukan
                                </option>
                                <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>
                                    Pengeluaran
                                </option>
                            </select>

                            @error('type')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" name="category"
                                class="form-control @error('category') is-invalid @enderror" value="{{ old('category') }}"
                                placeholder="Masukkan kategori..." required>

                            @error('category')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
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
