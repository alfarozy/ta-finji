@extends('layouts.auth')
@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <!-- Login -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="index.html" class="app-brand-link gap-2">
                                <span class="app-brand-logo demo">
                                    <!-- SVG LOGO TETAP -->
                                    <svg width="25" viewBox="0 0 25 42" ...>
                                        <!-- (isi SVG sama seperti sebelumnya) -->
                                    </svg>
                                </span>
                                <span class="app-brand-text demo text-body fw-bolder">Login</span>
                            </a>
                        </div>
                        <!-- /Logo -->

                        <h4 class="mb-2">Selamat datang! 👋</h4>
                        <p class="mb-4">Silakan masuk ke akun Anda untuk mulai menggunakan aplikasi.</p>
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif


                        <form id="formAuthentication" class="mb-3" action="{{ route('login.proccess') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control @error('password') is-invalid @enderror"
                                    id="email" name="email" placeholder="Masukkan email Anda" autofocus />
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Kata Sandi</label>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        placeholder="••••••••••••" aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">
                                    Masuk
                                </button>
                            </div>
                        </form>

                        <p class="text-center">
                            <span>Baru di platform kami?</span>
                            <a href="{{ route('register') }}">
                                <span>Buat akun baru</span>
                            </a>
                        </p>
                    </div>
                </div>
                <!-- /Login -->
            </div>
        </div>
    </div>
@endsection
