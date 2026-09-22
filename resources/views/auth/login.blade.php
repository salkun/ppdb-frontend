@extends('layouts.app')

@section('title', 'Masuk Akun Calon Siswa — SMPS2 Al-Muhajirin')

@section('content')
<div class="lp-auth-page" style="font-family: var(--font-landing);">
    <div class="lp-auth-glow-1"></div>
    <div class="lp-auth-glow-2"></div>

    <div class="lp-auth-container">
        <div class="lp-auth-card">
            <div class="lp-auth-card-top-accent"></div>

            <div class="lp-auth-header">
                <a href="{{ route('home') }}" class="lp-auth-icon-badge" style="background: transparent; box-shadow: none; border: none; padding: 0;" title="Kembali ke Beranda">
                    <img src="{{ asset('logo/logo.png') }}" alt="Logo SMPS2 Al-Muhajirin" style="width: 56px; height: 56px; object-fit: contain;">
                </a>
                <div style="display: flex; justify-content: center; gap: 6px; margin-bottom: 8px;">
                    <span class="lp-badge lp-badge-blue" style="font-size: 11.5px;">
                        Portal Calon Siswa
                    </span>
                    <span class="lp-badge lp-badge-yellow" style="font-size: 11.5px;">
                        PMB TP 2027–2028
                    </span>
                </div>
                <h1 class="lp-auth-title">Masuk ke Portal Murid</h1>
                <p class="lp-auth-subtitle">Masukkan alamat email dan kata sandi akun Anda untuk melanjutkan pendaftaran.</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center mb-4" style="border-radius: 12px; font-size: 13.5px;" role="alert">
                    <span class="material-symbols-outlined me-2" style="font-size: 20px;">error</span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center mb-4" style="border-radius: 12px; font-size: 13.5px;" role="alert">
                    <span class="material-symbols-outlined me-2" style="font-size: 20px;">check_circle</span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf

                <!-- Email Input -->
                <div class="lp-input-group">
                    <label for="email" class="lp-input-label">
                        Alamat Email Terdaftar <span style="color: var(--lp-red);">*</span>
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
                    @error('email')
                        <div class="text-danger mt-1" style="font-size: 12.5px; font-weight: 600;">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="lp-input-group mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="lp-input-label mb-0">
                            Kata Sandi Akun <span style="color: var(--lp-red);">*</span>
                        </label>
                    </div>
                    <div class="lp-input-wrapper">
                        <span class="material-symbols-outlined lp-input-icon">lock</span>
                        <input type="password" 
                               class="lp-input-field @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="Masukkan kata sandi" 
                               required>
                        <button type="button" class="lp-password-toggle" onclick="togglePasswordVisibility('password', this)" aria-label="Lihat kata sandi">
                            <span class="material-symbols-outlined" style="font-size: 20px;">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <div class="text-danger mt-1" style="font-size: 12.5px; font-weight: 600;">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="lp-btn-submit">
                    <span>Masuk ke Akun Sekarang</span>
                    <span class="material-symbols-outlined" style="font-size: 19px;">login</span>
                </button>
            </form>

            <div class="lp-auth-footer">
                <span style="font-size: 13.5px; color: var(--lp-on-surface-variant); display: block; margin-bottom: 6px;">
                    Belum memiliki akun pendaftaran calon siswa?
                </span>
                <a href="{{ route('register') }}" style="display: inline-flex; align-items: center; gap: 4px; font-size: 14px; font-weight: 700; color: var(--lp-primary); text-decoration: none;">
                    <span>Daftar Akun Calon Siswa Baru</span>
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                </a>

                <div class="mt-3 pt-3" style="border-top: 1px dashed var(--lp-surface-container); display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <a href="https://wa.me/6287821055283?text=Halo%20Panitia%20PMB%20SMPS2%20Al-Muhajirin,%20saya%20butuh%20bantuan%20login%20portal" target="_blank" rel="noopener" class="lp-auth-help-pill">
                        <span class="material-symbols-outlined" style="font-size: 16px;">chat</span>
                        <span>Butuh bantuan login? Hubungi Panitia WA</span>
                    </a>
                    <a href="{{ route('admin.login') }}" style="font-size: 12px; color: #94a3b8; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-outlined" style="font-size: 14px;">admin_panel_settings</span>
                        <span>Login Operator / Staf TU</span>
                    </a>
                </div>
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
