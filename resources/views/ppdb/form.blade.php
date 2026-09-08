@extends('layouts.app')

@section('title', 'Formulir Pendaftaran Siswa Baru (Standar Dapodik)')

@push('styles')
<style>
    /* Wizard Step Bar */
    .wizard-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-bottom: 2rem;
    }

    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 22px;
        left: 30px;
        right: 30px;
        height: 3px;
        background: #e2e8f0;
        z-index: 1;
    }

    .wizard-step-item {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
    }

    .wizard-step-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #ffffff;
        border: 3px solid #cbd5e1;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.05rem;
        transition: all 0.3s ease;
    }

    .wizard-step-item.active .wizard-step-circle {
        border-color: var(--accent-teal);
        background: var(--accent-teal);
        color: #ffffff;
        box-shadow: 0 0 0 5px rgba(13, 148, 136, 0.2);
    }

    .wizard-step-item.completed .wizard-step-circle {
        border-color: #10b981;
        background: #10b981;
        color: #ffffff;
    }

    .wizard-step-title {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        margin-top: 0.5rem;
        display: block;
    }

    .wizard-step-item.active .wizard-step-title {
        color: var(--accent-teal);
        font-weight: 700;
    }

    .wizard-step-item.completed .wizard-step-title {
        color: #10b981;
    }

    /* Section Card */
    .form-section {
        display: none;
    }

    .form-section.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Breadcrumb & Back -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Formulir Pendaftaran</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-0">Formulir Pendaftaran Calon Siswa</h3>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    @if($isLocked)
        <div class="alert alert-warning border-0 shadow-sm p-4 rounded-4 mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-lock-fill fs-2 text-warning me-3"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Formulir Pendaftaran Telah Dikunci Permanen</h5>
                    <p class="mb-0 small">
                        Selamat! Status pendaftaran Anda telah <strong>DITERIMA</strong> secara resmi sebagai siswa baru sekolah dan telah dimigrasikan ke database master SIAKAD. Formulir tidak dapat diubah kembali.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Wizard Steps Indicator -->
    <div class="wizard-steps d-none d-md-flex">
        <div class="wizard-step-item active" id="step-indicator-1">
            <div class="wizard-step-circle">1</div>
            <span class="wizard-step-title">Biodata Pokok</span>
        </div>
        <div class="wizard-step-item" id="step-indicator-2">
            <div class="wizard-step-circle">2</div>
            <span class="wizard-step-title">Identitas Tambahan</span>
        </div>
        <div class="wizard-step-item" id="step-indicator-3">
            <div class="wizard-step-circle">3</div>
            <span class="wizard-step-title">Alamat Domisili</span>
        </div>
        <div class="wizard-step-item" id="step-indicator-4">
            <div class="wizard-step-circle">4</div>
            <span class="wizard-step-title">Kontak Siswa</span>
        </div>
        <div class="wizard-step-item" id="step-indicator-5">
            <div class="wizard-step-circle">5</div>
            <span class="wizard-step-title">Data Orang Tua</span>
        </div>
        <div class="wizard-step-item" id="step-indicator-6">
            <div class="wizard-step-circle"><i class="bi bi-check2"></i></div>
            <span class="wizard-step-title">Konfirmasi</span>
        </div>
    </div>

    <!-- Mobile Step Progress Indicator -->
    <div class="d-md-none mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-bold small text-primary" id="mobile-step-text">Tahap 1 dari 6: Biodata Pokok</span>
            <span class="badge bg-primary rounded-pill" id="mobile-step-badge">1/6</span>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" id="mobile-step-progress" role="progressbar" style="width: 16%;"></div>
        </div>
    </div>

    <!-- Main Multi-Step Form Card -->
    <div class="card card-custom border-0 shadow-sm p-4 p-md-5">
        <form id="ppdbWizardForm" action="{{ route('ppdb.form.submit') }}" method="POST">
            @csrf

            @php
                // Extract existing nested form values if available
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

            <!-- ============================================== -->
            <!-- TAHAP 1: BIODATA POKOK SISWA -->
            <!-- ============================================== -->
            <div class="form-section active" id="step-1">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-2">
                        <i class="bi bi-person-lines-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Tahap 1: Biodata Pokok Siswa</h5>
                        <small class="text-muted">Data pokok calon siswa sesuai data Dukcapil dan Dapodik</small>
                    </div>
                </div>
                <hr class="mb-4">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nik" class="form-label fw-semibold">Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="nik" 
                               name="nik" 
                               value="{{ old('nik', $formData['nik'] ?? session('nik')) }}" 
                               maxlength="16" 
                               required 
                               pattern="\d{16}"
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="form-text small">16 digit angka terdaftar di Kartu Keluarga.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="nisn" class="form-label fw-semibold">Nomor Induk Siswa Nasional (NISN) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="nisn" 
                               name="nisn" 
                               value="{{ old('nisn', $formData['nisn'] ?? '') }}" 
                               placeholder="Contoh: 0051234567" 
                               maxlength="10" 
                               required 
                               pattern="\d{10}"
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="form-text small">10 digit NISN dari sekolah asal (SD/SMP).</div>
                    </div>

                    <div class="col-12">
                        <label for="full_name" class="form-label fw-semibold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="full_name" 
                               name="full_name" 
                               value="{{ old('full_name', $formData['full_name'] ?? session('full_name')) }}" 
                               placeholder="Nama lengkap sesuai akta kelahiran" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="first_name" class="form-label fw-semibold">Nama Depan <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="first_name" 
                               name="first_name" 
                               value="{{ old('first_name', $formData['first_name'] ?? '') }}" 
                               placeholder="Contoh: Ahmad" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="last_name" class="form-label fw-semibold">Nama Belakang</label>
                        <input type="text" 
                               class="form-control" 
                               id="last_name" 
                               name="last_name" 
                               value="{{ old('last_name', $formData['last_name'] ?? '') }}" 
                               placeholder="Contoh: Fauzi Rahman" 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-teal px-4" onclick="nextStep(1)">
                        Selanjutnya: Identitas Tambahan <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 2: IDENTITAS TAMBAHAN -->
            <!-- ============================================== -->
            <div class="form-section" id="step-2">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-2">
                        <i class="bi bi-card-text fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Tahap 2: Identitas Tambahan</h5>
                        <small class="text-muted">Keluarga, jenis kelamin, tempat tanggal lahir, dan agama</small>
                    </div>
                </div>
                <hr class="mb-4">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="family_card_number" class="form-label fw-semibold">Nomor Kartu Keluarga (No KK) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="family_card_number" 
                               name="family_card_number" 
                               value="{{ old('family_card_number', $formData['identity']['family_card_number'] ?? '') }}" 
                               placeholder="16 digit nomor KK" 
                               maxlength="16" 
                               pattern="\d{16}"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="gender" class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select" id="gender" name="gender" required {{ $isLocked ? 'disabled' : '' }}>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki" {{ old('gender', $formData['identity']['gender'] ?? '') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender', $formData['identity']['gender'] ?? '') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="religion" class="form-label fw-semibold">Agama <span class="text-danger">*</span></label>
                        <select class="form-select" id="religion" name="religion" required {{ $isLocked ? 'disabled' : '' }}>
                            <option value="">-- Pilih Agama --</option>
                            @php
                                $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
                                $currRel = old('religion', $formData['identity']['religion'] ?? '');
                            @endphp
                            @foreach($religions as $rel)
                                <option value="{{ $rel }}" {{ $currRel === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="place_of_birth" class="form-label fw-semibold">Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="place_of_birth" 
                               name="place_of_birth" 
                               value="{{ old('place_of_birth', $formData['identity']['place_of_birth'] ?? '') }}" 
                               placeholder="Kota/Kabupaten lahir" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-4">
                        <label for="date_of_birth" class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control" 
                               id="date_of_birth" 
                               name="date_of_birth" 
                               value="{{ old('date_of_birth', $formData['identity']['date_of_birth'] ?? '') }}" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(2)">
                        <i class="bi bi-arrow-left me-1"></i> Sebelumnya
                    </button>
                    <button type="button" class="btn btn-teal px-4" onclick="nextStep(2)">
                        Selanjutnya: Alamat Domisili <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 3: ALAMAT DOMISILI -->
            <!-- ============================================== -->
            <div class="form-section" id="step-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-2">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Tahap 3: Alamat Domisili</h5>
                        <small class="text-muted">Alamat tempat tinggal calon siswa saat ini</small>
                    </div>
                </div>
                <hr class="mb-4">

                <div class="row g-3">
                    <div class="col-12">
                        <label for="street_address" class="form-label fw-semibold">Alamat Jalan / Tempat Tinggal <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="street_address" 
                                  name="street_address" 
                                  rows="2" 
                                  placeholder="Nama jalan, gang, nomor rumah" 
                                  required 
                                  {{ $isLocked ? 'disabled' : '' }}>{{ old('street_address', $formData['address']['street_address'] ?? '') }}</textarea>
                    </div>

                    <div class="col-md-3 col-6">
                        <label for="rt" class="form-label fw-semibold">RT <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="rt" 
                               name="rt" 
                               value="{{ old('rt', $formData['address']['rt'] ?? '') }}" 
                               placeholder="Contoh: 002" 
                               maxlength="5" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-3 col-6">
                        <label for="rw" class="form-label fw-semibold">RW <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="rw" 
                               name="rw" 
                               value="{{ old('rw', $formData['address']['rw'] ?? '') }}" 
                               placeholder="Contoh: 005" 
                               maxlength="5" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="village" class="form-label fw-semibold">Kelurahan / Desa <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="village" 
                               name="village" 
                               value="{{ old('village', $formData['address']['village'] ?? '') }}" 
                               placeholder="Nama kelurahan/desa" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="district" class="form-label fw-semibold">Kecamatan <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="district" 
                               name="district" 
                               value="{{ old('district', $formData['address']['district'] ?? '') }}" 
                               placeholder="Nama kecamatan" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="postal_code" class="form-label fw-semibold">Kode Pos <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="postal_code" 
                               name="postal_code" 
                               value="{{ old('postal_code', $formData['address']['postal_code'] ?? '') }}" 
                               placeholder="5 digit kode pos" 
                               maxlength="10" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="residence_type" class="form-label fw-semibold">Status Tempat Tinggal <span class="text-danger">*</span></label>
                        <select class="form-select" id="residence_type" name="residence_type" required {{ $isLocked ? 'disabled' : '' }}>
                            <option value="">-- Pilih Tempat Tinggal --</option>
                            @php
                                $resTypes = ['Bersama Orang Tua', 'Wali', 'Kost', 'Asrama', 'Panti Asuhan', 'Lainnya'];
                                $currRes = old('residence_type', $formData['address']['residence_type'] ?? '');
                            @endphp
                            @foreach($resTypes as $res)
                                <option value="{{ $res }}" {{ $currRes === $res ? 'selected' : '' }}>{{ $res }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="transportation_mode" class="form-label fw-semibold">Moda Transportasi ke Sekolah <span class="text-danger">*</span></label>
                        <select class="form-select" id="transportation_mode" name="transportation_mode" required {{ $isLocked ? 'disabled' : '' }}>
                            <option value="">-- Pilih Transportasi --</option>
                            @php
                                $transModes = ['Sepeda Motor', 'Jalan Kaki', 'Sepeda', 'Angkutan Umum', 'Mobil Pribadi', 'Ojek Online', 'Lainnya'];
                                $currTrans = old('transportation_mode', $formData['address']['transportation_mode'] ?? '');
                            @endphp
                            @foreach($transModes as $tm)
                                <option value="{{ $tm }}" {{ $currTrans === $tm ? 'selected' : '' }}>{{ $tm }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(3)">
                        <i class="bi bi-arrow-left me-1"></i> Sebelumnya
                    </button>
                    <button type="button" class="btn btn-teal px-4" onclick="nextStep(3)">
                        Selanjutnya: Kontak Siswa <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 4: KONTAK SISWA -->
            <!-- ============================================== -->
            <div class="form-section" id="step-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-2">
                        <i class="bi bi-telephone-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Tahap 4: Kontak & Komunikasi</h5>
                        <small class="text-muted">Nomor telepon, WhatsApp, dan alamat email aktif calon siswa</small>
                    </div>
                </div>
                <hr class="mb-4">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="mobile_number" class="form-label fw-semibold">Nomor Handphone (HP) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-phone"></i></span>
                            <input type="text" 
                                   class="form-control" 
                                   id="mobile_number" 
                                   name="mobile_number" 
                                   value="{{ old('mobile_number', $formData['contact']['mobile_number'] ?? '') }}" 
                                   placeholder="Contoh: 081234567890" 
                                   required 
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="whatsapp_number" class="form-label fw-semibold">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success"><i class="bi bi-whatsapp"></i></span>
                            <input type="text" 
                                   class="form-control" 
                                   id="whatsapp_number" 
                                   name="whatsapp_number" 
                                   value="{{ old('whatsapp_number', $formData['contact']['whatsapp_number'] ?? '') }}" 
                                   placeholder="Contoh: 081234567890" 
                                   required 
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </div>
                        <div class="form-text small">Digunakan untuk konfirmasi dan pengumuman instan.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="phone_number" class="form-label fw-semibold">Nomor Telepon Rumah</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                            <input type="text" 
                                   class="form-control" 
                                   id="phone_number" 
                                   name="phone_number" 
                                   value="{{ old('phone_number', $formData['contact']['phone_number'] ?? '') }}" 
                                   placeholder="Contoh: 021-77889900 (opsional)" 
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Alamat Email Siswa <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $formData['contact']['email'] ?? session('email')) }}" 
                                   placeholder="nama@siswa.sch.id" 
                                   required 
                                   {{ $isLocked ? 'disabled' : '' }}>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(4)">
                        <i class="bi bi-arrow-left me-1"></i> Sebelumnya
                    </button>
                    <button type="button" class="btn btn-teal px-4" onclick="nextStep(4)">
                        Selanjutnya: Data Orang Tua <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 5: DATA ORANG TUA / WALI -->
            <!-- ============================================== -->
            <div class="form-section" id="step-5">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-2">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Tahap 5: Data Orang Tua / Wali</h5>
                        <small class="text-muted">Data ayah kandung, ibu kandung, dan wali (sesuai standar Dapodik)</small>
                    </div>
                </div>
                <hr class="mb-4">

                <!-- Nav Tabs Orang Tua -->
                <ul class="nav nav-pills mb-4 gap-2" id="parentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-3 py-2 fw-semibold" id="father-tab" data-bs-toggle="pill" data-bs-target="#father-pane" type="button" role="tab">
                            <i class="bi bi-person me-1"></i> Data Ayah Kandung
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" id="mother-tab" data-bs-toggle="pill" data-bs-target="#mother-pane" type="button" role="tab">
                            <i class="bi bi-person-heart me-1"></i> Data Ibu Kandung
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-2 fw-semibold" id="guardian-tab" data-bs-toggle="pill" data-bs-target="#guardian-pane" type="button" role="tab">
                            <i class="bi bi-shield-person me-1"></i> Data Wali (Opsional)
                        </button>
                    </li>
                </ul>

                @php
                    $educationList = [
                        '01' => 'Tidak Sekolah',
                        '02' => 'SD / Sederajat',
                        '03' => 'SMP / Sederajat',
                        '04' => 'SMA / SMK / Sederajat',
                        '05' => 'D1 / D2 / D3',
                        '06' => 'D4 / S1',
                        '07' => 'S2',
                        '08' => 'S3'
                    ];

                    $occupationList = [
                        '01' => 'Tidak Bekerja',
                        '02' => 'PNS / TNI / Polri',
                        '03' => 'Karyawan Swasta',
                        '04' => 'Wiraswasta / Pedagang',
                        '05' => 'Petani / Peternak / Nelayan',
                        '06' => 'Buruh / Pekerja Lepas',
                        '07' => 'Pensiunan',
                        '08' => 'Lainnya'
                    ];

                    $incomeList = [
                        '01' => 'Kurang dari Rp 1.000.000',
                        '02' => 'Rp 1.000.000 - Rp 2.000.000',
                        '03' => 'Rp 2.000.000 - Rp 5.000.000',
                        '04' => 'Rp 5.000.000 - Rp 10.000.000',
                        '05' => 'Lebih dari Rp 10.000.000',
                        '06' => 'Tidak Berpenghasilan'
                    ];
                @endphp

                <div class="tab-content" id="parentTabContent">
                    <!-- Tab Pane Ayah Kandung -->
                    <div class="tab-pane fade show active" id="father-pane" role="tabpanel">
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <strong class="text-primary"><i class="bi bi-info-circle me-1"></i> Identitas Ayah Kandung</strong>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="father_nik" class="form-label fw-semibold">NIK Ayah <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="father_nik" 
                                       name="father_nik" 
                                       value="{{ old('father_nik', $father['nik'] ?? '') }}" 
                                       placeholder="16 digit NIK ayah" 
                                       maxlength="16" 
                                       pattern="\d{16}"
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-6">
                                <label for="father_name" class="form-label fw-semibold">Nama Lengkap Ayah <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="father_name" 
                                       name="father_name" 
                                       value="{{ old('father_name', $father['full_name'] ?? '') }}" 
                                       placeholder="Nama lengkap ayah kandung" 
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="father_birth_year" class="form-label fw-semibold">Tahun Lahir <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="father_birth_year" 
                                       name="father_birth_year" 
                                       value="{{ old('father_birth_year', $father['birth_year'] ?? '') }}" 
                                       placeholder="Contoh: 1978" 
                                       min="1940" 
                                       max="2015" 
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="father_education" class="form-label fw-semibold">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                <select class="form-select" id="father_education" name="father_education" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Pendidikan --</option>
                                    @php $fEdu = old('father_education', $father['education_code'] ?? ''); @endphp
                                    @foreach($educationList as $code => $label)
                                        <option value="{{ $code }}" {{ $fEdu == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="father_occupation" class="form-label fw-semibold">Pekerjaan <span class="text-danger">*</span></label>
                                <select class="form-select" id="father_occupation" name="father_occupation" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @php $fOcc = old('father_occupation', $father['occupation_code'] ?? ''); @endphp
                                    @foreach($occupationList as $code => $label)
                                        <option value="{{ $code }}" {{ $fOcc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="father_income" class="form-label fw-semibold">Penghasilan Bulanan <span class="text-danger">*</span></label>
                                <select class="form-select" id="father_income" name="father_income" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Penghasilan --</option>
                                    @php $fInc = old('father_income', $father['income_code'] ?? ''); @endphp
                                    @foreach($incomeList as $code => $label)
                                        <option value="{{ $code }}" {{ $fInc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="father_phone" class="form-label fw-semibold">No HP Ayah</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="father_phone" 
                                       name="father_phone" 
                                       value="{{ old('father_phone', $father['phone_number'] ?? '') }}" 
                                       placeholder="0813..." 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="father_whatsapp" class="form-label fw-semibold">No WhatsApp Ayah</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="father_whatsapp" 
                                       name="father_whatsapp" 
                                       value="{{ old('father_whatsapp', $father['whatsapp_number'] ?? '') }}" 
                                       placeholder="0813..." 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Pane Ibu Kandung -->
                    <div class="tab-pane fade" id="mother-pane" role="tabpanel">
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <strong class="text-primary"><i class="bi bi-info-circle me-1"></i> Identitas Ibu Kandung</strong>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="mother_nik" class="form-label fw-semibold">NIK Ibu <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="mother_nik" 
                                       name="mother_nik" 
                                       value="{{ old('mother_nik', $mother['nik'] ?? '') }}" 
                                       placeholder="16 digit NIK ibu" 
                                       maxlength="16" 
                                       pattern="\d{16}"
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-6">
                                <label for="mother_name" class="form-label fw-semibold">Nama Lengkap Ibu <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="mother_name" 
                                       name="mother_name" 
                                       value="{{ old('mother_name', $mother['full_name'] ?? '') }}" 
                                       placeholder="Nama lengkap ibu kandung" 
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_birth_year" class="form-label fw-semibold">Tahun Lahir <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control" 
                                       id="mother_birth_year" 
                                       name="mother_birth_year" 
                                       value="{{ old('mother_birth_year', $mother['birth_year'] ?? '') }}" 
                                       placeholder="Contoh: 1980" 
                                       min="1940" 
                                       max="2015" 
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_education" class="form-label fw-semibold">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                <select class="form-select" id="mother_education" name="mother_education" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Pendidikan --</option>
                                    @php $mEdu = old('mother_education', $mother['education_code'] ?? ''); @endphp
                                    @foreach($educationList as $code => $label)
                                        <option value="{{ $code }}" {{ $mEdu == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_occupation" class="form-label fw-semibold">Pekerjaan <span class="text-danger">*</span></label>
                                <select class="form-select" id="mother_occupation" name="mother_occupation" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Pekerjaan --</option>
                                    @php $mOcc = old('mother_occupation', $mother['occupation_code'] ?? ''); @endphp
                                    @foreach($occupationList as $code => $label)
                                        <option value="{{ $code }}" {{ $mOcc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_income" class="form-label fw-semibold">Penghasilan Bulanan <span class="text-danger">*</span></label>
                                <select class="form-select" id="mother_income" name="mother_income" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Penghasilan --</option>
                                    @php $mInc = old('mother_income', $mother['income_code'] ?? ''); @endphp
                                    @foreach($incomeList as $code => $label)
                                        <option value="{{ $code }}" {{ $mInc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_phone" class="form-label fw-semibold">No HP Ibu</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="mother_phone" 
                                       name="mother_phone" 
                                       value="{{ old('mother_phone', $mother['phone_number'] ?? '') }}" 
                                       placeholder="0813..." 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_whatsapp" class="form-label fw-semibold">No WhatsApp Ibu</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="mother_whatsapp" 
                                       name="mother_whatsapp" 
                                       value="{{ old('mother_whatsapp', $mother['whatsapp_number'] ?? '') }}" 
                                       placeholder="0813..." 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Pane Wali Siswa (Opsional) -->
                    <div class="tab-pane fade" id="guardian-pane" role="tabpanel">
                        <div class="form-check form-switch mb-3 p-3 bg-light rounded-3 border">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="has_guardian" name="has_guardian" value="1" 
                                {{ old('has_guardian', !empty($guardian) ? '1' : '') ? 'checked' : '' }} onchange="toggleGuardianFields(this.checked)" {{ $isLocked ? 'disabled' : '' }}>
                            <label class="form-check-label fw-semibold text-dark" for="has_guardian">
                                Calon siswa tinggal bersama / diasuh oleh Wali (selain orang tua kandung)
                            </label>
                        </div>

                        <div id="guardian-fields" style="display: {{ old('has_guardian', !empty($guardian) ? '1' : '') ? 'block' : 'none' }};">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="guardian_nik" class="form-label fw-semibold">NIK Wali</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="guardian_nik" 
                                           name="guardian_nik" 
                                           value="{{ old('guardian_nik', $guardian['nik'] ?? '') }}" 
                                           placeholder="16 digit NIK wali" 
                                           maxlength="16" 
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-6">
                                    <label for="guardian_name" class="form-label fw-semibold">Nama Lengkap Wali</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="guardian_name" 
                                           name="guardian_name" 
                                           value="{{ old('guardian_name', $guardian['full_name'] ?? '') }}" 
                                           placeholder="Nama lengkap wali" 
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_birth_year" class="form-label fw-semibold">Tahun Lahir Wali</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="guardian_birth_year" 
                                           name="guardian_birth_year" 
                                           value="{{ old('guardian_birth_year', $guardian['birth_year'] ?? '') }}" 
                                           placeholder="Contoh: 1982" 
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_education" class="form-label fw-semibold">Pendidikan Terakhir</label>
                                    <select class="form-select" id="guardian_education" name="guardian_education" {{ $isLocked ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Pendidikan --</option>
                                        @php $gEdu = old('guardian_education', $guardian['education_code'] ?? ''); @endphp
                                        @foreach($educationList as $code => $label)
                                            <option value="{{ $code }}" {{ $gEdu == $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_occupation" class="form-label fw-semibold">Pekerjaan Wali</label>
                                    <select class="form-select" id="guardian_occupation" name="guardian_occupation" {{ $isLocked ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Pekerjaan --</option>
                                        @php $gOcc = old('guardian_occupation', $guardian['occupation_code'] ?? ''); @endphp
                                        @foreach($occupationList as $code => $label)
                                            <option value="{{ $code }}" {{ $gOcc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_income" class="form-label fw-semibold">Penghasilan Bulanan</label>
                                    <select class="form-select" id="guardian_income" name="guardian_income" {{ $isLocked ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Penghasilan --</option>
                                        @php $gInc = old('guardian_income', $guardian['income_code'] ?? ''); @endphp
                                        @foreach($incomeList as $code => $label)
                                            <option value="{{ $code }}" {{ $gInc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_phone" class="form-label fw-semibold">No HP Wali</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="guardian_phone" 
                                           name="guardian_phone" 
                                           value="{{ old('guardian_phone', $guardian['phone_number'] ?? '') }}" 
                                           placeholder="0813..." 
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_whatsapp" class="form-label fw-semibold">No WhatsApp Wali</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="guardian_whatsapp" 
                                           name="guardian_whatsapp" 
                                           value="{{ old('guardian_whatsapp', $guardian['whatsapp_number'] ?? '') }}" 
                                           placeholder="0813..." 
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(5)">
                        <i class="bi bi-arrow-left me-1"></i> Sebelumnya
                    </button>
                    <button type="button" class="btn btn-teal px-4" onclick="nextStep(5)">
                        Lanjut ke Tahap Review & Simpan <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 6: REVIEW & KONFIRMASI -->
            <!-- ============================================== -->
            <div class="form-section" id="step-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 text-success p-2 rounded-3 me-2">
                        <i class="bi bi-clipboard-check-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Tahap 6: Tinjau & Simpan Formulir</h5>
                        <small class="text-muted">Periksa kembali seluruh data yang telah Anda isikan sebelum disimpan</small>
                    </div>
                </div>
                <hr class="mb-4">

                <div class="card bg-light border-0 p-4 rounded-3 mb-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Ringkasan Data Pendaftaran</h6>
                    <div class="row g-3 small">
                        <div class="col-md-6">
                            <span class="text-muted d-block">Nama Lengkap Siswa:</span>
                            <strong class="text-dark" id="review-name">-</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">NIK / NISN:</span>
                            <strong class="text-dark" id="review-nik-nisn">-</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Jenis Kelamin / TTL:</span>
                            <strong class="text-dark" id="review-ttl">-</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Kontak (HP / WhatsApp):</span>
                            <strong class="text-dark" id="review-contact">-</strong>
                        </div>
                        <div class="col-md-12">
                            <span class="text-muted d-block">Alamat Domisili:</span>
                            <strong class="text-dark" id="review-address">-</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Nama Ayah Kandung:</span>
                            <strong class="text-dark" id="review-father">-</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block">Nama Ibu Kandung:</span>
                            <strong class="text-dark" id="review-mother">-</strong>
                        </div>
                    </div>
                </div>

                <div class="form-check p-3 bg-white rounded-3 border border-success-subtle mb-4">
                    <input class="form-check-input ms-0 me-3" type="checkbox" id="confirmation_check" required {{ $isLocked ? 'disabled' : '' }}>
                    <label class="form-check-label small text-dark" for="confirmation_check">
                        <strong>Pernyataan Kebenaran Data:</strong> Saya menyatakan bahwa seluruh data pokok, identitas tambahan, alamat, dan data orang tua/wali yang telah diisikan di atas adalah benar dan sesuai dengan dokumen resmi negara (Kartu Keluarga, Akta Kelahiran, dan Ijazah/Rapor).
                    </label>
                </div>

                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(6)">
                        <i class="bi bi-arrow-left me-1"></i> Perbaiki Isian
                    </button>
                    @if(!$isLocked)
                        <button type="submit" class="btn btn-teal btn-lg px-5 fw-bold shadow-sm">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Simpan & Kirim Formulir Dapodik
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary px-4" disabled>
                            <i class="bi bi-lock-fill me-1"></i> Formulir Terkunci (Telah Diterima)
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 6;

    function showStep(step) {
        // Hide all sections
        for (let i = 1; i <= totalSteps; i++) {
            const section = document.getElementById('step-' + i);
            const indicator = document.getElementById('step-indicator-' + i);
            if (section) section.classList.remove('active');
            if (indicator) {
                indicator.classList.remove('active');
                if (i < step) {
                    indicator.classList.add('completed');
                } else {
                    indicator.classList.remove('completed');
                }
            }
        }

        // Show target section
        const targetSection = document.getElementById('step-' + step);
        const targetIndicator = document.getElementById('step-indicator-' + step);
        if (targetSection) targetSection.classList.add('active');
        if (targetIndicator) targetIndicator.classList.add('active');

        // Mobile progress bar
        const mobileText = document.getElementById('mobile-step-text');
        const mobileBadge = document.getElementById('mobile-step-badge');
        const mobileProg = document.getElementById('mobile-step-progress');
        
        const stepTitles = [
            'Biodata Pokok',
            'Identitas Tambahan',
            'Alamat Domisili',
            'Kontak Siswa',
            'Data Orang Tua',
            'Tinjau & Konfirmasi'
        ];

        if (mobileText) mobileText.textContent = `Tahap ${step} dari ${totalSteps}: ${stepTitles[step - 1]}`;
        if (mobileBadge) mobileBadge.textContent = `${step}/${totalSteps}`;
        if (mobileProg) mobileProg.style.width = `${Math.round((step / totalSteps) * 100)}%`;

        // If step 6, populate review summary
        if (step === 6) {
            populateReview();
        }

        window.scrollTo({ top: 120, behavior: 'smooth' });
    }

    function validateStep(step) {
        const currentSection = document.getElementById('step-' + step);
        if (!currentSection) return true;

        const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            // Check HTML5 validity
            if (!input.checkValidity()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }

            // Realtime remove invalid on input
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                }
            }, { once: true });
        });

        if (!isValid) {
            const firstInvalid = currentSection.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }

        return isValid;
    }

    function nextStep(step) {
        if (validateStep(step)) {
            currentStep = step + 1;
            showStep(currentStep);
        }
    }

    function prevStep(step) {
        currentStep = step - 1;
        showStep(currentStep);
    }

    function toggleGuardianFields(isChecked) {
        const guardianFields = document.getElementById('guardian-fields');
        if (guardianFields) {
            guardianFields.style.display = isChecked ? 'block' : 'none';
        }
    }

    function populateReview() {
        const fullName = document.getElementById('full_name')?.value || '-';
        const nik = document.getElementById('nik')?.value || '-';
        const nisn = document.getElementById('nisn')?.value || '-';
        const gender = document.getElementById('gender')?.value || '-';
        const pob = document.getElementById('place_of_birth')?.value || '';
        const dob = document.getElementById('date_of_birth')?.value || '';
        const mobile = document.getElementById('mobile_number')?.value || '-';
        const wa = document.getElementById('whatsapp_number')?.value || '-';
        const street = document.getElementById('street_address')?.value || '-';
        const rt = document.getElementById('rt')?.value || '';
        const rw = document.getElementById('rw')?.value || '';
        const village = document.getElementById('village')?.value || '';
        const district = document.getElementById('district')?.value || '';
        const fatherName = document.getElementById('father_name')?.value || '-';
        const motherName = document.getElementById('mother_name')?.value || '-';

        document.getElementById('review-name').textContent = fullName;
        document.getElementById('review-nik-nisn').textContent = `${nik} / ${nisn}`;
        document.getElementById('review-ttl').textContent = `${gender}, ${pob} (${dob})`;
        document.getElementById('review-contact').textContent = `${mobile} (WA: ${wa})`;
        document.getElementById('review-address').textContent = `${street}, RT ${rt}/RW ${rw}, Kel. ${village}, Kec. ${district}`;
        document.getElementById('review-father').textContent = fatherName;
        document.getElementById('review-mother').textContent = motherName;
    }
</script>
@endpush
