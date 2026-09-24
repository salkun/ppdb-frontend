<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Portal SPMB Online') — Sistem Penerimaan Murid Baru</title>

    <!-- Official Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('logo/favicon-64x64.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/favicon-128x128.png') }}">

    <!-- Google Fonts: Geist Sans, Geist Mono, Newsreader (Editorial Serif) + Landing Page Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600&family=Geist:wght@300;400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400;1,6..72,500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Chivo:wght@700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS Core -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Phosphor Icons (Lightweight CSS Font) -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">

    <!-- Central PPDB Theme -->
    <link rel="stylesheet" href="{{ asset('css/ppdb-theme.css') }}?v={{ @filemtime(public_path('css/ppdb-theme.css')) ?: '2.1' }}">

    @stack('styles')
</head>
<body>

    @if(session()->has('api_token'))
        <!-- ======================================================== -->
        <!-- AUTHENTICATED STUDENT PORTAL (LP-THEMED DASHBOARD)      -->
        <!-- ======================================================== -->
        <div class="db-shell">
            <!-- 1. Desktop Sidebar (LP Theme) -->
            <aside class="db-sidebar">
                <div>
                    <!-- Brand -->
                    <a class="db-sidebar-brand" href="{{ route('dashboard') }}">
                        <div class="db-sidebar-brand-icon" style="background: transparent; box-shadow: none;">
                            <img src="{{ asset('logo/logo.png') }}" alt="Logo SMPS2 Al-Muhajirin" style="width: 38px; height: 38px; object-fit: contain;">
                        </div>
                        <div>
                            <span class="db-sidebar-brand-text">PPDB Online</span>
                            <div class="db-sidebar-brand-sub">
                                <span class="db-sidebar-brand-dot"></span>
                                <span>SMPS2 AL-MUHAJIRIN</span>
                            </div>
                        </div>
                    </a>

                    <!-- Navigation -->
                    <div style="margin-top: 1.25rem;">
                        <div class="db-sidebar-nav-label">Menu Utama</div>
                        <ul class="db-sidebar-nav">
                            <li>
                                <a class="db-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <span class="material-symbols-outlined">dashboard</span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a class="db-sidebar-link {{ request()->routeIs('ppdb.form') ? 'active' : '' }}" href="{{ route('ppdb.form') }}">
                                    <span class="material-symbols-outlined">edit_note</span>
                                    <span>Formulir Pendaftaran</span>
                                </a>
                            </li>
                            <li>
                                <a class="db-sidebar-link {{ request()->routeIs('ppdb.upload') ? 'active' : '' }}" href="{{ route('ppdb.upload') }}">
                                    <span class="material-symbols-outlined">cloud_upload</span>
                                    <span>Upload Berkas</span>
                                </a>
                            </li>
                            <li>
                                <a class="db-sidebar-link {{ request()->routeIs('ppdb.test-card') ? 'active' : '' }}" href="{{ route('ppdb.test-card') }}">
                                    <span class="material-symbols-outlined">badge</span>
                                    <span>Kartu Tes</span>
                                </a>
                            </li>
                            <li>
                                <a class="db-sidebar-link" href="{{ route('home') }}">
                                    <span class="material-symbols-outlined">info</span>
                                    <span>Informasi & Alur</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Footer -->
                <div class="db-sidebar-footer">
                    <a href="https://wa.me/6287821055283" target="_blank" rel="noopener" class="db-sidebar-link">
                        <span class="material-symbols-outlined">chat</span>
                        <span>Bantuan WhatsApp</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="db-sidebar-link text-danger" style="width:100%;border:none;background:none;cursor:pointer;">
                            <span class="material-symbols-outlined">logout</span>
                            <span>Keluar Sesi</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- 2. Mobile Offcanvas Drawer -->
            <div class="offcanvas offcanvas-start p-3" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel" style="width:300px;background:#fff;border-right:1px solid var(--lp-surface-container);font-family:var(--font-landing);z-index:1070;">
                <div class="offcanvas-header pb-3 border-bottom px-1" style="border-color:var(--lp-surface-container)!important;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="db-sidebar-brand-icon" style="width:38px;height:38px;background:transparent;box-shadow:none;">
                            <img src="{{ asset('logo/logo.png') }}" alt="Logo SMPS2 Al-Muhajirin" style="width: 36px; height: 36px; object-fit: contain;">
                        </div>
                        <div>
                            <span style="font-weight:700;font-size:15px;color:var(--lp-on-surface);display:block;">PPDB Online</span>
                            <span style="font-size:11px;font-weight:700;color:var(--lp-primary);">SMPS2 AL-MUHAJIRIN</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
                </div>
                <div class="offcanvas-body d-flex flex-column justify-content-between px-1 py-3" style="overflow-y:auto;padding-bottom:calc(6.5rem + env(safe-area-inset-bottom, 0px)) !important;">
                    <div>
                        <ul class="db-sidebar-nav">
                            <li><a class="db-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="material-symbols-outlined">dashboard</span><span>Dashboard</span></a></li>
                            <li><a class="db-sidebar-link {{ request()->routeIs('ppdb.form') ? 'active' : '' }}" href="{{ route('ppdb.form') }}"><span class="material-symbols-outlined">edit_note</span><span>Formulir Pendaftaran</span></a></li>
                            <li><a class="db-sidebar-link {{ request()->routeIs('ppdb.upload') ? 'active' : '' }}" href="{{ route('ppdb.upload') }}"><span class="material-symbols-outlined">cloud_upload</span><span>Upload Berkas</span></a></li>
                            <li><a class="db-sidebar-link {{ request()->routeIs('ppdb.test-card') ? 'active' : '' }}" href="{{ route('ppdb.test-card') }}"><span class="material-symbols-outlined">badge</span><span>Kartu Tes</span></a></li>
                            <li><a class="db-sidebar-link" href="{{ route('home') }}"><span class="material-symbols-outlined">info</span><span>Informasi & Alur</span></a></li>
                        </ul>
                    </div>
                    <div class="db-sidebar-footer" style="margin-top:auto;padding-top:1rem;border-top:1px solid var(--lp-surface-container);">
                        <a href="https://wa.me/6287821055283" target="_blank" rel="noopener" class="db-sidebar-link"><span class="material-symbols-outlined">chat</span><span>Bantuan WhatsApp</span></a>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">@csrf
                            <button type="submit" class="db-sidebar-link text-danger" style="width:100%;border:none;background:rgba(239,68,68,0.08);border-radius:10px;padding:10px 14px;cursor:pointer;display:flex;align-items:center;gap:10px;font-weight:600;margin-top:8px;"><span class="material-symbols-outlined">logout</span><span>Keluar Sesi</span></button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 3. Main Viewport -->
            <div class="db-viewport">
                <!-- Topbar -->
                <header class="db-topbar">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <button class="d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" style="background:none;border:1px solid var(--lp-surface-container);border-radius:10px;padding:6px 8px;cursor:pointer;display:flex;align-items:center;" aria-label="Menu Navigasi">
                            <span class="material-symbols-outlined" style="font-size:22px;color:var(--lp-on-surface);">menu</span>
                        </button>
                        <span class="db-topbar-breadcrumb d-none d-sm-inline">Portal /</span>
                        <span class="db-topbar-title">@yield('title', 'Dashboard')</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span class="db-badge db-badge-green d-none d-sm-inline-flex" style="font-size:10px;">
                            <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span> ONLINE
                        </span>
                        <div class="db-topbar-avatar" title="{{ session('full_name', 'Siswa') }}">
                            {{ strtoupper(substr(session('full_name', 'S'), 0, 1)) }}
                        </div>
                        <span style="font-size:13px;font-weight:600;color:var(--lp-on-surface);" class="d-none d-md-inline">{{ session('full_name', 'Siswa') }}</span>
                        <!-- Quick Logout Button (Bisa langsung diakses di Desktop & Mobile) -->
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size:12px;font-weight:600;border-radius:8px;" title="Keluar dari sesi akun">
                                <span class="material-symbols-outlined" style="font-size:16px;">logout</span>
                                <span class="d-none d-sm-inline">Keluar</span>
                            </button>
                        </form>
                    </div>
                </header>

                <!-- Flash Notifications -->
                <div style="max-width:1100px;width:100%;margin:0 auto;padding:0.75rem 1.5rem 0;">
                    @if(session('success'))
                        <div style="background:var(--lp-green-light);border:1px solid rgba(22,163,74,0.3);border-radius:12px;padding:0.85rem 1.25rem;margin-bottom:0.75rem;display:flex;align-items:center;gap:10px;font-size:14px;color:var(--lp-green);font-weight:600;" class="alert alert-dismissible fade show" role="alert">
                            <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
                            <span style="flex:1;">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup" style="font-size:10px;"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div style="background:var(--lp-red-light);border:1px solid rgba(220,38,38,0.3);border-radius:12px;padding:0.85rem 1.25rem;margin-bottom:0.75rem;display:flex;align-items:center;gap:10px;font-size:14px;color:var(--lp-red);font-weight:600;" class="alert alert-dismissible fade show" role="alert">
                            <span class="material-symbols-outlined" style="font-size:20px;">error</span>
                            <span style="flex:1;">{{ session('error') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup" style="font-size:10px;"></button>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div style="background:var(--lp-yellow-light);border:1px solid rgba(234,179,8,0.4);border-radius:12px;padding:0.85rem 1.25rem;margin-bottom:0.75rem;display:flex;align-items:center;gap:10px;font-size:14px;color:var(--lp-yellow-dark);font-weight:600;" class="alert alert-dismissible fade show" role="alert">
                            <span class="material-symbols-outlined" style="font-size:20px;">warning</span>
                            <span style="flex:1;">{{ session('warning') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup" style="font-size:10px;"></button>
                        </div>
                    @endif
                    @if($errors->any())
                        <div style="background:var(--lp-red-light);border:1px solid rgba(220,38,38,0.3);border-radius:12px;padding:0.85rem 1.25rem;margin-bottom:0.75rem;font-size:14px;color:var(--lp-red);" class="alert alert-dismissible fade show" role="alert">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;font-weight:700;">
                                <span class="material-symbols-outlined" style="font-size:20px;">error</span>
                                Periksa kembali isian berikut:
                            </div>
                            <ul style="margin:0;padding-left:1.25rem;font-weight:500;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup" style="font-size:10px;"></button>
                        </div>
                    @endif
                </div>

                <!-- Main Content -->
                <main style="flex:1;font-family:var(--font-landing);">
                    @yield('content')
                </main>
            </div>

            <!-- 4. Mobile Bottom Tab Navigation -->
            <nav class="db-mobile-tab-bar">
                <div class="db-mobile-tab-inner">
                    <a href="{{ route('dashboard') }}" class="db-mobile-tab {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('ppdb.form') }}" class="db-mobile-tab {{ request()->routeIs('ppdb.form') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">edit_note</span>
                        <span>Formulir</span>
                    </a>
                    <a href="{{ route('ppdb.upload') }}" class="db-mobile-tab {{ request()->routeIs('ppdb.upload') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">cloud_upload</span>
                        <span>Berkas</span>
                    </a>
                    <a href="{{ route('ppdb.test-card') }}" class="db-mobile-tab {{ request()->routeIs('ppdb.test-card') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">badge</span>
                        <span>Kartu Tes</span>
                    </a>
                    <a href="{{ route('home') }}" class="db-mobile-tab">
                        <span class="material-symbols-outlined">info</span>
                        <span>Info</span>
                    </a>
                </div>
            </nav>
        </div>
    @else
        <!-- ======================================================== -->
        <!-- PUBLIC GUEST LAYOUT (LANDING, LOGIN, REGISTER)           -->
        <!-- ======================================================== -->
        <!-- Landing Page Navbar -->
        @php
            $isHome = request()->routeIs('home');
            $homePrefix = $isHome ? '' : route('home');
        @endphp
        <header class="lp-navbar" style="font-family: var(--font-landing);">
            <div class="lp-navbar-inner">
                <a class="lp-brand" href="{{ route('home') }}">
                    <div class="lp-brand-icon" style="background: transparent; box-shadow: none;">
                        <img src="{{ asset('logo/logo.png') }}" alt="Logo SMPS2 Al-Muhajirin" style="width: 38px; height: 38px; object-fit: contain;">
                    </div>
                    <div>
                        <span class="lp-brand-text">SMPS2 Al-Muhajirin</span>
                        <div class="lp-brand-sub">
                            <span class="lp-brand-dot"></span>
                            <span class="lp-brand-badge">PMB TP 2027–2028</span>
                        </div>
                    </div>
                </a>

                <nav class="lp-nav" id="lpDesktopNav">
                    <div class="lp-nav-pill-track">
                        <a href="{{ $homePrefix }}#beranda" class="lp-nav-link active" data-section="beranda">Beranda</a>
                        <a href="{{ $homePrefix }}#informasi" class="lp-nav-link" data-section="informasi">Informasi</a>
                        <a href="{{ $homePrefix }}#persyaratan" class="lp-nav-link" data-section="persyaratan">Persyaratan</a>
                        <a href="{{ $homePrefix }}#program" class="lp-nav-link" data-section="program">Program</a>
                        <a href="{{ $homePrefix }}#biaya" class="lp-nav-link" data-section="biaya">Biaya</a>
                        <a href="{{ $homePrefix }}#alur" class="lp-nav-link" data-section="alur">Alur</a>
                    </div>
                </nav>

                <div class="lp-nav-actions">
                    @if(session()->has('api_token'))
                        <a class="lp-btn-login" href="{{ route('dashboard') }}" title="Dashboard Calon Siswa">
                            <span class="material-symbols-outlined" style="font-size:18px;">dashboard</span>
                            <span>Dashboard</span>
                        </a>
                    @else
                        <a class="lp-btn-login" href="{{ route('login') }}" title="Masuk Akun Santri">
                            <span class="material-symbols-outlined" style="font-size:18px;">login</span>
                            <span>Masuk</span>
                        </a>
                        <a class="lp-btn-primary" href="{{ route('register') }}">
                            <span>Daftar Sekarang</span>
                            <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                        </a>
                    @endif
                    <button class="lp-mobile-toggle" onclick="document.getElementById('lpMobileDrawer').classList.add('open')" aria-label="Buka Menu">
                        <span class="material-symbols-outlined" style="font-size:24px;">menu</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile Navigation Drawer -->
        <div class="lp-mobile-drawer" id="lpMobileDrawer">
            <div class="lp-mobile-backdrop" onclick="document.getElementById('lpMobileDrawer').classList.remove('open')"></div>
            <div class="lp-mobile-panel">
                <div class="lp-mobile-panel-header">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="lp-brand-icon" style="width:36px;height:36px;background:transparent;box-shadow:none;">
                            <img src="{{ asset('logo/logo.png') }}" alt="Logo SMPS2 Al-Muhajirin" style="width: 34px; height: 34px; object-fit: contain;">
                        </div>
                        <div>
                            <span style="font-weight:700;font-size:15px;color:var(--lp-on-surface);display:block;">SMPS2 Al-Muhajirin</span>
                            <span style="font-size:11px;font-weight:700;color:var(--lp-primary);">PMB TP 2027–2028</span>
                        </div>
                    </div>
                    <button onclick="document.getElementById('lpMobileDrawer').classList.remove('open')" style="background:none;border:none;cursor:pointer;padding:4px;" aria-label="Tutup">
                        <span class="material-symbols-outlined" style="font-size:24px;color:var(--lp-on-surface-variant);">close</span>
                    </button>
                </div>
                <nav class="lp-mobile-nav">
                    <a href="{{ $homePrefix }}#beranda" class="lp-mobile-link active" data-section="beranda" onclick="document.getElementById('lpMobileDrawer').classList.remove('open')">
                        <span class="material-symbols-outlined" style="font-size:22px;">home</span> Beranda
                    </a>
                    <a href="{{ $homePrefix }}#informasi" class="lp-mobile-link" data-section="informasi" onclick="document.getElementById('lpMobileDrawer').classList.remove('open')">
                        <span class="material-symbols-outlined" style="font-size:22px;">info</span> Informasi
                    </a>
                    <a href="{{ $homePrefix }}#persyaratan" class="lp-mobile-link" data-section="persyaratan" onclick="document.getElementById('lpMobileDrawer').classList.remove('open')">
                        <span class="material-symbols-outlined" style="font-size:22px;">checklist</span> Persyaratan
                    </a>
                    <a href="{{ $homePrefix }}#program" class="lp-mobile-link" data-section="program" onclick="document.getElementById('lpMobileDrawer').classList.remove('open')">
                        <span class="material-symbols-outlined" style="font-size:22px;">category</span> Program
                    </a>
                    <a href="{{ $homePrefix }}#biaya" class="lp-mobile-link" data-section="biaya" onclick="document.getElementById('lpMobileDrawer').classList.remove('open')">
                        <span class="material-symbols-outlined" style="font-size:22px;">payments</span> Biaya
                    </a>
                    <a href="{{ $homePrefix }}#alur" class="lp-mobile-link" data-section="alur" onclick="document.getElementById('lpMobileDrawer').classList.remove('open')">
                        <span class="material-symbols-outlined" style="font-size:22px;">timeline</span> Alur
                    </a>
                </nav>
                <div class="lp-mobile-panel-footer">
                    @if(session()->has('api_token'))
                        <a class="lp-btn-primary" href="{{ route('dashboard') }}" style="width:100%;justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px;">dashboard</span> Dashboard Saya
                        </a>
                    @else
                        <a class="lp-btn-primary" href="{{ route('register') }}" style="width:100%;justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px;">how_to_reg</span> Daftar Sekarang
                        </a>
                        <a class="lp-btn-secondary" href="{{ route('login') }}" style="width:100%;justify-content:center;">
                            <span class="material-symbols-outlined" style="font-size:18px;">login</span> Masuk Akun
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Flash Messages for Guests -->
        @if(session('success') || session('error') || $errors->any())
        <div style="max-width:1240px;margin:0 auto;padding:0 1rem;padding-top:calc(var(--lp-navbar-height, 80px) + 16px);">
            @if(session('success'))
                <div class="alert-document alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                    <i class="ph-bold ph-check-circle fs-5 me-2 flex-shrink-0"></i>
                    <div class="flex-grow-1">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-document alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                    <i class="ph-bold ph-warning-circle fs-5 me-2 flex-shrink-0"></i>
                    <div class="flex-grow-1">{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-document alert-danger alert-dismissible fade show mb-3" role="alert">
                    <div class="d-flex align-items-center mb-1">
                        <i class="ph-bold ph-x-circle fs-5 me-2"></i>
                        <strong>Periksa kembali beberapa isian berikut:</strong>
                    </div>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif
        </div>
        @endif

        <main class="flex-grow-1">
            @yield('content')
        </main>

        <!-- Landing Page Footer -->
        <footer class="lp-footer">
            <div class="lp-footer-inner">
                <div class="lp-footer-grid">
                    <div>
                        <div class="lp-footer-brand">
                            <div class="lp-footer-brand-icon" style="background: rgba(255,255,255,0.08); box-shadow: none;">
                                <img src="{{ asset('logo/logo-white.png') }}" alt="Logo SMPS2 Al-Muhajirin" style="width: 34px; height: 34px; object-fit: contain;">
                            </div>
                            <div>
                                <span style="font-size:18px;font-weight:700;color:#fff;line-height:1;">SMPS2 Al-Muhajirin</span>
                                <p style="font-size:12px;color:#94a3b8;margin-top:4px;">Lembaga Pendidikan Terpadu Purwakarta</p>
                            </div>
                        </div>
                        <p style="max-width:400px;margin-top:12px;font-size:14px;">Mewujudkan generasi Muslim yang kokoh akidah, berakhlak mulia, cerdas berprestasi, dan siap berkompetisi global dalam naungan pendidikan bermutu.</p>
                        <div style="display:flex;align-items:center;gap:8px;margin-top:12px;">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-yellow);">language</span>
                            <a href="https://smp2almuhajirin.sch.id" target="_blank" rel="noopener" style="font-size:14px;font-weight:700;color:var(--lp-primary-light);">smp2almuhajirin.sch.id</a>
                        </div>
                    </div>
                    <div>
                        <h3>Alamat Kampus Resmi</h3>
                        <div style="display:flex;align-items:flex-start;gap:10px;font-size:14px;color:#94a3b8;margin-top:4px;">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-primary-light);flex-shrink:0;margin-top:2px;">location_on</span>
                            <p>Jl. Ipik Gandamanah No. 33 Ciseureuh, Kec. Purwakarta, Kab. Purwakarta, Jawa Barat 41118</p>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:10px;font-size:12px;color:#64748b;margin-top:8px;">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-primary-light);flex-shrink:0;margin-top:2px;">schedule</span>
                            <p style="font-size:12px;">Senin – Jumat: 07.30 – 15.30 WIB<br>Sabtu: 08.00 – 12.00 WIB</p>
                        </div>
                    </div>
                    <div>
                        <h3>Layanan Informasi &amp; WA</h3>
                        <div style="display:flex;align-items:center;gap:8px;font-size:14px;margin-top:4px;">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-green);">chat</span>
                            <a href="https://wa.me/6287821055283" target="_blank" rel="noopener" style="font-weight:600;">0878-2105-5283</a>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;font-size:14px;margin-top:8px;">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-green);">chat</span>
                            <a href="https://wa.me/6287822282012" target="_blank" rel="noopener" style="font-weight:600;">0878-2228-2012</a>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:#64748b;margin-top:8px;">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-primary-light);">mail</span>
                            <span>info@smp2almuhajirin.sch.id</span>
                        </div>
                    </div>
                </div>
                <div class="lp-footer-bottom">
                    <p>Copyright &copy; {{ date('Y') }} SMPS2 Al-Muhajirin Purwakarta. Seluruh hak cipta dilindungi.</p>
                    <div class="lp-footer-badges">
                        <span style="color:var(--lp-yellow);font-weight:700;">Akreditasi A (Unggul)</span>
                        <span class="lp-footer-dot"></span>
                        <span style="color:var(--lp-primary-light);font-weight:700;">100% Berkas Digital</span>
                        <span class="lp-footer-dot"></span>
                        <span style="color:#fff;font-weight:600;">PPDB TP 2027–2028</span>
                    </div>
                </div>
            </div>
        </footer>
    @endif

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Native Scroll Entry Animation Observer -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08 });

                document.querySelectorAll('.fade-in-entry').forEach((el) => {
                    observer.observe(el);
                });
            } else {
                document.querySelectorAll('.fade-in-entry').forEach((el) => {
                    el.classList.add('is-visible');
                });
            }
        });

        // Navbar ScrollSpy for Landing Page Links
        document.addEventListener('DOMContentLoaded', () => {
            const sections = ['beranda', 'informasi', 'persyaratan', 'program', 'biaya', 'alur'];
            const sectionEls = sections.map(id => document.getElementById(id)).filter(Boolean);

            if (sectionEls.length > 0) {
                const desktopLinks = document.querySelectorAll('.lp-nav-link');
                const mobileLinks = document.querySelectorAll('.lp-mobile-link');

                function setActiveLink(currentId) {
                    desktopLinks.forEach(link => {
                        if (link.dataset.section === currentId) {
                            link.classList.add('active');
                        } else {
                            link.classList.remove('active');
                        }
                    });
                    mobileLinks.forEach(link => {
                        if (link.dataset.section === currentId) {
                            link.classList.add('active');
                        } else {
                            link.classList.remove('active');
                        }
                    });
                }

                if ('IntersectionObserver' in window) {
                    const navObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                setActiveLink(entry.target.id);
                            }
                        });
                    }, { rootMargin: '-20% 0px -60% 0px', threshold: 0 });

                    sectionEls.forEach(el => navObserver.observe(el));
                }

                // Immediate active feedback when clicked
                document.querySelectorAll('.lp-nav-link, .lp-mobile-link').forEach(link => {
                    link.addEventListener('click', function() {
                        const targetSection = this.dataset.section;
                        if (targetSection && document.getElementById(targetSection)) {
                            setActiveLink(targetSection);
                        }
                    });
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
