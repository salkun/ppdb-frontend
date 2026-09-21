@extends('layouts.app')

@section('title', 'Dashboard Calon Siswa')

@section('content')
<div class="db-content">

    @if(isset($error) && !empty($error))
        <div style="background:var(--lp-red-light);border:1px solid rgba(220,38,38,0.3);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:10px;font-size:14px;color:var(--lp-red);font-weight:600;">
            <span class="material-symbols-outlined" style="font-size:20px;">error</span>
            <span>{{ $error }}</span>
        </div>
    @endif

    @php
        $paymentStatus = $registration['payment_status'] ?? 'unpaid';
        $registrationStatus = $registration['registration_status'] ?? 'pending';
        $paymentProof = $registration['payment_proof_path'] ?? null;
        $formData = $registration['form_data'] ?? null;

        // Progress calculation
        $progressItems = 0;
        $totalItems = 3; // Payment, Form, Upload (berkas mock)
        if ($paymentStatus === 'paid') $progressItems++;
        if (!empty($formData)) $progressItems++;
        // Upload berkas mockup — always 0 for now
        $progressPercent = round(($progressItems / $totalItems) * 100);
    @endphp

    {{-- ============================================ --}}
    {{-- SECTION 1: Student Identity & Smart Next-Step --}}
    {{-- ============================================ --}}
    <div class="db-profile-card fade-in-entry">
        <div class="db-profile-card-top">
            <div class="db-profile-identity">
                <div class="db-profile-avatar">
                    {{ strtoupper(substr($user['full_name'] ?? 'S', 0, 2)) }}
                </div>
                <div class="db-profile-info">
                    <div class="db-profile-greeting">
                        <h3>Halo, {{ $user['full_name'] ?? 'Calon Siswa' }}! 👋</h3>
                        <span class="db-profile-badge-tp">TP {{ date('Y') }}/{{ date('Y') + 1 }}</span>
                    </div>
                    <div class="db-profile-meta">
                        @if(isset($registration['id']))
                            <span class="db-profile-reg-id">
                                <span class="material-symbols-outlined">badge</span>
                                ID: <strong>#{{ substr($registration['id'], 0, 8) }}</strong>
                            </span>
                        @endif
                        <span class="db-profile-date">
                            <span class="material-symbols-outlined">calendar_today</span>
                            {{ date('l, d F Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="db-profile-status-pill">
                @if($paymentStatus === 'paid')
                    @if(!empty($formData))
                        <div class="db-status-pill success">
                            <span class="material-symbols-outlined">verified</span>
                            <span>Berkas Siap</span>
                        </div>
                    @else
                        <div class="db-status-pill action-needed">
                            <span class="material-symbols-outlined">edit_note</span>
                            <span>Wajib Isi Form</span>
                        </div>
                    @endif
                @elseif($paymentStatus === 'pending_verification')
                    <div class="db-status-pill pending">
                        <span class="material-symbols-outlined">hourglass_top</span>
                        <span>Verifikasi Bayar</span>
                    </div>
                @elseif($paymentStatus === 'rejected')
                    <div class="db-status-pill unpaid">
                        <span class="material-symbols-outlined">cancel</span>
                        <span>Bayar Ditolak</span>
                    </div>
                @else
                    <div class="db-status-pill unpaid">
                        <span class="material-symbols-outlined">pending</span>
                        <span>Belum Bayar</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Smart Next-Step Alert Box --}}
        @if($paymentStatus === 'unpaid' || $paymentStatus === 'rejected')
            <div class="db-next-step step-warning">
                <div class="db-next-step-left">
                    <div class="db-next-step-icon">
                        <span class="material-symbols-outlined">payments</span>
                    </div>
                    <div class="db-next-step-content">
                        <div class="db-next-step-title">Langkah Saat Ini: Pembayaran Biaya Pendaftaran Rp 400.000</div>
                        <div class="db-next-step-desc">Silakan transfer ke rekening resmi sekolah dan unggah bukti transfer di bawah untuk membuka akses formulir pendaftaran.</div>
                    </div>
                </div>
                <a href="#payment-section" class="db-next-step-btn warning">
                    <span>Bayar Sekarang</span>
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_downward</span>
                </a>
            </div>
        @elseif($paymentStatus === 'pending_verification')
            <div class="db-next-step step-pending">
                <div class="db-next-step-left">
                    <div class="db-next-step-icon">
                        <span class="material-symbols-outlined">hourglass_top</span>
                    </div>
                    <div class="db-next-step-content">
                        <div class="db-next-step-title">Langkah Saat Ini: Menunggu Verifikasi Pembayaran</div>
                        <div class="db-next-step-desc">Bukti pembayaran Anda sedang diperiksa panitia PPDB. Akses pengisian formulir akan terbuka otomatis setelah disetujui.</div>
                    </div>
                </div>
                <div class="db-next-step-badge-review">
                    <span class="material-symbols-outlined" style="font-size:16px;">sync</span>
                    <span>Sedang Ditinjau</span>
                </div>
            </div>
        @elseif($paymentStatus === 'paid' && empty($formData))
            <div class="db-next-step step-primary">
                <div class="db-next-step-left">
                    <div class="db-next-step-icon">
                        <span class="material-symbols-outlined">edit_note</span>
                    </div>
                    <div class="db-next-step-content">
                        <div class="db-next-step-title">Langkah Selanjutnya: Isi Formulir Pendaftaran (Wajib)</div>
                        <div class="db-next-step-desc">Pembayaran Rp 400.000 telah lunas! Silakan lengkapi biodata calon siswa dan data orang tua/wali.</div>
                    </div>
                </div>
                <a href="{{ route('ppdb.form') }}" class="db-next-step-btn primary">
                    <span>Isi Formulir</span>
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                </a>
            </div>
        @elseif($paymentStatus === 'paid' && !empty($formData))
            <div class="db-next-step step-accent">
                <div class="db-next-step-left">
                    <div class="db-next-step-icon">
                        <span class="material-symbols-outlined">cloud_upload</span>
                    </div>
                    <div class="db-next-step-content">
                        <div class="db-next-step-title">Langkah Selanjutnya: Upload Berkas Dokumen (Wajib)</div>
                        <div class="db-next-step-desc">Formulir pendaftaran sudah tersimpan. Silakan unggah scan KK, Akta Kelahiran, NISN, dan Pas Foto 3x4.</div>
                    </div>
                </div>
                <a href="{{ route('ppdb.upload') }}" class="db-next-step-btn accent">
                    <span>Upload Berkas</span>
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                </a>
            </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- SECTION 2: Info Pembayaran (Tahap 1)         --}}
    {{-- ============================================ --}}
    <div class="db-payment-card fade-in-entry" id="payment-section">
        <div class="db-payment-header">
            <div class="db-payment-title">
                <div class="db-payment-title-icon">
                    <span class="material-symbols-outlined" style="font-size:22px;">payments</span>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--lp-primary);letter-spacing:0.04em;text-transform:uppercase;margin-bottom:2px;">TAHAP 1 — ADMINISTRASI</div>
                    <h3 style="margin:0;">Biaya Pendaftaran Siswa Baru</h3>
                    <span>Transfer ke rekening resmi sekolah untuk membuka akses formulir & berkas</span>
                </div>
            </div>
            @if($paymentStatus === 'paid')
                <span class="db-badge db-badge-green"><span class="material-symbols-outlined" style="font-size:14px;">verified</span> LUNAS</span>
            @elseif($paymentStatus === 'pending_verification')
                <span class="db-badge db-badge-yellow"><span class="material-symbols-outlined" style="font-size:14px;">schedule</span> VERIFIKASI</span>
            @elseif($paymentStatus === 'rejected')
                <span class="db-badge db-badge-red"><span class="material-symbols-outlined" style="font-size:14px;">cancel</span> DITOLAK</span>
            @else
                <span class="db-badge db-badge-red"><span class="material-symbols-outlined" style="font-size:14px;">pending</span> BELUM BAYAR</span>
            @endif
        </div>

        <div class="db-payment-amount">
            <div class="db-payment-nominal">Rp 400.000</div>
            <div class="db-payment-bank">
                <span class="db-payment-bank-label">Rekening Tujuan (Bank BRI)</span>
                <span class="db-payment-bank-num">0123-01-000456-53-8</span>
            </div>
        </div>

        @if($paymentStatus === 'paid')
            <div style="background:var(--lp-green-light);border:1px solid rgba(22,163,74,0.2);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span class="material-symbols-outlined" style="font-size:28px;color:var(--lp-green);">task_alt</span>
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:700;font-size:14px;color:var(--lp-green);">Pembayaran Terverifikasi Resmi</div>
                    <div style="font-size:13px;color:var(--lp-on-surface-variant);margin-top:2px;">Bukti transfer sudah divalidasi panitia. Akses pengisian formulir dan upload berkas sudah aktif di bawah.</div>
                </div>
                @if(!empty($paymentProof))
                    <a href="{{ $backendUrl . $paymentProof }}" target="_blank" style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;padding:8px 14px;background:#fff;border:1px solid rgba(22,163,74,0.3);border-radius:10px;font-size:12px;font-weight:700;color:var(--lp-green);text-decoration:none;">
                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> Lihat Bukti
                    </a>
                @endif
            </div>

        @elseif($paymentStatus === 'pending_verification')
            <div style="background:var(--lp-yellow-light);border:1px solid rgba(234,179,8,0.3);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span class="material-symbols-outlined" style="font-size:28px;color:var(--lp-yellow-dark);">hourglass_top</span>
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:700;font-size:14px;color:var(--lp-yellow-dark);">Menunggu Verifikasi Panitia</div>
                    <div style="font-size:13px;color:var(--lp-on-surface-variant);margin-top:2px;">Bukti transfer Anda sedang ditinjau. Estimasi maks. 1x24 jam kerja. Akses formulir akan terbuka otomatis.</div>
                </div>
                @if(!empty($paymentProof))
                    <a href="{{ $backendUrl . $paymentProof }}" target="_blank" style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;padding:8px 14px;background:#fff;border:1px solid rgba(234,179,8,0.4);border-radius:10px;font-size:12px;font-weight:700;color:var(--lp-yellow-dark);text-decoration:none;">
                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> Lihat
                    </a>
                @endif
            </div>
            <form action="{{ route('ppdb.upload-payment') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="font-size:13px;font-weight:600;color:var(--lp-on-surface-variant);margin-bottom:8px;">Perlu mengirim ulang bukti transfer?</div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf" required style="flex:1;min-width:200px;font-size:13px;padding:8px 12px;border:1.5px solid var(--lp-surface-container);border-radius:10px;background:#fff;font-family:inherit;">
                    <button type="submit" class="db-feature-btn db-feature-btn-outline" style="font-size:12px;padding:8px 14px;">
                        <span class="material-symbols-outlined" style="font-size:16px;">upload</span> Kirim Ulang
                    </button>
                </div>
            </form>

        @else
            @if($paymentStatus === 'rejected')
                <div style="background:var(--lp-red-light);border:1px solid rgba(220,38,38,0.2);border-radius:12px;padding:0.85rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--lp-red);font-weight:600;">
                    <span class="material-symbols-outlined" style="font-size:20px;">warning</span>
                    Bukti transfer sebelumnya ditolak. Silakan kirim ulang bukti yang valid.
                </div>
            @endif
            <form action="{{ route('ppdb.upload-payment') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="db-payment-upload-zone" onclick="this.querySelector('input[type=file]').click();" id="paymentDropZone">
                    <div class="db-payment-upload-icon">
                        <span class="material-symbols-outlined" style="font-size:26px;">cloud_upload</span>
                    </div>
                    <div style="font-size:15px;font-weight:700;color:var(--lp-on-surface);margin-bottom:4px;">Klik atau seret bukti transfer ke sini</div>
                    <div style="font-size:13px;color:var(--lp-on-surface-variant);">Foto struk ATM, screenshot m-Banking, atau PDF slip setoran</div>
                    <div style="font-size:11px;color:var(--lp-outline);margin-top:6px;">Format: JPG, PNG, WEBP, PDF — Maks. 5MB</div>
                    <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf" required style="display:none;" onchange="this.closest('form').querySelector('.upload-filename').textContent = this.files[0]?.name || '';">
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:0.75rem;flex-wrap:wrap;gap:8px;">
                    <span class="upload-filename" style="font-size:13px;color:var(--lp-primary);font-weight:600;"></span>
                    <button type="submit" class="db-feature-btn db-feature-btn-primary">
                        <span class="material-symbols-outlined" style="font-size:16px;">upload</span>
                        Unggah Bukti Pembayaran
                    </button>
                </div>
            </form>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- SECTION 3: Progress & 2 Fitur Wajib (Tahap 2) --}}
    {{-- ============================================ --}}
    <div class="db-progress-section fade-in-entry">
        <div class="db-progress-header">
            <div class="db-progress-title">
                <span class="material-symbols-outlined" style="font-size:22px;color:var(--lp-primary);">checklist</span>
                Tahap 2: Pengisian Formulir & Berkas (Wajib)
            </div>
            <span class="db-progress-percent">{{ $progressPercent }}% Selesai</span>
        </div>
        <div class="db-progress-bar-wrap">
            <div class="db-progress-bar-fill" style="width:{{ $progressPercent }}%;"></div>
        </div>

        {{-- 2 Feature Cards: Formulir & Upload Berkas --}}
        <div class="db-progress-grid">
            {{-- Card 1: Formulir Pendaftaran (WAJIB) --}}
            <div class="db-feature-card {{ !empty($formData) ? 'completed' : '' }} {{ $paymentStatus !== 'paid' ? 'locked' : '' }}">
                <div class="db-feature-icon" style="background:var(--lp-primary);color:#fff;">
                    <span class="material-symbols-outlined">edit_note</span>
                </div>
                <div class="db-feature-tag" style="background:rgba(214,227,255,0.6);color:var(--lp-primary);">
                    <span class="material-symbols-outlined" style="font-size:12px;">priority_high</span>
                    WAJIB DIISI
                </div>
                <h4>Formulir Pendaftaran</h4>
                <p>Lengkapi biodata siswa, data orang tua, alamat, dan kontak secara lengkap dan akurat.</p>

                @if($paymentStatus !== 'paid')
                    <div class="db-feature-status" style="background:var(--lp-surface-subtle);color:var(--lp-outline);">
                        <span class="material-symbols-outlined" style="font-size:16px;">lock</span>
                        Terkunci — Selesaikan pembayaran di atas
                    </div>
                @elseif(!empty($formData))
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <div class="db-feature-status" style="background:var(--lp-green-light);color:var(--lp-green);">
                            <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
                            Sudah Diisi
                        </div>
                        <a href="{{ route('ppdb.form') }}" class="db-feature-btn db-feature-btn-outline">
                            <span class="material-symbols-outlined" style="font-size:16px;">edit</span>
                            Lihat / Ubah
                        </a>
                    </div>
                @else
                    <a href="{{ route('ppdb.form') }}" class="db-feature-btn db-feature-btn-primary">
                        <span class="material-symbols-outlined" style="font-size:16px;">edit_note</span>
                        Isi Formulir Sekarang
                    </a>
                @endif
            </div>

            {{-- Card 2: Upload Berkas (WAJIB - Mockup) --}}
            <div class="db-feature-card {{ $paymentStatus !== 'paid' ? 'locked' : '' }}">
                <div class="db-feature-icon" style="background:var(--lp-yellow);color:#451a03;">
                    <span class="material-symbols-outlined">cloud_upload</span>
                </div>
                <div class="db-feature-tag" style="background:var(--lp-yellow-light);color:var(--lp-yellow-dark);">
                    <span class="material-symbols-outlined" style="font-size:12px;">priority_high</span>
                    WAJIB UPLOAD
                </div>
                <h4>Upload Berkas Softfile</h4>
                <p>Unggah scan/foto KK, Akta Kelahiran, NISN, dan Pas Foto digital 3x4 berformat JPG/PNG/PDF.</p>

                @if($paymentStatus !== 'paid')
                    <div class="db-feature-status" style="background:var(--lp-surface-subtle);color:var(--lp-outline);">
                        <span class="material-symbols-outlined" style="font-size:16px;">lock</span>
                        Terkunci — Selesaikan pembayaran di atas
                    </div>
                @else
                    <a href="{{ route('ppdb.upload') }}" class="db-feature-btn db-feature-btn-primary" style="background:linear-gradient(135deg,var(--lp-yellow-dark),#78350f);">
                        <span class="material-symbols-outlined" style="font-size:16px;">upload_file</span>
                        Upload Berkas
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- SECTION 4: Quick Actions --}}
    {{-- ============================================ --}}
    <div class="db-actions-grid fade-in-entry">
        <a href="{{ route('dashboard') }}" class="db-action-card">
            <div class="db-action-icon" style="background:var(--lp-primary);color:#fff;"><span class="material-symbols-outlined">dashboard</span></div>
            <h5>Dashboard</h5>
            <span class="db-action-status" style="background:rgba(214,227,255,0.6);color:var(--lp-primary);">Aktif</span>
        </a>
        <a href="{{ route('ppdb.form') }}" class="db-action-card {{ $paymentStatus !== 'paid' ? 'locked' : '' }}">
            <div class="db-action-icon" style="background:var(--lp-green);color:#fff;"><span class="material-symbols-outlined">edit_note</span></div>
            <h5>Formulir</h5>
            @if($paymentStatus !== 'paid')
                <span class="db-action-status" style="background:var(--lp-surface-subtle);color:var(--lp-outline);">Terkunci</span>
            @elseif(!empty($formData))
                <span class="db-action-status" style="background:var(--lp-green-light);color:var(--lp-green);">Lengkap ✓</span>
            @else
                <span class="db-action-status" style="background:rgba(214,227,255,0.6);color:var(--lp-primary);">Siap Diisi</span>
            @endif
        </a>
        <a href="{{ route('ppdb.test-card') }}" class="db-action-card {{ ($paymentStatus !== 'paid' || empty($formData)) ? 'locked' : '' }}">
            <div class="db-action-icon" style="background:var(--lp-red);color:#fff;"><span class="material-symbols-outlined">badge</span></div>
            <h5>Kartu Tes</h5>
            @if($paymentStatus === 'paid' && !empty($formData))
                <span class="db-action-status" style="background:var(--lp-green-light);color:var(--lp-green);">Siap Cetak</span>
            @else
                <span class="db-action-status" style="background:var(--lp-surface-subtle);color:var(--lp-outline);">Terkunci</span>
            @endif
        </a>
        <a href="#pengumuman" class="db-action-card" onclick="document.getElementById('pengumuman').scrollIntoView({behavior:'smooth'});return false;">
            <div class="db-action-icon" style="background:var(--lp-yellow);color:#451a03;"><span class="material-symbols-outlined">campaign</span></div>
            <h5>Pengumuman</h5>
            <span class="db-action-status" style="background:var(--lp-yellow-light);color:var(--lp-yellow-dark);">Lihat</span>
        </a>
    </div>

    {{-- ============================================ --}}
    {{-- SECTION 5: Status Seleksi --}}
    {{-- ============================================ --}}
    @if($registrationStatus === 'accepted')
        <div style="background:linear-gradient(135deg,#f0faf2,#dcfce7);border:1.5px solid rgba(22,163,74,0.3);border-radius:16px;padding:1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:16px;flex-wrap:wrap;" class="fade-in-entry">
            <div style="width:52px;height:52px;border-radius:14px;background:var(--lp-green);color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(22,163,74,0.25);flex-shrink:0;">
                <span class="material-symbols-outlined" style="font-size:28px;">celebration</span>
            </div>
            <div style="flex:1;min-width:200px;">
                <div style="font-family:var(--font-headline);font-size:18px;font-weight:800;color:var(--lp-green);">🎉 Selamat! Anda Resmi Diterima</div>
                <div style="font-size:13px;color:var(--lp-on-surface-variant);margin-top:4px;">Calon siswa telah diterima resmi di SMPS2 Al-Muhajirin. Silakan cetak kartu tes untuk tahap selanjutnya.</div>
            </div>
            <a href="{{ route('ppdb.test-card') }}" class="db-feature-btn db-feature-btn-success">
                <span class="material-symbols-outlined" style="font-size:16px;">print</span> Cetak Kartu Tes
            </a>
        </div>
    @elseif($registrationStatus === 'rejected')
        <div style="background:var(--lp-red-subtle);border:1.5px solid rgba(220,38,38,0.2);border-radius:16px;padding:1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:12px;" class="fade-in-entry">
            <span class="material-symbols-outlined" style="font-size:26px;color:var(--lp-red);">cancel</span>
            <div>
                <div style="font-weight:700;font-size:15px;color:var(--lp-red);">Tidak Lolos Seleksi</div>
                <div style="font-size:13px;color:var(--lp-on-surface-variant);">Keputusan resmi dewan panitia PPDB. Silakan hubungi panitia untuk informasi lebih lanjut.</div>
            </div>
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- SECTION 6: Data Akun --}}
    {{-- ============================================ --}}
    <div class="db-info-card fade-in-entry">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--lp-surface-container);">
            <div style="width:38px;height:38px;border-radius:10px;background:rgba(214,227,255,0.6);color:var(--lp-primary);display:flex;align-items:center;justify-content:center;">
                <span class="material-symbols-outlined" style="font-size:20px;">person</span>
            </div>
            <h3 style="font-family:var(--font-headline);font-size:16px;font-weight:700;color:var(--lp-on-surface);margin:0;">Identitas Akun Calon Siswa</h3>
        </div>
        <div class="db-info-row">
            <span class="db-info-row-label">Nama Lengkap</span>
            <span class="db-info-row-value">{{ $user['full_name'] ?? '-' }}</span>
        </div>
        <div class="db-info-row">
            <span class="db-info-row-label">Email Terdaftar</span>
            <span class="db-info-row-value">{{ $user['email'] ?? '-' }}</span>
        </div>
        <div class="db-info-row">
            <span class="db-info-row-label">Tanggal Registrasi</span>
            <span class="db-info-row-value" style="font-family:var(--font-mono);">{{ isset($registration['created_at']) ? date('d F Y, H:i', strtotime($registration['created_at'])) . ' WIB' : '-' }}</span>
        </div>
        <div class="db-info-row">
            <span class="db-info-row-label">Tahun Ajaran</span>
            <span class="db-info-row-value">{{ date('Y') }}/{{ date('Y') + 1 }}</span>
        </div>

        @if(!empty($formData))
            <div style="margin-top:1rem;padding-top:0.75rem;border-top:1px solid var(--lp-surface-container);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                    <span style="font-size:12px;font-weight:700;color:var(--lp-outline);text-transform:uppercase;letter-spacing:0.04em;">Ringkasan Formulir</span>
                    <span class="db-badge db-badge-green" style="font-size:10px;">TERSIMPAN</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                    <div class="db-info-row" style="border-bottom:none;padding:0.4rem 0;">
                        <span class="db-info-row-label" style="font-size:12px;">NISN</span>
                        <span class="db-info-row-value" style="font-family:var(--font-mono);font-size:13px;">{{ $formData['nisn'] ?? '-' }}</span>
                    </div>
                    <div class="db-info-row" style="border-bottom:none;padding:0.4rem 0;">
                        <span class="db-info-row-label" style="font-size:12px;">Jenis Kelamin</span>
                        <span class="db-info-row-value" style="font-size:13px;">{{ $formData['identity']['gender'] ?? '-' }}</span>
                    </div>
                    <div class="db-info-row" style="border-bottom:none;padding:0.4rem 0;">
                        <span class="db-info-row-label" style="font-size:12px;">Tempat Lahir</span>
                        <span class="db-info-row-value" style="font-size:13px;">{{ $formData['identity']['place_of_birth'] ?? '-' }}</span>
                    </div>
                    <div class="db-info-row" style="border-bottom:none;padding:0.4rem 0;">
                        <span class="db-info-row-label" style="font-size:12px;">Tanggal Lahir</span>
                        <span class="db-info-row-value" style="font-family:var(--font-mono);font-size:13px;">{{ $formData['identity']['date_of_birth'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- SECTION 7: Pengumuman --}}
    {{-- ============================================ --}}
    <div class="db-announcement fade-in-entry" id="pengumuman">
        <div class="db-announcement-header">
            <div class="db-announcement-header-icon">
                <span class="material-symbols-outlined" style="font-size:20px;">campaign</span>
            </div>
            <h3>Pengumuman Panitia PPDB</h3>
        </div>
        <div class="db-announcement-item">
            <div class="db-announcement-meta">
                <span class="material-symbols-outlined" style="font-size:14px;">schedule</span> {{ date('d F Y') }}
                <span style="margin:0 4px;">•</span>
                <span style="color:var(--lp-primary);">Informasi Penting</span>
            </div>
            <h4>Jadwal Asesmen & Observasi Calon Santri Baru</h4>
            <p>Jadwal asesmen minat bakat dan wawancara santri baru akan diinformasikan melalui portal ini dan WhatsApp setelah proses verifikasi berkas selesai. Pastikan nomor WhatsApp Anda aktif.</p>
        </div>
        <div class="db-announcement-item">
            <div class="db-announcement-meta">
                <span class="material-symbols-outlined" style="font-size:14px;">schedule</span> {{ date('d F Y') }}
                <span style="margin:0 4px;">•</span>
                <span style="color:var(--lp-green);">Panduan</span>
            </div>
            <h4>Pastikan Berkas Softfile Lengkap & Terbaca Jelas</h4>
            <p>Scan/foto Kartu Keluarga, Akta Kelahiran, NISN, dan Pas Foto harus dalam format JPG/PNG/PDF dengan ukuran maksimal 5MB. Pastikan semua teks pada dokumen terbaca dengan jelas.</p>
        </div>
        <div class="db-announcement-item">
            <div class="db-announcement-meta">
                <span class="material-symbols-outlined" style="font-size:14px;">schedule</span> {{ date('d F Y') }}
                <span style="margin:0 4px;">•</span>
                <span style="color:var(--lp-yellow-dark);">Bantuan</span>
            </div>
            <h4>Butuh Bantuan? Hubungi Panitia PPDB</h4>
            <p>Untuk pertanyaan teknis seputar pengisian formulir atau upload berkas, hubungi panitia PPDB melalui WhatsApp di <strong>0878-2105-5283</strong> atau <strong>0878-2228-2012</strong>.</p>
        </div>
    </div>

</div>
@endsection
