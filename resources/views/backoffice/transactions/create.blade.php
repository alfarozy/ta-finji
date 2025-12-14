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
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-income" value="income"
                                        {{ old('type', 'income') === 'income' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success w-100 py-2" for="type-income">
                                        <i class="bx bx-trending-up me-1"></i> Pemasukan
                                    </label>
                                </div>

                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-expense" value="expense"
                                        checked>
                                    <label class="btn btn-outline-danger w-100 py-2" for="type-expense">
                                        <i class="bx bx-trending-down me-1"></i> Pengeluaran
                                    </label>
                                </div>
                            </div>

                            @error('type')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select @error('transaction_category_id') is-invalid @enderror"
                                name="transaction_category_id" id="categorySelect" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-type="{{ $category->type }}"
                                        {{ old('transaction_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('transaction_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror" name="amount"
                                value="{{ old('amount') }}" placeholder="Masukkan jumlah..." required>

                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div class="mb-3">
                            <label class="form-label">Tanggal Transaksi</label>
                            <input type="date" class="form-control @error('transaction_date') is-invalid @enderror"
                                name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}"
                                required>

                            @error('transaction_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Tambahkan catatan...">{{ old('description') }}</textarea>
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const radios = document.querySelectorAll('input[name="type"]');
            const select = document.getElementById('categorySelect');
            const options = Array.from(select.options);

            function filter(type) {
                select.innerHTML = '';
                const placeholder = new Option('-- Pilih Kategori --', '');
                select.appendChild(placeholder);

                options.forEach(opt => {
                    if (opt.dataset.type === type) {
                        select.appendChild(opt);
                    }
                });
            }

            filter(document.querySelector('input[name="type"]:checked').value);

            radios.forEach(radio => {
                radio.addEventListener('change', () => filter(radio.value));
            });
        });
    </script>
@endpush
