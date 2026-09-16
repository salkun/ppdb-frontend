@extends('admin.layout')

@section('title', 'Data Pendaftar PPDB')
@section('header_title', 'Monitoring & Verifikasi Pendaftar')

@section('content')
<!-- KPI Summary Bento Cards -->
<div class="row g-3 mb-4">
    <!-- 1. Total Pendaftar -->
    <div class="col-sm-6 col-xl-3">
        <div class="bento-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="font-mono-meta text-secondary" style="font-size: 0.72rem;">TOTAL REGISTRASI</span>
                <span class="badge-pastel badge-pastel-neutral">SEMUA JALUR</span>
            </div>
            <div>
                <h3 class="font-serif-heading fs-2 text-dark mb-1">{{ $stats['total'] ?? 0 }}</h3>
                <span class="text-secondary small">Akun pendaftar terdaftar</span>
            </div>
        </div>
    </div>

    <!-- 2. Perlu Verifikasi Pembayaran -->
    <div class="col-sm-6 col-xl-3">
        <div class="bento-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="font-mono-meta text-secondary" style="font-size: 0.72rem;">PERLU VERIFIKASI</span>
                <span class="badge-pastel badge-pastel-yellow">ACTION NEEDED</span>
            </div>
            <div>
                <h3 class="font-serif-heading fs-2 text-dark mb-1">{{ $stats['pending_payment'] ?? 0 }}</h3>
                <span class="text-secondary small">Bukti transfer menunggu validasi</span>
            </div>
        </div>
    </div>

    <!-- 3. Pembayaran Lunas -->
    <div class="col-sm-6 col-xl-3">
        <div class="bento-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="font-mono-meta text-secondary" style="font-size: 0.72rem;">BIAYA LUNAS</span>
                <span class="badge-pastel badge-pastel-green">TERVALIDASI</span>
            </div>
            <div>
                <h3 class="font-serif-heading fs-2 text-dark mb-1">{{ $stats['paid'] ?? 0 }}</h3>
                <span class="text-secondary small">Akses formulir telah dibuka</span>
            </div>
        </div>
    </div>

    <!-- 4. Siswa Diterima -->
    <div class="col-sm-6 col-xl-3">
        <div class="bento-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="font-mono-meta text-secondary" style="font-size: 0.72rem;">CALON SISWA DITERIMA</span>
                <span class="badge-pastel badge-pastel-blue">SELEKSI AKHIR</span>
            </div>
            <div>
                <h3 class="font-serif-heading fs-2 text-dark mb-1">{{ $stats['accepted'] ?? 0 }}</h3>
                <span class="text-secondary small">Telah resmi diterima sekolah</span>
            </div>
        </div>
    </div>
</div>

