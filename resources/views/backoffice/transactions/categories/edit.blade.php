@extends('layouts.backoffice')

@section('title', 'Update Transaction Category')
@section('content')
    <div class="content-wrapper">
        <div class="container container-p-y d-flex justify-content-center">

            <div class="col-md-6 col-lg-6 card mb-4">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Update Transaction Category</h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">

                    <form action="{{ route('transactions-categories.update', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Jenis Kategori --}}
                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis Kategori --</option>
                                <option value="income" {{ old('type', $category->type) === 'income' ? 'selected' : '' }}>
                                    Pemasukan
                                </option>
                                <option value="expense" {{ old('type', $category->type) === 'expense' ? 'selected' : '' }}>
                                    Pengeluaran
                                </option>
                            </select>

                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nama Kategori --}}
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" name="category"
                                class="form-control @error('category') is-invalid @enderror"
                                value="{{ old('category', $category->name) }}" placeholder="Masukkan kategori..." required>

                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-primary col-12">
                                <i class="bx bx-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>


                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
@endpush
