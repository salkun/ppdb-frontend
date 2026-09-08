@extends('admin.layout')

@section('title', 'Detail Pendaftar - ' . ($account['full_name'] ?? 'Calon Siswa'))
@section('header_title', 'Dossier Pendaftar & Verifikasi Berkas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.ppdb.index') }}" class="text-decoration-none">Daftar Pendaftar</a></li>
                <li class="breadcrumb-item active">{{ $account['full_name'] ?? 'Detail' }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Dossier Lengkap Calon Peserta Didik</h4>
    </div>
    <a href="{{ route('admin.ppdb.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
    </a>
</div>

@php
    $pStatus = $registration['payment_status'] ?? 'unpaid';
    $rStatus = $registration['registration_status'] ?? 'pending';
    $paymentProof = $registration['payment_proof_path'] ?? null;
    $hasProof = !empty($paymentProof);
    $hasForm = !empty($formData);

    // Extract parents
    $father = null;
    $mother = null;
    $guardian = null;
    if (!empty($formData['student_parents']) && is_array($formData['student_parents'])) {
        foreach ($formData['student_parents'] as $sp) {
            $relType = $sp['relationship_type'] ?? null;
            if ($relType == 1) $father = $sp['parent'] ?? [];
            if ($relType == 2) $mother = $sp['parent'] ?? [];
            if ($relType == 3) $guardian = $sp['parent'] ?? [];
        }
    }
@endphp

<div class="row g-4">
    <!-- Kolom Kiri: Verifikasi Pembayaran & Aksi Penerimaan -->
    <div class="col-lg-4">
        <!-- Card 1: Bukti Pembayaran -->
        <div class="card card-custom mb-4">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-cash-stack text-success me-2"></i>Verifikasi Pembayaran
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <span class="text-muted small d-block">Status Pembayaran Saat Ini:</span>
                    @if($pStatus === 'paid')
                        <span class="badge bg-success fs-6 px-3 py-1 mt-1"><i class="bi bi-check-circle-fill me-1"></i> LUNAS (PAID)</span>
                    @elseif($pStatus === 'pending_verification')
                        <span class="badge bg-warning text-dark fs-6 px-3 py-1 mt-1"><i class="bi bi-clock-history me-1"></i> PERLU VERIFIKASI</span>
                    @elseif($pStatus === 'rejected')
                        <span class="badge bg-danger fs-6 px-3 py-1 mt-1"><i class="bi bi-x-circle-fill me-1"></i> DITOLAK (REJECTED)</span>
                    @else
                        <span class="badge bg-secondary fs-6 px-3 py-1 mt-1">BELUM MEMBAYAR</span>
                    @endif
                </div>

                @if($hasProof)
                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">Pratinjau Berkas Bukti Transfer:</span>
                        <div class="p-2 border rounded-3 bg-light text-center">
                            @php
                                $ext = pathinfo($paymentProof, PATHINFO_EXTENSION);
                            @endphp
                            @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']))
                                <img src="{{ $backendUrl . $paymentProof }}" alt="Bukti Transfer" class="img-fluid rounded shadow-sm mb-2" style="max-height: 220px; object-fit: contain;">
                            @else
                                <div class="py-4 text-primary">
                                    <i class="bi bi-file-earmark-pdf fs-1"></i>
                                    <div class="small fw-semibold mt-1">Berkas PDF Bukti Transfer</div>
                                </div>
                            @endif
                            <a href="{{ $backendUrl . $paymentProof }}" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Berkas Penuh
                            </a>
                        </div>
                    </div>
                @else
                    <div class="alert alert-light border text-muted small py-3 mb-3 text-center">
                        <i class="bi bi-file-earmark-x fs-3 d-block mb-1 text-secondary"></i>
                        Pendaftar belum mengunggah bukti transfer.
                    </div>
                @endif

                <!-- Form Ubah Status Pembayaran -->
                <form action="{{ route('admin.ppdb.verify-payment', $registration['id']) }}" method="POST" class="border-top pt-3">
                    @csrf
                    <label class="form-label small fw-semibold text-dark">Ubah / Verifikasi Status Pembayaran:</label>
                    <div class="mb-2">
                        <select class="form-select form-select-sm" name="payment_status" required>
                            <option value="paid" {{ $pStatus === 'paid' ? 'selected' : '' }}>Setujui: LUNAS (PAID)</option>
                            <option value="rejected" {{ $pStatus === 'rejected' ? 'selected' : '' }}>Tolak: TIDAK VALID (REJECTED)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Nominal Terverifikasi:</label>
                        <input type="number" class="form-control form-control-sm" name="payment_amount" value="{{ $registration['payment_amount'] ?: 250000 }}" required>
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm w-100 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Status Bayar
                    </button>
                </form>
            </div>
        </div>

        <!-- Card 2: Aksi Penerimaan Siswa & Migrasi ke SIAKAD -->
        <div class="card card-custom">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-mortarboard-fill text-primary me-2"></i>Status & Migrasi SIAKAD
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <span class="text-muted small d-block">Status Seleksi Penerimaan:</span>
                    @if($rStatus === 'accepted')
                        <div class="alert alert-success border-0 py-2 mt-2 mb-0 small">
                            <i class="bi bi-check-circle-fill me-1"></i> <strong>TELAH DITERIMA RESMI</strong><br>
                            Siswa telah dimigrasikan ke database master SIAKAD.
                        </div>
                    @elseif($rStatus === 'rejected')
                        <div class="alert alert-danger border-0 py-2 mt-2 mb-0 small">
                            <i class="bi bi-x-octagon-fill me-1"></i> <strong>TIDAK LOLOS SELEKSI</strong>
                        </div>
                    @else
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle fs-6 px-3 py-1 mt-1">
                            DALAM PROSES SELEKSI
                        </span>
                    @endif
                </div>

                @if($rStatus !== 'accepted')
                    <div class="border-top pt-3">
                        <p class="small text-muted mb-3">
                            <i class="bi bi-info-circle me-1"></i> <strong>Mesin Migrasi Data Atomik:</strong> Menerima calon siswa akan otomatis membuat akun siswa di master <code>users</code> dan mendistribusikan berkas formulir ke <code>students</code>, <code>student_identities</code>, <code>student_addresses</code>, dan <code>parents</code>.
                        </p>

                        @if($pStatus !== 'paid')
                            <button type="button" class="btn btn-secondary w-100 btn-sm py-2" disabled>
                                <i class="bi bi-lock-fill me-1"></i> Pembayaran Belum Lunas
                            </button>
                        @elseif(!$hasForm)
                            <button type="button" class="btn btn-secondary w-100 btn-sm py-2" disabled>
                                <i class="bi bi-file-earmark-x me-1"></i> Formulir Dapodik Belum Diisi
                            </button>
                        @else
                            <form action="{{ route('admin.ppdb.accept', $registration['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerima calon siswa ini? Tindakan ini akan memigrasikan seluruh data ke database master SIAKAD secara permanen.');">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-patch-check-fill me-1"></i> Terima & Migrasikan ke SIAKAD
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Peninjauan Data Formulir Dapodik -->
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-file-earmark-person-fill text-primary me-2"></i>Data Formulir Pendaftaran Standar Dapodik
                </h6>
                @if($hasForm)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Formulir Terisi</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Belum Melengkapi Form</span>
                @endif
            </div>

            <div class="card-body p-4">
                @if(!$hasForm)
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-text fs-1 d-block mb-2 text-secondary"></i>
                        <h5 class="fw-bold text-dark">Pendaftar Belum Mengisi Formulir Dapodik</h5>
                        <p class="small text-muted mb-0">Calon siswa belum melengkapi isian biodata pokok, identitas tambahan, alamat, kontak, dan data orang tua.</p>
                    </div>
                @else
                    <!-- Section 1: Biodata Pokok -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-person-fill me-1"></i> 1. Biodata Pokok Siswa
                        </h6>
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor Induk Kependudukan (NIK):</span>
                                <strong class="text-dark fs-6">{{ $formData['nik'] ?? '-' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor Induk Siswa Nasional (NISN):</span>
                                <strong class="text-dark fs-6">{{ $formData['nisn'] ?? '-' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nama Lengkap Siswa:</span>
                                <strong class="text-dark">{{ $formData['full_name'] ?? '-' }}</strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted d-block">Nama Depan:</span>
                                <span class="text-dark">{{ $formData['first_name'] ?? '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted d-block">Nama Belakang:</span>
                                <span class="text-dark">{{ $formData['last_name'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Identitas Tambahan -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-card-text me-1"></i> 2. Identitas Tambahan
                        </h6>
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor Kartu Keluarga (KK):</span>
                                <strong class="text-dark">{{ $formData['identity']['family_card_number'] ?? '-' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Jenis Kelamin:</span>
                                <strong class="text-dark">{{ $formData['identity']['gender'] ?? '-' }}</strong>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted d-block">Agama:</span>
                                <span class="text-dark">{{ $formData['identity']['religion'] ?? '-' }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted d-block">Tempat Lahir:</span>
                                <span class="text-dark">{{ $formData['identity']['place_of_birth'] ?? '-' }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted d-block">Tanggal Lahir:</span>
                                <span class="text-dark">{{ isset($formData['identity']['date_of_birth']) ? date('d F Y', strtotime($formData['identity']['date_of_birth'])) : '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Alamat Domisili -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-geo-alt-fill me-1"></i> 3. Alamat Domisili Siswa
                        </h6>
                        <div class="row g-3 small">
                            <div class="col-12">
                                <span class="text-muted d-block">Alamat Lengkap Jalan / Rumah:</span>
                                <strong class="text-dark">{{ $formData['address']['street_address'] ?? '-' }}</strong>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted d-block">RT / RW:</span>
                                <span class="text-dark">RT {{ $formData['address']['rt'] ?? '-' }} / RW {{ $formData['address']['rw'] ?? '-' }}</span>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted d-block">Kelurahan / Desa:</span>
                                <span class="text-dark">{{ $formData['address']['village'] ?? '-' }}</span>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted d-block">Kecamatan:</span>
                                <span class="text-dark">{{ $formData['address']['district'] ?? '-' }}</span>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted d-block">Kode Pos:</span>
                                <span class="text-dark">{{ $formData['address']['postal_code'] ?? '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Status Tempat Tinggal:</span>
                                <span class="text-dark">{{ $formData['address']['residence_type'] ?? '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Moda Transportasi ke Sekolah:</span>
                                <span class="text-dark">{{ $formData['address']['transportation_mode'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Kontak Calon Siswa -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-telephone-fill me-1"></i> 4. Kontak & Komunikasi
                        </h6>
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor Handphone (HP):</span>
                                <strong class="text-dark">{{ $formData['contact']['mobile_number'] ?? '-' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor WhatsApp Aktif:</span>
                                <strong class="text-success"><i class="bi bi-whatsapp me-1"></i>{{ $formData['contact']['whatsapp_number'] ?? '-' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor Telepon Rumah:</span>
                                <span class="text-dark">{{ $formData['contact']['phone_number'] ?? '-' }}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Alamat Email:</span>
                                <span class="text-dark">{{ $formData['contact']['email'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Data Orang Tua / Wali -->
                    <div>
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-people-fill me-1"></i> 5. Data Orang Tua & Wali Siswa
                        </h6>

                        <!-- Ayah Kandung -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="fw-bold text-dark mb-2"><i class="bi bi-person me-1"></i> Data Ayah Kandung</div>
                            <div class="row g-2 small">
                                <div class="col-md-6"><strong>NIK:</strong> {{ $father['nik'] ?? '-' }}</div>
                                <div class="col-md-6"><strong>Nama Lengkap:</strong> {{ $father['full_name'] ?? '-' }}</div>
                                <div class="col-md-4"><strong>Tahun Lahir:</strong> {{ $father['birth_year'] ?? '-' }}</div>
                                <div class="col-md-4"><strong>Pendidikan:</strong> {{ $father['education_code'] ?? '-' }}</div>
                                <div class="col-md-4"><strong>Pekerjaan:</strong> {{ $father['occupation_code'] ?? '-' }}</div>
                                <div class="col-md-6"><strong>Penghasilan:</strong> {{ $father['income_code'] ?? '-' }}</div>
                                <div class="col-md-6"><strong>No HP / WA:</strong> {{ $father['phone_number'] ?? '-' }}</div>
                            </div>
                        </div>

                        <!-- Ibu Kandung -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="fw-bold text-dark mb-2"><i class="bi bi-person-heart me-1"></i> Data Ibu Kandung</div>
                            <div class="row g-2 small">
                                <div class="col-md-6"><strong>NIK:</strong> {{ $mother['nik'] ?? '-' }}</div>
                                <div class="col-md-6"><strong>Nama Lengkap:</strong> {{ $mother['full_name'] ?? '-' }}</div>
                                <div class="col-md-4"><strong>Tahun Lahir:</strong> {{ $mother['birth_year'] ?? '-' }}</div>
                                <div class="col-md-4"><strong>Pendidikan:</strong> {{ $mother['education_code'] ?? '-' }}</div>
                                <div class="col-md-4"><strong>Pekerjaan:</strong> {{ $mother['occupation_code'] ?? '-' }}</div>
                                <div class="col-md-6"><strong>Penghasilan:</strong> {{ $mother['income_code'] ?? '-' }}</div>
                                <div class="col-md-6"><strong>No HP / WA:</strong> {{ $mother['phone_number'] ?? '-' }}</div>
                            </div>
                        </div>

                        <!-- Wali (jika ada) -->
                        @if(!empty($guardian))
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="fw-bold text-dark mb-2"><i class="bi bi-shield-person me-1"></i> Data Wali</div>
                                <div class="row g-2 small">
                                    <div class="col-md-6"><strong>NIK:</strong> {{ $guardian['nik'] ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Nama Lengkap:</strong> {{ $guardian['full_name'] ?? '-' }}</div>
                                    <div class="col-md-4"><strong>Tahun Lahir:</strong> {{ $guardian['birth_year'] ?? '-' }}</div>
                                    <div class="col-md-4"><strong>Pendidikan:</strong> {{ $guardian['education_code'] ?? '-' }}</div>
                                    <div class="col-md-4"><strong>Pekerjaan:</strong> {{ $guardian['occupation_code'] ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Penghasilan:</strong> {{ $guardian['income_code'] ?? '-' }}</div>
                                    <div class="col-md-6"><strong>No HP / WA:</strong> {{ $guardian['phone_number'] ?? '-' }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
