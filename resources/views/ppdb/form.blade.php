@extends('layouts.app')

@section('title', 'Formulir Pendaftaran Siswa Baru')

@section('content')
<div class="db-content db-form-container">

    {{-- Page Header --}}
    <div class="db-page-header">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:0.5rem;">
            <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:4px;font-size:13px;font-weight:600;color:var(--lp-primary);text-decoration:none;">
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Dashboard
            </a>
            <span style="color:var(--lp-outline);font-size:13px;">/</span>
            <span style="font-size:13px;font-weight:600;color:var(--lp-on-surface);">Formulir Pendaftaran</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 class="db-page-title">Formulir Pendaftaran Calon Siswa</h1>
                <p class="db-page-subtitle">Lengkapi 6 tahap isian data calon siswa dan orang tua. Data tersimpan aman di sistem PPDB.</p>
            </div>
            <div>
                @if($isLocked)
                    <span class="db-badge db-badge-green">
                        <span class="material-symbols-outlined" style="font-size:15px;">lock</span> FORMULIR TERKUNCI (DITERIMA)
                    </span>
                @else
                    <span class="db-badge db-badge-blue">
                        <span class="material-symbols-outlined" style="font-size:15px;">edit_note</span> DAPAT DISUNTING
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if($isLocked)
        <div style="background:var(--lp-green-light);border:1.5px solid rgba(22,163,74,0.3);border-radius:14px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:12px;">
            <span class="material-symbols-outlined" style="font-size:24px;color:var(--lp-green);flex-shrink:0;margin-top:2px;">verified_user</span>
            <div style="font-size:13.5px;color:var(--lp-green);line-height:1.5;">
                <strong>Pendaftaran Telah Diterima Resmi:</strong> Data formulir telah diverifikasi dan dikunci oleh panitia untuk menjaga keaslian data. Anda dapat meninjau isian di bawah ini dalam mode baca saja.
            </div>
        </div>
    @endif

    {{-- Desktop Stepper Bar --}}
    <div class="db-stepper">
        <div class="db-step-item active" id="step-indicator-1">
            <div class="db-step-number">1</div>
            <span>Biodata Pokok</span>
        </div>
        <div class="db-step-item" id="step-indicator-2">
            <div class="db-step-number">2</div>
            <span>Identitas Tambahan</span>
        </div>
        <div class="db-step-item" id="step-indicator-3">
            <div class="db-step-number">3</div>
            <span>Alamat Domisili</span>
        </div>
        <div class="db-step-item" id="step-indicator-4">
            <div class="db-step-number">4</div>
            <span>Kontak Siswa</span>
        </div>
        <div class="db-step-item" id="step-indicator-5">
            <div class="db-step-number">5</div>
            <span>Orang Tua / Wali</span>
        </div>
        <div class="db-step-item" id="step-indicator-6">
            <div class="db-step-number">6</div>
            <span>Tinjau & Simpan</span>
        </div>
    </div>

    {{-- Mobile Stepper Card --}}
    <div class="db-mobile-stepper">
        <div class="db-mobile-stepper-header">
            <span id="mobile-step-label">LANGKAH 1 DARI 6</span>
            <span id="mobile-step-percent">16%</span>
        </div>
        <div style="height:6px;background:var(--lp-surface-container);border-radius:999px;overflow:hidden;">
            <div id="mobile-step-progress" style="height:100%;width:16%;background:linear-gradient(90deg,var(--lp-primary),var(--lp-primary-container));border-radius:999px;transition:width 0.3s ease;"></div>
        </div>
        <div class="db-mobile-stepper-title" id="mobile-step-title">Langkah 1: Biodata Pokok Siswa</div>
    </div>

    {{-- Main Wizard Card --}}
    <div class="db-form-card">
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

            {{-- ============================================== --}}
            {{-- TAHAP 1: BIODATA POKOK SISWA                  --}}
            {{-- ============================================== --}}
            <div class="form-section active" id="step-1">
                <div class="db-form-section-header">
                    <div>
                        <h3 class="db-form-section-title">
                            <span class="material-symbols-outlined" style="color:var(--lp-primary);">person</span>
                            Biodata Pokok Calon Siswa
                        </h3>
                        <div class="db-form-section-subtitle">Data pokok siswa sesuai Kartu Keluarga & Akta Kelahiran</div>
                    </div>
                    <span class="db-badge db-badge-blue">LANGKAH 1 DARI 6</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nik" class="db-label">Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="nik" 
                               name="nik" 
                               value="{{ old('nik', $formData['nik'] ?? '') }}" 
                               placeholder="16 digit NIK pada KK"
                               maxlength="16" 
                               inputmode="numeric"
                               pattern="\d{16}"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="db-helper">16 digit angka sesuai Kartu Keluarga (KK).</div>
                    </div>

                    <div class="col-md-6">
                        <label for="nisn" class="db-label">Nomor Induk Siswa Nasional (NISN) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="nisn" 
                               name="nisn" 
                               value="{{ old('nisn', $formData['nisn'] ?? '') }}" 
                               placeholder="Contoh: 0051234567" 
                               maxlength="10" 
                               inputmode="numeric"
                               pattern="\d{10}"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="db-helper">10 digit nomor NISN dari sekolah asal (SD/MI).</div>
                    </div>

                    <div class="col-12">
                        <label for="full_name" class="db-label">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="full_name" 
                               name="full_name" 
                               value="{{ old('full_name', $formData['full_name'] ?? session('full_name')) }}" 
                               placeholder="Nama lengkap sesuai akta kelahiran resmi" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="first_name" class="db-label">Nama Depan <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="first_name" 
                               name="first_name" 
                               value="{{ old('first_name', $formData['first_name'] ?? '') }}" 
                               placeholder="Contoh: Ahmad" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="last_name" class="db-label">Nama Belakang</label>
                        <input type="text" 
                               class="db-input" 
                               id="last_name" 
                               name="last_name" 
                               value="{{ old('last_name', $formData['last_name'] ?? '') }}" 
                               placeholder="Contoh: Fauzi Rahman (opsional)" 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    {{-- Pilihan Jurusan / Peminatan --}}
                    <div class="col-12 mt-3 pt-2">
                        <label class="db-label">
                            Pilihan Jurusan / Peminatan <span class="text-danger">*</span>
                        </label>
                        <div class="db-helper" style="margin-top:-2px;margin-bottom:8px;">Pilih salah satu program peminatan yang diminati calon siswa:</div>
                        @php $currMajor = old('major', $formData['major'] ?? ''); @endphp
                        <div class="db-major-grid">
                            {{-- 1. Reguler --}}
                            <label class="db-major-card {{ $currMajor === 'reguler' ? 'selected' : '' }}" onclick="selectMajorCard(this)">
                                <div class="db-major-card-icon" style="background:var(--lp-primary);">
                                    <span class="material-symbols-outlined">school</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="radio" name="major" value="reguler" style="display:none;" 
                                           {{ $currMajor === 'reguler' ? 'checked' : '' }} required {{ $isLocked ? 'disabled' : '' }}>
                                    <div class="db-major-card-title">Reguler</div>
                                </div>
                                <div class="db-major-card-desc">Kurikulum nasional standar & pengembangan minat umum</div>
                            </label>

                            {{-- 2. Bahasa --}}
                            <label class="db-major-card {{ $currMajor === 'bahasa' ? 'selected' : '' }}" onclick="selectMajorCard(this)">
                                <div class="db-major-card-icon" style="background:#0284c7;">
                                    <span class="material-symbols-outlined">translate</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="radio" name="major" value="bahasa" style="display:none;" 
                                           {{ $currMajor === 'bahasa' ? 'checked' : '' }} required {{ $isLocked ? 'disabled' : '' }}>
                                    <div class="db-major-card-title">Bahasa</div>
                                </div>
                                <div class="db-major-card-desc">Peminatan bahasa asing (Arab & Inggris) dan literasi</div>
                            </label>

                            {{-- 3. Tahfidz --}}
                            <label class="db-major-card {{ $currMajor === 'tahfidz' ? 'selected' : '' }}" onclick="selectMajorCard(this)">
                                <div class="db-major-card-icon" style="background:var(--lp-green);">
                                    <span class="material-symbols-outlined">menu_book</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="radio" name="major" value="tahfidz" style="display:none;" 
                                           {{ $currMajor === 'tahfidz' ? 'checked' : '' }} required {{ $isLocked ? 'disabled' : '' }}>
                                    <div class="db-major-card-title">Tahfidz</div>
                                </div>
                                <div class="db-major-card-desc">Program intensif hafalan Al-Qur'an dan tahsin tartil</div>
                            </label>

                            {{-- 4. ICT --}}
                            <label class="db-major-card {{ $currMajor === 'ict' ? 'selected' : '' }}" onclick="selectMajorCard(this)">
                                <div class="db-major-card-icon" style="background:#7c3aed;">
                                    <span class="material-symbols-outlined">computer</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="radio" name="major" value="ict" style="display:none;" 
                                           {{ $currMajor === 'ict' ? 'checked' : '' }} required {{ $isLocked ? 'disabled' : '' }}>
                                    <div class="db-major-card-title">ICT (Teknologi)</div>
                                </div>
                                <div class="db-major-card-desc">Teknologi informasi, dasar pemrograman & multimedia</div>
                            </label>
                        </div>
                    </div>

                    {{-- Asal Sekolah --}}
                    <div class="col-md-6 mt-3">
                        <label for="school_origin" class="db-label">Nama Asal Sekolah <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="school_origin" 
                               name="school_origin" 
                               value="{{ old('school_origin', $formData['school_origin'] ?? '') }}" 
                               placeholder="Contoh: SDN 1 Purwakarta / SDIT Al-Muhajirin" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="db-helper">Nama sekolah jenjang sebelumnya (SD/MI/Sederajat).</div>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label for="school_origin_address" class="db-label">Alamat / Kota Asal Sekolah <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="school_origin_address" 
                               name="school_origin_address" 
                               value="{{ old('school_origin_address', $formData['school_origin_address'] ?? '') }}" 
                               placeholder="Contoh: Jl. Veteran No. 12, Purwakarta" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="db-helper">Alamat ringkas atau kota lokasi asal sekolah.</div>
                    </div>
                </div>

                <div class="db-form-actions">
                    <div></div>
                    <button type="button" class="db-form-btn-next" onclick="nextStep(1)">
                        <span>Lanjut: Identitas Tambahan</span>
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                    </button>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- TAHAP 2: IDENTITAS TAMBAHAN                   --}}
            {{-- ============================================== --}}
            <div class="form-section" id="step-2">
                <div class="db-form-section-header">
                    <div>
                        <h3 class="db-form-section-title">
                            <span class="material-symbols-outlined" style="color:var(--lp-primary);">badge</span>
                            Identitas Tambahan Siswa
                        </h3>
                        <div class="db-form-section-subtitle">Data nomor kartu keluarga, jenis kelamin, dan tanggal lahir</div>
                    </div>
                    <span class="db-badge db-badge-blue">LANGKAH 2 DARI 6</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="family_card_number" class="db-label">Nomor Kartu Keluarga (No KK) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="family_card_number" 
                               name="family_card_number" 
                               value="{{ old('family_card_number', $formData['identity']['family_card_number'] ?? '') }}" 
                               placeholder="16 digit nomor KK" 
                               maxlength="16" 
                               inputmode="numeric"
                               pattern="\d{16}"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="db-helper">Nomor KK yang tertera di bagian atas Kartu Keluarga.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="gender" class="db-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="db-select" id="gender" name="gender" required {{ $isLocked ? 'disabled' : '' }}>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" {{ old('gender', $formData['identity']['gender'] ?? '') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender', $formData['identity']['gender'] ?? '') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="religion" class="db-label">Agama <span class="text-danger">*</span></label>
                        <select class="db-select" id="religion" name="religion" required {{ $isLocked ? 'disabled' : '' }}>
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
                        <label for="place_of_birth" class="db-label">Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="place_of_birth" 
                               name="place_of_birth" 
                               value="{{ old('place_of_birth', $formData['identity']['place_of_birth'] ?? '') }}" 
                               placeholder="Kota/Kabupaten lahir" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-4">
                        <label for="date_of_birth" class="db-label">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="db-input" 
                               id="date_of_birth" 
                               name="date_of_birth" 
                               value="{{ old('date_of_birth', $formData['identity']['date_of_birth'] ?? '') }}" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="birth_order" class="db-label">Anak Ke- (Urutan Lahir) <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="db-input" 
                               id="birth_order" 
                               name="birth_order" 
                               value="{{ old('birth_order', $formData['identity']['birth_order'] ?? '') }}" 
                               placeholder="Contoh: 1, 2, dst." 
                               min="1" 
                               max="30" 
                               inputmode="numeric"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="siblings_count" class="db-label">Dari Berapa Bersaudara <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="db-input" 
                               id="siblings_count" 
                               name="siblings_count" 
                               value="{{ old('siblings_count', $formData['identity']['siblings_count'] ?? '') }}" 
                               placeholder="Contoh: 3" 
                               min="1" 
                               max="30" 
                               inputmode="numeric"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="db-helper">Jumlah saudara kandung termasuk diri siswa.</div>
                    </div>
                </div>

                <div class="db-form-actions">
                    <button type="button" class="db-form-btn-prev" onclick="prevStep(2)">
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                        <span>Sebelumnya</span>
                    </button>
                    <button type="button" class="db-form-btn-next" onclick="nextStep(2)">
                        <span>Lanjut: Alamat Domisili</span>
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                    </button>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- TAHAP 3: ALAMAT DOMISILI                      --}}
            {{-- ============================================== --}}
            <div class="form-section" id="step-3">
                <div class="db-form-section-header">
                    <div>
                        <h3 class="db-form-section-title">
                            <span class="material-symbols-outlined" style="color:var(--lp-primary);">home</span>
                            Alamat Domisili Siswa
                        </h3>
                        <div class="db-form-section-subtitle">Alamat tempat tinggal siswa saat ini</div>
                    </div>
                    <span class="db-badge db-badge-blue">LANGKAH 3 DARI 6</span>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="street_address" class="db-label">Alamat Jalan / Tempat Tinggal <span class="text-danger">*</span></label>
                        <textarea class="db-textarea" 
                                  id="street_address" 
                                  name="street_address" 
                                  rows="2" 
                                  placeholder="Nama jalan, gang, nomor rumah, atau nama perumahan" 
                                  required 
                                  {{ $isLocked ? 'disabled' : '' }}>{{ old('street_address', $formData['address']['street_address'] ?? '') }}</textarea>
                    </div>

                    <div class="col-md-3 col-6">
                        <label for="rt" class="db-label">RT <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
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
                        <label for="rw" class="db-label">RW <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
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
                        <label for="village" class="db-label">Kelurahan / Desa <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="village" 
                               name="village" 
                               value="{{ old('village', $formData['address']['village'] ?? '') }}" 
                               placeholder="Nama kelurahan atau desa" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="district" class="db-label">Kecamatan <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="district" 
                               name="district" 
                               value="{{ old('district', $formData['address']['district'] ?? '') }}" 
                               placeholder="Nama kecamatan" 
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="postal_code" class="db-label">Kode Pos <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
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
                        <label for="residence_type" class="db-label">Status Tempat Tinggal <span class="text-danger">*</span></label>
                        <select class="db-select" id="residence_type" name="residence_type" required {{ $isLocked ? 'disabled' : '' }}>
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
                        <label for="transportation_mode" class="db-label">Moda Transportasi ke Sekolah <span class="text-danger">*</span></label>
                        <select class="db-select" id="transportation_mode" name="transportation_mode" required {{ $isLocked ? 'disabled' : '' }}>
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

                <div class="db-form-actions">
                    <button type="button" class="db-form-btn-prev" onclick="prevStep(3)">
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                        <span>Sebelumnya</span>
                    </button>
                    <button type="button" class="db-form-btn-next" onclick="nextStep(3)">
                        <span>Lanjut: Kontak Siswa</span>
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                    </button>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- TAHAP 4: KONTAK SISWA                         --}}
            {{-- ============================================== --}}
            <div class="form-section" id="step-4">
                <div class="db-form-section-header">
                    <div>
                        <h3 class="db-form-section-title">
                            <span class="material-symbols-outlined" style="color:var(--lp-primary);">call</span>
                            Kontak & Komunikasi Siswa
                        </h3>
                        <div class="db-form-section-subtitle">Nomor handphone, WhatsApp, dan alamat email aktif</div>
                    </div>
                    <span class="db-badge db-badge-blue">LANGKAH 4 DARI 6</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="mobile_number" class="db-label">Nomor Handphone (HP) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="mobile_number" 
                               name="mobile_number" 
                               value="{{ old('mobile_number', $formData['contact']['mobile_number'] ?? '') }}" 
                               placeholder="Contoh: 081234567890" 
                               inputmode="tel"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="whatsapp_number" class="db-label">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="db-input" 
                               id="whatsapp_number" 
                               name="whatsapp_number" 
                               value="{{ old('whatsapp_number', $formData['contact']['whatsapp_number'] ?? '') }}" 
                               placeholder="Contoh: 081234567890" 
                               inputmode="tel"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                        <div class="db-helper">Digunakan untuk konfirmasi pendaftaran dan info pengumuman penting.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="phone_number" class="db-label">Nomor Telepon Rumah</label>
                        <input type="text" 
                               class="db-input" 
                               id="phone_number" 
                               name="phone_number" 
                               value="{{ old('phone_number', $formData['contact']['phone_number'] ?? '') }}" 
                               placeholder="Contoh: 0264-123456 (opsional)" 
                               inputmode="tel"
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="db-label">Alamat Email Siswa <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="db-input" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $formData['contact']['email'] ?? session('email')) }}" 
                               placeholder="nama@email.com" 
                               inputmode="email"
                               required 
                               {{ $isLocked ? 'disabled' : '' }}>
                    </div>
                </div>

                <div class="db-form-actions">
                    <button type="button" class="db-form-btn-prev" onclick="prevStep(4)">
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                        <span>Sebelumnya</span>
                    </button>
                    <button type="button" class="db-form-btn-next" onclick="nextStep(4)">
                        <span>Lanjut: Orang Tua</span>
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                    </button>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- TAHAP 5: DATA ORANG TUA / WALI                --}}
            {{-- ============================================== --}}
            <div class="form-section" id="step-5">
                <div class="db-form-section-header">
                    <div>
                        <h3 class="db-form-section-title">
                            <span class="material-symbols-outlined" style="color:var(--lp-primary);">family_restroom</span>
                            Data Orang Tua / Wali Siswa
                        </h3>
                        <div class="db-form-section-subtitle">Pilih tab Ayah, Ibu, atau Wali untuk melengkapi data keluarga</div>
                    </div>
                    <span class="db-badge db-badge-blue">LANGKAH 5 DARI 6</span>
                </div>

                {{-- Segmented Tabs for Parents --}}
                <div class="db-parent-tabs" id="parentTabs" role="tablist">
                    <button class="db-parent-tab-btn active" id="father-tab" data-bs-toggle="pill" data-bs-target="#father-pane" type="button" role="tab">
                        <span class="material-symbols-outlined" style="font-size:18px;">person</span> Ayah Kandung
                    </button>
                    <button class="db-parent-tab-btn" id="mother-tab" data-bs-toggle="pill" data-bs-target="#mother-pane" type="button" role="tab">
                        <span class="material-symbols-outlined" style="font-size:18px;">person_4</span> Ibu Kandung
                    </button>
                    <button class="db-parent-tab-btn" id="guardian-tab" data-bs-toggle="pill" data-bs-target="#guardian-pane" type="button" role="tab">
                        <span class="material-symbols-outlined" style="font-size:18px;">shield_person</span> Wali (Opsional)
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
                    {{-- Tab Ayah Kandung --}}
                    <div class="tab-pane fade show active" id="father-pane" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="father_nik" class="db-label">NIK Ayah <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="db-input" 
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
                                <label for="father_name" class="db-label">Nama Lengkap Ayah <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="db-input" 
                                       id="father_name" 
                                       name="father_name" 
                                       value="{{ old('father_name', $father['full_name'] ?? '') }}" 
                                       placeholder="Nama lengkap ayah kandung" 
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="father_birth_year" class="db-label">Tahun Lahir Ayah <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="db-input" 
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
                                <label for="father_education" class="db-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                <select class="db-select" id="father_education" name="father_education" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Pendidikan</option>
                                    @php $fEdu = old('father_education', $father['education_code'] ?? ''); @endphp
                                    @foreach($educationList as $code => $label)
                                        <option value="{{ $code }}" {{ $fEdu == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="father_occupation" class="db-label">Pekerjaan Ayah <span class="text-danger">*</span></label>
                                @php
                                    $fOccVal = old('father_occupation', $father['occupation_code'] ?? ($father['occupation'] ?? ''));
                                    if (isset($occupationList[$fOccVal])) {
                                        $fOccVal = $occupationList[$fOccVal];
                                    }
                                @endphp
                                <input type="text"
                                       class="db-input"
                                       id="father_occupation"
                                       name="father_occupation"
                                       value="{{ $fOccVal }}"
                                       placeholder="Contoh: Wiraswasta, PNS, Petani, dll"
                                       required
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="father_income" class="db-label">Penghasilan Bulanan <span class="text-danger">*</span></label>
                                <select class="db-select" id="father_income" name="father_income" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Penghasilan</option>
                                    @php $fInc = old('father_income', $father['income_code'] ?? ''); @endphp
                                    @foreach($incomeList as $code => $label)
                                        <option value="{{ $code }}" {{ $fInc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="father_phone" class="db-label">No HP Ayah</label>
                                <input type="text" 
                                       class="db-input" 
                                       id="father_phone" 
                                       name="father_phone" 
                                       value="{{ old('father_phone', $father['phone_number'] ?? '') }}" 
                                       placeholder="0812..." 
                                       inputmode="tel"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="father_whatsapp" class="db-label">No WhatsApp Ayah</label>
                                <input type="text" 
                                       class="db-input" 
                                       id="father_whatsapp" 
                                       name="father_whatsapp" 
                                       value="{{ old('father_whatsapp', $father['whatsapp_number'] ?? '') }}" 
                                       placeholder="0812..." 
                                       inputmode="tel"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-12">
                                <label for="father_email" class="db-label">Alamat Email Ayah</label>
                                <input type="email" 
                                       class="db-input" 
                                       id="father_email" 
                                       name="father_email" 
                                       value="{{ old('father_email', $father['email'] ?? '') }}" 
                                       placeholder="ayah@email.com (opsional)" 
                                       inputmode="email"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Ibu Kandung --}}
                    <div class="tab-pane fade" id="mother-pane" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="mother_nik" class="db-label">NIK Ibu <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="db-input" 
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
                                <label for="mother_name" class="db-label">Nama Lengkap Ibu <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="db-input" 
                                       id="mother_name" 
                                       name="mother_name" 
                                       value="{{ old('mother_name', $mother['full_name'] ?? '') }}" 
                                       placeholder="Nama lengkap ibu kandung" 
                                       required 
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_birth_year" class="db-label">Tahun Lahir Ibu <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="db-input" 
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
                                <label for="mother_education" class="db-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                                <select class="db-select" id="mother_education" name="mother_education" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Pendidikan</option>
                                    @php $mEdu = old('mother_education', $mother['education_code'] ?? ''); @endphp
                                    @foreach($educationList as $code => $label)
                                        <option value="{{ $code }}" {{ $mEdu == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_occupation" class="db-label">Pekerjaan Ibu <span class="text-danger">*</span></label>
                                @php
                                    $mOccVal = old('mother_occupation', $mother['occupation_code'] ?? ($mother['occupation'] ?? ''));
                                    if (isset($occupationList[$mOccVal])) {
                                        $mOccVal = $occupationList[$mOccVal];
                                    }
                                @endphp
                                <input type="text"
                                       class="db-input"
                                       id="mother_occupation"
                                       name="mother_occupation"
                                       value="{{ $mOccVal }}"
                                       placeholder="Contoh: Ibu Rumah Tangga, Guru, Pedagang, dll"
                                       required
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_income" class="db-label">Penghasilan Bulanan <span class="text-danger">*</span></label>
                                <select class="db-select" id="mother_income" name="mother_income" required {{ $isLocked ? 'disabled' : '' }}>
                                    <option value="">Pilih Penghasilan</option>
                                    @php $mInc = old('mother_income', $mother['income_code'] ?? ''); @endphp
                                    @foreach($incomeList as $code => $label)
                                        <option value="{{ $code }}" {{ $mInc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_phone" class="db-label">No HP Ibu</label>
                                <input type="text" 
                                       class="db-input" 
                                       id="mother_phone" 
                                       name="mother_phone" 
                                       value="{{ old('mother_phone', $mother['phone_number'] ?? '') }}" 
                                       placeholder="0812..." 
                                       inputmode="tel"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-md-4">
                                <label for="mother_whatsapp" class="db-label">No WhatsApp Ibu</label>
                                <input type="text" 
                                       class="db-input" 
                                       id="mother_whatsapp" 
                                       name="mother_whatsapp" 
                                       value="{{ old('mother_whatsapp', $mother['whatsapp_number'] ?? '') }}" 
                                       placeholder="0812..." 
                                       inputmode="tel"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>

                            <div class="col-12">
                                <label for="mother_email" class="db-label">Alamat Email Ibu</label>
                                <input type="email" 
                                       class="db-input" 
                                       id="mother_email" 
                                       name="mother_email" 
                                       value="{{ old('mother_email', $mother['email'] ?? '') }}" 
                                       placeholder="ibu@email.com (opsional)" 
                                       inputmode="email"
                                       {{ $isLocked ? 'disabled' : '' }}>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Wali Siswa --}}
                    <div class="tab-pane fade" id="guardian-pane" role="tabpanel">
                        <div style="background:var(--lp-surface-subtle);border:1.5px solid var(--lp-surface-container);border-radius:12px;padding:0.9rem 1.1rem;margin-bottom:1rem;">
                            <div class="form-check" style="display:flex;align-items:center;gap:8px;">
                                <input class="form-check-input mt-0" type="checkbox" id="has_guardian" name="has_guardian" value="1" 
                                    {{ old('has_guardian', !empty($guardian) ? '1' : '') ? 'checked' : '' }} onchange="toggleGuardianFields(this.checked)" {{ $isLocked ? 'disabled' : '' }}>
                                <label class="form-check-label" for="has_guardian" style="font-size:13px;font-weight:600;color:var(--lp-on-surface);">
                                    Calon siswa diasuh / tinggal bersama Wali (selain orang tua kandung)
                                </label>
                            </div>
                        </div>

                        <div id="guardian-fields" style="display: {{ old('has_guardian', !empty($guardian) ? '1' : '') ? 'block' : 'none' }};">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="guardian_nik" class="db-label">NIK Wali</label>
                                    <input type="text" 
                                           class="db-input" 
                                           id="guardian_nik" 
                                           name="guardian_nik" 
                                           value="{{ old('guardian_nik', $guardian['nik'] ?? '') }}" 
                                           placeholder="16 digit NIK wali" 
                                           maxlength="16" 
                                           inputmode="numeric"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-6">
                                    <label for="guardian_name" class="db-label">Nama Lengkap Wali</label>
                                    <input type="text" 
                                           class="db-input" 
                                           id="guardian_name" 
                                           name="guardian_name" 
                                           value="{{ old('guardian_name', $guardian['full_name'] ?? '') }}" 
                                           placeholder="Nama lengkap wali siswa" 
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_birth_year" class="db-label">Tahun Lahir Wali</label>
                                    <input type="number" 
                                           class="db-input" 
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
                                    <label for="guardian_education" class="db-label">Pendidikan Terakhir</label>
                                    <select class="db-select" id="guardian_education" name="guardian_education" {{ $isLocked ? 'disabled' : '' }}>
                                        <option value="">Pilih Pendidikan</option>
                                        @php $gEdu = old('guardian_education', $guardian['education_code'] ?? ''); @endphp
                                        @foreach($educationList as $code => $label)
                                            <option value="{{ $code }}" {{ $gEdu == $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_occupation" class="db-label">Pekerjaan Wali</label>
                                    @php
                                        $gOccVal = old('guardian_occupation', $guardian['occupation_code'] ?? ($guardian['occupation'] ?? ''));
                                        if (isset($occupationList[$gOccVal])) {
                                            $gOccVal = $occupationList[$gOccVal];
                                        }
                                    @endphp
                                    <input type="text"
                                           class="db-input"
                                           id="guardian_occupation"
                                           name="guardian_occupation"
                                           value="{{ $gOccVal }}"
                                           placeholder="Contoh: Pensiunan, Wiraswasta, dll"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_income" class="db-label">Penghasilan Bulanan</label>
                                    <select class="db-select" id="guardian_income" name="guardian_income" {{ $isLocked ? 'disabled' : '' }}>
                                        <option value="">Pilih Penghasilan</option>
                                        @php $gInc = old('guardian_income', $guardian['income_code'] ?? ''); @endphp
                                        @foreach($incomeList as $code => $label)
                                            <option value="{{ $code }}" {{ $gInc == $code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_phone" class="db-label">No HP Wali</label>
                                    <input type="text" 
                                           class="db-input" 
                                           id="guardian_phone" 
                                           name="guardian_phone" 
                                           value="{{ old('guardian_phone', $guardian['phone_number'] ?? '') }}" 
                                           placeholder="0812..." 
                                           inputmode="tel"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-4">
                                    <label for="guardian_whatsapp" class="db-label">No WhatsApp Wali</label>
                                    <input type="text" 
                                           class="db-input" 
                                           id="guardian_whatsapp" 
                                           name="guardian_whatsapp" 
                                           value="{{ old('guardian_whatsapp', $guardian['whatsapp_number'] ?? '') }}" 
                                           placeholder="0812..." 
                                           inputmode="tel"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-12">
                                    <label for="guardian_email" class="db-label">Alamat Email Wali</label>
                                    <input type="email" 
                                           class="db-input" 
                                           id="guardian_email" 
                                           name="guardian_email" 
                                           value="{{ old('guardian_email', $guardian['email'] ?? '') }}" 
                                           placeholder="wali@email.com (opsional)" 
                                           inputmode="email"
                                           {{ $isLocked ? 'disabled' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="db-form-actions">
                    <button type="button" class="db-form-btn-prev" onclick="prevStep(5)">
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                        <span>Sebelumnya</span>
                    </button>
                    <button type="button" class="db-form-btn-next" onclick="nextStep(5)">
                        <span>Lanjut: Tinjau Isian</span>
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                    </button>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- TAHAP 6: TINJAU & SIMPAN                      --}}
            {{-- ============================================== --}}
            <div class="form-section" id="step-6">
                <div class="db-form-section-header">
                    <div>
                        <h3 class="db-form-section-title">
                            <span class="material-symbols-outlined" style="color:var(--lp-green);">task_alt</span>
                            Tinjau & Simpan Formulir Pendaftaran
                        </h3>
                        <div class="db-form-section-subtitle">Periksa kembali ringkasan data sebelum melakukan pengiriman resmi</div>
                    </div>
                    <span class="db-badge db-badge-green">LANGKAH 6 DARI 6</span>
                </div>

                {{-- Document Summary Sheet --}}
                <div class="db-review-sheet">
                    <div style="font-size:12px;font-weight:700;color:var(--lp-primary);letter-spacing:0.04em;text-transform:uppercase;margin-bottom:1rem;display:flex;align-items:center;gap:6px;">
                        <span class="material-symbols-outlined" style="font-size:18px;">description</span>
                        RINGKASAN DATA PENDAFTARAN SISWA
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 db-review-item">
                            <div class="db-review-label">Nama Lengkap Siswa</div>
                            <div class="db-review-value" id="review-name">-</div>
                        </div>
                        <div class="col-md-6 db-review-item">
                            <div class="db-review-label">NIK / NISN</div>
                            <div class="db-review-value" id="review-nik-nisn">-</div>
                        </div>
                        <div class="col-md-6 db-review-item">
                            <div class="db-review-label">Pilihan Jurusan</div>
                            <div class="db-review-value" style="color:var(--lp-primary);" id="review-major">-</div>
                        </div>
                        <div class="col-md-6 db-review-item">
                            <div class="db-review-label">Asal Sekolah</div>
                            <div class="db-review-value" id="review-school">-</div>
                        </div>
                        <div class="col-md-6 db-review-item">
                            <div class="db-review-label">Identitas & Kelahiran</div>
                            <div class="db-review-value" id="review-ttl">-</div>
                        </div>
                        <div class="col-md-6 db-review-item">
                            <div class="db-review-label">Kontak (HP / WhatsApp)</div>
                            <div class="db-review-value" id="review-contact">-</div>
                        </div>
                        <div class="col-12 db-review-item">
                            <div class="db-review-label">Alamat Domisili</div>
                            <div class="db-review-value" id="review-address">-</div>
                        </div>
                        <div class="col-md-6 db-review-item">
                            <div class="db-review-label">Data Ayah Kandung</div>
                            <div class="db-review-value" id="review-father">-</div>
                        </div>
                        <div class="col-md-6 db-review-item">
                            <div class="db-review-label">Data Ibu Kandung</div>
                            <div class="db-review-value" id="review-mother">-</div>
                        </div>
                    </div>
                </div>

                <div style="background:rgba(214,227,255,0.35);border:1.5px solid rgba(0,90,180,0.18);border-radius:14px;padding:1.15rem 1.25rem;margin-bottom:1.5rem;">
                    <div class="form-check" style="display:flex;align-items:flex-start;gap:10px;">
                        <input class="form-check-input" type="checkbox" id="confirmation_check" required {{ $isLocked ? 'disabled' : '' }} style="margin-top:3px;flex-shrink:0;">
                        <label class="form-check-label" for="confirmation_check" style="font-size:13px;color:var(--lp-on-surface);line-height:1.5;">
                            <strong>Pernyataan Kebenaran Data:</strong> Saya menyatakan dengan sebenar-benarnya bahwa seluruh data pokok, identitas tambahan, domisili, kontak, serta data orang tua/wali yang saya isikan adalah benar, sah, dan sesuai dengan dokumen resmi (Kartu Keluarga, Akta Kelahiran, dan Ijazah).
                        </label>
                    </div>
                </div>

                <div class="db-form-actions">
                    <button type="button" class="db-form-btn-prev" onclick="prevStep(6)">
                        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                        <span>Ubah Isian</span>
                    </button>
                    @if(!$isLocked)
                        <button type="submit" class="db-form-btn-submit">
                            <span class="material-symbols-outlined" style="font-size:18px;">cloud_upload</span>
                            <span>Simpan & Kirim Formulir</span>
                        </button>
                    @else
                        <button type="button" class="db-form-btn-prev" disabled>
                            <span class="material-symbols-outlined" style="font-size:18px;">lock</span>
                            <span>Formulir Terkunci</span>
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
        'Biodata Pokok Siswa',
        'Identitas Tambahan',
        'Alamat Domisili',
        'Kontak & Komunikasi',
        'Data Orang Tua / Wali',
        'Tinjau & Simpan Formulir'
    ];

    function selectMajorCard(card) {
        document.querySelectorAll('.db-major-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        const radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    function showStep(step) {
        for (let i = 1; i <= totalSteps; i++) {
            const section = document.getElementById('step-' + i);
            const indicator = document.getElementById('step-indicator-' + i);
            if (section) section.classList.remove('active');
            if (indicator) {
                indicator.classList.remove('active');
                if (i < step) {
                    indicator.classList.add('completed');
                    const num = indicator.querySelector('.db-step-number');
                    if (num) num.innerHTML = '<span class="material-symbols-outlined" style="font-size:16px;">check</span>';
                } else {
                    indicator.classList.remove('completed');
                    const num = indicator.querySelector('.db-step-number');
                    if (num) num.textContent = i;
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
        if (mobileLabel) mobileLabel.textContent = `LANGKAH ${step} DARI ${totalSteps}`;
        if (mobilePercent) mobilePercent.textContent = `${pct}%`;
        if (mobileProgress) mobileProgress.style.width = `${pct}%`;
        if (mobileTitle) mobileTitle.textContent = `Langkah ${step}: ${stepTitles[step - 1]}`;

        if (step === 6) {
            populateReview();
        }

        window.scrollTo({ top: 40, behavior: 'smooth' });
    }

    function validateStep(step) {
        const currentSection = document.getElementById('step-' + step);
        if (!currentSection) return true;

        const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.checkValidity()) {
                input.style.borderColor = 'var(--lp-red)';
                input.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.15)';
                isValid = false;
            } else {
                input.style.borderColor = '';
                input.style.boxShadow = '';
            }

            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                }
            }, { once: true });
        });

        if (!isValid) {
            const firstInvalid = currentSection.querySelector('input:invalid, select:invalid, textarea:invalid');
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

        // Pilihan Jurusan
        const majorRadio = document.querySelector('input[name="major"]:checked');
        const majorLabels = {
            'reguler': 'REGULER (UMUM)',
            'bahasa': 'BAHASA (LITERASI & ASING)',
            'tahfidz': 'TAHFIDZ (AL-QURAN)',
            'ict': 'ICT (TEKNOLOGI)'
        };
        const majorVal = majorRadio ? (majorLabels[majorRadio.value] || majorRadio.value.toUpperCase()) : '-';
        const schoolOrigin = document.getElementById('school_origin')?.value || '-';
        const schoolOriginAddress = document.getElementById('school_origin_address')?.value || '';

        if (reviewName) reviewName.textContent = fullName;
        if (reviewNikNisn) reviewNikNisn.textContent = `${nik} / ${nisn}`;
        if (reviewMajor) reviewMajor.textContent = majorVal;
        if (reviewSchool) reviewSchool.textContent = schoolOriginAddress ? `${schoolOrigin} (${schoolOriginAddress})` : schoolOrigin;
        if (reviewTtl) reviewTtl.textContent = `${gender}, ${pob} (${dob}) — Anak ke-${birthOrder} dari ${siblingsCount} bersaudara`;
        if (reviewContact) reviewContact.textContent = `${mobile} (WhatsApp: ${wa})`;
        if (reviewAddress) reviewAddress.textContent = `${street}, RT ${rt}/RW ${rw}, Kel. ${village}, Kec. ${district}`;
        if (reviewFather) reviewFather.textContent = fatherEmail ? `${fatherName} (${fatherEmail})` : fatherName;
        if (reviewMother) reviewMother.textContent = motherEmail ? `${motherName} (${motherEmail})` : motherName;
    }

    // Segmented tabs switching for parent tabs
    document.querySelectorAll('#parentTabs .db-parent-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#parentTabs .db-parent-tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endpush
