@extends('admin.layout')

@section('title', 'Dashboard Admin SPMB')
@section('header_title', 'Dashboard Monitoring SPMB Online')

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

<!-- 3. Mid Section: Antrean Verifikasi & Modul Manajemen -->
<div class="row g-3 mb-4">
    <!-- Left: Antrean Cepat Verifikasi Pembayaran -->
    <div class="col-lg-8">
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
                                            @php
                                                $pvAmount = isset($pv['payment_amount']) && $pv['payment_amount'] !== '' && $pv['payment_amount'] !== null ? (float)$pv['payment_amount'] : 400000;
                                            @endphp
                                            @if($pvAmount == 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">Rp 0 (Prestasi)</span>
                                            @else
                                                <span class="fw-semibold text-dark">Rp {{ number_format($pvAmount, 0, ',', '.') }}</span>
                                            @endif
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
                                                                    <input type="number" class="form-control" name="payment_amount" value="{{ (int)(isset($pv['payment_amount']) && $pv['payment_amount'] !== '' && $pv['payment_amount'] !== null ? $pv['payment_amount'] : 400000) }}" min="0" step="1000" required>
                                                                    <div class="form-text small" style="font-size: 11px;">
                                                                        Isi <strong>0</strong> jika lolos jalur prestasi / beasiswa (bebas biaya).
                                                                    </div>
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

    <!-- Right: Modul Manajemen & Pintasan Menu -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white p-3 p-md-4 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary" style="font-size:20px;">apps</span>
                        <span>Modul Manajemen</span>
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size: 11px;">Pintasan</span>
                </div>
                <p class="text-muted small mb-3">Navigasi cepat pengelolaan operasional PPDB</p>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('admin.ppdb.students') }}" class="btn btn-outline-primary w-100 p-2 text-start d-flex align-items-center gap-2 rounded-3 hover-shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:20px;">groups</span>
                            <span class="small fw-semibold">Data Siswa</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.ppdb.users') }}" class="btn btn-outline-secondary w-100 p-2 text-start d-flex align-items-center gap-2 rounded-3 hover-shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:20px;">manage_accounts</span>
                            <span class="small fw-semibold">Data User</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.ppdb.documents') }}" class="btn btn-outline-secondary w-100 p-2 text-start d-flex align-items-center gap-2 rounded-3 hover-shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:20px;">folder_open</span>
                            <span class="small fw-semibold">Data Berkas</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('admin.ppdb.payments') }}" class="btn btn-outline-warning text-dark w-100 p-2 text-start d-flex align-items-center gap-2 rounded-3 hover-shadow-sm">
                            <span class="material-symbols-outlined" style="font-size:20px;">payments</span>
                            <span class="small fw-semibold">Kasir Bayar</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between small text-muted">
                <span>Total Pendaftar: <strong class="text-dark">{{ $stats['total'] ?? 0 }}</strong></span>
                <a href="{{ route('admin.ppdb.students') }}" class="text-decoration-none fw-semibold text-primary">Lihat Siswa &rarr;</a>
            </div>
        </div>
    </div>
</div>

