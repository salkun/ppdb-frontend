@extends('admin.layout')

@section('title', 'Verifikasi Pembayaran PPDB')
@section('header_title', 'Verifikasi Pembayaran Formulir PPDB')

@section('content')
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">PERLU VERIFIKASI</span>
                <span class="material-symbols-outlined text-warning" style="font-size: 20px;">hourglass_top</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['pending'] ?? 0 }}</h3>
            <span class="text-muted small">Bukti transfer baru masuk</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">SUDAH LUNAS</span>
                <span class="material-symbols-outlined text-success" style="font-size: 20px;">check_circle</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['paid'] ?? 0 }}</h3>
            <span class="text-muted small">Pembayaran sah tervalidasi</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">DITOLAK</span>
                <span class="material-symbols-outlined text-danger" style="font-size: 20px;">cancel</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['rejected'] ?? 0 }}</h3>
            <span class="text-muted small">Bukti transfer tidak valid</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">TOTAL DANA LUNAS</span>
                <span class="material-symbols-outlined text-primary" style="font-size: 20px;">account_balance_wallet</span>
            </div>
            <h4 class="fw-bold text-dark mb-0">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h4>
            <span class="text-muted small">Kas formulir terverifikasi</span>
        </div>
    </div>
</div>

<!-- Nav Pills & Search Toolbar -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white p-3 p-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <!-- Status Tabs -->
        <ul class="nav nav-pills gap-1">
            <li class="nav-item">
                <a class="nav-link py-1 px-3 fw-semibold {{ ($filters['status'] ?? 'pending') === 'pending' ? 'active' : '' }}" href="{{ route('admin.ppdb.payments', ['status' => 'pending']) }}" style="font-size: 13px;">
                    Menunggu ({{ $stats['pending'] ?? 0 }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 fw-semibold {{ ($filters['status'] ?? '') === 'paid' ? 'active' : '' }}" href="{{ route('admin.ppdb.payments', ['status' => 'paid']) }}" style="font-size: 13px;">
                    Lunas ({{ $stats['paid'] ?? 0 }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 fw-semibold {{ ($filters['status'] ?? '') === 'rejected' ? 'active' : '' }}" href="{{ route('admin.ppdb.payments', ['status' => 'rejected']) }}" style="font-size: 13px;">
                    Ditolak ({{ $stats['rejected'] ?? 0 }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-1 px-3 fw-semibold {{ ($filters['status'] ?? '') === 'unpaid' ? 'active' : '' }}" href="{{ route('admin.ppdb.payments', ['status' => 'unpaid']) }}" style="font-size: 13px;">
                    Belum Bayar ({{ $stats['unpaid'] ?? 0 }})
                </a>
            </li>
        </ul>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.ppdb.payments') }}" class="d-flex gap-2 w-100" style="max-width: 320px;">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? 'pending' }}">
            <div class="input-group">
                <input type="text" class="form-control form-control-sm" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari Nama, NIK, Email...">
                <button class="btn btn-sm btn-primary" type="submit">
                    <span class="material-symbols-outlined align-middle" style="font-size:16px;">search</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill" style="font-size: 12px;">
                {{ count($payments) }} Transaksi Ditampilkan
            </span>
            <span class="text-muted small d-none d-sm-inline">Daftar pembayaran formulir pendaftaran Rp 400.000</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light">
                <tr>
                    <th style="width: 48px;" class="ps-3 ps-md-4 text-center">No</th>
                    <th>Calon Siswa</th>
                    <th>Email & NIK</th>
                    <th>Nominal Biaya</th>
                    <th>Bukti Transfer</th>
                    <th>Status</th>
                    <th>Tanggal Masuk</th>
                    <th style="text-align: right; width: 140px;" class="pe-3 pe-md-4">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $index => $pay)
                    @php
                        $pStatus = $pay['payment_status'] ?? 'unpaid';
                        $acc = $pay['account'] ?? [];
                        $proofPath = $pay['payment_proof_path'] ?? null;
                    @endphp
                    <tr>
                        <td class="ps-3 ps-md-4 text-center text-muted fw-semibold">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $acc['full_name'] ?? 'Calon Siswa' }}</div>
                        </td>
                        <td>
                            <div class="text-dark">{{ $acc['email'] ?? '-' }}</div>
                            <div class="text-muted small">NIK: {{ $acc['nik'] ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">Rp {{ number_format($pay['payment_amount'] ?? 400000, 0, ',', '.') }}</span>
                        </td>
                        <td>
                            @if($proofPath)
                                <a href="{{ $backendUrl . $proofPath }}" target="_blank" class="badge bg-light text-primary border text-decoration-none py-1 px-2" title="Klik untuk lihat gambar bukti">
                                    <span class="material-symbols-outlined align-middle" style="font-size:14px;">image</span>
                                    Lihat Bukti
                                </a>
                            @else
                                <span class="text-muted small">Tidak Ada Bukti</span>
                            @endif
                        </td>
                        <td>
                            @if($pStatus === 'paid')
                                <span class="badge bg-success-subtle text-success py-1 px-2">Lunas</span>
                            @elseif($pStatus === 'pending_verification')
                                <span class="badge bg-warning-subtle text-warning py-1 px-2">Menunggu</span>
                            @elseif($pStatus === 'rejected')
                                <span class="badge bg-danger-subtle text-danger py-1 px-2">Ditolak</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary py-1 px-2">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ date('d/m/Y H:i', strtotime($pay['created_at'] ?? 'now')) }}
                        </td>
                        <td class="pe-3 pe-md-4 text-end">
                            <button type="button" class="btn btn-sm btn-primary py-1 px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $pay['id'] }}" style="font-size: 12.5px;">
                                {{ $pStatus === 'pending_verification' ? 'Verifikasi' : 'Kelola' }}
                            </button>

                            <!-- Modal Verifikasi Detail -->
                            <div class="modal fade" id="verifyModal{{ $pay['id'] }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered text-start">
                                    <div class="modal-content border-0 shadow rounded-3">
                                        <div class="modal-header border-bottom py-3">
                                            <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined text-primary">payments</span>
                                                Verifikasi Pembayaran PPDB
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <form action="{{ route('admin.ppdb.verify-payment', $pay['id']) }}" method="POST">
                                            @csrf
                                            <div class="modal-body p-3 p-md-4">
                                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                                    <div class="fw-bold text-dark mb-1">{{ $acc['full_name'] ?? '-' }}</div>
                                                    <div class="text-muted small">NIK: {{ $acc['nik'] ?? '-' }} &bull; Email: {{ $acc['email'] ?? '-' }}</div>
                                                </div>

                                                @if($proofPath)
                                                    <div class="mb-3 text-center">
                                                        <a href="{{ $backendUrl . $proofPath }}" target="_blank" class="d-inline-block border rounded-3 p-1 bg-light">
                                                            <img src="{{ $backendUrl . $proofPath }}" alt="Bukti Transfer" style="max-height: 240px; max-width: 100%; object-fit: contain;" class="rounded">
                                                        </a>
                                                        <div class="mt-1">
                                                            <a href="{{ $backendUrl . $proofPath }}" target="_blank" class="small fw-semibold text-primary text-decoration-none">
                                                                Buka Ukuran Penuh &rarr;
                                                            </a>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning py-2 small mb-3">
                                                        Pendaftar belum mengunggah bukti transfer fisik/struk.
                                                    </div>
                                                @endif

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark small mb-1">Keputusan Status Pembayaran:</label>
                                                    <select class="form-select" name="payment_status" required>
                                                        <option value="paid" {{ $pStatus === 'paid' ? 'selected' : '' }}>Setujui: Lunas (PAID)</option>
                                                        <option value="rejected" {{ $pStatus === 'rejected' ? 'selected' : '' }}>Tolak: Tidak Valid (REJECTED)</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark small mb-1">Nominal Terverifikasi (Rp):</label>
                                                    <input type="number" class="form-control" name="payment_amount" value="{{ (int)($pay['payment_amount'] ?? 400000) }}" step="1000" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top bg-light py-2">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan Keputusan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 36px;">payments</span>
                            <div class="small">Tidak ada data transaksi pembayaran pada filter ini.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
