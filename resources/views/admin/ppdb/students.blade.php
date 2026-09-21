@extends('admin.layout')

@section('title', 'Data Siswa PPDB')
@section('header_title', 'Data Calon Peserta Didik Baru')

@section('content')
<!-- Filter Toolbar Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white p-3 p-md-4">
    <form method="GET" action="{{ route('admin.ppdb.students') }}" class="row g-2 align-items-center">
        <!-- Search -->
        <div class="col-12 col-md-4 col-lg-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <span class="material-symbols-outlined" style="font-size:18px;">search</span>
                </span>
                <input type="text" 
                       class="form-control border-start-0 ps-0" 
                       name="search" 
                       value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Cari Nama, NIK, Asal Sekolah...">
            </div>
        </div>

        <!-- Jurusan -->
        <div class="col-6 col-md-4 col-lg-2">
            <select class="form-select" name="major">
                <option value="">-- Semua Jurusan --</option>
                <option value="reguler" {{ ($filters['major'] ?? '') === 'reguler' ? 'selected' : '' }}>Reguler</option>
                <option value="bahasa" {{ ($filters['major'] ?? '') === 'bahasa' ? 'selected' : '' }}>Bahasa</option>
                <option value="tahfidz" {{ ($filters['major'] ?? '') === 'tahfidz' ? 'selected' : '' }}>Tahfidz</option>
                <option value="ict" {{ ($filters['major'] ?? '') === 'ict' ? 'selected' : '' }}>ICT (IT)</option>
            </select>
        </div>

        <!-- Status Pembayaran -->
        <div class="col-6 col-md-4 col-lg-3">
            <select class="form-select" name="payment_status">
                <option value="">-- Semua Pembayaran --</option>
                <option value="pending_verification" {{ ($filters['paymentStatus'] ?? '') === 'pending_verification' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="paid" {{ ($filters['paymentStatus'] ?? '') === 'paid' ? 'selected' : '' }}>Lunas (Tervalidasi)</option>
                <option value="unpaid" {{ ($filters['paymentStatus'] ?? '') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                <option value="rejected" {{ ($filters['paymentStatus'] ?? '') === 'rejected' ? 'selected' : '' }}>Pembayaran Ditolak</option>
            </select>
        </div>

        <!-- Status Seleksi -->
        <div class="col-6 col-md-6 col-lg-2">
            <select class="form-select" name="registration_status">
                <option value="">-- Status Seleksi --</option>
                <option value="pending" {{ ($filters['registrationStatus'] ?? '') === 'pending' ? 'selected' : '' }}>Dalam Seleksi</option>
                <option value="accepted" {{ ($filters['registrationStatus'] ?? '') === 'accepted' ? 'selected' : '' }}>Resmi Diterima</option>
                <option value="rejected" {{ ($filters['registrationStatus'] ?? '') === 'rejected' ? 'selected' : '' }}>Tidak Lolos</option>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="col-6 col-md-6 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1 fw-semibold">
                <span class="material-symbols-outlined" style="font-size:18px;">filter_alt</span>
                <span>Filter</span>
            </button>
            @if(!empty($filters['search']) || !empty($filters['major']) || !empty($filters['paymentStatus']) || !empty($filters['registrationStatus']))
                <a href="{{ route('admin.ppdb.students') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-2" title="Reset Filter">
                    <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill" style="font-size: 12px;">
                {{ count($students) }} Siswa Ditemukan
            </span>
            <span class="text-muted small d-none d-sm-inline">Daftar pendaftar terdaftar di sistem PPDB</span>
        </div>

        <div class="dropdown">
            <button class="btn btn-sm btn-outline-success dropdown-toggle d-inline-flex align-items-center gap-1 fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-symbols-outlined" style="font-size:18px;">download</span>
                <span>Ekspor Data</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.ppdb.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}">
                        <span class="material-symbols-outlined text-success">table_chart</span>
                        <div>
                            <div class="fw-semibold text-dark" style="font-size: 13px;">Microsoft Excel (.xlsx)</div>
                            <div class="text-muted small" style="font-size: 11px;">Format spreadsheet resmi berstruktur</div>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.ppdb.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                        <span class="material-symbols-outlined text-primary">description</span>
                        <div>
                            <div class="fw-semibold text-dark" style="font-size: 13px;">File CSV (.csv)</div>
                            <div class="text-muted small" style="font-size: 11px;">Format UTF-8 untuk integrasi sistem</div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
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
                    <th>Formulir</th>
                    <th>Tgl Daftar</th>
                    <th style="text-align: right; width: 140px;" class="pe-3 pe-md-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $reg)
                    @php
                        $pStatus = $reg['payment_status'] ?? 'unpaid';
                        $rStatus = $reg['registration_status'] ?? 'pending';
                        $acc = $reg['account'] ?? [];
                        $form = $reg['form_data'] ?? [];
                        $major = $form['major'] ?? ($reg['student']['major'] ?? null);
                        $hasProof = !empty($reg['payment_proof_path']);
                        $hasForm = !empty($form);
                    @endphp
                    <tr>
                        <td class="ps-3 ps-md-4 text-center text-muted fw-semibold">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $acc['full_name'] ?? ($form['full_name'] ?? 'Calon Siswa') }}</div>
                            <div class="text-muted small d-flex flex-wrap align-items-center gap-1">
                                <span>NIK: {{ $acc['nik'] ?? '-' }}</span>
                                @if(!empty($form['school_origin']))
                                    <span>&bull;</span>
                                    <span class="text-truncate" style="max-width: 170px;" title="{{ $form['school_origin'] }}">{{ $form['school_origin'] }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($major)
                                <span class="badge bg-light text-primary border text-uppercase fw-semibold py-1 px-2" style="font-size: 11px;">
                                    {{ $major }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border py-1 px-2" style="font-size: 11px;">
                                    Belum Pilih
                                </span>
                            @endif
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

                            @if($hasProof)
                                <a href="{{ $backendUrl . $reg['payment_proof_path'] }}" target="_blank" class="badge bg-light text-secondary border text-decoration-none ms-1" title="Lihat Bukti Transfer">
                                    <span class="material-symbols-outlined align-middle" style="font-size:12px;">image</span>
                                </a>
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
                        <td>
                            @if($hasForm)
                                <span class="badge bg-success-subtle text-success py-1 px-2">Lengkap</span>
                            @else
                                <span class="badge bg-light text-muted border py-1 px-2">Belum Lengkap</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ date('d/m/Y', strtotime($reg['created_at'] ?? 'now')) }}
                        </td>
                        <td class="pe-3 pe-md-4 text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                <a href="{{ route('admin.ppdb.show', $reg['id']) }}" class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold" style="font-size: 12px;" title="Lihat Detail Dossier">
                                    Detail
                                </a>
                                <a href="{{ route('admin.ppdb.edit', $reg['id']) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 12px;" title="Edit Data Siswa">
                                    Edit
                                </a>

                                @if($hasProof && $pStatus !== 'paid')
                                    <button type="button" class="btn btn-sm btn-warning text-dark py-1 px-2" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $reg['id'] }}" title="Verifikasi Bukti">
                                        <span class="material-symbols-outlined align-middle" style="font-size:16px;">verified</span>
                                    </button>
                                @endif

                                <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $reg['id'] }}" title="Hapus Data">
                                    <span class="material-symbols-outlined align-middle" style="font-size:16px;">delete</span>
                                </button>
                            </div>

                            <!-- Modal Verifikasi Cepat -->
                            @if($hasProof && $pStatus !== 'paid')
                                <div class="modal fade" id="verifyModal{{ $reg['id'] }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 shadow rounded-3">
                                            <div class="modal-header border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <span class="material-symbols-outlined text-warning">verified</span>
                                                    Verifikasi Bukti Pembayaran
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <form action="{{ route('admin.ppdb.verify-payment', $reg['id']) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-3 p-md-4">
                                                    <div class="p-3 bg-light rounded-3 mb-3 border">
                                                        <div class="fw-bold text-dark mb-1">{{ $acc['full_name'] ?? '-' }}</div>
                                                        <div class="text-muted small">NIK: {{ $acc['nik'] ?? '-' }} &bull; Email: {{ $acc['email'] ?? '-' }}</div>
                                                    </div>

                                                    <div class="mb-3 text-center">
                                                        <a href="{{ $backendUrl . $reg['payment_proof_path'] }}" target="_blank" class="d-inline-block border rounded-3 p-1 bg-light">
                                                            <img src="{{ $backendUrl . $reg['payment_proof_path'] }}" alt="Bukti Transfer" style="max-height: 180px; max-width: 100%; object-fit: contain;" class="rounded">
                                                        </a>
                                                        <div class="mt-1">
                                                            <a href="{{ $backendUrl . $reg['payment_proof_path'] }}" target="_blank" class="small fw-semibold text-primary text-decoration-none">
                                                                Perbesar Bukti Transfer &rarr;
                                                            </a>
                                                        </div>
                                                    </div>

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
                            @endif

                            <!-- Modal Konfirmasi Hapus -->
                            <div class="modal fade" id="deleteModal{{ $reg['id'] }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered text-start">
                                    <div class="modal-content border-0 shadow rounded-3">
                                        <div class="modal-header border-bottom py-3">
                                            <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined">warning</span>
                                                Hapus Data Calon Siswa
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body p-3 p-md-4">
                                            <p class="text-dark small mb-2">
                                                Apakah Anda yakin ingin menghapus data calon siswa ini secara permanen dari sistem PPDB?
                                            </p>
                                            <div class="p-3 bg-light rounded-3 mb-2 border">
                                                <div class="fw-bold text-dark">{{ $acc['full_name'] ?? 'Calon Siswa' }}</div>
                                                <div class="text-muted small">NIK: {{ $acc['nik'] ?? '-' }}</div>
                                            </div>
                                            <span class="text-danger small">
                                                Data akun, berkas formulir, dan histori pembayaran akan dihapus permanen.
                                            </span>
                                        </div>
                                        <div class="modal-footer border-top bg-light py-2">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.ppdb.destroy', $reg['id']) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm fw-semibold">Ya, Hapus Permanen</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 36px;">search_off</span>
                            <div class="small">Tidak ada data calon siswa yang cocok dengan filter yang dipilih.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
