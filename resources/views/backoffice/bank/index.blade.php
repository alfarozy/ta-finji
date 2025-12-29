@extends('layouts.backoffice')

@section('title', 'Akun Bank')
@section('content')
    <div class="content-wrapper">
        <!-- Konten -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header">Akun Bank</h5>
                </div>

                <!-- Kartu Ringkasan -->
                <div class="row mb-4 justify-content-center">
                    <div class="col-md-6 col-12 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-body">
                                    @if ($bankAccount)
                                        {{-- STATE: SUDAH ADA AKUN --}}
                                        <div class="card-title d-flex align-items-start justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg"
                                                        alt="{{ $bankAccount->bank_name }}" class="rounded"
                                                        style="width: 80px; height: 40px; object-fit: contain;">
                                                </div>
                                            </div>
                                            <span class="badge bg-success">Aktif</span>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-end">
                                            <div class="mb-2">
                                                <p class="mb-1 fw-semibold">
                                                    {{ $bankAccount->account_name }}
                                                </p>
                                                <small class="text-muted">
                                                    {{ Str::mask($bankAccount->account_number, '*', 3, -3) }}
                                                </small>
                                            </div>

                                            <a class="btn btn-sm btn-outline-primary"
                                                href="{{ route('bank-account.edit', 'ok') }}">
                                                Ubah
                                            </a>
                                        </div>
                                    @else
                                        {{-- STATE: BELUM ADA AKUN --}}
                                        <div class="text-center py-3">
                                            <div class="avatar flex-shrink-0 mb-3">
                                                <i class="bx bx-university fs-1 text-muted"></i>
                                            </div>

                                            <span class="fw-semibold d-block mb-1">
                                                Belum Ada Akun Bank
                                            </span>
                                            <p class="text-muted small mb-3">
                                                Hubungkan akun bank untuk membaca mutasi otomatis
                                            </p>

                                            <a href="{{ route('bank-account.create') }}" class="btn btn-primary btn-sm">
                                                <i class="bx bx-plus me-1"></i> Hubungkan Akun Bank
                                            </a>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <!-- Modal Ubah Akun Bank -->
    <div class="modal fade" id="updateBankAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Akun Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="{{ route('bank-account.update', 'no-id') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info mb-3" role="alert">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <b>Akses hanya untuk baca data mutasi.</b> Mutasibank tidak dapat membuat perubahan atau
                                    melakukan
                                    transaksi melalui akun bank Anda. Kami berkerja sama dengan Moota.co untuk menyediakan
                                    layanan mutasi bank. Terimakasih.
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_bank_name" class="form-label">Nama Bank</label>
                                <select class="form-select" id="edit_bank_name" name="bank_name" required>
                                    <option value="bca" selected>Bank Central Asia (BCA)</option>
                                    <option value="bri" disabled>Bank Rakyat Indonesia (BRI) (Belum tersedia)</option>
                                    <option value="bni" disabled>Bank Negara Indonesia (BNI) (Belum tersedia)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="account_name" class="form-label">Nama Pemilik Rekening</label>
                                <input type="text" class="form-control" id="account_name" name="account_name"
                                    value="{{ $bankAccount->account_name ?? '' }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_account_number" class="form-label">Nomor Rekening</label>
                                <input type="text" class="form-control" id="edit_account_number"
                                    name="{{ $bankAccount->account_number ?? '' }}" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_balance" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" id="edit_account_name" name="password"
                                    value="password" required>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger me-auto" onclick="confirmDelete()">
                            <i class="bx bx-trash me-1"></i> Hapus Akun
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
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
                    <button type="button" class="btn btn-danger" onclick="deleteBankAccount()">Hapus Akun</button>
                </div>
            </div>
        </div>
    </div>

    <!-- / Konten -->

    <!-- Footer -->
    <footer class="content-footer footer bg-footer-theme">
        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
            <div class="mb-2 mb-md-0">
                ©
                <script>
                    document.write(new Date().getFullYear());
                </script>

                <a href="https://finji.app" target="_blank" class="footer-link fw-bolder">Hak cipta dilindungi</a>
            </div>
            <div>
                <a href="https://github.com/themeselection/sneat-html-admin-template-free/issues" target="_blank"
                    class="footer-link me-4">Dikembangkan oleh Alfarozy</a>
            </div>
        </div>
    </footer>
    <!-- / Footer -->

    <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete() {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            deleteModal.show();
        }

        function deleteBankAccount() {
            // Simulasi pemanggilan API (pakai ID akun dummy)
            simulateAPICall('DELETE', '/api/bank-accounts/1')
                .then(response => {
                    // Tutup semua modal
                    const updateModal = bootstrap.Modal.getInstance(document.getElementById('updateBankAccountModal'));
                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal'));
                    updateModal.hide();
                    deleteModal.hide();

                    // Tampilkan pesan sukses
                    showNotification('success', 'Akun bank berhasil dihapus!');

                    // Di aplikasi nyata, Anda bisa refresh halaman atau update UI
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                })
                .catch(error => {
                    showNotification('error', 'Gagal menghapus akun bank. Silakan coba lagi.');
                });
        }
    </script>
@endpush