<!-- 4. Visualisasi Grafik: Peminatan Jurusan & Jenis Kelamin -->
<div class="row g-3 mb-4">
    <!-- Kolom Kiri: Grafik Peminatan Jurusan -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary" style="font-size: 20px;">pie_chart</span>
                        <span>Grafik Peminatan Jurusan</span>
                    </h6>
                    <span class="text-muted small">Distribusi calon siswa berdasarkan pilihan program</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Tipe Grafik Jurusan">
                        <button type="button" class="btn btn-outline-primary py-1 px-2 active" id="btnMajorTypeDoughnut" onclick="setMajorChartType('doughnut')" title="Diagram Donat">
                            <span class="material-symbols-outlined align-middle" style="font-size: 16px;">pie_chart</span>
                        </button>
                        <button type="button" class="btn btn-outline-primary py-1 px-2" id="btnMajorTypeBar" onclick="setMajorChartType('bar')" title="Diagram Batang">
                            <span class="material-symbols-outlined align-middle" style="font-size: 16px;">bar_chart</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                @php
                    $rCount = $majorCounts['reguler'] ?? 0;
                    $bCount = $majorCounts['bahasa'] ?? 0;
                    $tCount = $majorCounts['tahfidz'] ?? 0;
                    $iCount = $majorCounts['ict'] ?? 0;
                    $uMajorCount = $majorCounts['belum_pilih'] ?? 0;
                    $totalMajor = $rCount + $bCount + $tCount + $iCount + $uMajorCount;
                @endphp

                @if($totalMajor > 0)
                    <div class="chart-wrapper position-relative" style="min-height: 250px; height: 260px;">
                        <canvas id="majorChart"></canvas>
                    </div>
                    <div class="row g-2 pt-3 mt-2 border-top">
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('admin.ppdb.students', ['major' => 'reguler']) }}" class="text-decoration-none d-block p-2 border rounded-3 text-center bg-light hover-shadow-sm transition-all" title="Klik untuk filter data Reguler">
                                <div class="text-primary fw-semibold small" style="font-size: 11px;">Reguler</div>
                                <div class="fw-bold text-dark fs-6">{{ $rCount }} <small class="text-muted fw-normal" style="font-size:10px;">({{ round(($rCount / max(1, $totalMajor)) * 100, 1) }}%)</small></div>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('admin.ppdb.students', ['major' => 'bahasa']) }}" class="text-decoration-none d-block p-2 border rounded-3 text-center bg-light hover-shadow-sm transition-all" title="Klik untuk filter data Bahasa">
                                <div class="text-info fw-semibold small" style="font-size: 11px;">Bahasa</div>
                                <div class="fw-bold text-dark fs-6">{{ $bCount }} <small class="text-muted fw-normal" style="font-size:10px;">({{ round(($bCount / max(1, $totalMajor)) * 100, 1) }}%)</small></div>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('admin.ppdb.students', ['major' => 'tahfidz']) }}" class="text-decoration-none d-block p-2 border rounded-3 text-center bg-light hover-shadow-sm transition-all" title="Klik untuk filter data Tahfidz">
                                <div class="text-success fw-semibold small" style="font-size: 11px;">Tahfidz</div>
                                <div class="fw-bold text-dark fs-6">{{ $tCount }} <small class="text-muted fw-normal" style="font-size:10px;">({{ round(($tCount / max(1, $totalMajor)) * 100, 1) }}%)</small></div>
                            </a>
                        </div>
                        <div class="col-6 col-sm-3">
                            <a href="{{ route('admin.ppdb.students', ['major' => 'ict']) }}" class="text-decoration-none d-block p-2 border rounded-3 text-center bg-light hover-shadow-sm transition-all" title="Klik untuk filter data ICT">
                                <div class="text-warning-emphasis fw-semibold small" style="font-size: 11px;">ICT / IT</div>
                                <div class="fw-bold text-dark fs-6">{{ $iCount }} <small class="text-muted fw-normal" style="font-size:10px;">({{ round(($iCount / max(1, $totalMajor)) * 100, 1) }}%)</small></div>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 px-3">
                        <span class="material-symbols-outlined text-muted mb-2" style="font-size: 38px;">pie_chart</span>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Data Pilihan Jurusan</h6>
                        <p class="text-muted small mb-0">Calon pendaftar belum memilih program peminatan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Grafik Laki-laki vs Perempuan -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-info" style="font-size: 20px;">wc</span>
                        <span>Grafik Laki-laki vs Perempuan</span>
                    </h6>
                    <span class="text-muted small">Perbandingan rasio gender pendaftar</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Tipe Grafik Gender">
                        <button type="button" class="btn btn-outline-primary py-1 px-2 active" id="btnGenderTypeDoughnut" onclick="setGenderChartType('doughnut')" title="Diagram Donat">
                            <span class="material-symbols-outlined align-middle" style="font-size: 16px;">pie_chart</span>
                        </button>
                        <button type="button" class="btn btn-outline-primary py-1 px-2" id="btnGenderTypeBar" onclick="setGenderChartType('bar')" title="Diagram Batang">
                            <span class="material-symbols-outlined align-middle" style="font-size: 16px;">bar_chart</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                @php
                    $maleCount = $genderCounts['laki_laki'] ?? 0;
                    $femaleCount = $genderCounts['perempuan'] ?? 0;
                    $uGenderCount = $genderCounts['belum_diisi'] ?? 0;
                    $totalKnownGender = $maleCount + $femaleCount;
                    $totalAllGender = $totalKnownGender + $uGenderCount;
                @endphp

                @if($totalAllGender > 0)
                    <div class="chart-wrapper position-relative" style="min-height: 250px; height: 260px;">
                        <canvas id="genderChart"></canvas>
                    </div>
                    <div class="row g-2 pt-3 mt-2 border-top">
                        <div class="col-6">
                            <a href="{{ route('admin.ppdb.students', ['search' => 'Laki-laki']) }}" class="text-decoration-none d-block p-2 border rounded-3 bg-light hover-shadow-sm transition-all" title="Klik untuk filter siswa Laki-laki">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-1" style="background-color: #0284c7;">
                                            <span class="material-symbols-outlined text-white" style="font-size: 14px;">male</span>
                                        </span>
                                        <span class="small fw-semibold text-dark">Laki-laki</span>
                                    </div>
                                    <div class="fw-bold text-dark fs-6">
                                        {{ $maleCount }}
                                        <small class="text-muted fw-normal" style="font-size: 11px;">
                                            ({{ $totalKnownGender > 0 ? round(($maleCount / $totalKnownGender) * 100, 1) : 0 }}%)
                                        </small>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('admin.ppdb.students', ['search' => 'Perempuan']) }}" class="text-decoration-none d-block p-2 border rounded-3 bg-light hover-shadow-sm transition-all" title="Klik untuk filter siswa Perempuan">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-1" style="background-color: #ec4899;">
                                            <span class="material-symbols-outlined text-white" style="font-size: 14px;">female</span>
                                        </span>
                                        <span class="small fw-semibold text-dark">Perempuan</span>
                                    </div>
                                    <div class="fw-bold text-dark fs-6">
                                        {{ $femaleCount }}
                                        <small class="text-muted fw-normal" style="font-size: 11px;">
                                            ({{ $totalKnownGender > 0 ? round(($femaleCount / $totalKnownGender) * 100, 1) : 0 }}%)
                                        </small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 px-3">
                        <span class="material-symbols-outlined text-muted mb-2" style="font-size: 38px;">wc</span>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Data Jenis Kelamin</h6>
                        <p class="text-muted small mb-0">Calon siswa belum mengisi kolom jenis kelamin pada formulir.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 5. Grafik Sebaran Calon Pendaftar Berdasarkan Asal Sekolah -->
