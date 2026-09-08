@extends('layouts.app')

@section('title', 'Masuk Portal PPDB')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-custom p-4 p-md-5 border-0 shadow-lg">
                <div class="text-center mb-4">
                    <div class="navbar-brand-badge mx-auto mb-3" style="width: 55px; height: 55px; font-size: 1.6rem;">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Masuk Portal PPDB</h3>
                    <p class="text-muted small">Gunakan NIK atau Email yang telah Anda daftarkan sebelumnya.</p>
                </div>

                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf

                    <!-- NIK / Identifier -->
                    <div class="mb-3">
                        <label for="nik" class="form-label fw-semibold text-dark">
                            NIK atau Alamat Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-badge"></i></span>
                            <input type="text" 
                                   class="form-control border-start-0 ps-0 @error('nik') is-invalid @enderror" 
                                   id="nik" 
                                   name="nik" 
                                   value="{{ old('nik') }}" 
                                   placeholder="Masukkan 16 digit NIK atau email" 
                                   required 
                                   autofocus>
                        </div>
                        @error('nik')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label fw-semibold text-dark mb-0">
                                Kata Sandi (Password) <span class="text-danger">*</span>
                            </label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key"></i></span>
                            <input type="password" 
                                   class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Masukkan kata sandi akun Anda" 
                                   required>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-navy w-100 py-2 fs-6 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Belum memiliki akun calon siswa? 
                        <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">Daftar Akun Baru</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
