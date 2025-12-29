@extends('layouts.backoffice')

@section('title', 'Hubungkan Akun Bank')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y d-flex justify-content-center">

            <div class="col-md-6 col-lg-6">
                <div class="card">

                    {{-- Header --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Hubungkan Akun Bank</h5>
                        <a href="{{ route('bank-account.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>

                    {{-- Body --}}
                    <div class="card-body">

                        {{-- Info --}}
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            <b>Akses hanya baca mutasi.</b> Sistem tidak dapat melakukan transaksi apa pun dari rekening
                            Anda.
                            Kami bekerja sama dengan <b>Moota.co</b>.
                        </div>

                        <form action="{{ route('bank-account.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                {{-- Bank --}}
                                <div class="mb-3 col-6">
                                    <label class="form-label">Nama Bank</label>
                                    <select name="bank_name" class="form-select @error('bank_name') is-invalid @enderror"
                                        required>
                                        <option value="">-- Pilih Bank --</option>
                                        <option value="bca" {{ old('bank_name') === 'bca' ? 'selected' : '' }}>
                                            Bank Central Asia (BCA)
                                        </option>
                                        <option value="bni" {{ old('bank_name') === 'bni' ? 'selected' : '' }}>
                                            Bank Negara Indonesia (BNI)
                                        </option>
                                        <option value="bri" {{ old('bank_name') === 'bri' ? 'selected' : '' }}>
                                            Bank Rakyat Indonesia (BRI)
                                        </option>
                                    </select>

                                    @error('bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- Nomor rekening --}}
                                <div class="mb-3 col-6">
                                    <label class="form-label">Nomor Rekening</label>
                                    <input type="text" name="account_number"
                                        class="form-control @error('account_number') is-invalid @enderror"
                                        value="{{ old('account_number') }}" inputmode="numeric"
                                        placeholder="Contoh: 123456789" required>

                                    @error('account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Nama pemilik --}}
                            <div class="mb-3">
                                <label class="form-label">Nama Pemilik Rekening</label>
                                <input type="text" name="account_name"
                                    class="form-control @error('account_name') is-invalid @enderror"
                                    value="{{ old('account_name') }}" placeholder="Contoh: Alfarozy" required>

                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Username --}}
                            <div class="mb-3">
                                <label class="form-label">Username Internet Banking</label>
                                <input type="text" name="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username') }}" required>

                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div class="mb-4">
                                <label class="form-label">Kata Sandi Internet Banking</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>

                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Action --}}
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('bank-account.index') }}" class="btn btn-outline-secondary">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-link me-1"></i> Hubungkan Akun
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
