@extends('admin.layout')

@section('title', 'Dashboard Admin PPDB')
@section('header_title', 'Dashboard Monitoring PPDB Online')

@section('content')
<!-- 1. Header Banner -->
<div class="card border-0 rounded-4 shadow-sm mb-4 text-white" style="background: linear-gradient(135deg, #005ab4 0%, #003770 100%);">
    <div class="card-body p-4 p-lg-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <div class="badge bg-white text-primary mb-2 px-3 py-2 fw-semibold rounded-pill" style="font-size: 11.5px;">
                    <span class="material-symbols-outlined align-middle" style="font-size:16px;">school</span>
                    Tahun Ajaran 2027/2028
                </div>
                <h4 class="fw-bold mb-1">Selamat Datang, {{ session('admin_user.username', 'Administrator') }}!</h4>
                <p class="text-white-50 mb-0 small" style="max-width: 620px;">
                    Pantau alur pendaftaran calon peserta didik baru SMP Al-Muhajirin Purwakarta, kelengkapan berkas, serta verifikasi bukti pembayaran secara real-time.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <!-- One-Click Master Sync Button -->
                <form action="{{ route('admin.ppdb.sync') }}" method="POST" class="d-inline mb-0">
                    @csrf
                    <button type="submit" class="btn btn-success text-white fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3" onclick="return confirm('Kirim seluruh data lokal yang belum tersinkron ke Master Data API?')">
                        <span class="material-symbols-outlined" style="font-size:18px;">sync</span>
                        <span>Sinkron ke Master API</span>
                        @if(($syncStats['pending'] ?? 0) > 0)
                            <span class="badge bg-white text-success rounded-pill ms-1" style="font-size: 11px;">{{ $syncStats['pending'] }}</span>
                        @endif
                    </button>
                </form>
                <a href="{{ route('admin.ppdb.users') }}" class="btn btn-outline-light text-white fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3">
                    <span class="material-symbols-outlined" style="font-size:18px;">group_add</span>
                    <span>Kelola & Import User</span>
                </a>
                <a href="{{ route('admin.ppdb.payments') }}" class="btn btn-warning text-dark fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3">
                    <span class="material-symbols-outlined" style="font-size:18px;">payments</span>
                    <span>Verifikasi ({{ $stats['pending_payment'] ?? 0 }})</span>
                </a>
                <a href="{{ route('admin.ppdb.export', ['format' => 'xlsx']) }}" class="btn btn-light text-primary fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3">
                    <span class="material-symbols-outlined" style="font-size:18px;">download</span>
                    <span>Export Excel</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Sync Status Widget (Local-First + Master API Status) -->
<div class="card border-0 shadow-sm rounded-3 bg-white mb-4 p-3 border-start border-4 {{ ($syncStats['failed'] ?? 0) > 0 ? 'border-danger' : (($syncStats['pending'] ?? 0) > 0 ? 'border-warning' : 'border-success') }}">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background-color: {{ ($syncStats['failed'] ?? 0) > 0 ? '#fee2e2' : (($syncStats['pending'] ?? 0) > 0 ? '#fef3c7' : '#dcfce7') }};">
                <span class="material-symbols-outlined {{ ($syncStats['failed'] ?? 0) > 0 ? 'text-danger' : (($syncStats['pending'] ?? 0) > 0 ? 'text-warning' : 'text-success') }}" style="font-size: 24px;">
                    {{ ($syncStats['failed'] ?? 0) > 0 ? 'sync_problem' : (($syncStats['pending'] ?? 0) > 0 ? 'sync' : 'cloud_done') }}
                </span>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark">Status Sinkronisasi Master API</h6>
                    @if(($syncStats['pending'] ?? 0) > 0)
                        <span class="badge bg-warning text-dark fw-semibold" style="font-size: 11px;">{{ $syncStats['pending'] }} data menunggu kirim</span>
                    @elseif(($syncStats['failed'] ?? 0) > 0)
                        <span class="badge bg-danger text-white fw-semibold" style="font-size: 11px;">{{ $syncStats['failed'] }} data perlu dicek</span>
                    @else
                        <span class="badge bg-success text-white fw-semibold" style="font-size: 11px;">Semua data tersinkron</span>
                    @endif
                </div>
                <div class="text-muted small mt-1">
                    <span>Target Server: <code class="text-primary">{{ config('ppdb.api_url', 'http://127.0.0.1:8001') }}</code></span>
                    <span class="mx-2">•</span>
                    <span>Database Lokal: <strong class="text-success">MySQL (Source of Truth)</strong></span>
                    <span class="mx-2">•</span>
                    <span>Terakhir Sinkron: <strong>{{ !empty($syncStats['last_synced_at']) ? \Carbon\Carbon::parse($syncStats['last_synced_at'])->diffForHumans() : 'Belum pernah' }}</strong></span>
                </div>
            </div>
        </div>

        <div>
            <form action="{{ route('admin.ppdb.sync') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3" onclick="return confirm('Kirim seluruh data lokal yang belum tersinkron ke Master Data API?')">
                    <span class="material-symbols-outlined" style="font-size: 18px;">send</span>
                    <span>Kirim ke Master Sekarang</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 2. Minimalist KPI Stats Strip -->
