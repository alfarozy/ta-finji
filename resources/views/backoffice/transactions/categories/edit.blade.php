@extends('layouts.backoffice')

@section('title', 'Update Transaction Category')
@section('content')
    <div class="content-wrapper">
        <div class="container container-p-y d-flex justify-content-center">

            <div class="col-md-6 col-lg-6 card mb-4">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Update Transaction Category</h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                </div>

                <div class="card-body">

                    <form action="{{ route('transactions.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required>
                                <option value="">-- Select Category Type --</option>
                                <option value="income">Income</option>
                                <option value="expanse">Expanse</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" name="category" placeholder="Enter category..."
                                required>
                        </div>


                        <button type="submit" class="btn btn-primary w-100 mt-2">
                            <i class="bx bx-save me-1"></i> Save Category
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
@endpush