<div class="row g-3 mb-4">
    <!-- Kolom Kiri: Visualisasi Grafik Interaktif -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary" style="font-size: 22px;">bar_chart</span>
                        <span>Grafik Pendaftar Berdasarkan Asal Sekolah</span>
                    </h6>
                    <span class="text-muted small">Distribusi calon peserta didik baru berdasarkan sekolah asal</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-1">
                    <!-- Switch Limit: Top 5 / Top 10 / Semua -->
                    <div class="btn-group btn-group-sm" role="group" aria-label="Filter Tampilan">
                        <button type="button" class="btn btn-outline-secondary py-1 px-2 active fw-medium" id="btnSchoolTop10" onclick="setSchoolView('top10')">Top 10</button>
                        <button type="button" class="btn btn-outline-secondary py-1 px-2 fw-medium" id="btnSchoolTop5" onclick="setSchoolView('top5')">Top 5</button>
                        <button type="button" class="btn btn-outline-secondary py-1 px-2 fw-medium" id="btnSchoolAll" onclick="setSchoolView('all')">Semua</button>
                    </div>

                    <!-- Switch Chart Type: Horizontal Bar / Doughnut -->
                    <div class="btn-group btn-group-sm ms-1" role="group" aria-label="Tipe Grafik">
                        <button type="button" class="btn btn-outline-primary py-1 px-2 active" id="btnChartTypeBar" onclick="setChartType('bar')" title="Diagram Batang Horizontal">
                            <span class="material-symbols-outlined align-middle" style="font-size: 16px;">align_horizontal_left</span>
                        </button>
                        <button type="button" class="btn btn-outline-primary py-1 px-2" id="btnChartTypeDoughnut" onclick="setChartType('doughnut')" title="Diagram Donat">
                            <span class="material-symbols-outlined align-middle" style="font-size: 16px;">pie_chart</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-3 p-md-4">
                @if(($schoolStats['total_unique'] ?? 0) > 0)
                    <!-- Chart Canvas Wrapper -->
                    <div class="chart-wrapper position-relative" style="min-height: 320px; height: 350px;">
                        <canvas id="schoolOriginChart"></canvas>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 mt-3 border-top small text-muted">
                        <div class="d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-info" style="font-size: 16px;">info</span>
                            <span>Klik pada batang/segmen diagram untuk menyaring calon siswa di <strong>Data Siswa</strong>.</span>
                        </div>
                        <div class="fw-medium text-dark">
                            Total Data Terisi: <span class="badge bg-primary-subtle text-primary">{{ $schoolStats['total_filled'] ?? 0 }} Siswa</span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 px-3">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                            <span class="material-symbols-outlined text-muted" style="font-size: 32px;">school</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Belum Ada Data Asal Sekolah</h6>
                        <p class="text-muted small mb-0" style="max-width: 420px; margin: 0 auto;">
                            Calon pendaftar belum melengkapi formulir pendaftaran atau belum mengisikan data asal sekolah.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Peringkat Asal Sekolah & Ringkasan -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-warning" style="font-size: 20px;">leaderboard</span>
                        <span>Peringkat Asal Sekolah</span>
                    </h6>
                    <span class="text-muted small">Top feeder sekolah calon pendaftar</span>
                </div>
                @if(($schoolStats['total_unique'] ?? 0) > 0)
                    <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 11px;">
                        {{ $schoolStats['total_unique'] }} Sekolah
                    </span>
                @endif
            </div>

            <div class="card-body p-3 p-md-4">
                <!-- Mini Metric Badges -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2 border rounded-3 bg-light text-center">
                            <div class="text-muted small fw-semibold" style="font-size: 11px;">Sekolah Terdata</div>
                            <div class="h5 fw-bold text-primary mb-0">{{ $schoolStats['total_unique'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded-3 bg-light text-center">
                            <div class="text-muted small fw-semibold" style="font-size: 11px;">Belum Mengisi</div>
                            <div class="h5 fw-bold text-secondary mb-0">{{ $schoolStats['total_unfilled'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                @if(!empty($schoolStats['top']))
                    <div class="school-rank-list d-flex flex-column gap-2">
                        @php
                            $rankIndex = 1;
                            $totalFilled = max(1, $schoolStats['total_filled'] ?? 1);
                        @endphp
                        @foreach($schoolStats['top'] as $schoolName => $count)
                            @php
                                $pct = round(($count / $totalFilled) * 100, 1);
                                $rankBadgeColor = match($rankIndex) {
                                    1 => 'background: linear-gradient(135deg, #f59e0b, #d97706); color: white;',
                                    2 => 'background: linear-gradient(135deg, #94a3b8, #64748b); color: white;',
                                    3 => 'background: linear-gradient(135deg, #b45309, #78350f); color: white;',
                                    default => 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;',
                                };
                            @endphp
                            <div class="p-2 border rounded-3 hover-shadow-sm transition-all" style="background: #ffffff;">
                                <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden flex-grow-1">
                                        <span class="badge rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold" style="width: 24px; height: 24px; font-size: 11px; {{ $rankBadgeColor }}">
                                            {{ $rankIndex }}
                                        </span>
                                        <span class="fw-semibold text-dark text-truncate small" title="{{ $schoolName }}">
                                            {{ $schoolName }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        <span class="badge bg-light text-dark border fw-bold" style="font-size: 11px;">
                                            {{ $count }} Siswa
                                        </span>
                                        <a href="{{ route('admin.ppdb.students', ['search' => $schoolName]) }}" class="btn btn-sm btn-outline-primary p-0 d-flex align-items-center justify-content-center rounded" style="width: 22px; height: 22px;" title="Lihat siswa dari {{ $schoolName }}">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">open_in_new</span>
                                        </a>
                                    </div>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between text-muted" style="font-size: 10.5px; margin-top: 2px;">
                                    <span>Porsi Pendaftar</span>
                                    <span class="fw-medium">{{ $pct }}%</span>
                                </div>
                            </div>
                            @php $rankIndex++; @endphp
                        @endforeach
                    </div>

                    @if(($schoolStats['total_unique'] ?? 0) > count($schoolStats['top']))
                        <div class="mt-3 text-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 py-1 fw-medium" data-bs-toggle="modal" data-bs-target="#allSchoolsModal">
                                <span class="material-symbols-outlined align-middle" style="font-size: 16px;">format_list_bulleted</span>
                                <span>Lihat Semua {{ $schoolStats['total_unique'] }} Sekolah Asal</span>
                            </button>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4 text-muted small">
                        Belum ada peringkat sekolah.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Daftar Lengkap Seluruh Asal Sekolah -->
@if(($schoolStats['total_unique'] ?? 0) > 0)
<div class="modal fade" id="allSchoolsModal" tabindex="-1" aria-labelledby="allSchoolsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="allSchoolsModalLabel">
                    <span class="material-symbols-outlined text-primary">school</span>
                    <span>Seluruh Asal Sekolah Calon Pendaftar ({{ $schoolStats['total_unique'] }} Sekolah)</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Nama Asal Sekolah</th>
                                <th style="width: 130px;" class="text-center">Jumlah Calon</th>
                                <th style="width: 110px;" class="text-center">Persentase</th>
                                <th style="width: 90px;" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $modalIndex = 1;
                                $totalFilledModal = max(1, $schoolStats['total_filled'] ?? 1);
                            @endphp
                            @foreach($schoolStats['all'] as $schoolName => $count)
                                @php
                                    $mPct = round(($count / $totalFilledModal) * 100, 1);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $modalIndex++ }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $schoolName }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1">
                                            {{ $count }} Siswa
                                        </span>
                                    </td>
                                    <td class="text-center text-muted small fw-medium">
                                        {{ $mPct }}%
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.ppdb.students', ['search' => $schoolName]) }}" class="btn btn-sm btn-outline-primary py-1 px-2 fw-medium" style="font-size: 12px;">
                                            Lihat Siswa
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- 5. Pendaftar Terbaru Table -->
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

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
    if (typeof Chart === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"><\/script>');
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const studentsBaseUrl = @json(route('admin.ppdb.students'));

    // ==========================================
    // 1. Grafik Peminatan Jurusan
    // ==========================================
    const majorCanvas = document.getElementById('majorChart');
    if (majorCanvas) {
        const majorData = @json($majorCounts ?? []);
        const rawLabels = ['Reguler', 'Program Bahasa', 'Tahfidz Al-Qur\'an', 'ICT & Digital IT'];
        const majorKeys = ['reguler', 'bahasa', 'tahfidz', 'ict'];
        const majorColors = ['#005ab4', '#0284c7', '#10b981', '#f59e0b'];

        let chartLabels = [...rawLabels];
        let chartKeys = [...majorKeys];
        let chartValues = [
            majorData.reguler || 0,
            majorData.bahasa || 0,
            majorData.tahfidz || 0,
            majorData.ict || 0
        ];
        let chartColors = [...majorColors];

        if ((majorData.belum_pilih || 0) > 0) {
            chartLabels.push('Belum Pilih');
            chartKeys.push('belum_pilih');
            chartValues.push(majorData.belum_pilih);
            chartColors.push('#94a3b8');
        }

        const totalMajors = chartValues.reduce((a, b) => a + b, 0);
        let majorChartType = 'doughnut';
        let majorChartInstance = null;

        function renderMajorChart() {
            if (majorChartInstance) majorChartInstance.destroy();
            const isDoughnut = majorChartType === 'doughnut';

            majorChartInstance = new Chart(majorCanvas, {
                type: majorChartType,
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Calon Siswa',
                        data: chartValues,
                        backgroundColor: isDoughnut ? chartColors : chartColors.map(c => c + 'dd'),
                        borderColor: isDoughnut ? '#ffffff' : chartColors,
                        borderWidth: isDoughnut ? 2 : 1,
                        borderRadius: isDoughnut ? 0 : 6,
                        hoverOffset: isDoughnut ? 6 : 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 12,
                                font: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 12, weight: '500' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.92)',
                            titleFont: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 13, weight: 'bold' },
                            bodyFont: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (ctx) {
                                    const val = ctx.raw || 0;
                                    const pct = totalMajors > 0 ? ((val / totalMajors) * 100).toFixed(1) : 0;
                                    return ` ${ctx.label}: ${val} Siswa (${pct}%)`;
                                },
                                afterLabel: function (ctx) {
                                    const key = chartKeys[ctx.dataIndex];
                                    return key !== 'belum_pilih' ? '💡 Klik untuk filter di Data Siswa' : '';
                                }
                            }
                        }
                    },
                    scales: isDoughnut ? {} : {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 11 } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 11, weight: '600' } },
                            grid: { display: false }
                        }
                    },
                    onClick: function (event, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const key = chartKeys[index];
                            if (key && key !== 'belum_pilih') {
                                window.location.href = studentsBaseUrl + '?major=' + encodeURIComponent(key);
                            }
                        }
                    },
                    onHover: function (event, elements) {
                        event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                    }
                }
            });
        }

        window.setMajorChartType = function(type) {
            majorChartType = type;
            document.querySelectorAll('#btnMajorTypeDoughnut, #btnMajorTypeBar').forEach(b => b.classList.remove('active'));
            if (type === 'doughnut') document.getElementById('btnMajorTypeDoughnut')?.classList.add('active');
            if (type === 'bar') document.getElementById('btnMajorTypeBar')?.classList.add('active');
            renderMajorChart();
        };

        renderMajorChart();
    }

    // ==========================================
    // 2. Grafik Laki-laki vs Perempuan
    // ==========================================
    const genderCanvas = document.getElementById('genderChart');
    if (genderCanvas) {
        const genderData = @json($genderCounts ?? []);
        let gLabels = ['Laki-laki', 'Perempuan'];
        let gValues = [genderData.laki_laki || 0, genderData.perempuan || 0];
        let gColors = ['#0284c7', '#ec4899'];

        if ((genderData.belum_diisi || 0) > 0) {
            gLabels.push('Belum Mengisi');
            gValues.push(genderData.belum_diisi);
            gColors.push('#cbd5e1');
        }

        const totalGender = gValues.reduce((a, b) => a + b, 0);
        let genderChartType = 'doughnut';
        let genderChartInstance = null;

        function renderGenderChart() {
            if (genderChartInstance) genderChartInstance.destroy();
            const isDoughnut = genderChartType === 'doughnut';

            genderChartInstance = new Chart(genderCanvas, {
                type: genderChartType,
                data: {
                    labels: gLabels,
                    datasets: [{
                        label: 'Calon Siswa',
                        data: gValues,
                        backgroundColor: isDoughnut ? gColors : gColors.map(c => c + 'dd'),
                        borderColor: isDoughnut ? '#ffffff' : gColors,
                        borderWidth: isDoughnut ? 2 : 1,
                        borderRadius: isDoughnut ? 0 : 6,
                        hoverOffset: isDoughnut ? 6 : 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 12,
                                font: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 12, weight: '500' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.92)',
                            titleFont: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 13, weight: 'bold' },
                            bodyFont: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (ctx) {
                                    const val = ctx.raw || 0;
                                    const pct = totalGender > 0 ? ((val / totalGender) * 100).toFixed(1) : 0;
                                    return ` ${ctx.label}: ${val} Siswa (${pct}%)`;
                                },
                                afterLabel: function (ctx) {
                                    return ctx.label !== 'Belum Mengisi' ? '💡 Klik untuk filter di Data Siswa' : '';
                                }
                            }
                        }
                    },
                    scales: isDoughnut ? {} : {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 11 } },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            ticks: { font: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 11, weight: '600' } },
                            grid: { display: false }
                        }
                    },
                    onClick: function (event, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const label = gLabels[index];
                            if (label && label !== 'Belum Mengisi') {
                                window.location.href = studentsBaseUrl + '?search=' + encodeURIComponent(label);
                            }
                        }
                    },
                    onHover: function (event, elements) {
                        event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                    }
                }
            });
        }

        window.setGenderChartType = function(type) {
            genderChartType = type;
            document.querySelectorAll('#btnGenderTypeDoughnut, #btnGenderTypeBar').forEach(b => b.classList.remove('active'));
            if (type === 'doughnut') document.getElementById('btnGenderTypeDoughnut')?.classList.add('active');
            if (type === 'bar') document.getElementById('btnGenderTypeBar')?.classList.add('active');
            renderGenderChart();
        };

        renderGenderChart();
    }

    // ==========================================
    // 3. Grafik Asal Sekolah
    // ==========================================
    const canvas = document.getElementById('schoolOriginChart');
    if (canvas) {
        const rawAllSchools = @json($schoolStats['all'] ?? []);
        const totalFilled = {{ (int)($schoolStats['total_filled'] ?? 0) }};

        // Palette warna tema yang harmonis
        const palette = [
            '#005ab4', '#0284c7', '#0d9488', '#10b981', '#f59e0b',
            '#6366f1', '#8b5cf6', '#ec4899', '#14b8a6', '#3b82f6',
            '#64748b', '#84cc16', '#e11d48', '#a855f7', '#06b6d4'
        ];

        let currentView = 'top10';
        let currentChartType = 'bar';
        let chartInstance = null;

        function getChartData(view) {
            const allEntries = Object.entries(rawAllSchools);
            let entries = [];

            if (view === 'top5') {
                entries = allEntries.slice(0, 5);
            } else if (view === 'top10') {
                entries = allEntries.slice(0, 10);
            } else {
                entries = allEntries;
            }

            const labels = entries.map(item => item[0]);
            const data = entries.map(item => item[1]);
            const colors = labels.map((_, i) => palette[i % palette.length]);

            return { labels, data, colors };
        }

        function createOrUpdateChart() {
            const { labels, data, colors } = getChartData(currentView);

            if (chartInstance) {
                chartInstance.destroy();
            }

            const isBar = currentChartType === 'bar';

            const config = {
                type: isBar ? 'bar' : 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Calon Pendaftar',
                        data: data,
                        backgroundColor: isBar ? colors.map(c => c + 'dd') : colors,
                        borderColor: isBar ? colors : '#ffffff',
                        borderWidth: isBar ? 1 : 2,
                        borderRadius: isBar ? 6 : 0,
                        hoverBackgroundColor: colors,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: isBar ? 'y' : 'x',
                    layout: {
                        padding: { top: 10, bottom: 10, left: 5, right: 15 }
                    },
                    plugins: {
                        legend: {
                            display: !isBar,
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 12,
                                font: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 12 },
                                generateLabels: function (chart) {
                                    const orig = Chart.overrides.doughnut.plugins.legend.labels.generateLabels(chart);
                                    return orig.map(l => {
                                        if (l.text.length > 22) {
                                            l.text = l.text.substring(0, 20) + '...';
                                        }
                                        return l;
                                    });
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.92)',
                            titleFont: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 13, weight: 'bold' },
                            bodyFont: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 12 },
                            padding: 12,
                            cornerRadius: 8,
                            boxPadding: 4,
                            callbacks: {
                                label: function (ctx) {
                                    const val = ctx.raw || 0;
                                    const pct = totalFilled > 0 ? ((val / totalFilled) * 100).toFixed(1) : 0;
                                    return ` ${val} Calon Pendaftar (${pct}%)`;
                                },
                                afterLabel: function () {
                                    return '💡 Klik untuk filter di Data Siswa';
                                }
                            }
                        }
                    },
                    scales: isBar ? {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 11 },
                            },
                            grid: { color: '#f1f5f9' },
                        },
                        y: {
                            ticks: {
                                font: { family: "'Plus Jakarta Sans', system-ui, sans-serif", size: 12, weight: '600' },
                                callback: function (val) {
                                    const label = this.getLabelForValue(val);
                                    return label.length > 24 ? label.substring(0, 22) + '…' : label;
                                }
                            },
                            grid: { display: false },
                        }
                    } : {},
                    onClick: function (event, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const schoolName = labels[index];
                            if (schoolName) {
                                window.location.href = studentsBaseUrl + '?search=' + encodeURIComponent(schoolName);
                            }
                        }
                    },
                    onHover: function (event, elements) {
                        event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                    }
                }
            };

            chartInstance = new Chart(canvas, config);
        }

        // Filter limit: Top 5, Top 10, Semua
        window.setSchoolView = function (view) {
            currentView = view;
            document.querySelectorAll('#btnSchoolTop10, #btnSchoolTop5, #btnSchoolAll').forEach(btn => btn.classList.remove('active'));
            if (view === 'top5') document.getElementById('btnSchoolTop5')?.classList.add('active');
            if (view === 'top10') document.getElementById('btnSchoolTop10')?.classList.add('active');
            if (view === 'all') document.getElementById('btnSchoolAll')?.classList.add('active');
            createOrUpdateChart();
        };

        // Filter tipe: Bar vs Doughnut
        window.setChartType = function (type) {
            currentChartType = type;
            document.querySelectorAll('#btnChartTypeBar, #btnChartTypeDoughnut').forEach(btn => btn.classList.remove('active'));
            if (type === 'bar') document.getElementById('btnChartTypeBar')?.classList.add('active');
            if (type === 'doughnut') document.getElementById('btnChartTypeDoughnut')?.classList.add('active');
            createOrUpdateChart();
        };

        // Render awal
        createOrUpdateChart();
    }
});
</script>
@endpush
