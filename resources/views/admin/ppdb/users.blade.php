@extends('admin.layout')

@section('title', 'Data User PPDB')
@section('header_title', 'Data Akun Calon Siswa & Orang Tua')

@section('content')

<!-- Hasil Rekap Import (Jika Baru Saja Melakukan Import) -->
@if(session('import_summary'))
    @php
        $summary = session('import_summary');
        $successCount = $summary['success_count'] ?? 0;
        $failedCount = $summary['failed_count'] ?? 0;
        $failedList = $summary['failed_list'] ?? [];
    @endphp
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden border-start border-4 {{ $failedCount > 0 ? 'border-warning' : 'border-success' }}">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined {{ $failedCount > 0 ? 'text-warning' : 'text-success' }}" style="font-size:24px;">
                        {{ $failedCount > 0 ? 'report' : 'check_circle' }}
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">Laporan Hasil Import Berkas Excel / CSV</h6>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1">
                        {{ $successCount }} Berhasil
                    </span>
                    @if($failedCount > 0)
                        <span class="badge bg-danger-subtle text-danger fw-semibold px-2 py-1">
                            {{ $failedCount }} Gagal / Dilewati
                        </span>
                    @endif
                </div>
            </div>
            <p class="text-muted small mb-0">
                Sistem telah selesai memproses {{ $summary['total_rows'] ?? 0 }} baris dari berkas yang diunggah.
            </p>

            @if(!empty($failedList))
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFailedRows">
                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span>
                        <span>Lihat Rincian Baris yang Gagal ({{ count($failedList) }})</span>
                    </button>
                    <div class="collapse mt-2" id="collapseFailedRows">
                        <div class="table-responsive rounded border bg-light">
                            <table class="table table-sm table-hover mb-0" style="font-size:12px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;" class="text-center">Baris</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Penyebab Kegagalan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($failedList as $fail)
                                        <tr>
                                            <td class="text-center fw-bold text-danger">{{ $fail['row'] }}</td>
                                            <td class="fw-semibold">{{ $fail['name'] }}</td>
                                            <td>{{ $fail['email'] }}</td>
                                            <td class="text-danger">{{ $fail['reason'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

<!-- 1. Ringkasan Metrik Data User (Sleek Metric Strip) -->
<div class="card border-0 shadow-sm rounded-3 bg-white mb-4 overflow-hidden">
    <div class="row g-0">
        <!-- Total Akun -->
        <div class="col-6 col-lg-3 p-3 p-md-4 border-end border-bottom border-lg-bottom-0">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 11px;">Total Akun</span>
                <span class="material-symbols-outlined text-primary" style="font-size: 20px;">groups</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">{{ $stats['total'] ?? count($users) }}</h3>
                <span class="text-muted small">pendaftar</span>
            </div>
            <div class="text-muted small" style="font-size: 12px;">
                Memiliki akses login portal
            </div>
        </div>

        <!-- Pembayaran Lunas -->
        <div class="col-6 col-lg-3 p-3 p-md-4 border-end-lg border-bottom border-lg-bottom-0">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 11px;">Biaya Lunas</span>
                <span class="material-symbols-outlined text-success" style="font-size: 20px;">verified</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">{{ $stats['paid'] ?? 0 }}</h3>
                <span class="badge bg-success-subtle text-success fw-semibold" style="font-size: 10px;">TERVALIDASI</span>
            </div>
            <div class="text-muted small" style="font-size: 12px;">
                Biaya registrasi Rp 400.000
            </div>
        </div>

        <!-- Belum Bayar -->
        <div class="col-6 col-lg-3 p-3 p-md-4 border-end">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 11px;">Belum Bayar</span>
                <span class="material-symbols-outlined text-warning" style="font-size: 20px;">hourglass_top</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">{{ $stats['unpaid'] ?? 0 }}</h3>
                <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold" style="font-size: 10px;">PENDING</span>
            </div>
            <div class="text-muted small" style="font-size: 12px;">
                Menunggu transfer/verifikasi
            </div>
        </div>

        <!-- Formulir Lengkap -->
        <div class="col-6 col-lg-3 p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 11px;">Formulir Terisi</span>
                <span class="material-symbols-outlined text-info" style="font-size: 20px;">assignment_turned_in</span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <h3 class="fw-bold text-dark mb-0">{{ $stats['has_form'] ?? 0 }}</h3>
                <span class="badge bg-info-subtle text-info fw-semibold" style="font-size: 10px;">LENGKAP</span>
            </div>
            <div class="text-muted small" style="font-size: 12px;">
                Biodata & berkas lengkap
            </div>
        </div>
    </div>
</div>

<!-- 2. Filter & Toolbar Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white p-3 p-md-4">
    <div class="row g-3 align-items-center justify-content-between">
        <!-- Form Filter -->
        <div class="col-lg-7">
            <form method="GET" action="{{ route('admin.ppdb.users') }}" class="row g-2 align-items-center">
                <!-- Search Input -->
                <div class="col-12 col-sm-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <span class="material-symbols-outlined" style="font-size:18px;">search</span>
                        </span>
                        <input type="text" 
                               class="form-control border-start-0 ps-0" 
                               name="search" 
                               value="{{ $filters['search'] ?? '' }}" 
                               placeholder="Cari Nama, Email, NIK, No. WhatsApp...">
                    </div>
                </div>

                <!-- Status Select -->
                <div class="col-8 col-sm-3">
                    <select class="form-select" name="status">
                        <option value="">-- Semua Status --</option>
                        <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="has_form" {{ ($filters['status'] ?? '') === 'has_form' ? 'selected' : '' }}>Sudah Isi Formulir</option>
                    </select>
                </div>

                <!-- Action Filter Buttons -->
                <div class="col-4 col-sm-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center" title="Terapkan Filter">
                        <span class="material-symbols-outlined" style="font-size:18px;">filter_alt</span>
                    </button>
                    @if(!empty($filters['search']) || !empty($filters['status']))
                        <a href="{{ route('admin.ppdb.users') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center" title="Reset Filter">
                            <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tombol Tambah & Import User -->
        <div class="col-lg-5 d-flex justify-content-lg-end gap-2 flex-wrap">
            <form action="{{ route('admin.ppdb.sync') }}" method="POST" class="d-inline mb-0">
                @csrf
                <button type="submit" class="btn btn-success fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3" onclick="return confirm('Kirim seluruh data lokal yang belum tersinkron ke Master Data API?')">
                    <span class="material-symbols-outlined" style="font-size:18px;">sync</span>
                    <span>Sinkron ke Master API</span>
                </button>
            </form>
            <button type="button" 
                    class="btn btn-outline-success fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3" 
                    data-bs-toggle="modal" 
                    data-bs-target="#modalImportExcel">
                <span class="material-symbols-outlined" style="font-size:18px;">upload_file</span>
                <span>Import Excel / CSV</span>
            </button>
            <button type="button" 
                    class="btn btn-primary fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3" 
                    data-bs-toggle="modal" 
                    data-bs-target="#modalAddUser">
                <span class="material-symbols-outlined" style="font-size:18px;">person_add</span>
                <span>Tambah User</span>
            </button>
        </div>
    </div>
</div>

<!-- 3. Table Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill" style="font-size: 12px;">
                {{ count($users) }} Data Akun Ditampilkan
            </span>
            <span class="text-muted small d-none d-md-inline">Seluruh akun memiliki akses masuk ke portal pendaftaran siswa</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
            <thead class="table-light">
                <tr>
                    <th style="width: 48px;" class="ps-3 ps-md-4 text-center text-muted">#</th>
                    <th style="min-width: 220px;">Calon Siswa</th>
                    <th style="min-width: 180px;">Email Login</th>
                    <th style="min-width: 150px;">NIK Identitas</th>
                    <th style="min-width: 150px;">Kontak WhatsApp</th>
                    <th style="min-width: 130px;">Status Bayar</th>
                    <th style="min-width: 110px;">Formulir</th>
                    <th style="min-width: 110px;">Sync Master</th>
                    <th style="min-width: 130px;">Terdaftar</th>
                    <th style="width: 140px; text-align: right;" class="pe-3 pe-md-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $u)
                    @php
                        // Format nomor WA untuk link wa.me
                        $rawPhone = $u['phone'] ?? '';
                        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                        if (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '62' . substr($cleanPhone, 1);
                        }
                    @endphp
                    <tr>
                        <td class="ps-3 ps-md-4 text-center text-muted fw-semibold">
                            {{ $index + 1 }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold small flex-shrink-0 shadow-sm" style="width: 36px; height: 36px; background: linear-gradient(135deg, #005ab4, #003366); font-size: 13px;">
                                    {{ strtoupper(substr($u['full_name'], 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $u['full_name'] }}</div>
                                    <span class="text-muted" style="font-size: 11.5px;">Calon Siswa</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-dark fw-medium">{{ $u['email'] }}</div>
                            <span class="text-muted" style="font-size: 11px;">Username login</span>
                        </td>
                        <td>
                            @if(!empty($u['nik']) && $u['nik'] !== '-')
                                <code class="text-secondary fw-semibold bg-light px-2 py-1 rounded border" style="font-size: 12px; letter-spacing: 0.02em;">
                                    {{ $u['nik'] }}
                                </code>
                            @else
                                <span class="text-muted small fst-italic">Belum diisi</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($cleanPhone))
                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 py-1 px-2 rounded-pill" style="font-size: 12px;" title="Kirim Pesan WhatsApp">
                                    <span class="material-symbols-outlined" style="font-size:15px;">chat</span>
                                    <span>{{ $u['phone'] }}</span>
                                </a>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 11px;">Belum diisi</span>
                            @endif
                        </td>
                        <td>
                            @if($u['payment_status'] === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 d-inline-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                                    <span>Lunas</span>
                                </span>
                            @elseif($u['payment_status'] === 'pending_verification')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 d-inline-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size:14px;">hourglass_top</span>
                                    <span>Verifikasi</span>
                                </span>
                            @elseif($u['payment_status'] === 'rejected')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 d-inline-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size:14px;">cancel</span>
                                    <span>Ditolak</span>
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 d-inline-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size:14px;">pending</span>
                                    <span>Belum Bayar</span>
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($u['has_form'])
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">Lengkap</span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1">Kosong</span>
                            @endif
                        </td>
                        <td>
                            @if(($u['sync_status'] ?? 'pending') === 'synced')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 d-inline-flex align-items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size:14px;">cloud_done</span>
                                    <span>Tersinkron</span>
                                </span>
                            @elseif(($u['sync_status'] ?? 'pending') === 'failed')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 d-inline-flex align-items-center gap-1" title="Gagal terhubung ke API">
                                    <span class="material-symbols-outlined" style="font-size:14px;">error</span>
                                    <span>Gagal</span>
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 d-inline-flex align-items-center gap-1" title="Tersimpan di MySQL lokal, siap dikirim ke Master API">
                                    <span class="material-symbols-outlined" style="font-size:14px;">schedule</span>
                                    <span>Pending</span>
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="text-dark small">{{ date('d M Y', strtotime($u['created_at'])) }}</div>
                            <span class="text-muted" style="font-size: 11px;">{{ date('H:i', strtotime($u['created_at'])) }} WIB</span>
                        </td>
                        <td class="pe-3 pe-md-4 text-end">
                            <div class="d-inline-flex gap-1">
                                <!-- Tombol Edit User -->
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary p-1 d-inline-flex align-items-center justify-content-center" 
                                        style="width: 32px; height: 32px;"
                                        title="Edit Akun User"
                                        onclick="openEditUserModal({
                                            id: '{{ $u['registration_id'] }}',
                                            name: '{{ addslashes($u['full_name']) }}',
                                            email: '{{ addslashes($u['email']) }}',
                                            nik: '{{ addslashes($u['nik']) }}',
                                            phone: '{{ addslashes($u['phone'] ?? '') }}',
                                            payment: '{{ $u['payment_status'] }}'
                                        })">
                                    <span class="material-symbols-outlined" style="font-size:17px;">edit</span>
                                </button>

                                <!-- Tombol Lihat Dossier -->
                                @if(!empty($u['registration_id']))
                                    <a href="{{ route('admin.ppdb.show', $u['registration_id']) }}" 
                                       class="btn btn-sm btn-outline-secondary p-1 d-inline-flex align-items-center justify-content-center" 
                                       style="width: 32px; height: 32px;"
                                       title="Lihat Dossier Lengkap">
                                        <span class="material-symbols-outlined" style="font-size:17px;">visibility</span>
                                    </a>
                                @endif

                                <!-- Tombol Hapus User -->
                                @if(!empty($u['registration_id']))
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger p-1 d-inline-flex align-items-center justify-content-center" 
                                            style="width: 32px; height: 32px;"
                                            title="Hapus Akun User"
                                            onclick="openDeleteUserModal('{{ $u['registration_id'] }}', '{{ addslashes($u['full_name']) }}', '{{ addslashes($u['email']) }}')">
                                        <span class="material-symbols-outlined" style="font-size:17px;">delete</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 42px;">person_search</span>
                            <h6 class="fw-bold text-dark mb-1">Data Akun Tidak Ditemukan</h6>
                            <p class="small text-muted mb-3">Tidak ada akun calon siswa yang sesuai dengan filter atau kata kunci pencarian Anda.</p>
                            @if(!empty($filters['search']) || !empty($filters['status']))
                                <a href="{{ route('admin.ppdb.users') }}" class="btn btn-sm btn-outline-primary">
                                    Reset Seluruh Filter
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================
     MODAL 1: TAMBAH USER MANUAL
     ======================================================== -->
<div class="modal fade" id="modalAddUser" tabindex="-1" aria-labelledby="modalAddUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center">
                        <span class="material-symbols-outlined" style="font-size: 20px;">person_add</span>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark" id="modalAddUserLabel">Tambah Akun Calon Siswa</h6>
                        <span class="text-muted small">Registrasi manual pendaftar baru oleh admin</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('admin.ppdb.users.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               name="full_name" 
                               placeholder="Contoh: Muhammad Rizky Pratama" 
                               required>
                    </div>

                    <!-- NIK -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">NIK Calon Siswa (16 Digit)</label>
                        <input type="text" 
                               class="form-control" 
                               name="nik" 
                               maxlength="16" 
                               placeholder="Contoh: 3201012345670001">
                        <div class="form-text">Masukkan 16 digit NIK pendaftar jika sudah tersedia.</div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Alamat Email Login <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control" 
                               name="email" 
                               placeholder="contoh: rizky@gmail.com" 
                               required>
                        <div class="form-text">Digunakan oleh siswa/orang tua untuk masuk ke portal.</div>
                    </div>

                    <!-- No. Telepon / WhatsApp -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">No. WhatsApp / Telepon</label>
                        <input type="text" 
                               class="form-control" 
                               name="phone" 
                               placeholder="contoh: 081234567890">
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold small text-dark mb-0">Password Akun <span class="text-danger">*</span></label>
                            <button type="button" 
                                    class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" 
                                    style="font-size: 11.5px;"
                                    onclick="generateRandomPassword('inputAddUserPassword')">
                                &circlearrowright; Acak Password
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   name="password" 
                                   id="inputAddUserPassword" 
                                   value="Siswa2026!" 
                                   required 
                                   minlength="6">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('inputAddUserPassword', 'iconPasswordEyeAdd')">
                                <span class="material-symbols-outlined" id="iconPasswordEyeAdd" style="font-size: 18px;">visibility</span>
                            </button>
                        </div>
                        <div class="form-text">Minimal 6 karakter. Default: <code>Siswa2026!</code></div>
                    </div>

                    <!-- Centang Langsung Lunas -->
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="mark_as_paid" value="1" id="checkMarkPaid">
                            <label class="form-check-label fw-semibold small text-dark" for="checkMarkPaid">
                                Tandai Biaya Pendaftaran Lunas Langsung (Rp 400.000)
                            </label>
                            <div class="text-muted small mt-1" style="font-size: 11.5px;">
                                Centang opsi ini jika calon siswa telah membayar tunai/offline di loket panitia sekolah.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold d-inline-flex align-items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check</span>
                        <span>Simpan Akun Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL 2: EDIT USER (QUICK EDIT)
     ======================================================== -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center">
                        <span class="material-symbols-outlined" style="font-size: 20px;">edit</span>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark" id="modalEditUserLabel">Edit Akun Calon Siswa</h6>
                        <span class="text-muted small">Perbarui data login & kontak pendaftar</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditUser" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               name="full_name" 
                               id="editUserName" 
                               required>
                    </div>

                    <!-- NIK -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">NIK Calon Siswa (16 Digit)</label>
                        <input type="text" 
                               class="form-control" 
                               name="nik" 
                               id="editUserNik" 
                               maxlength="16">
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Alamat Email Login <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control" 
                               name="email" 
                               id="editUserEmail" 
                               required>
                    </div>

                    <!-- No. Telepon / WhatsApp -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">No. WhatsApp / Telepon</label>
                        <input type="text" 
                               class="form-control" 
                               name="phone" 
                               id="editUserPhone" 
                               placeholder="contoh: 081234567890">
                    </div>

                    <!-- Status Pembayaran -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Status Biaya Pendaftaran <span class="text-danger">*</span></label>
                        <select class="form-select" name="payment_status" id="editUserPayment" required>
                            <option value="unpaid">Belum Membayar (Unpaid)</option>
                            <option value="pending_verification">Perlu Verifikasi Bukti Transfer</option>
                            <option value="paid">Lunas (Rp 400.000 Tervalidasi)</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>

                    <!-- Reset / Ubah Password (Opsional) -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold small text-dark mb-0">
                                Password Baru <span class="text-muted fw-normal">(Opsional)</span>
                            </label>
                            <button type="button" 
                                    class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold text-primary" 
                                    style="font-size: 11.5px;"
                                    onclick="generateRandomPassword('editUserPassword')">
                                &circlearrowright; Acak Password
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   name="password" 
                                   id="editUserPassword" 
                                   placeholder="Kosongkan jika tidak ingin mengubah password" 
                                   minlength="6">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('editUserPassword', 'iconPasswordEyeEdit')">
                                <span class="material-symbols-outlined" id="iconPasswordEyeEdit" style="font-size: 18px;">visibility</span>
                            </button>
                        </div>
                        <div class="form-text">Minimal 6 karakter. Kosongkan jika password tidak diubah.</div>
                    </div>

                    <!-- Link to Full Registration Edit -->
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small text-muted">Ingin ubah data akademik, asal sekolah & orang tua?</span>
                            <a href="#" id="linkEditFullRegistration" class="btn btn-sm btn-link text-primary fw-semibold p-0 text-decoration-none">
                                Edit Lengkap &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold d-inline-flex align-items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL 3: HAPUS USER (KONFIRMASI DELETE)
     ======================================================== -->
<div class="modal fade" id="modalDeleteUser" tabindex="-1" aria-labelledby="modalDeleteUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger-subtle">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-danger" style="font-size: 24px;">warning</span>
                    <h6 class="modal-title fw-bold text-danger mb-0" id="modalDeleteUserLabel">Konfirmasi Hapus Akun</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formDeleteUser" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <span class="material-symbols-outlined" style="font-size: 32px;">delete_forever</span>
                        </div>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Hapus Akun Calon Siswa Ini?</h6>
                    <p class="text-muted small mb-3">
                        Anda akan menghapus akun atas nama <strong class="text-dark" id="deleteUserName"></strong> (<span id="deleteUserEmail"></span>).
                    </p>
                    <div class="alert alert-danger p-2 small mb-0 text-start">
                        <strong>Perhatian:</strong> Tindakan ini permanen. Seluruh data biodata formulir, bukti pembayaran, dan akses akun pendaftar akan dihapus secara permanen dari sistem PPDB.
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-semibold d-inline-flex align-items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                        <span>Ya, Hapus Akun Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL 4: IMPORT USER VIA EXCEL / CSV
     ======================================================== -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center">
                        <span class="material-symbols-outlined" style="font-size: 20px;">upload_file</span>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark" id="modalImportExcelLabel">Import Akun Calon Siswa</h6>
                        <span class="text-muted small">Unggah berkas spreadsheet Excel (.xlsx) atau CSV</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('admin.ppdb.users.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <!-- Template Download Section -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small text-dark">Gunakan Template Standar</span>
                            <span class="badge bg-primary-subtle text-primary">Rekomendasi</span>
                        </div>
                        <p class="text-muted small mb-2" style="font-size: 12px;">
                            Pastikan susunan kolom berkas sesuai template agar data dapat terbaca dengan sempurna.
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.ppdb.users.template', ['format' => 'xlsx']) }}" 
                               class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 fw-semibold">
                                <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                                <span>Unduh Template Excel (.xlsx)</span>
                            </a>
                            <a href="{{ route('admin.ppdb.users.template', ['format' => 'csv']) }}" 
                               class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 fw-semibold">
                                <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                                <span>Unduh CSV (.csv)</span>
                            </a>
                        </div>
                    </div>

                    <!-- File Input -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Pilih Berkas Spreadsheet <span class="text-danger">*</span></label>
                        <input type="file" 
                               class="form-control" 
                               name="file" 
                               accept=".xlsx, .xls, .csv, .txt" 
                               required>
                        <div class="form-text">Format yang didukung: .xlsx, .xls, .csv (Maksimal 5MB).</div>
                    </div>

                    <!-- Default Password -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Password Default (Jika Kolom Password Kosong)</label>
                        <input type="text" 
                               class="form-control" 
                               name="default_password" 
                               value="Ppdb2026!">
                        <div class="form-text">Akan digunakan secara otomatis bila pendaftar di file belum memiliki kata sandi.</div>
                    </div>

                    <!-- Centang Otomatis Lunas Semua -->
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="mark_all_paid" value="1" id="checkMarkAllPaid">
                            <label class="form-check-label fw-semibold small text-dark" for="checkMarkAllPaid">
                                Tandai Seluruh Pendaftar yang Diimpor Sebagai Lunas (Rp 400.000)
                            </label>
                            <div class="text-muted small mt-1" style="font-size: 11.5px;">
                                Aktifkan jika mengimpor data kolektif gelombang khusus atau siswa mitra yang sudah melunasi biaya pendaftaran.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-semibold d-inline-flex align-items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 18px;">cloud_upload</span>
                        <span>Unggah & Mulai Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi Generate Password Acak
function generateRandomPassword(elementId) {
    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
    let pass = 'Siswa';
    for (let i = 0; i < 4; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    pass += '!';
    document.getElementById(elementId).value = pass;
}

// Fungsi Show / Hide Password
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerText = 'visibility';
    } else {
        input.type = 'password';
        icon.innerText = 'visibility_off';
    }
}

// Buka Modal Edit User
function openEditUserModal(user) {
    const baseUrl = "{{ url('admin/ppdb/users') }}";
    const editForm = document.getElementById('formEditUser');
    editForm.action = `${baseUrl}/${user.id}`;

    document.getElementById('editUserName').value = user.name || '';
    document.getElementById('editUserEmail').value = user.email || '';
    document.getElementById('editUserNik').value = user.nik && user.nik !== '-' ? user.nik : '';
    document.getElementById('editUserPhone').value = user.phone || '';
    document.getElementById('editUserPayment').value = user.payment || 'unpaid';
    
    // Reset password field
    const passInput = document.getElementById('editUserPassword');
    if (passInput) {
        passInput.value = '';
        passInput.type = 'password';
        const eyeIcon = document.getElementById('iconPasswordEyeEdit');
        if (eyeIcon) eyeIcon.innerText = 'visibility';
    }

    const fullEditLink = document.getElementById('linkEditFullRegistration');
    fullEditLink.href = "{{ url('admin/ppdb/registrations') }}/" + user.id + "/edit";

    const modal = new bootstrap.Modal(document.getElementById('modalEditUser'));
    modal.show();
}

// Buka Modal Delete User
function openDeleteUserModal(id, name, email) {
    const baseUrl = "{{ url('admin/ppdb/users') }}";
    const deleteForm = document.getElementById('formDeleteUser');
    deleteForm.action = `${baseUrl}/${id}`;

    document.getElementById('deleteUserName').innerText = name;
    document.getElementById('deleteUserEmail').innerText = email;

    const modal = new bootstrap.Modal(document.getElementById('modalDeleteUser'));
    modal.show();
}
</script>
@endsection
