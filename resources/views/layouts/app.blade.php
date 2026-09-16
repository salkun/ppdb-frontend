<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Portal PMB Online') — Sistem Penerimaan Murid Baru</title>

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
    <link rel="stylesheet" href="{{ asset('css/ppdb-theme.css') }}">

    @stack('styles')
</head>
<body>

    @if(session()->has('api_token'))
        <!-- ======================================================== -->
        <!-- AUTHENTICATED STUDENT PORTAL (SIDEBAR WORKSPACE LAYOUT)  -->
        <!-- ======================================================== -->
        <div class="workspace-shell">
            <!-- 1. Permanent Desktop Sidebar -->
            <aside class="app-sidebar">
                <div>
                    <!-- Brand Identifier -->
                    <a class="sidebar-brand" href="{{ route('dashboard') }}">
                        <div class="sidebar-brand-mark">
                            <i class="ph-bold ph-graduation-cap"></i>
                        </div>
                        <div>
                            <span class="d-block fw-bold text-dark lh-1" style="font-size: 0.95rem; letter-spacing: -0.01em;">PPDB Online</span>
                            <span class="font-mono-meta text-secondary" style="font-size: 0.65rem; letter-spacing: 0.04em;">PORTAL SISWA</span>
                        </div>
                    </a>

                    <!-- Student Mini Profile Card -->
                    <div class="sidebar-profile-card">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="font-mono-meta small text-secondary" style="font-size: 0.68rem;">CALON SISWA</span>
                            <span class="badge-pastel badge-pastel-green" style="font-size: 0.62rem; padding: 0.15rem 0.45rem;">AKTIF</span>
                        </div>
                        <strong class="text-dark d-block text-truncate small fw-semibold mb-1">{{ session('full_name', 'Calon Siswa') }}</strong>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-secondary small" style="font-size: 0.72rem;">NIK:</span>
                            <kbd class="kbd-key" style="font-size: 0.7rem;">{{ session('nik', '-') }}</kbd>
                        </div>
                    </div>

                    <!-- Vertical Navigation Menu -->
                    <div class="mb-2">
                        <span class="font-mono-meta text-secondary px-2 mb-1 d-block text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.06em;">MENU UTAMA</span>
                        <ul class="nav-sidebar">
                            <li>
                                <a class="nav-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="ph-bold ph-squares-four"></i>
                                    <span>Dashboard & Status</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-sidebar-link {{ request()->routeIs('ppdb.form') ? 'active' : '' }}" href="{{ route('ppdb.form') }}">
                                    <i class="ph-bold ph-file-text"></i>
                                    <span>Formulir Pendaftaran</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-sidebar-link" href="{{ route('home') }}">
                                    <i class="ph-bold ph-house"></i>
                                    <span>Informasi & Alur</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Sidebar Footer: Help & Logout -->
                <div class="sidebar-footer">
                    <a href="https://wa.me/" target="_blank" class="nav-sidebar-link mb-1 text-decoration-none" style="font-size: 0.8rem;">
                        <i class="ph-bold ph-chat-circle-dots"></i>
                        <span>Bantuan Panitia PPDB</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="nav-sidebar-link w-100 border-0 bg-transparent text-danger">
                            <i class="ph-bold ph-sign-out text-danger"></i>
                            <span>Keluar Sesi</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- 2. Mobile Offcanvas Drawer -->
            <div class="offcanvas offcanvas-start offcanvas-sidebar p-3" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
                <div class="offcanvas-header pb-3 border-bottom px-1" style="border-color: var(--border-light) !important;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="sidebar-brand-mark">
                            <i class="ph-bold ph-graduation-cap"></i>
                        </div>
                        <div>
                            <span class="d-block fw-bold text-dark lh-1" style="font-size: 0.95rem;">PPDB Online</span>
                            <span class="font-mono-meta text-secondary" style="font-size: 0.65rem;">PORTAL SISWA</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
                </div>

                <div class="offcanvas-body d-flex flex-column justify-content-between px-1 py-3">
                    <div>
                        <!-- Profile Card Mobile -->
                        <div class="sidebar-profile-card">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="font-mono-meta small text-secondary" style="font-size: 0.68rem;">CALON SISWA</span>
                                <span class="badge-pastel badge-pastel-green" style="font-size: 0.62rem; padding: 0.15rem 0.45rem;">AKTIF</span>
                            </div>
                            <strong class="text-dark d-block text-truncate small fw-semibold mb-1">{{ session('full_name', 'Calon Siswa') }}</strong>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-secondary small" style="font-size: 0.72rem;">NIK:</span>
                                <kbd class="kbd-key" style="font-size: 0.7rem;">{{ session('nik', '-') }}</kbd>
                            </div>
                        </div>

                        <!-- Menu Items Mobile -->
                        <ul class="nav-sidebar">
                            <li>
                                <a class="nav-sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="ph-bold ph-squares-four"></i>
                                    <span>Dashboard & Status</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-sidebar-link {{ request()->routeIs('ppdb.form') ? 'active' : '' }}" href="{{ route('ppdb.form') }}">
                                    <i class="ph-bold ph-file-text"></i>
                                    <span>Formulir Pendaftaran</span>
                                </a>
                            </li>
                            <li>
                                <a class="nav-sidebar-link" href="{{ route('home') }}">
                                    <i class="ph-bold ph-house"></i>
                                    <span>Informasi & Alur</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Footer Mobile Drawer -->
                    <div class="sidebar-footer">
                        <a href="https://wa.me/" target="_blank" class="nav-sidebar-link mb-1 text-decoration-none">
                            <i class="ph-bold ph-chat-circle-dots"></i>
                            <span>Bantuan Panitia PPDB</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="nav-sidebar-link w-100 border-0 bg-transparent text-danger">
                                <i class="ph-bold ph-sign-out text-danger"></i>
                                <span>Keluar Sesi</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 3. Main Viewport & Workspace Canvas -->
            <div class="app-main-viewport">
                <!-- Topbar Context Bar -->
                <header class="app-topbar">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Mobile Hamburger Button (< 992px) -->
                        <button class="btn btn-minimal-secondary d-lg-none py-1 px-2 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                            <i class="ph-bold ph-list fs-5 text-dark"></i>
                        </button>
                        <div class="d-flex align-items-center gap-2">
                            <span class="font-mono-meta small text-secondary d-none d-sm-inline">PORTAL /</span>
                            <span class="fw-semibold text-dark small">@yield('title', 'Portal Siswa')</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <span class="badge-pastel badge-pastel-neutral d-none d-sm-inline-flex">
                            <i class="ph-bold ph-check"></i> SINKRON
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-mono-meta small fw-bold" style="width: 30px; height: 30px; background-color: var(--text-main); font-size: 0.75rem;">
                                {{ strtoupper(substr(session('full_name', 'S'), 0, 1)) }}
                            </div>
                            <span class="small fw-semibold text-dark d-none d-md-inline text-truncate" style="max-width: 140px;">
                                {{ session('full_name', 'Siswa') }}
                            </span>
                        </div>
                    </div>
                </header>

                <!-- Flash Notification Container -->
                <div class="container-fluid px-3 px-md-4 pt-3">
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

                    @if(session('warning'))
                        <div class="alert-document alert-warning alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                            <i class="ph-bold ph-warning fs-5 me-2 flex-shrink-0"></i>
                            <div class="flex-grow-1">{{ session('warning') }}</div>
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

                <!-- Main Content Yield -->
                <main class="flex-grow-1 px-3 px-md-4 py-2">
                    @yield('content')
                </main>
            </div>
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
                    <div class="lp-brand-icon">
                        <span class="material-symbols-outlined" style="font-size:26px;">school</span>
                    </div>
                    <div>
                        <span class="lp-brand-text">SMPS2 Al-Muhajirin</span>
                        <div class="lp-brand-sub">
                            <span class="lp-brand-dot"></span>
                            <span class="lp-brand-badge">PMB TP {{ date('Y') }}–{{ date('Y') + 1 }}</span>
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
                        <div class="lp-brand-icon" style="width:36px;height:36px;">
                            <span class="material-symbols-outlined" style="font-size:20px;">school</span>
                        </div>
                        <div>
                            <span style="font-weight:700;font-size:15px;color:var(--lp-on-surface);display:block;">SMPS2 Al-Muhajirin</span>
                            <span style="font-size:11px;font-weight:700;color:var(--lp-primary);">PMB TP {{ date('Y') }}–{{ date('Y') + 1 }}</span>
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
                            <div class="lp-footer-brand-icon">
                                <span class="material-symbols-outlined" style="font-size:24px;">school</span>
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
                        <span style="color:#fff;font-weight:600;">PPDB TP {{ date('Y') }}–{{ date('Y') + 1 }}</span>
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