<div class="card border-0 shadow-sm rounded-3 bg-white mb-4 overflow-hidden">
    <div class="row g-0">
        <!-- 1. Total Registrasi -->
        <div class="col-6 col-lg-3 p-3 p-md-4 border-end border-bottom border-lg-bottom-0">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 11px;">Total Registrasi</span>
                <span class="material-symbols-outlined text-primary" style="font-size: 20px;">groups</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">{{ $stats['total'] ?? 0 }}</h3>
                <span class="text-muted small">pendaftar</span>
            </div>
            <div class="small">
                <a href="{{ route('admin.ppdb.students') }}" class="text-primary fw-semibold text-decoration-none" style="font-size: 12px;">
                    Lihat Data Siswa &rarr;
                </a>
            </div>
        </div>

        <!-- 2. Perlu Verifikasi -->
        <div class="col-6 col-lg-3 p-3 p-md-4 border-end-lg border-bottom border-lg-bottom-0">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 11px;">Perlu Verifikasi</span>
                <span class="material-symbols-outlined text-warning" style="font-size: 20px;">hourglass_top</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">{{ $stats['pending_payment'] ?? 0 }}</h3>
                <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold" style="font-size: 10px;">ACTION NEEDED</span>
            </div>
            <div class="small">
                <a href="{{ route('admin.ppdb.payments') }}" class="text-warning-emphasis fw-semibold text-decoration-none" style="font-size: 12px;">
                    Periksa Bukti &rarr;
                </a>
            </div>
        </div>

        <!-- 3. Pembayaran Lunas -->
        <div class="col-6 col-lg-3 p-3 p-md-4 border-end">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 11px;">Pembayaran Lunas</span>
                <span class="material-symbols-outlined text-success" style="font-size: 20px;">verified</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">{{ $stats['paid'] ?? 0 }}</h3>
                <span class="badge bg-success-subtle text-success fw-semibold" style="font-size: 10px;">TERVALIDASI</span>
            </div>
            <div class="text-muted small" style="font-size: 12px;">
                Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <!-- 4. Calon Siswa Diterima -->
        <div class="col-6 col-lg-3 p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 11px;">Siswa Diterima</span>
                <span class="material-symbols-outlined text-info" style="font-size: 20px;">school</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">{{ $stats['accepted'] ?? 0 }}</h3>
                <span class="badge bg-info-subtle text-info fw-semibold" style="font-size: 10px;">LOLOS SELEKSI</span>
            </div>
            <div class="text-muted small" style="font-size: 12px;">
                Tercatat di SIAKAD
            </div>
        </div>
    </div>
</div>

