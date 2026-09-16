@extends('layouts.app')

@section('title', 'Formulir Pendaftaran Siswa Baru')

@section('content')
<div class="container-xl py-3 form-content-wrap">
    <!-- Header Banner -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3" style="border-color: var(--border-light) !important;">
        <div>
            <div class="d-inline-flex align-items-center gap-2 mb-1">
                <span class="badge-pastel badge-pastel-neutral">FORMULIR DIGITAL</span>
                <span class="font-mono-meta small text-secondary">DATA POKOK PENDIDIKAN</span>
            </div>
            <h2 class="font-serif-heading fs-2 text-dark mb-0">Formulir Pendaftaran Calon Siswa</h2>
            <p class="text-secondary small mb-0 mt-1">Lengkapi 6 tahap isian data calon siswa dan orang tua secara akurat.</p>
        </div>
        <div>
            @if($isLocked)
                <span class="badge-pastel badge-pastel-green py-2 px-3">
                    <i class="ph-bold ph-lock-key"></i> FORMULIR TERKUNCI (DITERIMA)
                </span>
            @else
                <span class="badge-pastel badge-pastel-blue py-2 px-3">
                    <i class="ph-bold ph-pencil-simple"></i> DAPAT DISUNTING
                </span>
            @endif
        </div>
    </div>

    @if($isLocked)
        <div class="alert-document alert-success mb-4 d-flex align-items-center">
            <i class="ph-bold ph-shield-check fs-4 me-2 flex-shrink-0"></i>
            <div>
                <strong>Pendaftaran Telah Diterima Resmi:</strong> Data formulir telah dikunci untuk menjaga integritas migrasi ke pangkalan data sekolah. Anda dapat meninjau isian di bawah ini dalam mode baca saja.
            </div>
        </div>
    @endif

    <!-- Mobile Progress Indicator -->
    <div class="mobile-step-bar">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-mono-meta text-secondary small" id="mobile-step-label">TAHAP 01 DARI 06</span>
            <span class="font-mono-meta fw-semibold text-dark small" id="mobile-step-percent">16%</span>
        </div>
        <div class="progress" style="height: 4px; background: var(--surface-muted);">
            <div class="progress-bar bg-dark" id="mobile-step-progress" role="progressbar" style="width: 16%;"></div>
        </div>
        <div class="mt-2 text-dark fw-semibold small text-truncate" id="mobile-step-title">
            Tahap 1: Biodata Pokok Siswa
        </div>
    </div>

    <!-- Desktop Stepper Indicator -->
    <div class="wizard-desktop-nav">
        <div class="wizard-steps-container">
            <div class="wizard-step-item active" id="step-indicator-1">
                <div class="wizard-step-node">01</div>
                <span class="wizard-step-label">Biodata Pokok</span>
            </div>
            <div class="wizard-step-item" id="step-indicator-2">
                <div class="wizard-step-node">02</div>
                <span class="wizard-step-label">Identitas Tambahan</span>
            </div>
            <div class="wizard-step-item" id="step-indicator-3">
                <div class="wizard-step-node">03</div>
                <span class="wizard-step-label">Alamat Domisili</span>
            </div>
            <div class="wizard-step-item" id="step-indicator-4">
                <div class="wizard-step-node">04</div>
                <span class="wizard-step-label">Kontak Siswa</span>
            </div>
            <div class="wizard-step-item" id="step-indicator-5">
                <div class="wizard-step-node">05</div>
                <span class="wizard-step-label">Orang Tua / Wali</span>
            </div>
            <div class="wizard-step-item" id="step-indicator-6">
                <div class="wizard-step-node">06</div>
                <span class="wizard-step-label">Tinjau & Simpan</span>
            </div>
        </div>
    </div>

    <!-- Main Wizard Card Container -->
    <div class="bento-card p-4 p-md-5">
        <form id="ppdbWizardForm" action="{{ route('ppdb.form.submit') }}" method="POST">
            @csrf

            @php
                // Ekstraksi data orang tua jika sudah pernah tersimpan sebelumnya
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
                <div class="bento-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="font-mono-meta small text-secondary">TAHAP 01 / BIODATA</span>
                        <h5 class="fw-semibold text-dark mb-0">Biodata Pokok Calon Siswa</h5>
                    </div>
                    <span class="badge-pastel badge-pastel-neutral">DATA DUKCAPIL</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nik" class="form-label">Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-mono-meta" 
                               id="nik" 
                               name="nik" 
                               value="{{ old('nik', $formData['nik'] ?? session('nik')) }}" 
                               maxlength="16" 
                               inputmode="numeric"
                               pattern="\d{16}"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="form-text">16 digit angka sesuai Kartu Keluarga (KK).</div>
                    </div>

                    <div class="col-md-6">
                        <label for="nisn" class="form-label">Nomor Induk Siswa Nasional (NISN) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-mono-meta" 
                               id="nisn" 
                               name="nisn" 
                               value="{{ old('nisn', $formData['nisn'] ?? '') }}" 
                               placeholder="Contoh: 0051234567" 
                               maxlength="10" 
                               inputmode="numeric"
                               pattern="\d{10}"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="form-text">10 digit nomor NISN dari sekolah asal.</div>
                    </div>

                    <div class="col-12">
                        <label for="full_name" class="form-label">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="full_name" 
                               name="full_name" 
                               value="{{ old('full_name', $formData['full_name'] ?? session('full_name')) }}" 
                               placeholder="Nama lengkap sesuai akta kelahiran resmi" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="first_name" class="form-label">Nama Depan <span class="text-danger">*</span></label>
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
                        <label for="last_name" class="form-label">Nama Belakang</label>
                        <input type="text" 
                               class="form-control" 
                               id="last_name" 
                               name="last_name" 
                               value="{{ old('last_name', $formData['last_name'] ?? '') }}" 
                               placeholder="Contoh: Fauzi Rahman (opsional)" 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <!-- Pilihan Jurusan -->
                    <div class="col-12 mt-4 pt-2 border-top" style="border-color: var(--border-light) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-semibold text-dark">
                                Pilihan Jurusan / Peminatan <span class="text-danger">*</span>
                            </label>
                            <span class="font-mono-meta small text-secondary">PILIH SALAH SATU</span>
                        </div>
                        @php $currMajor = old('major', $formData['major'] ?? ''); @endphp
                        <div class="row g-2">
                            <!-- 1. Reguler -->
                            <div class="col-sm-6 col-lg-3">
                                <label class="d-block p-3 rounded-2 border h-100 cursor-pointer position-relative" 
                                       style="border-color: {{ $currMajor === 'reguler' ? 'var(--text-primary)' : 'var(--border-light)' }} !important; background: var(--surface-bg);">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <input type="radio" name="major" value="reguler" class="form-check-input mt-0" 
                                               {{ $currMajor === 'reguler' ? 'checked' : '' }} required {{ $isLocked ? 'disabled' : '' }}>
                                        <span class="fw-semibold text-dark small">Reguler</span>
                                    </div>
                                    <span class="text-secondary small d-block" style="font-size: 0.75rem;">Kurikulum Umum &amp; Kejuruan Standar</span>
                                </label>
                            </div>

                            <!-- 2. Bahasa -->
                            <div class="col-sm-6 col-lg-3">
                                <label class="d-block p-3 rounded-2 border h-100 cursor-pointer position-relative" 
                                       style="border-color: {{ $currMajor === 'bahasa' ? 'var(--text-primary)' : 'var(--border-light)' }} !important; background: var(--surface-bg);">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <input type="radio" name="major" value="bahasa" class="form-check-input mt-0" 
                                               {{ $currMajor === 'bahasa' ? 'checked' : '' }} required {{ $isLocked ? 'disabled' : '' }}>
                                        <span class="fw-semibold text-dark small">Bahasa</span>
                                    </div>
                                    <span class="text-secondary small d-block" style="font-size: 0.75rem;">Peminatan Bahasa Asing &amp; Literasi</span>
                                </label>
                            </div>

                            <!-- 3. Tahfidz -->
                            <div class="col-sm-6 col-lg-3">
                                <label class="d-block p-3 rounded-2 border h-100 cursor-pointer position-relative" 
                                       style="border-color: {{ $currMajor === 'tahfidz' ? 'var(--text-primary)' : 'var(--border-light)' }} !important; background: var(--surface-bg);">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <input type="radio" name="major" value="tahfidz" class="form-check-input mt-0" 
                                               {{ $currMajor === 'tahfidz' ? 'checked' : '' }} required {{ $isLocked ? 'disabled' : '' }}>
                                        <span class="fw-semibold text-dark small">Tahfidz</span>
                                    </div>
                                    <span class="text-secondary small d-block" style="font-size: 0.75rem;">Program Khusus Tahfizhul Qur'an</span>
                                </label>
                            </div>

                            <!-- 4. ICT -->
                            <div class="col-sm-6 col-lg-3">
                                <label class="d-block p-3 rounded-2 border h-100 cursor-pointer position-relative" 
                                       style="border-color: {{ $currMajor === 'ict' ? 'var(--text-primary)' : 'var(--border-light)' }} !important; background: var(--surface-bg);">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <input type="radio" name="major" value="ict" class="form-check-input mt-0" 
                                               {{ $currMajor === 'ict' ? 'checked' : '' }} required {{ $isLocked ? 'disabled' : '' }}>
                                        <span class="fw-semibold text-dark small">ICT</span>
                                    </div>
                                    <span class="text-secondary small d-block" style="font-size: 0.75rem;">Teknologi Informasi &amp; Komputer</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Asal Sekolah & Alamat Sekolah Asal -->
                    <div class="col-md-6 mt-3">
                        <label for="school_origin" class="form-label">Nama Asal Sekolah <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="school_origin" 
                               name="school_origin" 
                               value="{{ old('school_origin', $formData['school_origin'] ?? '') }}" 
                               placeholder="Contoh: SDN 01 Pagi / SDIT Yapis Jayapura" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="form-text">Nama sekolah jenjang sebelumnya (SD/MI/Sederajat).</div>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label for="school_origin_address" class="form-label">Alamat Sekolah Asal <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="school_origin_address" 
                               name="school_origin_address" 
                               value="{{ old('school_origin_address', $formData['school_origin_address'] ?? '') }}" 
                               placeholder="Contoh: Jl. Raya Pendidikan No. 45, Jakarta Selatan" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="form-text">Alamat atau kota lokasi sekolah jenjang sebelumnya.</div>
                    </div>
                </div>

                <div class="mobile-action-bar">
                    <div class="d-none d-md-block"></div>
                    <button type="button" class="btn-minimal-primary px-4" onclick="nextStep(1)">
                        Selanjutnya: Identitas Tambahan <i class="ph-bold ph-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 2: IDENTITAS TAMBAHAN -->
            <!-- ============================================== -->
            <div class="form-section" id="step-2">
                <div class="bento-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="font-mono-meta small text-secondary">TAHAP 02 / IDENTITAS</span>
                        <h5 class="fw-semibold text-dark mb-0">Identitas Tambahan Siswa</h5>
                    </div>
                    <span class="badge-pastel badge-pastel-neutral">KARTU KELUARGA</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="family_card_number" class="form-label">Nomor Kartu Keluarga (No KK) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-mono-meta" 
                               id="family_card_number" 
                               name="family_card_number" 
                               value="{{ old('family_card_number', $formData['identity']['family_card_number'] ?? '') }}" 
                               placeholder="16 digit nomor KK" 
                               maxlength="16" 
                               inputmode="numeric"
                               pattern="\d{16}"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="gender" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select" id="gender" name="gender" required {{ $isLocked ? 'disabled' : '' }}>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" {{ old('gender', $formData['identity']['gender'] ?? '') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender', $formData['identity']['gender'] ?? '') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="religion" class="form-label">Agama <span class="text-danger">*</span></label>
                        <select class="form-select" id="religion" name="religion" required {{ $isLocked ? 'disabled' : '' }}>
                            <option value="">Pilih Agama</option>
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
                        <label for="place_of_birth" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
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
                        <label for="date_of_birth" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control font-mono-meta" 
                               id="date_of_birth" 
                               name="date_of_birth" 
                               value="{{ old('date_of_birth', $formData['identity']['date_of_birth'] ?? '') }}" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="birth_order" class="form-label">Anak Ke- (Urutan Lahir) <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control font-mono-meta" 
                               id="birth_order" 
                               name="birth_order" 
                               value="{{ old('birth_order', $formData['identity']['birth_order'] ?? '') }}" 
                               placeholder="Contoh: 1, 2, dst." 
                               min="1" 
                               max="30" 
                               inputmode="numeric"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="form-text">Urutan kelahiran anak dalam susunan keluarga.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="siblings_count" class="form-label">Dari Berapa Bersaudara <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control font-mono-meta" 
                               id="siblings_count" 
                               name="siblings_count" 
                               value="{{ old('siblings_count', $formData['identity']['siblings_count'] ?? '') }}" 
                               placeholder="Contoh: 3" 
                               min="1" 
                               max="30" 
                               inputmode="numeric"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="form-text">Total saudara kandung termasuk diri siswa.</div>
                    </div>
                </div>

                <div class="mobile-action-bar">
                    <button type="button" class="btn-minimal-secondary px-4" onclick="prevStep(2)">
                        <i class="ph-bold ph-arrow-left me-1"></i> Sebelumnya
                    </button>
                    <button type="button" class="btn-minimal-primary px-4" onclick="nextStep(2)">
                        Selanjutnya: Domisili <i class="ph-bold ph-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 3: ALAMAT DOMISILI -->
            <!-- ============================================== -->
            <div class="form-section" id="step-3">
                <div class="bento-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="font-mono-meta small text-secondary">TAHAP 03 / DOMISILI</span>
                        <h5 class="fw-semibold text-dark mb-0">Alamat Domisili Siswa</h5>
                    </div>
                    <span class="badge-pastel badge-pastel-neutral">TEMPAT TINGGAL</span>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="street_address" class="form-label">Alamat Jalan / Tempat Tinggal <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="street_address" 
                                  name="street_address" 
                                  rows="2" 
                                  placeholder="Nama jalan, nomor rumah, perumahan" 
                                  required 
                                  {{ $isLocked ? 'disabled' : '' }}>{{ old('street_address', $formData['address']['street_address'] ?? '') }}</textarea>
                    </div>

                    <div class="col-md-3 col-6">
                        <label for="rt" class="form-label">RT <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-mono-meta" 
                               id="rt" 
                               name="rt" 
                               value="{{ old('rt', $formData['address']['rt'] ?? '') }}" 
                               placeholder="Contoh: 002" 
                               maxlength="5" 
                               inputmode="numeric"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-3 col-6">
                        <label for="rw" class="form-label">RW <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-mono-meta" 
                               id="rw" 
                               name="rw" 
                               value="{{ old('rw', $formData['address']['rw'] ?? '') }}" 
                               placeholder="Contoh: 005" 
                               maxlength="5" 
                               inputmode="numeric"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="village" class="form-label">Kelurahan / Desa <span class="text-danger">*</span></label>
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
                        <label for="district" class="form-label">Kecamatan <span class="text-danger">*</span></label>
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
                        <label for="postal_code" class="form-label">Kode Pos <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-mono-meta" 
                               id="postal_code" 
                               name="postal_code" 
                               value="{{ old('postal_code', $formData['address']['postal_code'] ?? '') }}" 
                               placeholder="5 digit kode pos" 
                               maxlength="10" 
                               inputmode="numeric"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="residence_type" class="form-label">Status Tempat Tinggal <span class="text-danger">*</span></label>
                        <select class="form-select" id="residence_type" name="residence_type" required {{ $isLocked ? 'disabled' : '' }}>
                            <option value="">Pilih Status Tinggal</option>
                            @php
                                $resTypes = ['Bersama Orang Tua', 'Wali', 'Kos', 'Asrama', 'Panti Asuhan', 'Lainnya'];
                                $currRes = old('residence_type', $formData['address']['residence_type'] ?? '');
                            @endphp
                            @foreach($resTypes as $rt)
                                <option value="{{ $rt }}" {{ $currRes === $rt ? 'selected' : '' }}>{{ $rt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="transportation_mode" class="form-label">Moda Transportasi ke Sekolah <span class="text-danger">*</span></label>
                        <select class="form-select" id="transportation_mode" name="transportation_mode" required {{ $isLocked ? 'disabled' : '' }}>
                            <option value="">Pilih Transportasi</option>
                            @php
                                $transModes = ['Jalan Kaki', 'Sepeda Motor', 'Mobil Pribadi', 'Angkutan Umum', 'Antar Jemput Sekolah', 'Kereta Api / KRL', 'Lainnya'];
                                $currTrans = old('transportation_mode', $formData['address']['transportation_mode'] ?? '');
                            @endphp
                            @foreach($transModes as $tm)
                                <option value="{{ $tm }}" {{ $currTrans === $tm ? 'selected' : '' }}>{{ $tm }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mobile-action-bar">
                    <button type="button" class="btn-minimal-secondary px-4" onclick="prevStep(3)">
                        <i class="ph-bold ph-arrow-left me-1"></i> Sebelumnya
                    </button>
                    <button type="button" class="btn-minimal-primary px-4" onclick="nextStep(3)">
                        Selanjutnya: Kontak <i class="ph-bold ph-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 4: KONTAK SISWA -->
            <!-- ============================================== -->
            <div class="form-section" id="step-4">
                <div class="bento-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="font-mono-meta small text-secondary">TAHAP 04 / KONTAK</span>
                        <h5 class="fw-semibold text-dark mb-0">Kontak & Komunikasi Siswa</h5>
                    </div>
                    <span class="badge-pastel badge-pastel-neutral">KOMUNIKASI AKTIF</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="mobile_number" class="form-label">Nomor Handphone (HP) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-mono-meta" 
                               id="mobile_number" 
                               name="mobile_number" 
                               value="{{ old('mobile_number', $formData['contact']['mobile_number'] ?? '') }}" 
                               placeholder="Contoh: 081234567890" 
                               inputmode="tel"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="whatsapp_number" class="form-label">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-mono-meta" 
                               id="whatsapp_number" 
                               name="whatsapp_number" 
                               value="{{ old('whatsapp_number', $formData['contact']['whatsapp_number'] ?? '') }}" 
                               placeholder="Contoh: 081234567890" 
                               inputmode="tel"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="form-text">Digunakan untuk konfirmasi dan pengumuman instan.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="phone_number" class="form-label">Nomor Telepon Rumah</label>
                        <input type="text" 
                               class="form-control font-mono-meta" 
                               id="phone_number" 
                               name="phone_number" 
                               value="{{ old('phone_number', $formData['contact']['phone_number'] ?? '') }}" 
                               placeholder="Contoh: 021-77889900 (opsional)" 
                               inputmode="tel"
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Alamat Email Siswa <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $formData['contact']['email'] ?? session('email')) }}" 
                               placeholder="nama@siswa.sch.id" 
                               inputmode="email"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="mobile-action-bar">
                    <button type="button" class="btn-minimal-secondary px-4" onclick="prevStep(4)">
                        <i class="ph-bold ph-arrow-left me-1"></i> Sebelumnya
                    </button>
                    <button type="button" class="btn-minimal-primary px-4" onclick="nextStep(4)">
                        Selanjutnya: Orang Tua <i class="ph-bold ph-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 5: DATA ORANG TUA / WALI -->
            <!-- ============================================== -->
            <div class="form-section" id="step-5">
                <div class="bento-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="font-mono-meta small text-secondary">TAHAP 05 / KELUARGA</span>
                        <h5 class="fw-semibold text-dark mb-0">Data Orang Tua / Wali Siswa</h5>
                    </div>
                    <span class="badge-pastel badge-pastel-neutral">DATA KELUARGA</span>
                </div>

                <!-- Segmented Tabs for Parents -->
                <div class="segmented-control mb-4" id="parentTabs" role="tablist">
                    <button class="segmented-btn active" id="father-tab" data-bs-toggle="pill" data-bs-target="#father-pane" type="button" role="tab">
                        <i class="ph-bold ph-user"></i> Ayah Kandung
                    </button>
                    <button class="segmented-btn" id="mother-tab" data-bs-toggle="pill" data-bs-target="#mother-pane" type="button" role="tab">
                        <i class="ph-bold ph-heart"></i> Ibu Kandung
                    </button>
                    <button class="segmented-btn" id="guardian-tab" data-bs-toggle="pill" data-bs-target="#guardian-pane" type="button" role="tab">
                        <i class="ph-bold ph-shield"></i> Wali (Opsional)
                    </button>
                </div>

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
                    <!-- Tab Ayah Kandung -->
                    <div class="tab-pane fade show active" id="father-pane" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="father_nik" class="form-label">NIK Ayah <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control font-mono-meta" 
                                       id="father_nik" 
                                       name="father_nik" 
                                       value="{{ old('father_nik', $father['nik'] ?? '') }}" 
                                       placeholder="16 digit NIK ayah" 
                                       maxlength="16" 
                                       inputmode="numeric"
                                       pattern="\d{16}"
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-6">
                                <label for="father_name" class="form-label">Nama Lengkap Ayah <span class="text-danger">*</span></label>
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
                                <label for="father_birth_year" class="form-label">Tahun Lahir Ayah <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control font-mono-meta" 
                                       id="father_birth_year" 
                                       name="father_birth_year" 
                                       value="{{ old('father_birth_year', $father['birth_year'] ?? '') }}" 
                                       placeholder="Contoh: 1978" 
                                       min="1930" 
                                       max="2015" 
                                       inputmode="numeric"
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="father_education" class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                <select class="form-select" id="father_education" name="father_education" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Pendidikan</option>
                                    @php $fEdu = old('father_education', $father['education_code'] ?? ''); @endphp
                                    @foreach($educationList as $code => $label)
                                        <option value="{{ $code }}" {{ $fEdu == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="father_occupation" class="form-label">Pekerjaan <span class="text-danger">*</span></label>
                                <select class="form-select" id="father_occupation" name="father_occupation" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Pekerjaan</option>
                                    @php $fOcc = old('father_occupation', $father['occupation_code'] ?? ''); @endphp
                                    @foreach($occupationList as $code => $label)
                                        <option value="{{ $code }}" {{ $fOcc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="father_income" class="form-label">Penghasilan Bulanan <span class="text-danger">*</span></label>
                                <select class="form-select" id="father_income" name="father_income" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Penghasilan</option>
                                    @php $fInc = old('father_income', $father['income_code'] ?? ''); @endphp
                                    @foreach($incomeList as $code => $label)
                                        <option value="{{ $code }}" {{ $fInc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="father_phone" class="form-label">No HP Ayah</label>
                                <input type="text" 
                                       class="form-control font-mono-meta" 
                                       id="father_phone" 
                                       name="father_phone" 
                                       value="{{ old('father_phone', $father['phone_number'] ?? '') }}" 
                                       placeholder="0813..." 
                                       inputmode="tel"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="father_whatsapp" class="form-label">No WhatsApp Ayah</label>
                                <input type="text" 
                                       class="form-control font-mono-meta" 
                                       id="father_whatsapp" 
                                       name="father_whatsapp" 
                                       value="{{ old('father_whatsapp', $father['whatsapp_number'] ?? '') }}" 
                                       placeholder="0813..." 
                                       inputmode="tel"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-12">
                                <label for="father_email" class="form-label">Alamat Email Ayah</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="father_email" 
                                       name="father_email" 
                                       value="{{ old('father_email', $father['email'] ?? '') }}" 
                                       placeholder="ayah@example.com (opsional)" 
                                       inputmode="email"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Ibu Kandung -->
                    <div class="tab-pane fade" id="mother-pane" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="mother_nik" class="form-label">NIK Ibu <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control font-mono-meta" 
                                       id="mother_nik" 
                                       name="mother_nik" 
                                       value="{{ old('mother_nik', $mother['nik'] ?? '') }}" 
                                       placeholder="16 digit NIK ibu" 
                                       maxlength="16" 
                                       inputmode="numeric"
                                       pattern="\d{16}"
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-6">
                                <label for="mother_name" class="form-label">Nama Lengkap Ibu <span class="text-danger">*</span></label>
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
                                <label for="mother_birth_year" class="form-label">Tahun Lahir Ibu <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control font-mono-meta" 
                                       id="mother_birth_year" 
                                       name="mother_birth_year" 
                                       value="{{ old('mother_birth_year', $mother['birth_year'] ?? '') }}" 
                                       placeholder="Contoh: 1980" 
                                       min="1930" 
                                       max="2015" 
                                       inputmode="numeric"
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_education" class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                <select class="form-select" id="mother_education" name="mother_education" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Pendidikan</option>
                                    @php $mEdu = old('mother_education', $mother['education_code'] ?? ''); @endphp
                                    @foreach($educationList as $code => $label)
                                        <option value="{{ $code }}" {{ $mEdu == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_occupation" class="form-label">Pekerjaan <span class="text-danger">*</span></label>
                                <select class="form-select" id="mother_occupation" name="mother_occupation" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Pekerjaan</option>
                                    @php $mOcc = old('mother_occupation', $mother['occupation_code'] ?? ''); @endphp
                                    @foreach($occupationList as $code => $label)
                                        <option value="{{ $code }}" {{ $mOcc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_income" class="form-label">Penghasilan Bulanan <span class="text-danger">*</span></label>
                                <select class="form-select" id="mother_income" name="mother_income" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Penghasilan</option>
                                    @php $mInc = old('mother_income', $mother['income_code'] ?? ''); @endphp
                                    @foreach($incomeList as $code => $label)
                                        <option value="{{ $code }}" {{ $mInc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_phone" class="form-label">No HP Ibu</label>
                                <input type="text" 
                                       class="form-control font-mono-meta" 
                                       id="mother_phone" 
                                       name="mother_phone" 
                                       value="{{ old('mother_phone', $mother['phone_number'] ?? '') }}" 
                                       placeholder="0813..." 
                                       inputmode="tel"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_whatsapp" class="form-label">No WhatsApp Ibu</label>
                                <input type="text" 
                                       class="form-control font-mono-meta" 
                                       id="mother_whatsapp" 
                                       name="mother_whatsapp" 
                                       value="{{ old('mother_whatsapp', $mother['whatsapp_number'] ?? '') }}" 
                                       placeholder="0813..." 
                                       inputmode="tel"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-12">
                                <label for="mother_email" class="form-label">Alamat Email Ibu</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="mother_email" 
                                       name="mother_email" 
                                       value="{{ old('mother_email', $mother['email'] ?? '') }}" 
                                       placeholder="ibu@example.com (opsional)" 
                                       inputmode="email"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Wali Siswa -->
                    <div class="tab-pane fade" id="guardian-pane" role="tabpanel">
                        <div class="p-3 rounded-2 border mb-3" style="background-color: var(--surface-muted); border-color: var(--border-light) !important;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_guardian" name="has_guardian" value="1" 
                                    {{ old('has_guardian', !empty($guardian) ? '1' : '') ? 'checked' : '' }} onchange="toggleGuardianFields(this.checked)" {{ $isLocked ? 'disabled' : '' }}>
                                <label class="form-check-label fw-medium text-dark small" for="has_guardian">
                                    Calon siswa diasuh / tinggal bersama Wali (selain orang tua kandung)
                                </label>
                            </div>
                        </div>

                        <div id="guardian-fields" style="display: {{ old('has_guardian', !empty($guardian) ? '1' : '') ? 'block' : 'none' }};">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="guardian_nik" class="form-label">NIK Wali</label>
                                    <input type="text" 
                                           class="form-control font-mono-meta" 
                                           id="guardian_nik" 
                                           name="guardian_nik" 
                                           value="{{ old('guardian_nik', $guardian['nik'] ?? '') }}" 
                                           placeholder="16 digit NIK wali" 
                                           maxlength="16" 
                                           inputmode="numeric"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-6">
                                    <label for="guardian_name" class="form-label">Nama Lengkap Wali</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="guardian_name" 
                                           name="guardian_name" 
                                           value="{{ old('guardian_name', $guardian['full_name'] ?? '') }}" 
                                           placeholder="Nama lengkap wali siswa" 
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_birth_year" class="form-label">Tahun Lahir Wali</label>
                                    <input type="number" 
                                           class="form-control font-mono-meta" 
                                           id="guardian_birth_year" 
                                           name="guardian_birth_year" 
                                           value="{{ old('guardian_birth_year', $guardian['birth_year'] ?? '') }}" 
                                           placeholder="Contoh: 1982" 
                                           min="1930" 
                                           max="2015" 
                                           inputmode="numeric"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_education" class="form-label">Pendidikan Terakhir</label>
                                    <select class="form-select" id="guardian_education" name="guardian_education" {{ $isLocked ? 'disabled' : '' }}>
                                        <option value="">Pilih Pendidikan</option>
                                        @php $gEdu = old('guardian_education', $guardian['education_code'] ?? ''); @endphp
                                        @foreach($educationList as $code => $label)
                                            <option value="{{ $code }}" {{ $gEdu == $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_occupation" class="form-label">Pekerjaan</label>
                                    <select class="form-select" id="guardian_occupation" name="guardian_occupation" {{ $isLocked ? 'disabled' : '' }}>
                                        <option value="">Pilih Pekerjaan</option>
                                        @php $gOcc = old('guardian_occupation', $guardian['occupation_code'] ?? ''); @endphp
                                        @foreach($occupationList as $code => $label)
                                            <option value="{{ $code }}" {{ $gOcc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_income" class="form-label">Penghasilan Bulanan</label>
                                    <select class="form-select" id="guardian_income" name="guardian_income" {{ $isLocked ? 'disabled' : '' }}>
                                        <option value="">Pilih Penghasilan</option>
                                        @php $gInc = old('guardian_income', $guardian['income_code'] ?? ''); @endphp
                                        @foreach($incomeList as $code => $label)
                                            <option value="{{ $code }}" {{ $gInc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_phone" class="form-label">No HP Wali</label>
                                    <input type="text" 
                                           class="form-control font-mono-meta" 
                                           id="guardian_phone" 
                                           name="guardian_phone" 
                                           value="{{ old('guardian_phone', $guardian['phone_number'] ?? '') }}" 
                                           placeholder="0813..." 
                                           inputmode="tel"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_whatsapp" class="form-label">No WhatsApp Wali</label>
                                    <input type="text" 
                                           class="form-control font-mono-meta" 
                                           id="guardian_whatsapp" 
                                           name="guardian_whatsapp" 
                                           value="{{ old('guardian_whatsapp', $guardian['whatsapp_number'] ?? '') }}" 
                                           placeholder="0813..." 
                                           inputmode="tel"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-12">
                                    <label for="guardian_email" class="form-label">Alamat Email Wali</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="guardian_email" 
                                           name="guardian_email" 
                                           value="{{ old('guardian_email', $guardian['email'] ?? '') }}" 
                                           placeholder="wali@example.com (opsional)" 
                                           inputmode="email"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mobile-action-bar">
                    <button type="button" class="btn-minimal-secondary px-4" onclick="prevStep(5)">
                        <i class="ph-bold ph-arrow-left me-1"></i> Sebelumnya
                    </button>
                    <button type="button" class="btn-minimal-primary px-4" onclick="nextStep(5)">
                        Tinjau Isian <i class="ph-bold ph-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAHAP 6: REVIEW & KONFIRMASI -->
            <!-- ============================================== -->
            <div class="form-section" id="step-6">
                <div class="bento-header d-flex align-items-center justify-content-between">
                    <div>
                        <span class="font-mono-meta small text-secondary">TAHAP 06 / VERIFIKASI AKHIR</span>
                        <h5 class="fw-semibold text-dark mb-0">Tinjau & Simpan Formulir Pendaftaran</h5>
                    </div>
                    <span class="badge-pastel badge-pastel-green">LEMBAR PERIKSA</span>
                </div>

                <!-- Document Summary Sheet -->
                <div class="p-3 p-md-4 bg-white rounded-2 border mb-4" style="border-color: var(--border-light) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: var(--border-light) !important;">
                        <span class="font-mono-meta small text-secondary">RINGKASAN ISIAN PENDAFTARAN</span>
                        <span class="badge-pastel badge-pastel-neutral">SIAP KIRIM</span>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-md-6">
                            <span class="text-secondary d-block">Nama Lengkap Siswa:</span>
                            <strong class="text-dark" id="review-name">-</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary d-block">NIK / NISN:</span>
                            <span class="font-mono-meta text-dark fw-medium" id="review-nik-nisn">-</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary d-block">Pilihan Jurusan:</span>
                            <span class="badge-pastel badge-pastel-blue fw-semibold" id="review-major">-</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary d-block">Asal Sekolah:</span>
                            <span class="text-dark fw-medium" id="review-school">-</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary d-block">Identitas & Kelahiran:</span>
                            <span class="text-dark fw-medium" id="review-ttl">-</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary d-block">Kontak (HP / WA):</span>
                            <span class="font-mono-meta text-dark fw-medium" id="review-contact">-</span>
                        </div>
                        <div class="col-12">
                            <span class="text-secondary d-block">Alamat Domisili:</span>
                            <span class="text-dark fw-medium" id="review-address">-</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary d-block">Data Ayah Kandung:</span>
                            <span class="text-dark fw-medium" id="review-father">-</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-secondary d-block">Data Ibu Kandung:</span>
                            <span class="text-dark fw-medium" id="review-mother">-</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-2 border mb-4" style="background-color: var(--surface-muted); border-color: var(--border-light) !important;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmation_check" required {{ $isLocked ? 'disabled' : '' }}>
                        <label class="form-check-label small text-dark" for="confirmation_check">
                            <strong>Pernyataan Kebenaran Data:</strong> Saya menyatakan bahwa seluruh data pokok, identitas tambahan, domisili, dan data orang tua/wali yang diisikan adalah benar dan sesuai dengan dokumen resmi negara (Kartu Keluarga, Akta Kelahiran, dan Ijazah).
                        </label>
                    </div>
                </div>

                <div class="mobile-action-bar">
                    <button type="button" class="btn-minimal-secondary px-4" onclick="prevStep(6)">
                        <i class="ph-bold ph-arrow-left me-1"></i> Perbaiki Isian
                    </button>
                    @if(!$isLocked)
                        <button type="submit" class="btn-minimal-primary px-4">
                            <i class="ph-bold ph-cloud-arrow-up me-1"></i> Simpan & Kirim Formulir
                        </button>
                    @else
                        <button type="button" class="btn-minimal-secondary px-4" disabled>
                            <i class="ph-bold ph-lock-key me-1"></i> Formulir Terkunci
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

    const stepTitles = [
        'Tahap 1: Biodata Pokok Siswa',
        'Tahap 2: Identitas Tambahan',
        'Tahap 3: Alamat Domisili',
        'Tahap 4: Kontak & Komunikasi',
        'Tahap 5: Data Orang Tua / Wali',
        'Tahap 6: Tinjau & Simpan Formulir'
    ];

    function showStep(step) {
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

        const targetSection = document.getElementById('step-' + step);
        const targetIndicator = document.getElementById('step-indicator-' + step);
        if (targetSection) targetSection.classList.add('active');
        if (targetIndicator) targetIndicator.classList.add('active');

        // Update Mobile Progress
        const mobileLabel = document.getElementById('mobile-step-label');
        const mobilePercent = document.getElementById('mobile-step-percent');
        const mobileProgress = document.getElementById('mobile-step-progress');
        const mobileTitle = document.getElementById('mobile-step-title');

        const pct = Math.round((step / totalSteps) * 100);
        if (mobileLabel) mobileLabel.textContent = `TAHAP 0${step} DARI 0${totalSteps}`;
        if (mobilePercent) mobilePercent.textContent = `${pct}%`;
        if (mobileProgress) mobileProgress.style.width = `${pct}%`;
        if (mobileTitle) mobileTitle.textContent = stepTitles[step - 1];

        if (step === 6) {
            populateReview();
        }

        window.scrollTo({ top: 60, behavior: 'smooth' });
    }

    function validateStep(step) {
        const currentSection = document.getElementById('step-' + step);
        if (!currentSection) return true;

        const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.checkValidity()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }

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
        const birthOrder = document.getElementById('birth_order')?.value || '-';
        const siblingsCount = document.getElementById('siblings_count')?.value || '-';
        const mobile = document.getElementById('mobile_number')?.value || '-';
        const wa = document.getElementById('whatsapp_number')?.value || '-';
        const street = document.getElementById('street_address')?.value || '-';
        const rt = document.getElementById('rt')?.value || '';
        const rw = document.getElementById('rw')?.value || '';
        const village = document.getElementById('village')?.value || '';
        const district = document.getElementById('district')?.value || '';
        const fatherName = document.getElementById('father_name')?.value || '-';
        const fatherEmail = document.getElementById('father_email')?.value || '';
        const motherName = document.getElementById('mother_name')?.value || '-';
        const motherEmail = document.getElementById('mother_email')?.value || '';

        const reviewName = document.getElementById('review-name');
        const reviewNikNisn = document.getElementById('review-nik-nisn');
        const reviewMajor = document.getElementById('review-major');
        const reviewSchool = document.getElementById('review-school');
        const reviewTtl = document.getElementById('review-ttl');
        const reviewContact = document.getElementById('review-contact');
        const reviewAddress = document.getElementById('review-address');
        const reviewFather = document.getElementById('review-father');
        const reviewMother = document.getElementById('review-mother');

        // Pilihan Jurusan & Asal Sekolah
        const majorRadio = document.querySelector('input[name="major"]:checked');
        const majorLabels = {
            'reguler': 'REGULER',
            'bahasa': 'BAHASA',
            'tahfidz': 'TAHFIDZ',
            'ict': 'ICT (TEKNOLOGI)'
        };
        const majorVal = majorRadio ? (majorLabels[majorRadio.value] || majorRadio.value.toUpperCase()) : '-';
        const schoolOrigin = document.getElementById('school_origin')?.value || '-';
        const schoolOriginAddress = document.getElementById('school_origin_address')?.value || '';

        if (reviewName) reviewName.textContent = fullName;
        if (reviewNikNisn) reviewNikNisn.textContent = `${nik} / ${nisn}`;
        if (reviewMajor) reviewMajor.textContent = majorVal;
        if (reviewSchool) reviewSchool.textContent = schoolOriginAddress ? `${schoolOrigin} — ${schoolOriginAddress}` : schoolOrigin;
        if (reviewTtl) reviewTtl.textContent = `${gender}, ${pob} (${dob}) — Anak ke-${birthOrder} dari ${siblingsCount} bersaudara`;
        if (reviewContact) reviewContact.textContent = `${mobile} (WhatsApp: ${wa})`;
        if (reviewAddress) reviewAddress.textContent = `${street}, RT ${rt}/RW ${rw}, Kel. ${village}, Kec. ${district}`;
        if (reviewFather) reviewFather.textContent = fatherEmail ? `${fatherName} (${fatherEmail})` : fatherName;
        if (reviewMother) reviewMother.textContent = motherEmail ? `${motherName} (${motherEmail})` : motherName;
    }

    // Segmented buttons tab switching style sync
    document.querySelectorAll('#parentTabs .segmented-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#parentTabs .segmented-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endpush
