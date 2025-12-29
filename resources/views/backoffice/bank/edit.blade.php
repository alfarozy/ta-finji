@extends('layouts.backoffice')

@section('title', 'Ubah Akun Bank')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y d-flex justify-content-center">

            <div class="col-md-6 col-lg-6">
                <div class="card">

                    {{-- Header --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ubah Akun Bank</h5>
                        <a href="{{ route('bank-account.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>

                    {{-- Body --}}
                    <div class="card-body">

                        {{-- Info --}}
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            <b>Akses hanya baca mutasi.</b>
                            Sistem tidak dapat melakukan transaksi apa pun dari rekening Anda.
                            Kami bekerja sama dengan <b>Moota.co</b>.
                        </div>

                        <form action="{{ route('bank-account.update', $bankAccount->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                {{-- Bank --}}
                                <div class="mb-3 col-6">
                                    <label class="form-label">Nama Bank</label>
                                    <select name="bank_name" class="form-select @error('bank_name') is-invalid @enderror"
                                        required>
                                        <option value="">-- Pilih Bank --</option>
                                        <option value="bca"
                                            {{ old('bank_name', $bankAccount->bank_name) === 'BCA' ? 'selected' : '' }}>
                                            Bank Central Asia (BCA)
                                        </option>
                                        <option value="bni"
                                            {{ old('bank_name', $bankAccount->bank_name) === 'BNI' ? 'selected' : '' }}>
                                            Bank Negara Indonesia (BNI)
                                        </option>
                                        <option value="bri"
                                            {{ old('bank_name', $bankAccount->bank_name) === 'BRI' ? 'selected' : '' }}>
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
                                        value="{{ old('account_number', $bankAccount->account_number) }}"
                                        inputmode="numeric" required>

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
                                    value="{{ old('account_name', $bankAccount->account_name) }}" required>

                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Username --}}
                            <div class="mb-3">
                                <label class="form-label">Username Internet Banking</label>
                                <input type="text" name="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username', $bankAccount->username) }}" required>

                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div class="mb-4">
                                <label class="form-label">
                                    Kata Sandi Internet Banking
                                    <small class="text-muted">(kosongkan jika tidak ingin mengubah)</small>
                                </label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror">

                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Action --}}
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('ok')">
                                    <i class="bx bx-trash me-1"></i> Hapus Akun Bank
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="bx bx-trash text-danger fs-1 mb-3"></i>
                        <h6>Hapus Akun Bank?</h6>
                        <p class="text-muted">Apakah Anda yakin ingin menghapus akun bank ini? Tindakan ini tidak dapat
                            dibatalkan.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('bank-account.destroy', 'ok') }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus Akun</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function confirmDelete() {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            deleteModal.show();
        }
    </script>
@endpush
