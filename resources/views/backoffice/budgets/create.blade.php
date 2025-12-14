@extends('layouts.backoffice')

@section('title', 'Tambah Anggaran Bulanan')
@section('content')
    <div class="content-wrapper">
        <div class="container py-4 d-flex justify-content-center">

            <div class="col-md-6 card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tambah Anggaran Bulanan</h5>
                    <a href="{{ route('budgets.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('budgets.store') }}" method="POST">
                        @csrf

                        {{-- Kategori --}}
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="transaction_category_id"
                                class="form-select @error('transaction_category_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('transaction_category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('transaction_category_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Jumlah Anggaran --}}
                        <div class="mb-3">
                            <label class="form-label">Total Anggaran (per bulan)</label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount') }}" placeholder="Masukkan jumlah..." required>

                            @error('amount')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-3">
                            <label class="form-label">Deskripsi (Opsional)</label>
                            <input type="text" name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                value="{{ old('description') }}" placeholder="Catatan...">

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Anggaran
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