<!-- 3. Mid Section: Antrean Verifikasi & Kuota Jurusan -->
<div class="row g-3 mb-4">
    <!-- Left: Antrean Cepat Verifikasi Pembayaran -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-warning" style="font-size:20px;">pending_actions</span>
                        Antrean Verifikasi Pembayaran Rp 400.000
                    </h6>
                    <span class="text-muted small">Bukti transfer yang menunggu persetujuan operator</span>
                </div>
                <a href="{{ route('admin.ppdb.payments') }}" class="btn btn-sm btn-outline-primary py-1 px-3 rounded-pill fw-semibold" style="font-size: 12px;">
                    Buka Semua ({{ count($pendingVerifications) }})
                </a>
            </div>
            <div class="card-body p-0">
                @if(count($pendingVerifications) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 ps-md-4">Pendaftar</th>
                                    <th>Nominal</th>
                                    <th>Bukti</th>
                                    <th class="text-end pe-3 pe-md-4">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($pendingVerifications, 0, 5) as $pv)
                                    @php
                                        $acc = $pv['account'] ?? [];
                                        $proofPath = $pv['payment_proof_path'] ?? null;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 ps-md-4">
                                            <div class="fw-bold text-dark">{{ $acc['full_name'] ?? 'Calon Siswa' }}</div>
                                            <div class="text-muted small">NIK: {{ $acc['nik'] ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">Rp 400.000</span>
                                        </td>
                                        <td>
                                            @if($proofPath)
                                                <a href="{{ ppdb_proof_url($proofPath, $backendUrl) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none py-1 px-2">
                                                    <span class="material-symbols-outlined align-middle" style="font-size:14px;">image</span>
                                                    Lihat Foto
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3 pe-md-4">
                                            <button type="button" class="btn btn-sm btn-primary py-1 px-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#quickVerifyModal{{ $pv['id'] }}">
                                                Verifikasi
                                            </button>

                                            <!-- Modal Verifikasi Cepat -->
                                            <div class="modal fade" id="quickVerifyModal{{ $pv['id'] }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered text-start">
                                                    <div class="modal-content border-0 shadow rounded-3">
                                                        <div class="modal-header border-bottom py-3">
                                                            <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                                <span class="material-symbols-outlined text-primary">verified</span>
                                                                Verifikasi Pembayaran Formulir
                                                            </h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                        </div>
                                                        <form action="{{ route('admin.ppdb.verify-payment', $pv['id']) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body p-3 p-md-4">
                                                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                                                    <div class="fw-bold text-dark mb-1">{{ $acc['full_name'] ?? '-' }}</div>
                                                                    <div class="text-muted small">NIK: {{ $acc['nik'] ?? '-' }} &bull; Email: {{ $acc['email'] ?? '-' }}</div>
                                                                </div>

                                                                @if($proofPath)
                                                                    <div class="mb-3 text-center">
                                                                        <a href="{{ ppdb_proof_url($proofPath, $backendUrl) }}" target="_blank" class="d-inline-block border rounded-3 p-1 bg-light">
                                                                            <img src="{{ ppdb_proof_url($proofPath, $backendUrl) }}" alt="Bukti Transfer" style="max-height: 180px; max-width: 100%; object-fit: contain;" class="rounded">
                                                                        </a>
                                                                        <div class="mt-1">
                                                                            <a href="{{ ppdb_proof_url($proofPath, $backendUrl) }}" target="_blank" class="small fw-semibold text-primary text-decoration-none">
                                                                                Perbesar Bukti Transfer &rarr;
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold text-dark small mb-1">Keputusan Status Pembayaran:</label>
                                                                    <select class="form-select" name="payment_status" required>
                                                                        <option value="paid">Setujui: Lunas (PAID)</option>
                                                                        <option value="rejected">Tolak: Tidak Valid (REJECTED)</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold text-dark small mb-1">Nominal Terverifikasi (Rp):</label>
                                                                    <input type="number" class="form-control" name="payment_amount" value="400000" step="1000" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-top bg-light py-2">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan Verifikasi</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 px-3">
                        <span class="material-symbols-outlined text-success mb-2" style="font-size: 42px;">task_alt</span>
                        <h6 class="fw-bold text-dark mb-1">Semua Pembayaran Selesai Diverifikasi</h6>
                        <p class="text-muted small mb-0">Tidak ada antrean pembayaran yang menunggu konfirmasi saat ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right: Kuota Jurusan & Pintasan Menu -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-size:20px;">pie_chart</span>
                    Peminatan Jurusan
                </h6>
                <span class="text-muted small">Jumlah pendaftar berdasarkan program studi</span>
            </div>
            <div class="card-body p-3 px-md-4">
                @php
                    $targetTotal = 270;
                    $rCount = $majorCounts['reguler'] ?? 0;
                    $bCount = $majorCounts['bahasa'] ?? 0;
                    $tCount = $majorCounts['tahfidz'] ?? 0;
                    $iCount = $majorCounts['ict'] ?? 0;
                @endphp
                <!-- Reguler -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-dark">Reguler (Umum)</span>
                        <span class="text-muted fw-bold">{{ $rCount }} Siswa</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, ($rCount / 120) * 100) }}%"></div>
                    </div>
                </div>

                <!-- Bahasa -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-dark">Program Bahasa</span>
                        <span class="text-muted fw-bold">{{ $bCount }} Siswa</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, ($bCount / 60) * 100) }}%"></div>
                    </div>
                </div>

                <!-- Tahfidz -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-dark">Tahfidz Al-Qur'an</span>
                        <span class="text-muted fw-bold">{{ $tCount }} Siswa</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, ($tCount / 60) * 100) }}%"></div>
                    </div>
                </div>

                <!-- ICT -->
                <div>
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="fw-semibold text-dark">ICT & Digital IT</span>
                        <span class="text-muted fw-bold">{{ $iCount }} Siswa</span>
                    </div>
                    <div class="progress" style="height: 7px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(100, ($iCount / 30) * 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Modules Card -->
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <h6 class="fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing: 0.04em;">Modul Manajemen</h6>
            <div class="row g-2">
                <div class="col-6">
                    <a href="{{ route('admin.ppdb.students') }}" class="btn btn-outline-primary w-100 p-2 text-start d-flex align-items-center gap-2 rounded-3">
                        <span class="material-symbols-outlined" style="font-size:20px;">groups</span>
                        <span class="small fw-semibold">Data Siswa</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('admin.ppdb.users') }}" class="btn btn-outline-secondary w-100 p-2 text-start d-flex align-items-center gap-2 rounded-3">
                        <span class="material-symbols-outlined" style="font-size:20px;">manage_accounts</span>
                        <span class="small fw-semibold">Data User</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('admin.ppdb.documents') }}" class="btn btn-outline-secondary w-100 p-2 text-start d-flex align-items-center gap-2 rounded-3">
                        <span class="material-symbols-outlined" style="font-size:20px;">folder_open</span>
                        <span class="small fw-semibold">Data Berkas</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('admin.ppdb.payments') }}" class="btn btn-outline-warning text-dark w-100 p-2 text-start d-flex align-items-center gap-2 rounded-3">
                        <span class="material-symbols-outlined" style="font-size:20px;">payments</span>
                        <span class="small fw-semibold">Kasir Bayar</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. Pendaftar Terbaru Table -->
