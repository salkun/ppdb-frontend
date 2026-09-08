@extends('layouts.app')

@section('title', 'Pendaftaran Akun Baru PPDB')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-custom p-4 p-md-5 border-0 shadow-lg">
                <div class="text-center mb-4">
                    <div class="navbar-brand-badge mx-auto mb-3" style="width: 55px; height: 55px; font-size: 1.6rem;">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Registrasi Akun PPDB</h3>
                    <p class="text-muted small">Buat akun untuk memulai proses pendaftaran calon peserta didik baru.</p>
                </div>

                <form action="{{ route('register.submit') }}" method="POST">
                    @csrf

                    <!-- NIK Calon Siswa -->
                    <div class="mb-3">
                        <label for="nik" class="form-label fw-semibold text-dark">
                            Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-card-heading"></i></span>
                            <input type="text" 
                                   class="form-control border-start-0 ps-0 @error('nik') is-invalid @enderror" 
                                   id="nik" 
                                   name="nik" 
                                   value="{{ old('nik') }}" 
                                   placeholder="16 digit NIK sesuai Kartu Keluarga" 
                                   maxlength="16" 
                                   required 
                                   pattern="\d{16}"
                                   title="NIK harus 16 digit angka">
                        </div>
                        <div class="form-text text-muted small">NIK akan digunakan sebagai identitas utama login akun.</div>
                        @error('nik')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-semibold text-dark">
                            Nama Lengkap Calon Siswa <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" 
                                   class="form-control border-start-0 ps-0 @error('full_name') is-invalid @enderror" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="{{ old('full_name') }}" 
                                   placeholder="Nama lengkap sesuai akta kelahiran" 
                                   required>
                        </div>
                        @error('full_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-dark">
                            Alamat Email Aktif <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" 
                                   class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="nama@email.com" 
                                   required>
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold text-dark">
                            Kata Sandi (Password) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" 
                                   class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimal 6 karakter" 
                                   minlength="6" 
                                   required>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold text-dark">
                            Ulangi Kata Sandi <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-check"></i></span>
                            <input type="password" 
                                   class="form-control border-start-0 ps-0" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Ketik ulang kata sandi di atas" 
                                   minlength="6" 
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-teal w-100 py-2 fs-6 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Buat Akun Pendaftaran
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Sudah pernah membuat akun? 
                        <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
