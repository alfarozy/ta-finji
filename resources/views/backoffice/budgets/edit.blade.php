@extends('layouts.backoffice')

@section('title', 'Update Monthly Budget')
@section('content')
    <div class="content-wrapper">
        <div class="container py-4 d-flex justify-content-center">

            <div class="col-md-6 card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Update Monthly Budget</h5>
                    <a href="{{ route('budgets.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('budgets.store') }}" method="POST">
                        @csrf

                        {{-- Category --}}
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="transaction_category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label">Budget Amount (per month)</label>
                            <input type="number" name="amount" class="form-control" placeholder="Enter amount..."
                                required>
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" name="description" class="form-control" placeholder="Notes...">
                        </div>

                        <a href="" class="btn btn-outline-danger mr-3">
                            <i class="bx bx-trash me-1"></i> Delete
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save Budget
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
