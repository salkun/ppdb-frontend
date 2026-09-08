<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Portal PPDB Online') - Sistem Penerimaan Siswa Baru</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-navy: #0f172a;
            --primary-blue: #1e3a8a;
            --accent-teal: #0d9488;
            --accent-teal-hover: #0f766e;
            --accent-gold: #f59e0b;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styling */
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-navy) 0%, #1e293b 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 0.85rem 0;
        }

        .navbar-brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent-teal) 0%, #14b8a6 100%);
            color: #ffffff;
            font-size: 1.25rem;
            margin-right: 0.75rem;
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.3);
        }

        .nav-link-custom {
            color: #cbd5e1 !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-link-custom:hover, .nav-link-custom.active {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
        }

        /* Card Modern Styling */
        .card-custom {
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-custom-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
            font-weight: 700;
        }

        /* Button Styling */
        .btn-teal {
            background: linear-gradient(135deg, var(--accent-teal) 0%, #0f766e 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.6rem 1.35rem;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);
            transition: all 0.2s ease;
        }

        .btn-teal:hover {
            background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13, 148, 136, 0.35);
        }

        .btn-navy {
            background: var(--primary-navy);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.6rem 1.35rem;
            transition: all 0.2s ease;
        }

        .btn-navy:hover {
            background: #1e293b;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Footer */
        .footer-custom {
            background: var(--primary-navy);
            color: #94a3b8;
            margin-top: auto;
            border-top: 1px solid #1e293b;
            padding: 2.5rem 0 1.5rem 0;
        }

        /* Badge Enhancements */
        .badge-status {
            padding: 0.45em 0.85em;
            font-weight: 600;
            border-radius: 30px;
            letter-spacing: 0.3px;
        }
    </style>

    @stack('styles')
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <div class="navbar-brand-badge">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                    <span class="fw-bold fs-5 text-white tracking-wide d-block leading-none">PPDB ONLINE</span>
                    <small class="text-white-50" style="font-size: 0.72rem; letter-spacing: 1px;">PORTAL PENDAFTARAN RESMI</small>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house-door me-1"></i> Beranda
                        </a>
                    </li>

                    @if(session()->has('api_token'))
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom {{ request()->routeIs('ppdb.form') ? 'active' : '' }}" href="{{ route('ppdb.form') }}">
                                <i class="bi bi-file-earmark-person me-1"></i> Formulir Dapodik
                            </a>
                        </li>
                        <li class="nav-item dropdown ms-lg-2">
                            <a class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-6 me-2 text-warning"></i>
                                <span class="fw-semibold text-truncate" style="max-width: 140px;">{{ session('full_name', 'Calon Siswa') }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 rounded-3">
                                <li>
                                    <div class="px-3 py-2 border-bottom">
                                        <small class="text-muted d-block">Masuk sebagai NIK:</small>
                                        <strong class="text-dark">{{ session('nik', '-') }}</strong>
                                    </div>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('dashboard') }}">
                                        <i class="bi bi-grid me-2 text-primary"></i> Status Pendaftaran
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-teal btn-sm px-3 ms-lg-2" href="{{ route('register') }}">
                                <i class="bi bi-pencil-square me-1"></i> Daftar Sekarang
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Global Flash Messages -->
    <div class="container mt-4">
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

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill fs-5 me-2 text-info"></i>
                <div class="flex-grow-1">{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-x-octagon-fill fs-5 me-2"></i>
                    <strong>Terdapat beberapa kesalahan pengisian formulir:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Main Content Yield -->
    <main class="flex-grow-1 mb-5">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container">
            <div class="row gy-4 align-items-center justify-content-between">
                <div class="col-md-6 text-center text-md-start">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2">
                        <i class="bi bi-mortarboard-fill fs-4 text-warning me-2"></i>
                        <span class="fw-bold text-white fs-5">Sistem PPDB Terintegrasi</span>
                    </div>
                    <p class="small text-muted mb-0">
                        Aplikasi antarmuka resmi Penerimaan Peserta Didik Baru terhubung langsung dengan backend SIAKAD & Manajemen Akademik Sekolah.
                    </p>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <div class="small text-muted mb-1">Butuh bantuan teknis / informasi pendaftaran?</div>
                    <a href="https://wa.me/" target="_blank" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1">
                        <i class="bi bi-whatsapp text-success me-1"></i> Hubungi Helpdesk PPDB
                    </a>
                </div>
            </div>
            <hr class="border-secondary my-4 opacity-25">
            <div class="text-center small text-muted">
                &copy; {{ date('Y') }} Portal PPDB Online. Seluruh hak cipta dilindungi undang-undang.
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
