@extends('layouts.app')

@section('title', 'Penerimaan Peserta Didik Baru (PPDB) Online')

@section('content')
<!-- Hero Section -->
<section class="py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #ffffff;">
    <div class="container py-4">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7 text-center text-lg-start">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm">
                    <i class="bi bi-stars me-1"></i> PENERIMAAN PESERTA DIDIK BARU TAHUN AJARAN 2026/2027
                </span>
                <h1 class="display-4 fw-extrabold mb-3 lh-sm text-white">
                    Mewujudkan Masa Depan Cemerlang Dimulai dari Sini
                </h1>
                <p class="lead text-white-50 mb-4 pe-lg-4">
                    Selamat datang di Portal Resmi PPDB. Daftarkan diri Anda secara online dengan mudah, transparan, dan terintegrasi langsung dengan basis data pokok pendidikan (Dapodik).
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    @if(session()->has('api_token'))
                        <a href="{{ route('dashboard') }}" class="btn btn-teal btn-lg px-4 py-3">
                            <i class="bi bi-speedometer2 me-2"></i> Buka Dashboard Anda
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-teal btn-lg px-4 py-3">
                            <i class="bi bi-pencil-square me-2"></i> Daftar Akun Calon Siswa
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Akun
                        </a>
                    @endif
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card card-custom border-0 p-4 shadow-lg text-dark">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4 me-3">
                            <i class="bi bi-calendar2-check-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Informasi Gelombang</h5>
                            <small class="text-muted">Gelombang I (Jalur Reguler & Prestasi)</small>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Pembukaan Pendaftaran:</span>
                            <strong class="text-dark">01 September 2026</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Batas Akhir Berkas:</span>
                            <strong class="text-danger">30 September 2026</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Biaya Registrasi:</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6">Rp 250.000</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Sistem Validasi:</span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">Otomatis Terverifikasi</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-navy w-100 py-2 fw-semibold">
                        Mulai Proses Pendaftaran <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Flow Steps Section -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="text-primary fw-bold text-uppercase tracking-wider small">Alur Pendaftaran</span>
            <h2 class="fw-bold text-dark mt-1">4 Langkah Mudah Menjadi Siswa Baru</h2>
            <p class="text-muted">Ikuti alur tahapan pendaftaran online berikut hingga Anda resmi terdaftar sebagai peserta didik baru.</p>
        </div>

        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-custom h-100 p-4 text-center border-top border-4 border-primary">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 65px; height: 65px;">
                        <i class="bi bi-person-plus-fill fs-3"></i>
                    </div>
                    <span class="badge bg-light text-primary border mb-2 fw-bold">Langkah 1</span>
                    <h5 class="fw-bold text-dark">Buat Akun PPDB</h5>
                    <p class="small text-muted mb-0">Daftarkan akun dengan NIK, nama lengkap, dan email aktif Anda untuk mendapatkan akses portal.</p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-custom h-100 p-4 text-center border-top border-4 border-warning">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 65px; height: 65px;">
                        <i class="bi bi-credit-card-2-front-fill fs-3"></i>
                    </div>
                    <span class="badge bg-light text-warning border mb-2 fw-bold">Langkah 2</span>
                    <h5 class="fw-bold text-dark">Bayar & Upload Bukti</h5>
                    <p class="small text-muted mb-0">Lakukan pembayaran administrasi pendaftaran lalu unggah foto/PDF bukti bayar di dashboard.</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-custom h-100 p-4 text-center border-top border-4 border-info">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 65px; height: 65px;">
                        <i class="bi bi-ui-checks fs-3"></i>
                    </div>
                    <span class="badge bg-light text-info border mb-2 fw-bold">Langkah 3</span>
                    <h5 class="fw-bold text-dark">Lengkapi Data Dapodik</h5>
                    <p class="small text-muted mb-0">Setelah pembayaran terverifikasi, isi formulir biodata, alamat, kontak, dan data orang tua.</p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="card card-custom h-100 p-4 text-center border-top border-4 border-success">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 65px; height: 65px;">
                        <i class="bi bi-patch-check-fill fs-3"></i>
                    </div>
                    <span class="badge bg-light text-success border mb-2 fw-bold">Langkah 4</span>
                    <h5 class="fw-bold text-dark">Pengumuman & SIAKAD</h5>
                    <p class="small text-muted mb-0">Pantau pengumuman kelulusan. Calon siswa yang diterima akan otomatis dimigrasikan ke SIAKAD.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Requirement & Documents Section -->
<section class="py-5" style="background-color: #f1f5f9;">
    <div class="container py-4">
        <div class="row align-items-center gy-4">
            <div class="col-lg-6">
                <span class="text-primary fw-bold text-uppercase tracking-wider small">Persiapan Pendaftaran</span>
                <h2 class="fw-bold text-dark mt-1 mb-3">Dokumen yang Wajib Disiapkan</h2>
                <p class="text-muted mb-4">
                    Pastikan Anda telah menyiapkan data dan dokumen pendukung berikut sebelum melengkapi formulir pendaftaran:
                </p>

                <div class="d-flex mb-3">
                    <div class="text-success me-3 fs-4"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Nomor Induk Kependudukan (NIK) & Nomor KK</h6>
                        <small class="text-muted">16 digit NIK calon siswa dan nomor Kartu Keluarga yang tercatat di Dukcapil.</small>
                    </div>
                </div>

                <div class="d-flex mb-3">
                    <div class="text-success me-3 fs-4"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Nomor Induk Siswa Nasional (NISN)</h6>
                        <small class="text-muted">10 digit NISN resmi dari jenjang sekolah sebelumnya (SD/SMP).</small>
                    </div>
                </div>

                <div class="d-flex mb-3">
                    <div class="text-success me-3 fs-4"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Data Lengkap Orang Tua / Wali</h6>
                        <small class="text-muted">NIK, tahun lahir, jenjang pendidikan terakhir, pekerjaan, dan penghasilan rata-rata ayah dan ibu.</small>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="text-success me-3 fs-4"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Bukti Transfer Biaya Registrasi</h6>
                        <small class="text-muted">Foto struk ATM, bukti transfer m-Banking dalam format JPG/PNG/PDF (maks 5MB).</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card card-custom p-4 bg-white border-0 shadow">
                    <div class="p-3 bg-primary bg-opacity-10 rounded-3 mb-3 text-center">
                        <i class="bi bi-bank2 fs-1 text-primary"></i>
                        <h5 class="fw-bold text-primary mt-2 mb-0">Rekening Resmi Pembayaran PPDB</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Bank Tujuan:</td>
                                    <td class="fw-bold text-dark">BANK BRI (Bank Rakyat Indonesia)</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nomor Rekening:</td>
                                    <td class="fw-bold text-dark fs-5 text-primary">0123-01-000456-53-8</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Atas Nama:</td>
                                    <td class="fw-bold text-dark">PPDB SEKOLAH UNGGULAN</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nominal Transfer:</td>
                                    <td class="fw-bold text-danger fs-5">Rp 250.000,-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning d-flex align-items-center mt-3 mb-0 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0"></i>
                        <div>Harap cantumkan <strong>NIK Calon Siswa</strong> pada berita transfer untuk mempercepat proses verifikasi.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
