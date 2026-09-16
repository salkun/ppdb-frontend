<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Admin Panel PPDB') — Portal Sekolah</title>

    <!-- Google Fonts: Geist Sans, Newsreader Serif & Geist Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500;600&family=Geist:wght@300;400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400..600;1,6..72,400..600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Phosphor Icons (Lightweight CSS Font) -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">

    <!-- Central PPDB Theme -->
    <link rel="stylesheet" href="{{ asset('css/ppdb-theme.css') }}">

    @stack('styles')
</head>
<body>
    <div class="workspace-shell">
        <!-- ============================================== -->
        <!-- DESKTOP FIXED SIDEBAR -->
        <!-- ============================================== -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div>
                <!-- Brand Header -->
                <a href="{{ route('admin.ppdb.index') }}" class="sidebar-brand">
                    <div class="sidebar-brand-mark">
                        <i class="ph-bold ph-shield-check"></i>
                    </div>
                    <div>
                        <span class="font-mono-meta small fw-bold text-dark d-block" style="letter-spacing: 0.04em;">PPDB ADMIN</span>
                        <span class="text-secondary" style="font-size: 0.7rem;">PANEL OPERATOR</span>
                    </div>
                </a>

                <!-- Operator Profile Card -->
                <div class="sidebar-operator-card">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold small text-white" style="width: 28px; height: 28px; background-color: var(--text-main); font-size: 0.75rem;">
                            {{ strtoupper(substr(session('admin_user.username', 'A'), 0, 1)) }}
                        </div>
                        <div class="overflow-hidden flex-grow-1">
                            <span class="text-dark small fw-semibold d-block text-truncate">
                                {{ session('admin_user.username', 'Administrator') }}
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="d-inline-block rounded-circle" style="width: 6px; height: 6px; background-color: #346538;"></span>
                                <span class="font-mono-meta text-secondary" style="font-size: 0.68rem;">ONLINE</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Sections -->
                <div class="mt-3">
                    <span class="font-mono-meta text-secondary px-2 mb-1 d-block text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.06em;">MENU OPERATOR</span>
                    <ul class="nav-sidebar">
                        <li>
                            <a class="nav-sidebar-link {{ request()->routeIs('admin.ppdb.*') ? 'active' : '' }}" href="{{ route('admin.ppdb.index') }}">
                                <i class="ph-bold ph-users-three"></i>
                                <span>Data Pendaftar PPDB</span>
                            </a>
                        </li>
                        <li>
                            <a class="nav-sidebar-link" href="{{ route('home') }}" target="_blank">
                                <i class="ph-bold ph-arrow-square-out"></i>
                                <span>Portal Siswa Publik</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Sidebar Bottom Logout -->
            <div class="pt-3 border-top" style="border-color: var(--border-light) !important;">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-sidebar-link w-100 border-0 bg-transparent text-danger justify-content-start" style="cursor: pointer;">
                        <i class="ph-bold ph-sign-out text-danger"></i>
                        <span>Keluar Admin</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ============================================== -->
        <!-- MOBILE OFFCANVAS DRAWER -->
        <!-- ============================================== -->
        <div class="offcanvas offcanvas-start admin-drawer" tabindex="-1" id="adminMobileDrawer">
            <div class="offcanvas-header pb-2 border-bottom" style="border-color: var(--border-light) !important;">
                <div class="d-flex align-items-center gap-2">
                    <div class="sidebar-brand-mark">
                        <i class="ph-bold ph-shield-check"></i>
                    </div>
                    <span class="font-mono-meta fw-bold text-dark">PPDB ADMIN</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column justify-content-between px-0 py-3">
                <div>
                    <div class="sidebar-operator-card mx-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold small text-white" style="width: 28px; height: 28px; background-color: var(--text-main);">
                                {{ strtoupper(substr(session('admin_user.username', 'A'), 0, 1)) }}
                            </div>
                            <span class="text-dark small fw-semibold text-truncate">{{ session('admin_user.username', 'Administrator') }}</span>
                        </div>
                    </div>
                    <ul class="nav-sidebar px-2">
                        <li>
                            <a class="nav-sidebar-link {{ request()->routeIs('admin.ppdb.*') ? 'active' : '' }}" href="{{ route('admin.ppdb.index') }}">
                                <i class="ph-bold ph-users-three"></i>
                                <span>Data Pendaftar PPDB</span>
                            </a>
                        </li>
                        <li>
                            <a class="nav-sidebar-link" href="{{ route('home') }}" target="_blank">
                                <i class="ph-bold ph-arrow-square-out"></i>
                                <span>Portal Siswa Publik</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="px-2 pt-3 border-top" style="border-color: var(--border-light) !important;">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-sidebar-link w-100 border-0 bg-transparent text-danger justify-content-start">
                            <i class="ph-bold ph-sign-out text-danger"></i>
                            <span>Keluar Admin</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MAIN WORKSPACE CONTENT -->
        <!-- ============================================== -->
        <div class="workspace-content">
            <!-- Header Topbar -->
            <header class="workspace-topbar">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-minimal-secondary d-lg-none py-1 px-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileDrawer">
                        <i class="ph-bold ph-list fs-5"></i>
                    </button>
                    <h5 class="font-serif-heading text-dark mb-0 fs-5">@yield('header_title', 'Manajemen Pendaftaran Siswa Baru')</h5>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="font-mono-meta small text-secondary p-1 px-2 bg-white rounded-1 border d-none d-sm-inline-flex align-items-center gap-1" style="border-color: var(--border-light) !important; font-size: 0.75rem;">
                        <i class="ph-bold ph-calendar-blank"></i> {{ date('d M Y') }}
                    </span>
                    <a href="{{ route('home') }}" class="btn-minimal-secondary py-1 px-2" style="font-size: 0.78rem; min-height: 32px;" target="_blank">
                        <i class="ph-bold ph-arrow-square-out"></i> Web Publik
                    </a>
                </div>
            </header>

            <!-- Alerts / Notifications -->
            <div class="container-fluid px-3 px-lg-4 mt-3">
                @if(session('success'))
                    <div class="alert-document alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                        <i class="ph-bold ph-check-circle fs-5 me-2 flex-shrink-0 text-success"></i>
                        <div class="flex-grow-1">{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-document alert-danger alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                        <i class="ph-bold ph-warning-circle fs-5 me-2 flex-shrink-0 text-danger"></i>
                        <div class="flex-grow-1">{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert-document alert-warning alert-dismissible fade show d-flex align-items-center mb-3" role="alert">
                        <i class="ph-bold ph-info fs-5 me-2 flex-shrink-0" style="color: #956400;"></i>
                        <div class="flex-grow-1">{{ session('warning') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="container-fluid px-3 px-lg-4 py-2 flex-grow-1">
                @yield('content')
            </main>

            <!-- Workspace Footer -->
            <footer class="py-3 px-3 px-lg-4 border-top text-secondary small" style="border-color: var(--border-light) !important; font-size: 0.78rem;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span>&copy; {{ date('Y') }} Portal PPDB Online. Panel Administrasi Pendaftaran Siswa Baru.</span>
                    <span class="font-mono-meta" style="font-size: 0.72rem;">SINKRONISASI: AKTIF</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