<div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-dark mb-0">Pendaftar Terbaru</h6>
            <span class="text-muted small">8 pendaftar paling baru yang masuk ke sistem</span>
        </div>
        <a href="{{ route('admin.ppdb.students') }}" class="btn btn-sm btn-primary py-1 px-3 rounded-pill fw-semibold" style="font-size: 12.5px;">
            Lihat Semua Data Siswa &rarr;
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light">
                <tr>
                    <th style="width: 48px;" class="ps-3 ps-md-4 text-center">No</th>
                    <th>Calon Siswa</th>
                    <th>Jurusan</th>
                    <th>Status Bayar</th>
                    <th>Status Seleksi</th>
                    <th>Tanggal</th>
                    <th style="text-align: right; width: 140px;" class="pe-3 pe-md-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentRegistrations as $index => $reg)
                    @php
                        $pStatus = $reg['payment_status'] ?? 'unpaid';
                        $rStatus = $reg['registration_status'] ?? 'pending';
                        $acc = $reg['account'] ?? [];
                        $form = $reg['form_data'] ?? [];
                        $major = $form['major'] ?? ($reg['student']['major'] ?? '-');
                    @endphp
                    <tr>
                        <td class="ps-3 ps-md-4 text-center text-muted fw-semibold">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $acc['full_name'] ?? ($form['full_name'] ?? 'Calon Siswa') }}</div>
                            <div class="text-muted small">NIK: {{ $acc['nik'] ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border text-uppercase fw-semibold py-1 px-2" style="font-size: 11px;">
                                {{ $major ?: 'BELUM PILIH' }}
                            </span>
                        </td>
                        <td>
                            @if($pStatus === 'paid')
                                <span class="badge bg-success-subtle text-success py-1 px-2">Lunas</span>
                            @elseif($pStatus === 'pending_verification')
                                <span class="badge bg-warning-subtle text-warning py-1 px-2">Verifikasi</span>
                            @elseif($pStatus === 'rejected')
                                <span class="badge bg-danger-subtle text-danger py-1 px-2">Ditolak</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary py-1 px-2">Belum Bayar</span>
                            @endif
                        </td>
                        <td>
                            @if($rStatus === 'accepted')
                                <span class="badge bg-success-subtle text-success py-1 px-2">Diterima</span>
                            @elseif($rStatus === 'rejected')
                                <span class="badge bg-danger-subtle text-danger py-1 px-2">Tidak Lolos</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary py-1 px-2">Dalam Seleksi</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ date('d/m/Y H:i', strtotime($reg['created_at'] ?? 'now')) }}
                        </td>
                        <td class="pe-3 pe-md-4 text-end text-nowrap">
                            <a href="{{ route('admin.ppdb.show', $reg['id']) }}?tab=dokumen" class="btn btn-sm btn-light border text-primary py-1 px-2" title="Lihat Berkas Dokumen Siswa">
                                <span class="material-symbols-outlined align-middle" style="font-size: 15px;">folder_open</span>
                            </a>
                            <a href="{{ route('admin.ppdb.show', $reg['id']) }}" class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold" style="font-size: 12px;">
                                Detail
                            </a>
                            <a href="{{ route('admin.ppdb.edit', $reg['id']) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 12px;">
                                Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 36px;">inbox</span>
                            <div class="small">Belum ada data pendaftar yang masuk.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
