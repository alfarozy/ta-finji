@extends('layouts.backoffice')

@section('title', 'Dashboard')
@section('content')
    <div class="content-wrapper">
        <!-- Content -->

        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-lg-12 order-1">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-title d-flex align-items-start justify-content-between">
                                        <div class="avatar flex-shrink-0">
                                            <i class="fas fa-arrow-trend-up fa-2x text-success" aria-hidden="true"></i>
                                        </div>

                                    </div>
                                    <span class="fw-semibold d-block mb-1">Pemasukan</span>
                                    <h3 class="card-title text-nowrap mb-1">Rp5,000,000</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-title d-flex align-items-start justify-content-between">
                                        <div class="avatar flex-shrink-0">
                                            <i class="fa fa-arrow-trend-down fa-2x text-danger" aria-hidden="true"></i>
                                        </div>

                                    </div>
                                    <span class="fw-semibold d-block mb-1">Pengeluaran</span>
                                    <h3 class="card-title text-nowrap mb-1">Rp1,500,000</h3>

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 col-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-title d-flex align-items-start justify-content-between">
                                        <div class="avatar flex-shrink-0">
                                            <i class="fa fa-wallet fa-2x text-primary" aria-hidden="true"></i>
                                        </div>

                                    </div>
                                    <span class="fw-semibold d-block mb-1">Saldo saat ini</span>
                                    <h3 class="card-title text-nowrap mb-1">Rp3,500,000</h3>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Total Revenue -->
                <div class="col-12 order-2 order-md-3 order-lg-2 mb-4">
                    <div class="card">
                        <div class="row row-bordered g-0">
                            <div class="col-md-12">
                                <h5 class="card-header m-0 me-2 pb-3">Ringkasan keuangan</h5>
                                <div id="totalRevenueChart" class="px-2"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- / Content -->

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

    <div class="content-backdrop fade"></div>
    </div>
@endsection
