@extends('layouts.backoffice')

@section('title', 'Tambah Transaksi')
@section('content')
    <div class="content-wrapper">
        <div class="container flex-grow-1 container-p-y d-flex justify-content-center">

            <div class="col-md-6 col-lg-6 card mb-4">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Transaksi</h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">

                    <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Type --}}
                        <div class="mb-3">
                            <label class="form-label">Jenis Transaksi</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="income" value="income"
                                        {{ old('type', $transaction->type) === 'income' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success w-100" for="income">
                                        Pemasukan
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="expense" value="expense"
                                        {{ old('type', $transaction->type) === 'expense' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger w-100" for="expense">
                                        Pengeluaran
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Category --}}
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="transaction_category_id" id="categorySelect"
                                class="form-select @error('transaction_category_id') is-invalid @enderror">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-type="{{ $category->type }}"
                                        {{ old('transaction_category_id', $transaction->transaction_category_id) == $category->id ? 'selected' : '' }}>
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
                                value="{{ old('amount', $transaction->amount) }}" required>
                        </div>

                        {{-- Date --}}
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="transaction_date"
                                value="{{ old('transaction_date', $transaction->transaction_date->toDateString()) }}">
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description', $transaction->description) }}</textarea>
                        </div>

                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-primary col-12">
                                Simpan Perubahan
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
