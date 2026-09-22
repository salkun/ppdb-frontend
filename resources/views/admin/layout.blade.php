<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Admin Panel PPDB') — SMP Al-Muhajirin</title>

    <!-- Official Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('logo/favicon-64x64.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/favicon-128x128.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Material Symbols Outlined -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Phosphor Icons (For Dossier & Edit icons) -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Central PPDB Theme -->
    <link rel="stylesheet" href="{{ asset('css/ppdb-theme.css') }}">

    @stack('styles')
</head>
<body style="background-color: #f8fafc; font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
    <div class="admin-shell">
        <!-- ============================================== -->
        <!-- 1. DESKTOP PERMANENT SIDEBAR                   -->
        <!-- ============================================== -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div>
                <!-- Brand Header -->
                <a href="{{ route('admin.ppdb.index') }}" class="admin-brand">
                    <div class="admin-brand-icon" style="background: transparent; box-shadow: none;">
                        <img src="{{ asset('logo/logo.png') }}" alt="Logo SMP Al-Muhajirin" style="width: 36px; height: 36px; object-fit: contain;">
                    </div>
                    <div>
                        <div class="admin-brand-title">PPDB Admin</div>
                        <div class="admin-brand-sub">SMP AL-MUHAJIRIN</div>
                    </div>
                </a>

                <!-- Operator Profile Card -->
                <div class="p-2 mb-3 rounded-3 border bg-light d-flex align-items-center gap-2">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold small flex-shrink-0" style="width: 34px; height: 34px; background: linear-gradient(135deg, #005ab4, #003e7e); font-size: 13px;">
                        {{ strtoupper(substr(session('admin_user.username', 'A'), 0, 1)) }}
                    </div>
                    <div class="overflow-hidden flex-grow-1">
                        <div class="fw-semibold text-dark text-truncate" style="font-size: 13px;">
                            {{ session('admin_user.username', 'Administrator') }}
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="d-inline-block rounded-circle bg-success" style="width: 6px; height: 6px;"></span>
                            <span class="text-muted text-uppercase fw-semibold" style="font-size: 10px;">OPERATOR AKTIF</span>
                        </div>
                    </div>
                </div>

                <!-- Navigation Group: Menu Utama -->
                <div class="admin-nav-group-label">Menu Utama</div>
                <ul class="admin-nav-list mb-3">
                    <li>
                        <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.index') || request()->routeIs('admin.ppdb.dashboard') ? 'active' : '' }}" href="{{ route('admin.ppdb.index') }}">
                            <span class="material-symbols-outlined">dashboard</span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.students') ? 'active' : '' }}" href="{{ route('admin.ppdb.students') }}">
                            <span class="material-symbols-outlined">groups</span>
                            <span>Data Siswa</span>
                        </a>
                    </li>
                    <li>
                        <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.users') ? 'active' : '' }}" href="{{ route('admin.ppdb.users') }}">
                            <span class="material-symbols-outlined">manage_accounts</span>
                            <span>Data User</span>
                        </a>
                    </li>
                    <li>
                        <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.documents') ? 'active' : '' }}" href="{{ route('admin.ppdb.documents') }}">
                            <span class="material-symbols-outlined">folder_open</span>
                            <span>Data Berkas</span>
                        </a>
                    </li>
                    <li>
                        <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.payments') ? 'active' : '' }}" href="{{ route('admin.ppdb.payments') }}">
                            <span class="material-symbols-outlined">payments</span>
                            <span>Verifikasi Bayar</span>
                        </a>
                    </li>
                </ul>

                <!-- Navigation Group: Laporan & Alat -->
                <div class="admin-nav-group-label">Laporan & Pintasan</div>
                <ul class="admin-nav-list">
                    <li>
                        <a class="admin-nav-link" href="{{ route('admin.ppdb.export', ['format' => 'xlsx']) }}">
                            <span class="material-symbols-outlined">download</span>
                            <span>Export Excel (.xlsx)</span>
                        </a>
                    </li>
                    <li>
                        <a class="admin-nav-link" href="{{ route('home') }}" target="_blank">
                            <span class="material-symbols-outlined">open_in_new</span>
                            <span>Lihat Web Publik</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Sidebar Bottom Logout -->
            <div class="pt-3 border-top mt-auto">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-nav-link w-100 border-0 bg-transparent text-danger p-2" style="cursor: pointer;">
                        <span class="material-symbols-outlined text-danger">logout</span>
                        <span class="fw-semibold">Keluar Admin</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ============================================== -->
        <!-- 2. MOBILE OFFCANVAS DRAWER                     -->
        <!-- ============================================== -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="adminMobileDrawer" aria-labelledby="adminMobileDrawerLabel" style="width: 280px;">
            <div class="offcanvas-header border-bottom p-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="admin-brand-icon" style="width: 36px; height: 36px; background: transparent; box-shadow: none;">
                        <img src="{{ asset('logo/logo.png') }}" alt="Logo SMP Al-Muhajirin" style="width: 34px; height: 34px; object-fit: contain;">
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 15px;">PPDB Admin</div>
                        <div class="text-primary fw-semibold" style="font-size: 11px;">SMP AL-MUHAJIRIN</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
                <div>
                    <!-- Mobile Operator Card -->
                    <div class="p-2 mb-3 rounded-3 border bg-light d-flex align-items-center gap-2">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold small flex-shrink-0" style="width: 32px; height: 32px; background: #005ab4; font-size: 12px;">
                            {{ strtoupper(substr(session('admin_user.username', 'A'), 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-semibold text-dark text-truncate" style="font-size: 13px;">{{ session('admin_user.username', 'Administrator') }}</div>
                            <span class="text-success small fw-semibold" style="font-size: 10px;">● ONLINE</span>
                        </div>
                    </div>

                    <!-- Navigation Items -->
                    <div class="admin-nav-group-label">Menu Utama</div>
                    <ul class="admin-nav-list mb-3">
                        <li>
                            <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.index') || request()->routeIs('admin.ppdb.dashboard') ? 'active' : '' }}" href="{{ route('admin.ppdb.index') }}">
                                <span class="material-symbols-outlined">dashboard</span>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.students') ? 'active' : '' }}" href="{{ route('admin.ppdb.students') }}">
                                <span class="material-symbols-outlined">groups</span>
                                <span>Data Siswa</span>
                            </a>
                        </li>
                        <li>
                            <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.users') ? 'active' : '' }}" href="{{ route('admin.ppdb.users') }}">
                                <span class="material-symbols-outlined">manage_accounts</span>
                                <span>Data User</span>
                            </a>
                        </li>
                        <li>
                            <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.documents') ? 'active' : '' }}" href="{{ route('admin.ppdb.documents') }}">
                                <span class="material-symbols-outlined">folder_open</span>
                                <span>Data Berkas</span>
                            </a>
                        </li>
                        <li>
                            <a class="admin-nav-link {{ request()->routeIs('admin.ppdb.payments') ? 'active' : '' }}" href="{{ route('admin.ppdb.payments') }}">
                                <span class="material-symbols-outlined">payments</span>
                                <span>Verifikasi Bayar</span>
                            </a>
                        </li>
                    </ul>

                    <div class="admin-nav-group-label">Laporan & Web</div>
                    <ul class="admin-nav-list">
                        <li>
                            <a class="admin-nav-link" href="{{ route('admin.ppdb.export', ['format' => 'xlsx']) }}">
                                <span class="material-symbols-outlined">download</span>
                                <span>Export Excel</span>
                            </a>
                        </li>
                        <li>
                            <a class="admin-nav-link" href="{{ route('home') }}" target="_blank">
                                <span class="material-symbols-outlined">open_in_new</span>
                                <span>Web Publik</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="pt-3 border-top">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="admin-nav-link w-100 border-0 bg-transparent text-danger p-2">
                            <span class="material-symbols-outlined text-danger">logout</span>
                            <span class="fw-semibold">Keluar Admin</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- 3. MAIN WORKSPACE VIEWPORT                     -->
        <!-- ============================================== -->
        <div class="admin-viewport">
            <!-- Header Topbar -->
            <header class="admin-topbar">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm d-lg-none d-flex align-items-center p-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileDrawer" aria-label="Toggle navigation">
                        <span class="material-symbols-outlined" style="font-size:22px;">menu</span>
                    </button>
                    <div>
                        <h5 class="fw-bold text-dark mb-0 fs-6">@yield('header_title', 'PPDB Admin Panel')</h5>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- One-Click Bulk Sync to Master API -->
                    <form action="{{ route('admin.ppdb.sync') }}" method="POST" class="d-inline mb-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 py-1 px-3 fw-semibold shadow-sm" style="font-size: 12px;" onclick="return confirm('Kirim seluruh data pendaftar & verifikasi lokal ke Master Data API?')">
                            <span class="material-symbols-outlined" style="font-size:16px;">sync</span>
                            <span>Sinkron ke Master API</span>
                        </button>
                    </form>

                    <span class="badge bg-white text-secondary border d-none d-md-inline-flex align-items-center gap-1 py-2 px-3 fw-medium" style="font-size: 12px;">
                        <span class="material-symbols-outlined" style="font-size:16px;">calendar_today</span>
                        {{ date('d M Y') }}
                    </span>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-primary d-none d-sm-inline-flex align-items-center gap-1 py-1 px-3" style="font-size: 12.5px;" target="_blank">
                        <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span>
                        <span>Web Publik</span>
                    </a>
                </div>
            </header>

            <!-- Alerts / Notifications -->
            <div class="container-fluid px-3 px-lg-4 pt-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 rounded-3 shadow-sm border-0" role="alert" style="background-color: #dcfce7; color: #15803d;">
                        <span class="material-symbols-outlined flex-shrink-0" style="font-size:22px;">check_circle</span>
                        <div class="flex-grow-1 fw-medium" style="font-size:14px;">{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 rounded-3 shadow-sm border-0" role="alert" style="background-color: #fee2e2; color: #b91c1c;">
                        <span class="material-symbols-outlined flex-shrink-0" style="font-size:22px;">error</span>
                        <div class="flex-grow-1 fw-medium" style="font-size:14px;">{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-3 rounded-3 shadow-sm border-0" role="alert" style="background-color: #fef9c3; color: #a16207;">
                        <span class="material-symbols-outlined flex-shrink-0" style="font-size:22px;">warning</span>
                        <div class="flex-grow-1 fw-medium" style="font-size:14px;">{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif
            </div>

            <!-- Page Body Content -->
            <main class="container-fluid px-3 px-lg-4 py-3 flex-grow-1">
                @yield('content')
            </main>

            <!-- Workspace Footer -->
            <footer class="py-3 px-3 px-lg-4 border-top bg-white text-muted small mt-auto" style="font-size: 12px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span>&copy; {{ date('Y') }} PPDB Online SMP Al-Muhajirin Purwakarta. All rights reserved.</span>
                    <span class="fw-semibold text-primary">SINKRONISASI AKTIF</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
