@extends('layouts.app')

@section('title', 'Pendaftaran Akun Baru PMB — SMPS2 Al-Muhajirin')

@section('content')
<div class="lp-auth-page" style="font-family: var(--font-landing);">
    <div class="lp-auth-glow-1"></div>
    <div class="lp-auth-glow-2"></div>

    <div class="lp-auth-container" style="max-width: 540px;">
        <div class="lp-auth-card">
            <div class="lp-auth-card-top-accent"></div>

            <div class="lp-auth-header">
                <a href="{{ route('home') }}" class="lp-auth-icon-badge" title="Kembali ke Beranda">
                    <span class="material-symbols-outlined" style="font-size: 28px;">how_to_reg</span>
                </a>
                <div style="display: flex; justify-content: center; gap: 6px; margin-bottom: 8px;">
                    <span class="lp-badge lp-badge-blue" style="font-size: 11.5px;">
                        Pendaftaran Siswa Baru
                    </span>
                    <span class="lp-badge lp-badge-yellow" style="font-size: 11.5px;">
                        PMB TP {{ date('Y') }}–{{ date('Y') + 1 }}
                    </span>
                </div>
                <h1 class="lp-auth-title">Buat Akun PMB Online</h1>
                <p class="lp-auth-subtitle">Lengkapi formulir singkat berikut untuk membuat akun pendaftaran calon santri baru.</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center mb-4" style="border-radius: 12px; font-size: 13.5px;" role="alert">
                    <span class="material-symbols-outlined me-2" style="font-size: 20px;">error</span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf

                <!-- Email Aktif -->
                <div class="lp-input-group">
                    <label for="email" class="lp-input-label">
                        Alamat Email Aktif <span style="color: var(--lp-red);">*</span>
                    </label>
                    <div class="lp-input-wrapper">
                        <span class="material-symbols-outlined lp-input-icon">alternate_email</span>
                        <input type="email" 
                               class="lp-input-field @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="contoh: wali@gmail.com" 
                               inputmode="email"
                               required 
                               autofocus>
                    </div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                        Email digunakan untuk login portal, verifikasi, dan pengumuman seleksi.
                    </div>
                    @error('email')
                        <div class="text-danger mt-1" style="font-size: 12.5px; font-weight: 600;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nama Lengkap -->
                <div class="lp-input-group">
                    <label for="full_name" class="lp-input-label">
                        Nama Lengkap Calon Siswa <span style="color: var(--lp-red);">*</span>
                    </label>
                    <div class="lp-input-wrapper">
                        <span class="material-symbols-outlined lp-input-icon">person</span>
                        <input type="text" 
                               class="lp-input-field @error('full_name') is-invalid @enderror" 
                               id="full_name" 
                               name="full_name" 
                               value="{{ old('full_name') }}" 
                               placeholder="Nama lengkap sesuai akta kelahiran" 
                               required>
                    </div>
                    @error('full_name')
                        <div class="text-danger mt-1" style="font-size: 12.5px; font-weight: 600;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password & Confirmation -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label for="password" class="lp-input-label">
                            Kata Sandi <span style="color: var(--lp-red);">*</span>
                        </label>
                        <div class="lp-input-wrapper">
                            <span class="material-symbols-outlined lp-input-icon">lock</span>
                            <input type="password" 
                                   class="lp-input-field @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Min. 6 karakter" 
                                   minlength="6" 
                                   required>
                            <button type="button" class="lp-password-toggle" onclick="togglePasswordVisibility('password', this)" aria-label="Lihat kata sandi">
                                <span class="material-symbols-outlined" style="font-size: 20px;">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="password_confirmation" class="lp-input-label">
                            Ulangi Kata Sandi <span style="color: var(--lp-red);">*</span>
                        </label>
                        <div class="lp-input-wrapper">
                            <span class="material-symbols-outlined lp-input-icon">lock_reset</span>
                            <input type="password" 
                                   class="lp-input-field" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   placeholder="Ulangi sandi" 
                                   required>
                            <button type="button" class="lp-password-toggle" onclick="togglePasswordVisibility('password_confirmation', this)" aria-label="Lihat konfirmasi kata sandi">
                                <span class="material-symbols-outlined" style="font-size: 20px;">visibility</span>
                            </button>
                        </div>
                    </div>
                    @error('password')
                        <div class="col-12 text-danger mt-1" style="font-size: 12.5px; font-weight: 600;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="lp-btn-submit">
                    <span>Daftarkan Akun Siswa Baru</span>
                    <span class="material-symbols-outlined" style="font-size: 19px;">arrow_forward</span>
                </button>
            </form>

            <div class="lp-auth-footer">
                <span style="font-size: 13.5px; color: var(--lp-on-surface-variant); display: block; margin-bottom: 6px;">
                    Sudah memiliki akun pendaftaran sebelumnya?
                </span>
                <a href="{{ route('login') }}" style="display: inline-flex; align-items: center; gap: 4px; font-size: 14px; font-weight: 700; color: var(--lp-primary); text-decoration: none;">
                    <span>Masuk ke Akun Portal Santri</span>
                    <span class="material-symbols-outlined" style="font-size: 16px;">login</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('.material-symbols-outlined');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }
</script>
@endsection
