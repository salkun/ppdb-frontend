<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal PMB — SMPS2 Al-Muhajirin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('logo/favicon-64x64.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/favicon-128x128.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Chivo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Chivo:wght@700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Central PPDB Theme -->
    <link rel="stylesheet" href="{{ asset('css/ppdb-theme.css') }}">

    <style>
        :root {
            --adm-bg: #0b1329;
            --adm-card-bg: #111c38;
            --adm-border: rgba(255, 255, 255, 0.08);
            --adm-primary: #0066cc;
            --adm-primary-hover: #0052a3;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background-color: var(--adm-bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glows */
        .adm-glow-1 {
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 380px;
            background: radial-gradient(circle, rgba(0, 102, 204, 0.22) 0%, rgba(14, 165, 233, 0.1) 45%, transparent 70%);
            filter: blur(80px);
            pointer-events: none;
        }

        .adm-glow-2 {
            position: absolute;
            bottom: -50px;
            right: 10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(22, 163, 74, 0.1) 0%, transparent 65%);
            filter: blur(80px);
            pointer-events: none;
        }

        /* Subtle grid background */
        .adm-grid-pattern {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px);
            background-size: 32px 32px;
            opacity: 0.7;
            pointer-events: none;
        }

        .adm-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            background: rgba(17, 28, 56, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 2.5rem 2.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.05);
        }

        .adm-card-top-line {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0066cc, #38bdf8, #10b981);
            border-radius: 24px 24px 0 0;
        }

        .adm-brand-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
            margin-bottom: 1.25rem;
        }

        .adm-brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .adm-title {
            font-family: 'Chivo', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.35rem;
            letter-spacing: -0.01em;
        }

        .adm-subtitle {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 0;
            line-height: 1.5;
        }

        .adm-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 9999px;
            color: #34d399;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            margin-bottom: 0.75rem;
        }

        .adm-status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
        }

        .adm-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 0.45rem;
        }

        .adm-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .adm-input-icon {
            position: absolute;
            left: 14px;
            color: #64748b;
            font-size: 20px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .adm-input {
            width: 100%;
            padding: 12px 14px 12px 44px;
            background: rgba(11, 19, 41, 0.6);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            color: #f8fafc;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .adm-input::placeholder {
            color: #64748b;
        }

        .adm-input:focus {
            border-color: #38bdf8;
            background: rgba(11, 19, 41, 0.9);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
        }

        .adm-input-wrap:focus-within .adm-input-icon {
            color: #38bdf8;
        }

        .adm-toggle-pw {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color 0.2s;
        }

        .adm-toggle-pw:hover {
            color: #38bdf8;
        }

        .adm-btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 13px 20px;
            background: linear-gradient(135deg, #0066cc, #0284c7);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(0, 102, 204, 0.35);
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .adm-btn-submit:hover {
            background: linear-gradient(135deg, #0052a3, #0369a1);
            box-shadow: 0 8px 25px rgba(0, 102, 204, 0.45);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .adm-footer {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            text-align: center;
        }

        .adm-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #94a3b8;
            font-size: 13px;
            text-decoration: none;
            transition: color 0.2s;
        }

        .adm-back-link:hover {
            color: #38bdf8;
        }
    </style>
</head>
<body>

    <div class="adm-glow-1"></div>
    <div class="adm-glow-2"></div>
    <div class="adm-grid-pattern"></div>

    <div class="adm-card">
        <div class="adm-card-top-line"></div>

        <div class="text-center mb-4">
            <div class="adm-brand-icon">
                <img src="{{ asset('logo/logo.png') }}" alt="Logo SMPS2 Al-Muhajirin">
            </div>
            <div>
                <span class="adm-status-badge">
                    <span class="adm-status-dot"></span>
                    PANEL OPERATOR SIAKAD &amp; PMB
                </span>
            </div>
            <h1 class="adm-title">SMPS2 Al-Muhajirin</h1>
            <p class="adm-subtitle">Portal login khusus panitia PPDB dan staf tata usaha terotorisasi.</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-3" style="background: rgba(220, 38, 38, 0.15); border: 1px solid rgba(220, 38, 38, 0.3); color: #fca5a5; font-size: 13px; border-radius: 12px;" role="alert">
                <span class="material-symbols-outlined me-2" style="font-size: 18px;">warning</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning d-flex align-items-center mb-3" style="background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.3); color: #fde047; font-size: 13px; border-radius: 12px;" role="alert">
                <span class="material-symbols-outlined me-2" style="font-size: 18px;">info</span>
                <div>{{ session('warning') }}</div>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info d-flex align-items-center mb-3" style="background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); color: #7dd3fc; font-size: 13px; border-radius: 12px;" role="alert">
                <span class="material-symbols-outlined me-2" style="font-size: 18px;">check_circle</span>
                <div>{{ session('info') }}</div>
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf

            <!-- Username Operator -->
            <div class="mb-3">
                <label for="username" class="adm-label">
                    Username Operator <span style="color: #f87171;">*</span>
                </label>
                <div class="adm-input-wrap">
                    <span class="material-symbols-outlined adm-input-icon">person</span>
                    <input type="text" 
                           class="adm-input @error('username') is-invalid @enderror" 
                           id="username" 
                           name="username" 
                           value="{{ old('username') }}" 
                           placeholder="Masukkan username admin" 
                           required 
                           autofocus>
                </div>
                @error('username')
                    <div class="mt-1" style="color: #f87171; font-size: 12px; font-weight: 600;">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="adm-label">
                    Kata Sandi <span style="color: #f87171;">*</span>
                </label>
                <div class="adm-input-wrap">
                    <span class="material-symbols-outlined adm-input-icon">lock</span>
                    <input type="password" 
                           class="adm-input @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Masukkan kata sandi" 
                           required>
                    <button type="button" class="adm-toggle-pw" onclick="togglePasswordVisibility('password', this)" aria-label="Lihat kata sandi">
                        <span class="material-symbols-outlined" style="font-size: 20px;">visibility</span>
                    </button>
                </div>
                @error('password')
                    <div class="mt-1" style="color: #f87171; font-size: 12px; font-weight: 600;">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="adm-btn-submit">
                <span>Masuk Panel Operator</span>
                <span class="material-symbols-outlined" style="font-size: 19px;">login</span>
            </button>
        </form>

        <div class="adm-footer">
            <a href="{{ route('home') }}" class="adm-back-link">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
                <span>Kembali ke Beranda Utama PPDB</span>
            </a>
            <div class="mt-3" style="font-size: 11px; color: #64748b;">
                Akses terenkripsi &bull; SMPS2 Al-Muhajirin Purwakarta
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
</body>
</html>
