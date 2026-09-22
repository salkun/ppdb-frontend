@extends('layouts.app')

@section('title', 'Penerimaan Murid Baru — SMPS2 Al-Muhajirin')

@section('content')
<div class="lp-main">

    {{-- ============================================================ --}}
    {{-- SECTION 1: HERO --}}
    {{-- ============================================================ --}}
    <div class="lp-hero-wrap" id="beranda">
        <div class="lp-hero-glow-1"></div>
        <div class="lp-hero-glow-2"></div>
        <div class="lp-hero-glow-3"></div>

        <div class="lp-hero-inner">
            {{-- Left Column --}}
            <div style="display:flex;flex-direction:column;align-items:flex-start;gap:1rem;">

                {{-- Headline --}}
                <h1 class="lp-hero-title">
                    Penerimaan Murid Baru<br>
                    <span class="lp-hero-title-gradient">SMPS2 Al-Muhajirin</span>
                </h1>

                {{-- Subtitle --}}
                <p class="lp-hero-subtitle">
                    Mulai langkah pendidikan terbaik bersama lingkungan belajar yang berkarakter islami, religius, unggul akademik &amp; teknologi, serta siap menghadapi tantangan masa depan.
                </p>

                {{-- CTA Buttons --}}
                <div class="lp-hero-ctas">
                    @if(session()->has('api_token'))
                        <a class="lp-btn-primary" href="{{ route('dashboard') }}">
                            <span class="material-symbols-outlined" style="font-size:20px;">dashboard</span>
                            <span>Buka Dashboard Saya</span>
                        </a>
                    @else
                        <a class="lp-btn-primary" href="{{ route('register') }}">
                            <span>Daftar Sekarang</span>
                            <span class="material-symbols-outlined" style="font-size:20px;">arrow_forward</span>
                        </a>
                        <a class="lp-btn-secondary" href="#persyaratan">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-primary);">upload_file</span>
                            <span>Cek Berkas Softfile</span>
                        </a>
                        <a class="lp-btn-green" href="https://wa.me/6287821055283" target="_blank" rel="noopener">
                            <span class="material-symbols-outlined" style="font-size:19px;">chat</span>
                            <span>Konsultasi WA</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Right Column: Hero Card --}}
            <div style="margin-top:1rem;">
                <div class="lp-hero-card">
                    <div class="lp-hero-img-wrap">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDYsRsGLds_FuWAE6Hca3AHNWT2QrVG4pspY_xOQrvC9i93AC2nw6OUmQn2qdWDZna0QJ3S5A4tIp4KzvZRmd1guEliDkuGGoZSf6ntKrRZkdYjRNY2lkE0LgXQRALeU4NSD4Z6W4-NYBO4PA7NPmO5j1rT0j3yQq1V5OuUbeEksVSlCHlfpW4mMv3ogvUmSFdyrgd9E0a2dgyWC8vl8m22WsZ5UfZUOtQ2oU3mSJzpe5U8woxkB9tw" alt="Siswa SMP Full Day Al-Muhajirin belajar kolaboratif">
                        <div class="lp-hero-img-overlay"></div>
                        <div class="lp-hero-img-badge">
                            <span class="material-symbols-outlined" style="font-size:16px;color:var(--lp-green);">check_circle</span>
                            Pendaftaran Online 2027/2028
                        </div>
                        <div class="lp-hero-img-bottom">
                            <div>
                                <p class="lp-hero-img-bottom-title">Lingkungan Akademik Unggul</p>
                                <p class="lp-hero-img-bottom-sub">Kampus Ciseureuh, Purwakarta — Berakhlak &amp; Berprestasi</p>
                            </div>
                            <div class="lp-hero-img-star">
                                <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-yellow);">stars</span>
                            </div>
                        </div>
                    </div>
                    {{-- Stats --}}
                    <div class="lp-hero-stats">
                        <div class="lp-hero-stat" style="background:var(--lp-primary-subtle);border-color:rgba(0,90,180,0.1);">
                            <span class="lp-hero-stat-val" style="color:var(--lp-primary);">100%</span>
                            <span class="lp-hero-stat-label">Lulusan Unggul</span>
                        </div>
                        <div class="lp-hero-stat" style="background:rgba(254,249,195,0.6);border-color:rgba(234,179,8,0.3);">
                            <span class="lp-hero-stat-val" style="color:var(--lp-yellow-dark);">4 Track</span>
                            <span class="lp-hero-stat-label">Kelas Pilihan</span>
                        </div>
                        <div class="lp-hero-stat" style="background:rgba(220,252,231,0.7);border-color:rgba(22,163,74,0.2);">
                            <span class="lp-hero-stat-val" style="color:var(--lp-green);">15+</span>
                            <span class="lp-hero-stat-label">Ekstrakurikuler</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECTION 2: INFORMASI SINGKAT PPDB (3 Cards) --}}
    {{-- ============================================================ --}}
    <section class="lp-info-section" id="informasi">
        <div class="lp-section">
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;">
                <span class="lp-section-tag">Akses Cepat PPDB</span>
                <h2 class="lp-section-title">Panduan Pokok Pendaftaran PPDB</h2>
                <p class="lp-section-desc">Akses cepat ketentuan berkas digital, peminatan program studi, dan transparansi rincian biaya pendidikan.</p>
            </div>

            <div class="lp-info-grid">
                {{-- Card 1: Persyaratan Softfile (Blue) --}}
                <div class="lp-info-card" style="--hover-border:rgba(0,90,180,0.4);">
                    <div>
                        <div class="lp-info-card-icon" style="background:var(--lp-primary);color:var(--lp-on-primary);">
                            <span class="material-symbols-outlined" style="font-size:26px;">cloud_upload</span>
                        </div>
                        <span class="lp-info-card-tag" style="background:rgba(214,227,255,0.5);color:var(--lp-primary);">100% Soft File</span>
                        <h3>Persyaratan Berkas Digital</h3>
                        <p>Cukup siapkan file scan atau foto jelas dokumen resmi (KK, Akta Lahir, NISN) untuk diunggah langsung via portal.</p>
                    </div>
                    <div class="lp-info-card-footer">
                        <a class="lp-info-card-link" href="#persyaratan" style="color:var(--lp-primary);">
                            <span>Lihat Detail Softfile</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>

                {{-- Card 2: Program (Yellow) --}}
                <div class="lp-info-card">
                    <div>
                        <div class="lp-info-card-icon" style="background:var(--lp-yellow);color:#451a03;">
                            <span class="material-symbols-outlined" style="font-size:26px;">category</span>
                        </div>
                        <span class="lp-info-card-tag" style="background:var(--lp-yellow-light);color:var(--lp-yellow-dark);">Peminatan Bakat</span>
                        <h3>4 Pilihan Program</h3>
                        <p>Pilihan kelas Reguler, Bahasa (Oxford TeachCast), Tahfizh Al-Qur'an, dan Kelas ICT / Komputer Terapan.</p>
                    </div>
                    <div class="lp-info-card-footer">
                        <a class="lp-info-card-link" href="#program" style="color:var(--lp-yellow-dark);">
                            <span>Lihat Detail Program</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>

                {{-- Card 3: Biaya (Green) --}}
                <div class="lp-info-card">
                    <div>
                        <div class="lp-info-card-icon" style="background:var(--lp-green);color:var(--lp-on-primary);">
                            <span class="material-symbols-outlined" style="font-size:26px;">payments</span>
                        </div>
                        <span class="lp-info-card-tag" style="background:var(--lp-green-light);color:var(--lp-green);">Transparansi Finansial</span>
                        <h3>Rincian Biaya Transparan</h3>
                        <p>Rincian tabel biaya resmi transparan untuk santri Putra dan Putri dengan diskon khusus bagi alumni &amp; bersaudara.</p>
                    </div>
                    <div class="lp-info-card-footer">
                        <a class="lp-info-card-link" href="#biaya" style="color:var(--lp-green);">
                            <span>Lihat Simulasi Biaya</span>
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 3: PERSYARATAN SOFTFILE --}}
    {{-- ============================================================ --}}
    <section class="lp-softfile-section" id="persyaratan">
        <div class="lp-section">
            {{-- Banner --}}
            <div class="lp-softfile-banner">
                <div style="display:flex;align-items:flex-start;gap:1rem;">
                    <div class="lp-softfile-banner-icon">
                        <span class="material-symbols-outlined" style="font-size:30px;">cloud_done</span>
                    </div>
                    <div>
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:0.5rem;margin-bottom:4px;">
                            <span style="padding:2px 10px;border-radius:9999px;background:var(--lp-yellow);color:#451a03;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:0.04em;">PENTING &amp; PRAKTIS</span>
                            <span style="padding:2px 10px;border-radius:9999px;background:var(--lp-green-light);color:var(--lp-green);font-size:12px;font-weight:700;">Bebas Ribet</span>
                        </div>
                        <h3 class="lp-font-headline" style="font-size:22px;font-weight:900;color:#fff;margin:0;">100% Berkas Digital (Soft File)</h3>
                        <p style="font-size:14px;color:rgba(191,219,254,1);margin-top:4px;max-width:600px;">
                            <strong>Tidak perlu membawa berkas fisik atau fotokopi ke sekolah saat pendaftaran awal.</strong> Cukup unggah file scan atau foto jelas berkas asli melalui formulir pendaftaran online.
                        </p>
                    </div>
                </div>
                <a class="lp-btn-secondary" href="{{ route('register') }}" style="flex-shrink:0;">
                    <span class="material-symbols-outlined" style="font-size:18px;">upload</span>
                    <span>Upload Berkas</span>
                </a>
            </div>

            {{-- Requirements Card --}}
            <div class="lp-softfile-card">
                <div class="lp-softfile-grid">
                    {{-- Left: Title --}}
                    <div style="display:flex;flex-direction:column;gap:1rem;">
                        <span class="lp-section-tag" style="width:fit-content;">
                            <span class="material-symbols-outlined" style="font-size:18px;">checklist</span>
                            Kelengkapan Berkas Digital
                        </span>
                        <h2 class="lp-font-headline" style="font-size:30px;font-weight:800;color:var(--lp-on-surface);margin:0;">Persyaratan Unggah Softfile</h2>
                        <p style="font-size:16px;color:var(--lp-on-surface-variant);line-height:1.6;">
                            Calon santri baru hanya perlu menyiapkan dokumen-dokumen utama berikut dalam bentuk digital. Pastikan teks terbaca jelas, tidak terpotong, dan tidak buram saat difoto atau discan.
                        </p>
                        <div class="lp-softfile-format-box">
                            <div style="display:flex;align-items:center;gap:8px;color:var(--lp-primary);font-weight:700;font-size:14px;">
                                <span class="material-symbols-outlined" style="font-size:20px;">info</span>
                                <span>Format File yang Diterima</span>
                            </div>
                            <div class="lp-softfile-format-tags">
                                <span class="lp-softfile-format-tag" style="color:var(--lp-primary);">PDF (Maks. 5 MB)</span>
                                <span class="lp-softfile-format-tag" style="color:var(--lp-green);">JPG / JPEG (Jelas)</span>
                                <span class="lp-softfile-format-tag" style="color:var(--lp-yellow-dark);">PNG (Jelas)</span>
                            </div>
                            <p style="font-size:12px;color:var(--lp-on-surface-variant);line-height:1.6;margin-top:8px;">
                                Dokumen asli cukup diperlihatkan nanti saat sesi wawancara dan observasi santri baru tanpa perlu menyerahkan fotokopi legalisir.
                            </p>
                        </div>
                    </div>

                    {{-- Right: Items --}}
                    <div class="lp-softfile-items">
                        <div class="lp-softfile-item">
                            <div class="lp-softfile-item-icon" style="background:var(--lp-primary);color:var(--lp-on-primary);">
                                <span class="material-symbols-outlined" style="font-size:20px;">badge</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:16px;color:var(--lp-on-surface);">Scan / Foto Asli Kartu Keluarga (KK)</span>
                                    <span style="padding:2px 8px;border-radius:4px;background:var(--lp-primary-light);color:var(--lp-primary);font-size:11px;font-weight:700;">Wajib (Softfile)</span>
                                </div>
                                <p style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:2px;">File format PDF atau foto JPG/PNG yang jelas, memperlihatkan seluruh nomor KK dan data anggota keluarga ber-barcode aktif.</p>
                            </div>
                            <div class="lp-softfile-item-check"><span class="material-symbols-outlined" style="font-size:18px;">done</span></div>
                        </div>

                        <div class="lp-softfile-item">
                            <div class="lp-softfile-item-icon" style="background:var(--lp-green);color:var(--lp-on-primary);">
                                <span class="material-symbols-outlined" style="font-size:20px;">description</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:16px;color:var(--lp-on-surface);">Scan / Foto Asli Akta Kelahiran</span>
                                    <span style="padding:2px 8px;border-radius:4px;background:var(--lp-green-light);color:var(--lp-green);font-size:11px;font-weight:700;">Wajib (Softfile)</span>
                                </div>
                                <p style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:2px;">File format PDF atau foto JPG/PNG asli untuk validasi tanggal lahir dan data kependudukan calon santri.</p>
                            </div>
                            <div class="lp-softfile-item-check"><span class="material-symbols-outlined" style="font-size:18px;">done</span></div>
                        </div>

                        <div class="lp-softfile-item">
                            <div class="lp-softfile-item-icon" style="background:var(--lp-yellow);color:#451a03;">
                                <span class="material-symbols-outlined" style="font-size:20px;">pin</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:16px;color:var(--lp-on-surface);">Tangkapan Layar / Bukti Cetak Digital NISN</span>
                                    <span style="padding:2px 8px;border-radius:4px;background:var(--lp-yellow-light);color:var(--lp-yellow-dark);font-size:11px;font-weight:700;">Wajib (Softfile)</span>
                                </div>
                                <p style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:2px;">Screenshot status aktif Nomor Induk Siswa Nasional dari laman resmi Kemendikbudristek (nisn.data.kemdikbud.go.id) atau surat keterangan sekolah.</p>
                            </div>
                            <div class="lp-softfile-item-check"><span class="material-symbols-outlined" style="font-size:18px;">done</span></div>
                        </div>

                        <div class="lp-softfile-item">
                            <div class="lp-softfile-item-icon" style="background:var(--lp-red);color:var(--lp-on-primary);">
                                <span class="material-symbols-outlined" style="font-size:20px;">account_box</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:16px;color:var(--lp-on-surface);">Pas Foto Digital Calon Siswa (Terbaru)</span>
                                    <span style="padding:2px 8px;border-radius:4px;background:var(--lp-red-light);color:var(--lp-red);font-size:11px;font-weight:700;">Softfile Foto</span>
                                </div>
                                <p style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:2px;">File foto rapi (format JPG/PNG) ukuran 3x4 berlatar belakang merah atau biru, berpakaian sopan atau berseragam sekolah asal.</p>
                            </div>
                            <div class="lp-softfile-item-check"><span class="material-symbols-outlined" style="font-size:18px;">done</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 4: 4 PROGRAM UNGGULAN --}}
    {{-- ============================================================ --}}
    <section class="lp-program-section" id="program">
        <div class="lp-section">
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;">
                <span class="lp-section-tag">Kurikulum Holistik Terpadu</span>
                <h2 class="lp-section-title">4 Pilihan Program Unggulan</h2>
                <p class="lp-section-desc">Didesain khusus untuk mengasah potensi intelektual, spiritual qur'ani, kecakapan bahasa global, dan literasi teknologi digital santri.</p>
            </div>

            <div class="lp-program-grid">
                {{-- Program 1: Reguler --}}
                <div class="lp-program-card">
                    <div>
                        <div class="lp-program-card-icon" style="background:var(--lp-primary);color:var(--lp-on-primary);box-shadow:0 4px 12px rgba(0,90,180,0.25);">
                            <span class="material-symbols-outlined" style="font-size:26px;">school</span>
                        </div>
                        <div class="lp-program-card-meta">
                            <span style="font-size:12px;font-weight:700;color:var(--lp-primary);">Program 01</span>
                            <span class="lp-program-tag" style="background:var(--lp-primary-light);color:var(--lp-primary);">TERFAVORIT</span>
                        </div>
                        <h3>Kelas Reguler</h3>
                        <p>Pembelajaran terpadu dengan kurikulum nasional merdeka dan penguatan dasar kepesantrenan Al-Muhajirin.</p>
                    </div>
                    <div class="lp-program-card-footer" style="background:var(--lp-primary-subtle);border-color:rgba(0,90,180,0.1);color:var(--lp-primary);">
                        <span class="material-symbols-outlined" style="font-size:16px;">verified</span>
                        <span>Integrasi Kurikulum Merdeka</span>
                    </div>
                </div>

                {{-- Program 2: Bahasa --}}
                <div class="lp-program-card">
                    <div>
                        <div class="lp-program-card-icon" style="background:var(--lp-yellow);color:#451a03;">
                            <span class="material-symbols-outlined" style="font-size:26px;">translate</span>
                        </div>
                        <div class="lp-program-card-meta">
                            <span style="font-size:12px;font-weight:700;color:var(--lp-yellow-dark);">Program 02</span>
                            <span class="lp-program-tag" style="background:var(--lp-yellow-light);color:var(--lp-yellow-dark);">INTERNATIONAL</span>
                        </div>
                        <h3>Kelas Bahasa</h3>
                        <p>Penguatan aktif Bahasa Inggris dan Arab berstandar internasional bekerja sama resmi bersama Oxford TeachCast.</p>
                    </div>
                    <div class="lp-program-card-footer" style="background:rgba(254,249,195,0.5);border-color:rgba(234,179,8,0.2);color:var(--lp-yellow-dark);">
                        <span class="material-symbols-outlined" style="font-size:16px;">stars</span>
                        <span>Native Speaker &amp; Lab Bahasa</span>
                    </div>
                </div>

                {{-- Program 3: Tahfizh --}}
                <div class="lp-program-card">
                    <div>
                        <div class="lp-program-card-icon" style="background:var(--lp-green);color:var(--lp-on-primary);box-shadow:0 4px 12px rgba(22,163,74,0.2);">
                            <span class="material-symbols-outlined" style="font-size:26px;">menu_book</span>
                        </div>
                        <div class="lp-program-card-meta">
                            <span style="font-size:12px;font-weight:700;color:var(--lp-green);">Program 03</span>
                            <span class="lp-program-tag" style="background:var(--lp-green-light);color:var(--lp-green);">QUR'ANI</span>
                        </div>
                        <h3>Kelas Tahfizh</h3>
                        <p>Fokus intensif hafalan Al-Qur'an tartil &amp; mutqin, bimbingan tahsin bersanad, serta mutaba'ah halaqah berkala.</p>
                    </div>
                    <div class="lp-program-card-footer" style="background:rgba(220,252,231,0.5);border-color:rgba(22,163,74,0.2);color:var(--lp-green);">
                        <span class="material-symbols-outlined" style="font-size:16px;">task_alt</span>
                        <span>Target Minimal 3–5 Juz</span>
                    </div>
                </div>

                {{-- Program 4: ICT --}}
                <div class="lp-program-card">
                    <div>
                        <div class="lp-program-card-icon" style="background:var(--lp-primary-dark);color:var(--lp-on-primary);box-shadow:0 4px 12px rgba(0,67,140,0.2);">
                            <span class="material-symbols-outlined" style="font-size:26px;">terminal</span>
                        </div>
                        <div class="lp-program-card-meta">
                            <span style="font-size:12px;font-weight:700;color:var(--lp-primary-dark);">Program 04</span>
                            <span class="lp-program-tag" style="background:var(--lp-red-light);color:var(--lp-red);">DIGITAL</span>
                        </div>
                        <h3>Kelas ICT / Komputer</h3>
                        <p>Penguasaan teknologi informasi, logika coding dasar, multimedia grafis, dan pemanfaatan AI yang bijak &amp; aplikatif.</p>
                    </div>
                    <div class="lp-program-card-footer" style="background:var(--lp-surface-subtle);border-color:var(--lp-surface-container);color:var(--lp-primary);">
                        <span class="material-symbols-outlined" style="font-size:16px;">computer</span>
                        <span>Lab Modern &amp; Coding Project</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 5: RINCIAN BIAYA (INTERACTIVE TABS) --}}
    {{-- ============================================================ --}}
    <section class="lp-pricing-section" id="biaya">
        <div class="lp-section">
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;margin-bottom:2rem;">
                <span class="lp-section-tag">Transparansi Finansial</span>
                <h2 class="lp-section-title">Rincian Biaya PPDB TP 2027–2028</h2>
                <p class="lp-section-desc">Pilih tab peminatan untuk melihat tabel rincian biaya pendidikan secara lengkap antara santri Putra dan Putri.</p>
            </div>

            {{-- Tab Controls --}}
            <div class="lp-pricing-tabs">
                <div class="lp-pricing-tabs-inner">
                    <button class="lp-tab-btn active" id="lp-tab-reguler" onclick="lpSwitchTab('reguler')" type="button">Kelas Reguler</button>
                    <button class="lp-tab-btn" id="lp-tab-bahasa" onclick="lpSwitchTab('bahasa')" type="button">Kelas Bahasa</button>
                    <button class="lp-tab-btn" id="lp-tab-tahfizh" onclick="lpSwitchTab('tahfizh')" type="button">Kelas Tahfizh</button>
                    <button class="lp-tab-btn" id="lp-tab-ict" onclick="lpSwitchTab('ict')" type="button">Kelas ICT</button>
                </div>
            </div>

            {{-- Pricing Table Card --}}
            <div class="lp-pricing-card">
                <div class="lp-pricing-header">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="lp-pricing-header-icon">
                            <span class="material-symbols-outlined" style="font-size:22px;">account_balance_wallet</span>
                        </div>
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="font-size:16px;font-weight:700;color:var(--lp-on-surface);" id="lpActiveProgramName">Program: Kelas Reguler</span>
                                <span id="lpProgramBadge" style="padding:2px 8px;background:rgba(0,90,180,0.1);color:var(--lp-primary);font-weight:700;font-size:11px;border-radius:6px;">Kelas Reguler</span>
                            </div>
                            <p style="font-size:12px;color:var(--lp-on-surface-variant);margin:0;" id="lpProgramDesc">Rincian resmi biaya masuk tahun ajaran baru TP 2027–2028</p>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="padding:4px 12px;background:var(--lp-surface-pure);color:var(--lp-primary);font-weight:700;font-size:12px;border-radius:9999px;border:1px solid rgba(0,90,180,0.2);">Putra &amp; Putri</span>
                        <span style="padding:4px 12px;background:var(--lp-yellow-light);color:var(--lp-yellow-dark);font-weight:700;font-size:12px;border-radius:9999px;border:1px solid rgba(234,179,8,0.3);">TP 2027/2028</span>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="lp-pricing-table">
                        <thead>
                            <tr>
                                <th style="text-align:center;width:56px;">No</th>
                                <th>Rincian Komponen Biaya</th>
                                <th style="text-align:right;width:176px;">Putra</th>
                                <th style="text-align:right;width:176px;">Putri</th>
                            </tr>
                        </thead>
                        <tbody id="lpFeeTableBody"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span class="material-symbols-outlined" style="font-size:22px;">price_check</span>
                                        <span style="font-size:14px;">TOTAL BIAYA PENDAFTARAN AWAL</span>
                                    </div>
                                </td>
                                <td style="text-align:right;font-size:16px;" id="lpTotalPutra">Rp10.945.000</td>
                                <td style="text-align:right;font-size:16px;" id="lpTotalPutri">Rp11.145.000</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Summary Cards --}}
                <div class="lp-pricing-summary">
                    <div class="lp-pricing-summary-card" style="background:var(--lp-primary-subtle);border-color:rgba(0,90,180,0.2);">
                        <div>
                            <span style="font-size:12px;font-weight:700;color:var(--lp-on-surface-variant);">Total Estimasi Santri Putra:</span>
                            <p class="lp-pricing-summary-val" style="color:var(--lp-primary);margin:0;" id="lpBadgePutra">Rp10.945.000</p>
                        </div>
                        <div class="lp-pricing-summary-icon" style="background:rgba(0,90,180,0.1);color:var(--lp-primary);">
                            <span class="material-symbols-outlined" style="font-size:26px;">male</span>
                        </div>
                    </div>
                    <div class="lp-pricing-summary-card" style="background:rgba(220,252,231,0.6);border-color:rgba(22,163,74,0.2);">
                        <div>
                            <span style="font-size:12px;font-weight:700;color:var(--lp-on-surface-variant);">Total Estimasi Santri Putri:</span>
                            <p class="lp-pricing-summary-val" style="color:var(--lp-green);margin:0;" id="lpBadgePutri">Rp11.145.000</p>
                        </div>
                        <div class="lp-pricing-summary-icon" style="background:rgba(22,163,74,0.1);color:var(--lp-green);">
                            <span class="material-symbols-outlined" style="font-size:26px;">female</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="lp-pricing-notes">
                <div style="display:flex;align-items:center;gap:8px;color:var(--lp-red);font-weight:700;font-size:16px;margin-bottom:1rem;">
                    <span class="material-symbols-outlined" style="font-size:22px;">notification_important</span>
                    <span>Catatan Penting, Diskon Khusus &amp; Skema Pembayaran</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr;gap:1rem;">
                    <div>
                        <div class="lp-pricing-note-item" style="background:var(--lp-red-subtle);border-color:rgba(220,38,38,0.2);">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-red);flex-shrink:0;margin-top:2px;">stars</span>
                            <div style="font-size:12px;color:var(--lp-on-surface-variant);">
                                <strong style="color:var(--lp-red);">1. Diskon Registrasi Alumni SD Plus Al-Muhajirin:</strong>
                                <p style="margin:2px 0 0;color:var(--lp-on-surface);">Potongan biaya registrasi alumni SD Plus Al-Muhajirin sebesar <strong style="color:var(--lp-red);">Rp1.200.000</strong>.</p>
                            </div>
                        </div>
                        <div class="lp-pricing-note-item" style="background:rgba(254,249,195,0.7);border-color:rgba(234,179,8,0.4);">
                            <span class="material-symbols-outlined" style="font-size:20px;color:var(--lp-yellow-dark);flex-shrink:0;margin-top:2px;">group</span>
                            <div style="font-size:12px;color:var(--lp-on-surface-variant);">
                                <strong style="color:var(--lp-yellow-dark);">2. Diskon Registrasi Kakak Beradik:</strong>
                                <p style="margin:2px 0 0;color:var(--lp-on-surface);">Potongan registrasi kakak beradik non alumni SD Plus Al-Muhajirin sebesar <strong style="color:var(--lp-yellow-dark);">Rp400.000</strong>.</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="lp-pricing-note-text">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-primary);flex-shrink:0;margin-top:2px;">info</span>
                            <span><strong>3. Biaya Tambahan:</strong> Belum termasuk biaya Ekstrakulikuler dan Buku Paket.</span>
                        </div>
                        <div class="lp-pricing-note-text">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-red);flex-shrink:0;margin-top:2px;">timer</span>
                            <span><strong>4. Batas Waktu Registrasi:</strong> Batas waktu registrasi <strong style="color:var(--lp-red);">3 bulan setelah pengumuman kelulusan</strong> (syarat dan ketentuan berlaku).</span>
                        </div>
                        <div class="lp-pricing-note-text">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--lp-green);flex-shrink:0;margin-top:2px;">payments</span>
                            <span><strong>Sistem Pembayaran:</strong> Pembayaran dilakukan via rekening resmi Bank Mandiri atau Loket Tata Usaha Gedung SMPS 2 Al-Muhajirin.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 6: ALUR PENDAFTARAN --}}
    {{-- ============================================================ --}}
    <section class="lp-timeline-section" id="alur">
        <div class="lp-section">
            <div style="text-align:center;display:flex;flex-direction:column;align-items:center;">
                <span class="lp-section-tag">Tahapan Terstruktur</span>
                <h2 class="lp-section-title">Alur Pendaftaran Mudah &amp; Cepat</h2>
                <p class="lp-section-desc">Empat langkah sederhana menjadi santri berprestasi di SMP Full Day Al-Muhajirin Purwakarta.</p>
            </div>

            <div class="lp-timeline-grid">
                {{-- Step 1 --}}
                <div class="lp-timeline-card fade-in-entry">
                    <div class="lp-timeline-step" style="background:var(--lp-primary);color:var(--lp-on-primary);box-shadow:0 4px 12px rgba(0,90,180,0.25);">01</div>
                    <span class="lp-timeline-label" style="color:var(--lp-primary);">Tahap Pertama</span>
                    <h3>Daftar Akun Online</h3>
                    <p>Isi identitas diri awal calon santri dan buat akun portal PPDB secara mandiri melalui website.</p>
                </div>

                {{-- Step 2 --}}
                <div class="lp-timeline-card fade-in-entry" style="border-color:rgba(234,179,8,0.4);">
                    <div class="lp-timeline-step" style="background:var(--lp-yellow);color:#451a03;">02</div>
                    <span class="lp-timeline-label" style="color:var(--lp-yellow-dark);">Tahap Kedua</span>
                    <h3>Upload Softfile &amp; Program</h3>
                    <p>Unggah file scan/foto jelas KK, Akta, NISN, Pas Foto digital dan tentukan pilihan kelas peminatan secara online.</p>
                </div>

                {{-- Step 3 --}}
                <div class="lp-timeline-card fade-in-entry">
                    <div class="lp-timeline-step" style="background:var(--lp-red);color:var(--lp-on-primary);box-shadow:0 4px 12px rgba(220,38,38,0.25);">03</div>
                    <span class="lp-timeline-label" style="color:var(--lp-red);">Tahap Ketiga</span>
                    <h3>Verifikasi &amp; Asesmen</h3>
                    <p>Pemeriksaan softfile oleh panitia, dilanjutkan observasi minat bakat serta wawancara santri &amp; orang tua.</p>
                </div>

                {{-- Step 4 --}}
                <div class="lp-timeline-card fade-in-entry">
                    <div class="lp-timeline-step" style="background:var(--lp-green);color:var(--lp-on-primary);box-shadow:0 4px 12px rgba(22,163,74,0.25);">04</div>
                    <span class="lp-timeline-label" style="color:var(--lp-green);">Tahap Akhir</span>
                    <h3>Pengumuman &amp; Registrasi</h3>
                    <p>Hasil seleksi diumumkan via akun portal, dilanjutkan proses konfirmasi daftar ulang administrasi santri baru.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- SECTION 7: CTA BANNER --}}
    {{-- ============================================================ --}}
    <section class="lp-cta-section">
        <div class="lp-section">
            <div class="lp-cta-banner">
                <div class="lp-cta-glow-1"></div>
                <div class="lp-cta-glow-2"></div>
                <div class="lp-cta-content">
                    <div class="lp-cta-icon">
                        <span class="material-symbols-outlined" style="font-size:30px;color:var(--lp-yellow);">school</span>
                    </div>
                    <div class="lp-cta-badge">KUOTA TERBATAS TIAP ROMBEL</div>
                    <h2 class="lp-cta-title">Siap Bergabung Bersama SMP Full Day Al-Muhajirin?</h2>
                    <p class="lp-cta-desc">Pendaftaran sangat mudah dengan sistem <strong>100% softfile dokumen</strong>. Amankan kursi calon santri sebelum kuota tiap kelas peminatan terpenuhi!</p>
                    <div class="lp-cta-buttons">
                        <a class="lp-btn-red" href="{{ route('register') }}">
                            <span class="material-symbols-outlined" style="font-size:20px;">how_to_reg</span>
                            <span>Daftar Sekarang Online</span>
                        </a>
                        <a class="lp-btn-cta-green" href="https://wa.me/6287821055283" target="_blank" rel="noopener">
                            <span class="material-symbols-outlined" style="font-size:20px;">chat</span>
                            <span>Konsultasi WhatsApp (0878-2105-5283)</span>
                        </a>
                    </div>
                    <p class="lp-cta-help">
                        <span class="material-symbols-outlined" style="font-size:16px;">contact_support</span>
                        <span>Butuh panduan teknis formulir? Hubungi juga panitia admisi: <strong>0878-2228-2012</strong></span>
                    </p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    // ===== Interactive Pricing Tab Logic (Sumber: Rincian Biaya 2728.xlsx) =====
    const regulerFeeItems = [
        { label: "Infak Pendidikan Per Bulan (SPP)", putra: "Rp700.000", putri: "Rp700.000" },
        { label: "Pengembangan Sarana Prasarana Pendidikan", putra: "Rp2.500.000", putri: "Rp2.500.000" },
        { label: "Infak Bangunan", putra: "Rp4.000.000", putri: "Rp4.000.000" },
        { label: "Kalender, Majalah, Buku Karya Siswa, Buku Hijroti, Raport, Buku Panduan Ibadah, Foto, Kartu Pelajar", putra: "Rp550.000", putri: "Rp550.000" },
        { label: "Milad Yayasan", putra: "Rp100.000", putri: "Rp100.000" },
        { label: "Ujian Selama Setahun", putra: "Rp800.000", putri: "Rp800.000" },
        { label: "Kegiatan Kesiswaan dan Asesmen", putra: "Rp550.000", putri: "Rp550.000" },
        { label: "Seragam Sekolah dan Atribut: Pramuka, Putih Biru, Rompi, Seragam Khas, Seragam Batik, Seragam Muslim, Kaos Olahraga, dan Kerudung (Khusus Putri)", putra: "Rp1.595.000", putri: "Rp1.795.000" },
        { label: "Infaq Masjid", putra: "Rp150.000", putri: "Rp150.000" }
    ];

    const takhosusFeeItems = [
        { label: "Infak Pendidikan Per Bulan (SPP)", putra: "Rp800.000", putri: "Rp800.000" },
        { label: "Pengembangan Sarana Prasarana Pendidikan", putra: "Rp2.500.000", putri: "Rp2.500.000" },
        { label: "Infak Bangunan", putra: "Rp4.000.000", putri: "Rp4.000.000" },
        { label: "Kalender, Majalah, Buku Karya Siswa, Buku Hijroti, Raport, Buku Panduan Ibadah, Foto, Kartu Pelajar", putra: "Rp550.000", putri: "Rp550.000" },
        { label: "Milad Yayasan", putra: "Rp100.000", putri: "Rp100.000" },
        { label: "Ujian Selama Setahun", putra: "Rp800.000", putri: "Rp800.000" },
        { label: "Kegiatan Kesiswaan dan Asesmen", putra: "Rp550.000", putri: "Rp550.000" },
        { label: "Seragam Sekolah dan Atribut: Pramuka, Putih Biru, Rompi, Seragam Khas, Seragam Batik, Seragam Muslim, Kaos Olahraga, dan Kerudung (Khusus Putri)", putra: "Rp1.595.000", putri: "Rp1.795.000" },
        { label: "Infaq Masjid", putra: "Rp150.000", putri: "Rp150.000" }
    ];

    const lpFeeData = {
        reguler: {
            name: "Program: Kelas Reguler",
            badge: "Kelas Reguler",
            desc: "Rincian resmi biaya masuk tahun ajaran baru TP 2027–2028",
            items: regulerFeeItems,
            totalPutra: "Rp10.945.000",
            totalPutri: "Rp11.145.000"
        },
        bahasa: {
            name: "Program: Kelas Bahasa",
            badge: "Kelas Takhosus",
            desc: "Rumpun Kelas Takhosus (Bahasa, ICT, Tahfidz) TP 2027–2028",
            items: takhosusFeeItems,
            totalPutra: "Rp11.045.000",
            totalPutri: "Rp11.245.000"
        },
        tahfizh: {
            name: "Program: Kelas Tahfizh",
            badge: "Kelas Takhosus",
            desc: "Rumpun Kelas Takhosus (Bahasa, ICT, Tahfidz) TP 2027–2028",
            items: takhosusFeeItems,
            totalPutra: "Rp11.045.000",
            totalPutri: "Rp11.245.000"
        },
        ict: {
            name: "Program: Kelas ICT",
            badge: "Kelas Takhosus",
            desc: "Rumpun Kelas Takhosus (Bahasa, ICT, Tahfidz) TP 2027–2028",
            items: takhosusFeeItems,
            totalPutra: "Rp11.045.000",
            totalPutri: "Rp11.245.000"
        }
    };

    function lpRenderTable(key) {
        const data = lpFeeData[key];
        if (!data) return;

        const tbody = document.getElementById('lpFeeTableBody');
        const progName = document.getElementById('lpActiveProgramName');
        const progBadge = document.getElementById('lpProgramBadge');
        const progDesc = document.getElementById('lpProgramDesc');
        const totalPutra = document.getElementById('lpTotalPutra');
        const totalPutri = document.getElementById('lpTotalPutri');
        const badgePutra = document.getElementById('lpBadgePutra');
        const badgePutri = document.getElementById('lpBadgePutri');

        if (progName) progName.innerText = data.name;
        if (progBadge) {
            progBadge.innerText = data.badge;
            if (data.badge === 'Kelas Reguler') {
                progBadge.style.background = 'rgba(0,90,180,0.1)';
                progBadge.style.color = 'var(--lp-primary)';
            } else {
                progBadge.style.background = 'rgba(13,148,136,0.12)';
                progBadge.style.color = '#0f766e';
            }
        }
        if (progDesc) progDesc.innerText = data.desc;
        if (totalPutra) totalPutra.innerText = data.totalPutra;
        if (totalPutri) totalPutri.innerText = data.totalPutri;
        if (badgePutra) badgePutra.innerText = data.totalPutra;
        if (badgePutri) badgePutri.innerText = data.totalPutri;

        if (tbody) {
            let html = '';
            data.items.forEach(function(item, i) {
                const bg = i % 2 === 0 ? '' : 'background:var(--lp-surface-subtle);';
                html += '<tr style="' + bg + '">' +
                    '<td style="text-align:center;font-weight:500;color:var(--lp-on-surface-variant);">' + (i + 1) + '</td>' +
                    '<td style="font-weight:500;">' + item.label + '</td>' +
                    '<td style="text-align:right;font-weight:600;">' + item.putra + '</td>' +
                    '<td style="text-align:right;font-weight:600;">' + item.putri + '</td>' +
                    '</tr>';
            });
            tbody.innerHTML = html;
        }
    }

    function lpSwitchTab(selected) {
        var tabs = ['reguler', 'bahasa', 'tahfizh', 'ict'];
        tabs.forEach(function(tab) {
            var btn = document.getElementById('lp-tab-' + tab);
            if (!btn) return;
            if (tab === selected) {
                btn.className = 'lp-tab-btn active';
            } else {
                btn.className = 'lp-tab-btn';
            }
        });
        lpRenderTable(selected);
    }

    document.addEventListener('DOMContentLoaded', function() {
        lpRenderTable('reguler');
    });
</script>
@endpush
