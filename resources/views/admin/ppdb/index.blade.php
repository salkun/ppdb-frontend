@extends('admin.layout')

@section('title', 'Data Pendaftar PPDB')
@section('header_title', 'Monitoring & Verifikasi Pendaftar PPDB')

@section('content')
<!-- KPI Summary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Pendaftar</span>
                    <h3 class="fw-bold mb-0 text-dark mt-1">{{ $stats['total'] ?? 0 }}</h3>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Perlu Verifikasi</span>
                    <h3 class="fw-bold mb-0 text-warning mt-1">{{ $stats['pending_payment'] ?? 0 }}</h3>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Pembayaran Lunas</span>
                    <h3 class="fw-bold mb-0 text-success mt-1">{{ $stats['paid'] ?? 0 }}</h3>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Siswa Diterima</span>
                    <h3 class="fw-bold mb-0 text-info mt-1">{{ $stats['accepted'] ?? 0 }}</h3>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-3">
                    <i class="bi bi-mortarboard-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search Toolbar -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.ppdb.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari Nama atau NIK...">
                </div>
            </div>

            <div class="col-md-3">
                <select class="form-select form-select-sm" name="payment_status">
                    <option value="">-- Semua Status Bayar --</option>
                    <option value="pending_verification" {{ ($filters['paymentStatus'] ?? '') === 'pending_verification' ? 'selected' : '' }}>⏳ Menunggu Verifikasi</option>
                    <option value="paid" {{ ($filters['paymentStatus'] ?? '') === 'paid' ? 'selected' : '' }}>✅ Lunas (Paid)</option>
                    <option value="unpaid" {{ ($filters['paymentStatus'] ?? '') === 'unpaid' ? 'selected' : '' }}>❌ Belum Bayar</option>
                    <option value="rejected" {{ ($filters['paymentStatus'] ?? '') === 'rejected' ? 'selected' : '' }}>⛔ Pembayaran Ditolak</option>
                </select>
            </div>

            <div class="col-md-3">
                <select class="form-select form-select-sm" name="registration_status">
                    <option value="">-- Semua Status Seleksi --</option>
                    <option value="pending" {{ ($filters['registrationStatus'] ?? '') === 'pending' ? 'selected' : '' }}>Proses Seleksi</option>
                    <option value="accepted" {{ ($filters['registrationStatus'] ?? '') === 'accepted' ? 'selected' : '' }}>Resmi Diterima</option>
                    <option value="rejected" {{ ($filters['registrationStatus'] ?? '') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm px-3 flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan Filter
                </button>
                @if(!empty($filters['search']) || !empty($filters['paymentStatus']) || !empty($filters['registrationStatus']))
                    <a href="{{ route('admin.ppdb.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Data Registrations Table -->
<div class="card card-custom">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-table me-2 text-primary"></i>Daftar Seluruh Pendaftar PPDB
        </h6>
        <span class="badge bg-secondary rounded-pill">{{ count($registrations) }} Pendaftar Ditemukan</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-nowrap">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th class="ps-3" style="width: 50px;">No</th>
                    <th>Calon Siswa</th>
                    <th>Kontak</th>
                    <th>Status Pembayaran</th>
                    <th>Status Seleksi</th>
                    <th>Form Dapodik</th>
                    <th>Tgl Daftar</th>
                    <th class="text-end pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $index => $reg)
                    @php
                        $pStatus = $reg['payment_status'] ?? 'unpaid';
                        $rStatus = $reg['registration_status'] ?? 'pending';
                        $acc = $reg['account'] ?? [];
                        $hasProof = !empty($reg['payment_proof_path']);
                        $hasForm = !empty($reg['form_data']);
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted small">{{ $index + 1 }}</td>
                        <td>
                            <strong class="text-dark d-block">{{ $acc['full_name'] ?? 'Calon Siswa' }}</strong>
                            <small class="text-muted font-monospace"><i class="bi bi-person-badge me-1"></i>{{ $acc['nik'] ?? '-' }}</small>
                        </td>
                        <td>
                            <small class="d-block text-dark">{{ $acc['email'] ?? '-' }}</small>
                            <small class="text-muted"><i class="bi bi-calendar2 me-1"></i>{{ date('d/m/Y H:i', strtotime($reg['created_at'])) }}</small>
                        </td>
                        <td>
                            @if($pStatus === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Lunas
                                </span>
                            @elseif($pStatus === 'pending_verification')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                    <i class="bi bi-clock-history me-1"></i> Verifikasi
                                </span>
                            @elseif($pStatus === 'rejected')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="bi bi-x-circle-fill me-1"></i> Ditolak
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                    Belum Bayar
                                </span>
                            @endif

                            @if($hasProof)
                                <a href="{{ $backendUrl . $reg['payment_proof_path'] }}" target="_blank" class="badge bg-light text-primary border text-decoration-none ms-1" title="Lihat Berkas Bukti Transfer">
                                    <i class="bi bi-file-earmark-image"></i>
                                </a>
                            @endif
                        </td>
                        <td>
                            @if($rStatus === 'accepted')
                                <span class="badge bg-success px-2 py-1"><i class="bi bi-patch-check-fill me-1"></i> Diterima</span>
                            @elseif($rStatus === 'rejected')
                                <span class="badge bg-danger px-2 py-1"><i class="bi bi-x-octagon-fill me-1"></i> Tidak Lolos</span>
                            @else
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">Menunggu Seleksi</span>
                            @endif
                        </td>
                        <td>
                            @if($hasForm)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check2 me-1"></i> Lengkap
                                </span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1">Belum Diisi</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ date('d M Y', strtotime($reg['created_at'])) }}</td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.ppdb.show', $reg['id']) }}" class="btn btn-outline-primary" title="Tinjau Berkas Lengkap">
                                    <i class="bi bi-eye-fill me-1"></i> Detail
                                </a>

                                @if($hasProof && $pStatus !== 'paid')
                                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $reg['id'] }}" title="Verifikasi Cepat Pembayaran">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                @endif
                            </div>

                            <!-- Modal Quick Verify -->
                            @if($hasProof && $pStatus !== 'paid')
                                <div class="modal fade" id="verifyModal{{ $reg['id'] }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header border-bottom">
                                                <h6 class="modal-title fw-bold">Verifikasi Pembayaran Pendaftar</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.ppdb.verify-payment', $reg['id']) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small mb-0">Nama Pendaftar:</label>
                                                        <div class="fw-bold text-dark">{{ $acc['full_name'] ?? '-' }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small mb-0">Bukti Transfer:</label>
                                                        <div class="mt-1">
                                                            <a href="{{ $backendUrl . $reg['payment_proof_path'] }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Berkas di Tab Baru
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Status Pembayaran:</label>
                                                        <select class="form-select" name="payment_status" required>
                                                            <option value="paid">Setujui: LUNAS (PAID)</option>
                                                            <option value="rejected">Tolak Bukti Pembayaran (REJECTED)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small">Nominal Terverifikasi (Rp):</label>
                                                        <input type="number" class="form-control" name="payment_amount" value="250000" step="1000" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success btn-sm px-3 fw-bold">Simpan Verifikasi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            Tidak ada data pendaftar yang cocok dengan filter yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
