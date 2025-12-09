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
                            <select name="transaction_category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jumlah Anggaran --}}
                        <div class="mb-3">
                            <label class="form-label">Jumlah Anggaran (per bulan)</label>
                            <input type="number" name="amount" class="form-control" placeholder="Masukkan jumlah..."
                                required>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-3">
                            <label class="form-label">Deskripsi (Opsional)</label>
                            <input type="text" name="description" class="form-control" placeholder="Catatan...">
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
