@extends('layouts.backoffice')

@section('title', 'Kategori transaksi')
@push('styles')
    <style>
        .swal2-container {
            z-index: 99999 !important;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-header">Kategori transaksi</h5>
                    <div class="d-flex">

                        <a href="{{ route('transactions-categories.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-plus me-1"></i> Kategori baru
                        </a>
                    </div>
                </div>

                <div class="table-responsive text-nowrap">
                    <table id="table" class="table table-striped">
                        <thead>
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="55%">Kategoru</th>
                                <th width="30%" class="text-center">Tipe</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $item)
                                <tr>
                                    <th class="text-center">{{ $loop->iteration }}</th>
                                    <td>{{ $item->name }}</td>
                                    <td class="text-center">
                                        @if ($item->type == 'income')
                                            <span class="badge bg-label-success me-1">Income</span>
                                        @else
                                            <span class="badge bg-label-danger me-1">Expense</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                    href="{{ route('transactions-categories.edit', $item->id) }}"><i
                                                        class="bx bx-edit-alt me-1"></i> Edit</a>
                                                <button type="button" class="dropdown-item text-danger"
                                                    data-id="{{ $item->id }}" onclick="confirmDeleteCategory(this)">
                                                    <i class="bx bx-trash me-1"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>

                </div>
            </div>
        </div>

    </div>
    <!-- Footer -->
    <footer class="content-footer footer bg-footer-theme">
        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
            <div class="mb-2 mb-md-0">
                ©
                <script>
                    document.write(new Date().getFullYear());
                </script>

                <a href="https://finji.app" target="_blank" class="footer-link fw-bolder">Hak Cipta dilindungi</a>
            </div>
            <div>


                <a href="https://github.com/themeselection/sneat-html-admin-template-free/issues" target="_blank"
                    class="footer-link me-4">Dev by Alfarozy</a>
            </div>
        </div>
    </footer>
    <!-- / Footer -->
    <form id="delete-category-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>


    <div class="content-backdrop fade"></div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDeleteCategory(el) {
            const categoryId = el.dataset.id;
            const form = document.getElementById('delete-category-form');

            form.action = `/transactions-categories/${categoryId}`;

            Swal.fire({
                title: 'Hapus kategori?',
                text: 'Kategori yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
@endpush