<!-- Filter Toolbar -->
<div class="bento-card mb-4 p-3">
    <form method="GET" action="{{ route('admin.ppdb.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-secondary" style="border-color: var(--border-light) !important;">
                    <i class="ph-bold ph-magnifying-glass"></i>
                </span>
                <input type="text" 
                       class="form-control border-start-0 ps-0" 
                       name="search" 
                       value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Cari Nama, NIK, Asal Sekolah...">
            </div>
        </div>

        <div class="col-md-2">
            <select class="form-select" name="major">
                <option value="">-- Semua Jurusan --</option>
                <option value="reguler" {{ ($filters['major'] ?? '') === 'reguler' ? 'selected' : '' }}>Reguler</option>
                <option value="bahasa" {{ ($filters['major'] ?? '') === 'bahasa' ? 'selected' : '' }}>Bahasa</option>
                <option value="tahfidz" {{ ($filters['major'] ?? '') === 'tahfidz' ? 'selected' : '' }}>Tahfidz</option>
                <option value="ict" {{ ($filters['major'] ?? '') === 'ict' ? 'selected' : '' }}>ICT</option>
            </select>
        </div>

        <div class="col-md-3">
            <select class="form-select" name="payment_status">
                <option value="">-- Semua Pembayaran --</option>
                <option value="pending_verification" {{ ($filters['paymentStatus'] ?? '') === 'pending_verification' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="paid" {{ ($filters['paymentStatus'] ?? '') === 'paid' ? 'selected' : '' }}>Lunas (Tervalidasi)</option>
                <option value="unpaid" {{ ($filters['paymentStatus'] ?? '') === 'unpaid' ? 'selected' : '' }}>Belum Membayar</option>
                <option value="rejected" {{ ($filters['paymentStatus'] ?? '') === 'rejected' ? 'selected' : '' }}>Pembayaran Ditolak</option>
            </select>
        </div>

        <div class="col-md-2">
            <select class="form-select" name="registration_status">
                <option value="">-- Semua Status --</option>
                <option value="pending" {{ ($filters['registrationStatus'] ?? '') === 'pending' ? 'selected' : '' }}>Proses Seleksi</option>
                <option value="accepted" {{ ($filters['registrationStatus'] ?? '') === 'accepted' ? 'selected' : '' }}>Resmi Diterima</option>
                <option value="rejected" {{ ($filters['registrationStatus'] ?? '') === 'rejected' ? 'selected' : '' }}>Tidak Lolos</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-minimal-primary flex-grow-1" style="min-height: 38px;">
                <i class="ph-bold ph-funnel"></i> Filter
            </button>
            @if(!empty($filters['search']) || !empty($filters['paymentStatus']) || !empty($filters['registrationStatus']) || !empty($filters['major']))
                <a href="{{ route('admin.ppdb.index') }}" class="btn-minimal-secondary" style="min-height: 38px; padding: 0.5rem 0.75rem;" title="Reset Filter">
                    <i class="ph-bold ph-arrow-counter-clockwise"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Table Container Bento -->
<div class="bento-card p-0 overflow-hidden mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center p-3 border-bottom" style="border-color: var(--border-light) !important; background-color: var(--surface-bg);">
        <div>
            <h6 class="fw-semibold text-dark mb-0">Daftar Seluruh Calon Peserta Didik</h6>
            <span class="text-secondary small" style="font-size: 0.8rem;">Data sinkron real-time dengan basis data pendaftaran sekolah.</span>
        </div>
        <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
            <span class="badge-pastel badge-pastel-neutral font-mono-meta">
                {{ count($registrations) }} Pendaftar Ditemukan
            </span>
            <div class="dropdown">
                <button class="btn-minimal-secondary dropdown-toggle d-flex align-items-center gap-1 py-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-height: 32px; font-size: 0.82rem;">
                    <i class="ph-bold ph-microsoft-excel-logo text-success"></i>
                    <span>Export Data</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border: 1px solid var(--border-light) !important; min-width: 220px;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.ppdb.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}">
                            <i class="ph-bold ph-file-xls text-success fs-5"></i>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size: 0.85rem;">Microsoft Excel (.xlsx)</div>
                                <div class="text-secondary small" style="font-size: 0.75rem;">Format tabel resmi berstruktur</div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.ppdb.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                            <i class="ph-bold ph-file-csv text-primary fs-5"></i>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size: 0.85rem;">File CSV (.csv)</div>
                                <div class="text-secondary small" style="font-size: 0.75rem;">Format data UTF-8 untuk sistem</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table-document">
            <thead>
                <tr>
                    <th style="width: 48px; text-align: center;">No</th>
                    <th>Calon Siswa</th>
                    <th>Kontak Terdaftar</th>
                    <th>Status Pembayaran</th>
                    <th>Status Seleksi</th>
                    <th>Formulir</th>
                    <th>Tgl Registrasi</th>
                    <th style="text-align: right; width: 120px;">Aksi</th>
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
                        <td class="font-mono-meta text-secondary text-center" style="font-size: 0.82rem;">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong class="text-dark">{{ $acc['full_name'] ?? 'Calon Siswa' }}</strong>
                                @php
                                    $itemMajor = $reg['form_data']['major'] ?? ($reg['student']['major'] ?? null);
                                    $itemMajorBadge = match(strtolower((string)$itemMajor)) {
                                        'reguler' => ['label' => 'REGULER', 'class' => 'badge-pastel-neutral'],
                                        'bahasa' => ['label' => 'BAHASA', 'class' => 'badge-pastel-blue'],
                                        'tahfidz' => ['label' => 'TAHFIDZ', 'class' => 'badge-pastel-green'],
                                        'ict' => ['label' => 'ICT', 'class' => 'badge-pastel-yellow'],
                                        default => null
                                    };
                                @endphp
                                @if($itemMajorBadge)
                                    <span class="badge-pastel {{ $itemMajorBadge['class'] }} py-0 px-2" style="font-size: 0.68rem;">{{ $itemMajorBadge['label'] }}</span>
                                @endif
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 font-mono-meta text-secondary" style="font-size: 0.78rem;">
                                <span><i class="ph-bold ph-identification-card me-1"></i>{{ $acc['nik'] ?? '-' }}</span>
                                @if(!empty($reg['form_data']['school_origin']))
                                    <span>&bull;</span>
                                    <span class="text-truncate text-secondary" style="max-width: 160px;" title="{{ $reg['form_data']['school_origin'] }}">
                                        <i class="ph-bold ph-buildings me-1"></i>{{ $reg['form_data']['school_origin'] }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="text-dark d-block mb-1" style="font-size: 0.85rem;">{{ $acc['email'] ?? '-' }}</span>
                            <span class="font-mono-meta text-secondary" style="font-size: 0.78rem;">
                                <i class="ph-bold ph-clock me-1"></i>{{ date('d/m/Y H:i', strtotime($reg['created_at'])) }}
                            </span>
                        </td>
                        <td>
                            @if($pStatus === 'paid')
                                <span class="badge-pastel badge-pastel-green">
                                    <i class="ph-bold ph-check"></i> Lunas
                                </span>
                            @elseif($pStatus === 'pending_verification')
                                <span class="badge-pastel badge-pastel-yellow">
                                    <i class="ph-bold ph-hourglass"></i> Verifikasi
                                </span>
                            @elseif($pStatus === 'rejected')
                                <span class="badge-pastel badge-pastel-red">
                                    <i class="ph-bold ph-x"></i> Ditolak
                                </span>
                            @else
                                <span class="badge-pastel badge-pastel-neutral">
                                    Belum Bayar
                                </span>
                            @endif

                            @if($hasProof)
                                <a href="{{ $backendUrl . $reg['payment_proof_path'] }}" 
                                   target="_blank" 
                                   class="badge-pastel badge-pastel-neutral text-decoration-none ms-1" 
                                   title="Lihat Berkas Bukti Transfer">
                                    <i class="ph-bold ph-image"></i> Berkas
                                </a>
                            @endif
                        </td>
                        <td>
                            @if($rStatus === 'accepted')
                                <span class="badge-pastel badge-pastel-green">
                                    <i class="ph-bold ph-seal-check"></i> Diterima
                                </span>
                            @elseif($rStatus === 'rejected')
                                <span class="badge-pastel badge-pastel-red">
                                    <i class="ph-bold ph-x-circle"></i> Tidak Lolos
                                </span>
                            @else
                                <span class="badge-pastel badge-pastel-blue">
                                    Dalam Seleksi
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($hasForm)
                                <span class="badge-pastel badge-pastel-green">
                                    <i class="ph-bold ph-check"></i> Lengkap
                                </span>
                            @else
                                <span class="badge-pastel badge-pastel-neutral">
                                    Belum Diisi
                                </span>
                            @endif
                        </td>
                        <td class="font-mono-meta text-secondary" style="font-size: 0.8rem;">
                            {{ date('d M Y', strtotime($reg['created_at'])) }}
                        </td>
                        <td style="text-align: right;">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('admin.ppdb.show', $reg['id']) }}" 
                                   class="btn-minimal-secondary py-1 px-2" 
                                   style="font-size: 0.8rem; min-height: 32px;" 
                                   title="Tinjau Dossier Lengkap">
                                    <i class="ph-bold ph-eye"></i> Detail
                                </a>

                                <a href="{{ route('admin.ppdb.edit', $reg['id']) }}" 
                                   class="btn-minimal-secondary py-1 px-2" 
                                   style="font-size: 0.8rem; min-height: 32px;" 
                                   title="Edit Data Pendaftar">
                                    <i class="ph-bold ph-pencil-simple"></i> Edit
                                </a>

                                @if($hasProof && $pStatus !== 'paid')
                                    <button type="button" 
                                            class="btn-minimal-primary py-1 px-2" 
                                            style="font-size: 0.8rem; min-height: 32px;" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#verifyModal{{ $reg['id'] }}" 
                                            title="Verifikasi Pembayaran">
                                        <i class="ph-bold ph-check"></i>
                                    </button>
                                @endif

                                <button type="button" 
                                        class="btn-minimal-secondary text-danger py-1 px-2 border-danger-subtle" 
                                        style="font-size: 0.8rem; min-height: 32px;" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteIndexModal{{ $reg['id'] }}" 
                                        title="Hapus Data Pendaftar">
                                    <i class="ph-bold ph-trash"></i>
                                </button>
                            </div>

                            <!-- Modal Verifikasi Cepat -->
                            @if($hasProof && $pStatus !== 'paid')
                                <div class="modal fade" id="verifyModal{{ $reg['id'] }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content bento-card p-0 shadow-sm border" style="border-color: var(--border-light) !important;">
                                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="border-color: var(--border-light) !important;">
                                                <h6 class="fw-semibold text-dark mb-0">Verifikasi Pembayaran</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <form action="{{ route('admin.ppdb.verify-payment', $reg['id']) }}" method="POST">
                                                @csrf
                                                <div class="p-3 space-y-3">
                                                    <div class="mb-3">
                                                        <span class="text-secondary small d-block">Nama Calon Siswa:</span>
                                                        <strong class="text-dark">{{ $acc['full_name'] ?? '-' }}</strong>
                                                    </div>

                                                    <div class="mb-3">
                                                        <span class="text-secondary small d-block mb-1">Berkas Bukti Transfer:</span>
                                                        <a href="{{ $backendUrl . $reg['payment_proof_path'] }}" target="_blank" class="btn-minimal-secondary py-1 px-2 text-decoration-none" style="font-size: 0.82rem;">
                                                            <i class="ph-bold ph-arrow-square-out me-1"></i> Buka Berkas di Tab Baru
                                                        </a>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-dark mb-1">Status Pembayaran:</label>
                                                        <select class="form-select" name="payment_status" required>
                                                            <option value="paid">Setujui: LUNAS (PAID)</option>
                                                            <option value="rejected">Tolak: TIDAK VALID (REJECTED)</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold text-dark mb-1">Nominal Terverifikasi (Rp):</label>
                                                        <input type="number" class="form-control font-mono-meta" name="payment_amount" value="250000" step="1000" required>
                                                    </div>
                                                </div>
                                                <div class="p-3 border-top d-flex justify-content-end gap-2" style="border-color: var(--border-light) !important; background-color: var(--surface-muted);">
                                                    <button type="button" class="btn-minimal-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn-minimal-primary">Simpan Verifikasi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Modal Konfirmasi Hapus -->
                            <div class="modal fade" id="deleteIndexModal{{ $reg['id'] }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered text-start">
                                    <div class="modal-content bento-card p-0 shadow-sm border" style="border-color: var(--border-light) !important;">
                                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="border-color: var(--border-light) !important;">
                                            <div class="d-flex align-items-center gap-2 text-danger">
                                                <i class="ph-bold ph-warning-octagon fs-5"></i>
                                                <h6 class="fw-bold mb-0">Hapus Data Calon Siswa</h6>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="p-3">
                                            <p class="text-dark small mb-2">
                                                Apakah Anda yakin ingin menghapus data calon siswa ini secara permanen?
                                            </p>
                                            <div class="p-2 mb-2 rounded bg-light border">
                                                <strong class="text-dark d-block" style="font-size: 0.88rem;">{{ $acc['full_name'] ?? 'Calon Siswa' }}</strong>
                                                <span class="font-mono-meta text-secondary" style="font-size: 0.78rem;">NIK: {{ $acc['nik'] ?? '-' }}</span>
                                            </div>
                                            <span class="text-danger small" style="font-size: 0.78rem;">
                                                <i class="ph-bold ph-info me-1"></i> Data formulir dan akun pendaftaran akan dihapus permanen.
                                            </span>
                                        </div>
                                        <div class="p-3 border-top d-flex justify-content-end gap-2" style="border-color: var(--border-light) !important; background-color: var(--surface-muted);">
                                            <button type="button" class="btn-minimal-secondary py-1 px-3" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.ppdb.destroy', $reg['id']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm py-1 px-3 d-inline-flex align-items-center gap-1">
                                                    <i class="ph-bold ph-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">
                            <i class="ph-bold ph-tray fs-2 d-block mb-2 text-secondary"></i>
                            <span class="small">Tidak ada data calon siswa yang cocok dengan filter yang dipilih.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
