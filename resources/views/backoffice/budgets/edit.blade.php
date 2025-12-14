@extends('layouts.backoffice')

@section('title', 'Update Monthly Budget')
@section('content')
    <div class="content-wrapper">
        <div class="container py-4 d-flex justify-content-center">

            <div class="col-md-6 card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Update Monthly Budget</h5>
                    <a href="{{ route('budgets.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('budgets.update', $budget->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Category --}}
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="transaction_category_id"
                                class="form-select @error('transaction_category_id') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('transaction_category_id', $budget->transaction_category_id) == $cat->id ? 'selected' : '' }}>
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

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label">Budget Amount (per month)</label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount', $budget->amount) }}" placeholder="Enter amount..." required>

                            @error('amount')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                value="{{ old('description', $budget->description) }}" placeholder="Notes...">

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary col-12">
                                <i class="bx bx-save me-1"></i> Simpan anggaran
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
