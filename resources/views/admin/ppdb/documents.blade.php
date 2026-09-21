@extends('admin.layout')

@section('title', 'Data Berkas PPDB')
@section('header_title', 'Monitoring Kelengkapan Berkas Calon Siswa')

@section('content')
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">TOTAL SISWA</span>
                <span class="material-symbols-outlined text-primary" style="font-size: 20px;">folder_open</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['total'] ?? 0 }}</h3>
            <span class="text-muted small">Pendaftar dalam pemantauan</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">BERKAS LENGKAP</span>
                <span class="material-symbols-outlined text-success" style="font-size: 20px;">task_alt</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['complete'] ?? 0 }}</h3>
            <span class="text-muted small">Semua dokumen terpenuhi</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">BELUM LENGKAP</span>
                <span class="material-symbols-outlined text-warning" style="font-size: 20px;">pending</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['incomplete'] ?? 0 }}</h3>
            <span class="text-muted small">Perlu melengkapi dokumen</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">BUKTI BAYAR</span>
                <span class="material-symbols-outlined text-info" style="font-size: 20px;">receipt_long</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['has_proof'] ?? 0 }}</h3>
            <span class="text-muted small">Bukti transfer terunggah</span>
        </div>
    </div>
</div>

<!-- Filter Toolbar Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white p-3 p-md-4">
    <form method="GET" action="{{ route('admin.ppdb.documents') }}" class="row g-2 align-items-center">
        <!-- Search -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <span class="material-symbols-outlined" style="font-size:18px;">search</span>
                </span>
                <input type="text" 
                       class="form-control border-start-0 ps-0" 
                       name="search" 
                       value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Cari Nama Calon Siswa atau NIK...">
            </div>
        </div>

        <!-- Filter Status Dokumen -->
        <div class="col-6 col-md-3 col-lg-3">
            <select class="form-select" name="doc_status">
                <option value="">-- Semua Status Dokumen --</option>
                <option value="complete" {{ ($filters['docStatus'] ?? '') === 'complete' ? 'selected' : '' }}>Berkas Lengkap</option>
                <option value="incomplete" {{ ($filters['docStatus'] ?? '') === 'incomplete' ? 'selected' : '' }}>Belum Lengkap</option>
                <option value="has_payment" {{ ($filters['docStatus'] ?? '') === 'has_payment' ? 'selected' : '' }}>Ada Bukti Bayar</option>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="col-6 col-md-3 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1 fw-semibold">
                <span class="material-symbols-outlined" style="font-size:18px;">filter_alt</span>
                <span>Filter</span>
            </button>
            @if(!empty($filters['search']) || !empty($filters['docStatus']))
                <a href="{{ route('admin.ppdb.documents') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-2" title="Reset Filter">
                    <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill" style="font-size: 12px;">
                {{ count($documents) }} Data Berkas Siswa
            </span>
            <span class="text-muted small d-none d-sm-inline">Matriks kelengkapan dokumen administratif calon siswa</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light">
                <tr>
                    <th style="width: 48px;" class="ps-3 ps-md-4 text-center">No</th>
                    <th>Nama Calon Siswa</th>
                    <th class="text-center">Bukti Bayar</th>
                    <th class="text-center">Kartu Keluarga</th>
                    <th class="text-center">Akta Kelahiran</th>
                    <th class="text-center">NISN / Ijazah</th>
                    <th class="text-center">Pas Foto 3x4</th>
                    <th class="text-center">Status Berkas</th>
                    <th style="text-align: right; width: 100px;" class="pe-3 pe-md-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $index => $doc)
                    <tr>
                        <td class="ps-3 ps-md-4 text-center text-muted fw-semibold">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $doc['full_name'] }}</div>
                            <div class="text-muted small">NIK: {{ $doc['nik'] }}</div>
                        </td>
                        <!-- Bukti Bayar -->
                        <td class="text-center">
                            @if($doc['has_payment_proof'])
                                <button type="button" class="btn btn-sm btn-light text-success border p-1" data-bs-toggle="modal" data-bs-target="#proofModal{{ $doc['id'] }}" title="Lihat Bukti Transfer">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">check_circle</span>
                                </button>

                                <!-- Modal Preview Bukti Bayar -->
                                <div class="modal fade" id="proofModal{{ $doc['id'] }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 shadow rounded-3">
                                            <div class="modal-header border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark">Bukti Pembayaran — {{ $doc['full_name'] }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <div class="modal-body text-center p-3">
                                                <img src="{{ $backendUrl . $doc['payment_proof_path'] }}" alt="Bukti Transfer" style="max-height: 380px; max-width: 100%; object-fit: contain;" class="rounded border">
                                            </div>
                                            <div class="modal-footer border-top py-2">
                                                <a href="{{ $backendUrl . $doc['payment_proof_path'] }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka Ukuran Penuh</a>
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Diunggah">cancel</span>
                            @endif
                        </td>
                        <!-- KK -->
                        <td class="text-center">
                            @if($doc['has_kk'])
                                <span class="material-symbols-outlined text-success" style="font-size:18px;" title="Terunggah">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Ada">cancel</span>
                            @endif
                        </td>
                        <!-- Akta -->
                        <td class="text-center">
                            @if($doc['has_akta'])
                                <span class="material-symbols-outlined text-success" style="font-size:18px;" title="Terunggah">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Ada">cancel</span>
                            @endif
                        </td>
                        <!-- NISN -->
                        <td class="text-center">
                            @if($doc['has_nisn'])
                                <span class="material-symbols-outlined text-success" style="font-size:18px;" title="Terverifikasi">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Ada">cancel</span>
                            @endif
                        </td>
                        <!-- Foto -->
                        <td class="text-center">
                            @if($doc['has_foto'])
                                <span class="material-symbols-outlined text-success" style="font-size:18px;" title="Terunggah">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Ada">cancel</span>
                            @endif
                        </td>
                        <!-- Status Kelengkapan -->
                        <td class="text-center">
                            @if($doc['is_complete'])
                                <span class="badge bg-success-subtle text-success py-1 px-2">Lengkap (5/5)</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning py-1 px-2">Sebagian ({{ $doc['uploaded_count'] }}/5)</span>
                            @endif
                        </td>
                        <td class="pe-3 pe-md-4 text-end">
                            <a href="{{ route('admin.ppdb.show', $doc['id']) }}" class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold" style="font-size: 12px;">
                                Dossier
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 36px;">folder_off</span>
                            <div class="small">Tidak ada data berkas yang cocok dengan filter.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
