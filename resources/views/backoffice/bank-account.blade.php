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
                                @if (true)
                                    <!-- State jika sudah ada data akun bank (dummy) -->
                                    <div class="card-title d-flex align-items-start justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar flex-shrink-0 me-3">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg"
                                                    alt="BCA" class="rounded"
                                                    style="width: 80px; height: 40px; object-fit: contain;">
                                            </div>
                                        </div>
                                        <span class="badge bg-label-success">
                                            Aktif
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-end">
                                        <div class="mb-2">
                                            <p class="mb-1 fw-semibold">Alfarozy</p>
                                            <small class="text-muted">213040003</small>
                                        </div>

                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#updateBankAccountModal">
                                            Ubah
                                        </button>
                                    </div>
                                @else
                                    <!-- State jika belum ada data akun bank -->
                                    <div class="text-center py-3">
                                        <div class="avatar flex-shrink-0 mb-3">
                                            <i class="fas fa-university fa-2x text-muted" aria-hidden="true"></i>
                                        </div>
                                        <span class="fw-semibold d-block mb-1">Belum Ada Akun Bank</span>
                                        <p class="text-muted small mb-3">Anda belum menghubungkan akun bank</p>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#createBankAccountModal">
                                            <i class="bx bx-plus me-1"></i> Hubungkan Akun Bank
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Tambah Akun Bank -->
    <div class="modal fade" id="createBankAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Akun Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="updateBankAccountForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="alert alert-info mb-3" role="alert">
                                <i class="bx bx-info-circle me-2"></i>
                                <b>Akses hanya untuk baca data mutasi.</b> Mutasibank tidak dapat membuat perubahan atau
                                melakukan
                                transaksi melalui akun bank Anda. Kami berkerja sama dengan Moota.co untuk menyediakan
                                layanan mutasi bank. Terimakasih.
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_bank_name" class="form-label">Nama Bank</label>
                                <select class="form-select" id="edit_bank_name" name="bank_name" required>
                                    <option value="BCA" selected>Bank Central Asia (BCA)</option>
                                    <option value="BRI" disabled>Bank Rakyat Indonesia (BRI) (Belum tersedia)</option>
                                    <option value="BRI" disabled>Bank Negara Indonesia (BNI) (Belum tersedia)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_account_name" class="form-label">Nama Pemilik Rekening</label>
                                <input type="text" class="form-control" id="edit_account_ame" name="account_number"
                                    value="Alfarozy" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="account_number"
                                    value="alfarozy" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_account_number" class="form-label">Nomor Rekening</label>
                                <input type="text" class="form-control" id="edit_account_number" name="account_number"
                                    value="213040003" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_balance" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" id="edit_account_name" name="account_name"
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

    <!-- Modal Ubah Akun Bank -->
    <div class="modal fade" id="updateBankAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Akun Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="updateBankAccountForm">
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
                                    <option value="BCA" selected>Bank Central Asia (BCA)</option>
                                    <option value="BRI" disabled>Bank Rakyat Indonesia (BRI) (Belum tersedia)</option>
                                    <option value="BRI" disabled>Bank Negara Indonesia (BNI) (Belum tersedia)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_account_name" class="form-label">Nama Pemilik Rekening</label>
                                <input type="text" class="form-control" id="edit_account_ame" name="account_number"
                                    value="Alfarozy" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="account_number"
                                    value="alfarozy" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_account_number" class="form-label">Nomor Rekening</label>
                                <input type="text" class="form-control" id="edit_account_number"
                                    name="account_number" value="213040003" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_balance" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" id="edit_account_name" name="account_name"
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
        // Handle submit form tambah akun bank
        document.getElementById('createBankAccountForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Ambil data form
            const formData = new FormData(this);
            const data = {
                bank_name: formData.get('bank_name'),
                account_number: formData.get('account_number'),
                account_name: formData.get('account_name'),
                balance: formData.get('balance'),
                is_active: formData.get('is_active') ? true : false,
                currency: formData.get('currency'),
                description: formData.get('description')
            };

            // Simulasi pemanggilan API
            simulateAPICall('POST', '/api/bank-accounts', data)
                .then(response => {
                    // Tutup modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById(
                        'createBankAccountModal'));
                    modal.hide();

                    // Tampilkan pesan sukses
                    showNotification('success', 'Akun bank berhasil dibuat!');

                    // Reset form
                    this.reset();

                    // Di aplikasi nyata, Anda bisa refresh halaman atau update UI
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                })
                .catch(error => {
                    showNotification('error', 'Gagal membuat akun bank. Silakan coba lagi.');
                });
        });

        // Handle submit form ubah akun bank
        document.getElementById('updateBankAccountForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Ambil data form
            const formData = new FormData(this);
            const data = {
                bank_name: formData.get('bank_name'),
                account_number: formData.get('account_number'),
                account_name: formData.get('account_name'),
                balance: formData.get('balance'),
                is_active: formData.get('is_active') ? true : false,
                currency: formData.get('currency'),
                description: formData.get('description')
            };

            // Simulasi pemanggilan API (pakai ID akun dummy)
            simulateAPICall('PUT', '/api/bank-accounts/1', data)
                .then(response => {
                    // Tutup modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById(
                        'updateBankAccountModal'));
                    modal.hide();

                    // Tampilkan pesan sukses
                    showNotification('success', 'Akun bank berhasil diperbarui!');

                    // Di aplikasi nyata, Anda bisa refresh halaman atau update UI
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                })
                .catch(error => {
                    showNotification('error', 'Gagal memperbarui akun bank. Silakan coba lagi.');
                });
        });

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

        // Fungsi simulasi pemanggilan API
        function simulateAPICall(method, url, data = null) {
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    // Simulasi respons sukses
                    console.log(`Panggilan API: ${method} ${url}`, data);
                    resolve({
                        success: true,
                        message: 'Operasi berhasil dilakukan',
                        data: data
                    });

                    // Simulasi error (uncomment untuk testing error)
                    // reject(new Error('Simulasi error API'));
                }, 1000);
            });
        }

        // Fungsi untuk menampilkan notifikasi
        function showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'bx-check-circle' : 'bx-error';

            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bx ${icon} me-2 fs-5"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Ubah logo bank berdasarkan pilihan
        document.getElementById('bank_name').addEventListener('change', function(e) {
            updateBankLogo(this.value, 'create');
        });

        document.getElementById('edit_bank_name').addEventListener('change', function(e) {
            updateBankLogo(this.value, 'edit');
        });

        function updateBankLogo(bankName, type) {
            const bankLogos = {
                'BCA': 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg',
                'BRI': 'https://upload.wikimedia.org/wikipedia/commons/6/68/Bank_Rakyat_Indonesia_%28BRI%29_logo.svg',
                'Mandiri': 'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg',
                'BNI': 'https://upload.wikimedia.org/wikipedia/commons/9/93/Logo_BNI.png',
                'BTN': 'https://upload.wikimedia.org/wikipedia/commons/7/7e/Bank_Tabungan_Negara_%28BTN%29_logo.svg',
                'CIMB': 'https://upload.wikimedia.org/wikipedia/commons/9/9d/CIMB_logo.svg',
                'Other': null
            };

            // Implementasi nyata: update preview logo di dalam modal
            console.log(`Bank dipilih: ${bankName} untuk form ${type}`);
        }
    </script>
@endpush
