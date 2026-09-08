<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Admin Panel PPDB') - SIAKAD Sekolah</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #0d9488;
            --main-bg: #f8fafc;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--main-bg);
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .admin-sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .admin-sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #1e293b;
            display: flex;
            align-items: center;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }

        .admin-nav-link:hover {
            color: #ffffff;
            background-color: var(--sidebar-hover);
        }

        .admin-nav-link.active {
            color: #ffffff;
            background-color: rgba(13, 148, 136, 0.15);
            border-left-color: var(--sidebar-active);
            font-weight: 600;
        }

        .admin-nav-link i {
            font-size: 1.15rem;
            margin-right: 0.75rem;
            width: 22px;
        }

        /* Main Content Wrapper */
        .admin-content-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .admin-topbar {
            height: 68px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .card-custom {
            border: 1px solid var(--border-color);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 15px -3px rgba(15, 23, 42, 0.04);
        }

        .btn-teal {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-teal:hover {
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
            color: #ffffff;
        }

        @media (max-width: 991.98px) {
            .admin-sidebar {
                margin-left: -260px;
            }
            .admin-sidebar.show {
                margin-left: 0;
            }
            .admin-content-wrapper {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar Admin -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-brand">
            <div class="rounded-3 bg-teal p-2 me-2 text-white bg-info bg-opacity-25" style="width: 38px; height: 38px; display:flex; align-items:center; justify-content:center;">
                <i class="bi bi-shield-lock-fill text-info fs-5"></i>
            </div>
            <div>
                <span class="text-white fw-bold d-block" style="letter-spacing: 0.5px;">ADMIN PPDB</span>
                <small class="text-muted" style="font-size: 0.72rem;">SIAKAD GATEWAY</small>
            </div>
        </div>

        <div class="py-3">
            <small class="text-uppercase text-secondary fw-bold px-4 mb-2 d-block" style="font-size: 0.7rem; letter-spacing: 1px;">Menu Utama</small>
            <a href="{{ route('admin.ppdb.index') }}" class="admin-nav-link {{ request()->routeIs('admin.ppdb.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Data Pendaftar PPDB
            </a>
            <a href="{{ route('home') }}" target="_blank" class="admin-nav-link">
                <i class="bi bi-box-arrow-up-right"></i> Lihat Portal Publik
            </a>
        </div>

        <div class="mt-auto p-4 border-top border-secondary border-opacity-25" style="position: absolute; bottom: 0; width: 100%;">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-person-circle fs-3 text-warning me-2"></i>
                <div class="overflow-hidden">
                    <span class="text-white small fw-bold d-block text-truncate">{{ session('admin_user.username', 'Administrator') }}</span>
                    <span class="badge bg-success bg-opacity-25 text-success small py-0">Online</span>
                </div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill">
                    <i class="bi bi-box-arrow-right me-1"></i> Keluar Admin
                </button>
            </form>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="admin-content-wrapper">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-2" type="button" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h5 class="fw-bold mb-0 text-dark">@yield('header_title', 'Manajemen Pendaftaran Siswa Baru')</h5>
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                    <i class="bi bi-calendar3 me-1"></i> {{ date('d M Y') }}
                </span>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" target="_blank">
                    <i class="bi bi-globe me-1"></i> Web PPDB
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="container-fluid px-4 mt-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                    <div class="flex-grow-1">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                    <div class="flex-grow-1">{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-circle-fill fs-5 me-2 text-warning"></i>
                    <div class="flex-grow-1">{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <!-- Main Content Area -->
        <main class="container-fluid px-4 py-3 flex-grow-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 text-muted small text-center text-md-start">
            &copy; {{ date('Y') }} Sistem Informasi Akademik & PPDB Admin. Terhubung ke FastAPI Backend.
        </footer>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
