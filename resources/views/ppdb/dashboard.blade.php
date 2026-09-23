@extends('layouts.app')

@push('styles')
<style>
    .pay-method-card {
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        position: relative;
        user-select: none;
    }
    .pay-method-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
    }
    .pay-method-card.active {
        border-color: var(--lp-primary);
        background: #eff6ff;
        box-shadow: 0 4px 14px rgba(0, 102, 204, 0.12);
    }
    .pay-method-card[data-method="cash"].active {
        border-color: #0d9488;
        background: #f0fdfa;
        box-shadow: 0 4px 14px rgba(13, 148, 136, 0.12);
    }
    .pay-method-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .pay-method-icon.tf {
        background: rgba(0, 102, 204, 0.1);
        color: var(--lp-primary);
    }
    .pay-method-icon.cash {
        background: rgba(13, 148, 136, 0.1);
        color: #0d9488;
    }
    .pay-method-radio {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        display: inline-block;
        position: relative;
        transition: all 0.2s;
    }
    .pay-method-card.active .pay-method-radio {
        border-color: var(--lp-primary);
        background: var(--lp-primary);
    }
    .pay-method-card[data-method="cash"].active .pay-method-radio {
        border-color: #0d9488;
        background: #0d9488;
    }
    .pay-method-card.active .pay-method-radio::after {
        content: '';
        position: absolute;
        width: 6px;
        height: 6px;
        background: #ffffff;
        border-radius: 50%;
        top: 4px;
        left: 4px;
    }
</style>
@endpush

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
        $formData = is_array($registration['form_data'] ?? null) 
            ? $registration['form_data'] 
            : (json_decode($registration['form_data'] ?? '', true) ?: []);

        $documents = $formData['documents'] ?? [];
        $docCount = (!empty($documents['kk']) || !empty($formData['kk_path']) ? 1 : 0)
                  + (!empty($documents['akta']) || !empty($formData['birth_cert_path']) ? 1 : 0)
                  + (!empty($documents['nisn']) || !empty($formData['nisn_path']) ? 1 : 0)
                  + (!empty($documents['foto']) || !empty($formData['photo_path']) ? 1 : 0);
        $hasDocs = $docCount > 0;
        $isAllDocs = ($docCount >= 4);

        // Formulir PPDB dinyatakan benar-benar sudah diisi jika calon siswa sudah submit data pokok (NIK & Jurusan)
        $isFormFilled = !empty($formData['nik']) && !empty($formData['major']);

        // Progress calculation
        $progressItems = 0;
        $totalItems = 3; // Payment, Form, Upload
        if ($paymentStatus === 'paid') $progressItems++;
        if ($isFormFilled) $progressItems++;
        if ($isAllDocs) {
            $progressItems++;
        } elseif ($hasDocs) {
            $progressItems += ($docCount / 4);
        }
        $progressPercent = min(100, round(($progressItems / $totalItems) * 100));
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
                        <span class="db-profile-badge-tp">TP 2027/2028</span>
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
                    @if($isFormFilled && $isAllDocs)
                        <div class="db-status-pill success">
                            <span class="material-symbols-outlined">verified</span>
                            <span>Lengkap 100%</span>
                        </div>
                    @elseif($isFormFilled)
                        <div class="db-status-pill action-needed" style="background:#fff7ed;color:#c2410c;border-color:#fed7aa;">
                            <span class="material-symbols-outlined">cloud_upload</span>
                            <span>Berkas ({{ $docCount }}/4)</span>
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
        @elseif($paymentStatus === 'paid' && !$isFormFilled)
            <div class="db-next-step step-primary">
                <div class="db-next-step-left">
                    <div class="db-next-step-icon">
                        <span class="material-symbols-outlined">edit_note</span>
                    </div>
                    <div class="db-next-step-content">
                        <div class="db-next-step-title">Langkah Selanjutnya: Isi Formulir Pendaftaran (Wajib)</div>
                        <div class="db-next-step-desc">Pembayaran Rp 400.000 telah lunas & terverifikasi! Silakan lengkapi biodata calon siswa, data orang tua/wali, dan pilihan jurusan.</div>
                    </div>
                </div>
                <a href="{{ route('ppdb.form') }}" class="db-next-step-btn primary">
                    <span>Isi Formulir</span>
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                </a>
            </div>
        @elseif($paymentStatus === 'paid' && $isFormFilled && !$isAllDocs)
            <div class="db-next-step step-accent">
                <div class="db-next-step-left">
                    <div class="db-next-step-icon">
                        <span class="material-symbols-outlined">cloud_upload</span>
                    </div>
                    <div class="db-next-step-content">
                        <div class="db-next-step-title">Langkah Selanjutnya: Lengkapi Berkas Dokumen ({{ $docCount }}/4 Terunggah)</div>
                        <div class="db-next-step-desc">Formulir pendaftaran sudah tersimpan. Masih ada {{ 4 - $docCount }} dokumen yang belum diunggah (scan KK, Akta Kelahiran, NISN, dan Pas Foto 3x4).</div>
                    </div>
                </div>
                <a href="{{ route('ppdb.upload') }}" class="db-next-step-btn accent">
                    <span>Lengkapi Berkas</span>
                    <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                </a>
            </div>
        @elseif($paymentStatus === 'paid' && $isFormFilled && $isAllDocs)
            <div class="db-next-step step-success">
                <div class="db-next-step-left">
                    <div class="db-next-step-icon">
                        <span class="material-symbols-outlined">task_alt</span>
                    </div>
                    <div class="db-next-step-content">
                        @if($registrationStatus === 'accepted')
                            <div class="db-next-step-title">🎉 Selamat! Anda Resmi Diterima di SMPS2 Al-Muhajirin</div>
                            <div class="db-next-step-desc">Semua berkas telah diverifikasi dan Anda dinyatakan lolos seleksi. Silakan unduh / cetak kartu tanda peserta untuk daftar ulang.</div>
                        @else
                            <div class="db-next-step-title">Semua Tahap Selesai: Formulir & Berkas Lengkap (100%)</div>
                            <div class="db-next-step-desc">Biodata dan seluruh 4 dokumen persyaratan telah tersimpan di sistem panitia. Silakan simpan / cetak Kartu Tanda Peserta Ujian Anda.</div>
                        @endif
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('ppdb.test-card') }}" class="db-next-step-btn success">
                        <span class="material-symbols-outlined" style="font-size:16px;">badge</span>
                        <span>Cetak Kartu Ujian</span>
                    </a>
                    <a href="{{ route('ppdb.upload') }}" class="db-next-step-btn" style="background:#ffffff;border:1px solid #bbf7d0;color:#166534;box-shadow:none;">
                        <span class="material-symbols-outlined" style="font-size:16px;">folder_open</span>
                        <span>Lihat Berkas</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- SECTION 2: Info Pembayaran (Tahap 1)         --}}
    {{-- ============================================ --}}
    @php
        $payMethod = $registration['payment_method'] ?? 'transfer';
    @endphp
    <div class="db-payment-card fade-in-entry" id="payment-section">
        <div class="db-payment-header">
            <div class="db-payment-title">
                <div class="db-payment-title-icon">
                    <span class="material-symbols-outlined" style="font-size:22px;">payments</span>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--lp-primary);letter-spacing:0.04em;text-transform:uppercase;margin-bottom:2px;">TAHAP 1 — ADMINISTRASI</div>
                    <h3 style="margin:0;">Biaya Pendaftaran Siswa Baru</h3>
                    <span>Tersedia metode Transfer Bank (TF) atau Bayar Tunai (Cash). Keduanya wajib mengunggah bukti.</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                @if(!empty($payMethod) && $paymentStatus !== 'unpaid')
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:700;background:{{ $payMethod === 'cash' ? 'rgba(13,148,136,0.12)' : 'rgba(0,102,204,0.12)' }};color:{{ $payMethod === 'cash' ? '#0f766e' : 'var(--lp-primary)' }};border:1px solid {{ $payMethod === 'cash' ? 'rgba(13,148,136,0.3)' : 'rgba(0,102,204,0.3)' }};">
                        <span class="material-symbols-outlined" style="font-size:14px;">{{ $payMethod === 'cash' ? 'payments' : 'account_balance' }}</span>
                        {{ $payMethod === 'cash' ? 'Tunai (Cash di Loket)' : 'Transfer Bank (TF)' }}
                    </span>
                @endif
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
        </div>

        <div class="db-payment-amount">
            <div class="db-payment-nominal">Rp 400.000</div>
            <div class="db-payment-bank" id="payInfoTransfer" style="{{ $payMethod === 'cash' ? 'display:none;' : '' }}">
                <span class="db-payment-bank-label">Rekening Tujuan (Bank Mandiri)</span>
                <span class="db-payment-bank-num">1730004960275</span>
                <span style="font-size:11px;color:var(--lp-on-surface-variant);display:block;margin-top:2px;">a.n. PUTRI MUNAWWAROH</span>
            </div>
            <div class="db-payment-bank" id="payInfoCash" style="{{ $payMethod === 'cash' ? '' : 'display:none;' }};background:rgba(13,148,136,0.06);border-color:rgba(13,148,136,0.25);">
                <span class="db-payment-bank-label" style="color:#0f766e;">Lokasi Pembayaran Tunai</span>
                <span class="db-payment-bank-num" style="color:#0f766e;font-size:14px;">Loket TU / Panitia PPDB</span>
                <span style="font-size:11px;color:#115e59;display:block;margin-top:2px;">Gedung SMPS2 Al-Muhajirin</span>
            </div>
        </div>

        @if($paymentStatus === 'paid')
            <div style="background:var(--lp-green-light);border:1px solid rgba(22,163,74,0.2);border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span class="material-symbols-outlined" style="font-size:28px;color:var(--lp-green);">task_alt</span>
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:700;font-size:14px;color:var(--lp-green);">Pembayaran Terverifikasi Resmi</div>
                    <div style="font-size:13px;color:var(--lp-on-surface-variant);margin-top:2px;">
                        Bukti {{ $payMethod === 'cash' ? 'kuitansi pembayaran tunai (cash)' : 'transfer bank' }} telah divalidasi panitia. Akses pengisian formulir dan upload berkas sudah aktif di bawah.
                    </div>
                </div>
                @if(!empty($paymentProof))
                    <a href="{{ ppdb_proof_url($paymentProof, $backendUrl ?? null) }}" target="_blank" style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;padding:8px 14px;background:#fff;border:1px solid rgba(22,163,74,0.3);border-radius:10px;font-size:12px;font-weight:700;color:var(--lp-green);text-decoration:none;">
                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> Lihat Bukti
                    </a>
                @endif
            </div>

        @elseif($paymentStatus === 'pending_verification')
            <div style="background:var(--lp-yellow-light);border:1px solid rgba(234,179,8,0.3);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span class="material-symbols-outlined" style="font-size:28px;color:var(--lp-yellow-dark);">hourglass_top</span>
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:700;font-size:14px;color:var(--lp-yellow-dark);">Menunggu Verifikasi Panitia</div>
                    <div style="font-size:13px;color:var(--lp-on-surface-variant);margin-top:2px;">
                        Bukti {{ $payMethod === 'cash' ? 'kuitansi tunai' : 'transfer' }} Anda sedang ditinjau panitia. Estimasi maks. 1x24 jam kerja. Akses formulir akan terbuka otomatis setelah disetujui.
                    </div>
                </div>
                @if(!empty($paymentProof))
                    <a href="{{ ppdb_proof_url($paymentProof, $backendUrl ?? null) }}" target="_blank" style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;padding:8px 14px;background:#fff;border:1px solid rgba(234,179,8,0.4);border-radius:10px;font-size:12px;font-weight:700;color:var(--lp-yellow-dark);text-decoration:none;">
                        <span class="material-symbols-outlined" style="font-size:16px;">visibility</span> Lihat Bukti
                    </a>
                @endif
            </div>

            <!-- Form Kirim Ulang Bukti jika diperlukan -->
            <form action="{{ route('ppdb.upload-payment') }}" method="POST" enctype="multipart/form-data" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:1rem 1.25rem;">
                @csrf
                <input type="hidden" name="payment_method" value="{{ $payMethod }}">
                <div style="font-size:13px;font-weight:700;color:var(--lp-on-surface);margin-bottom:4px;">Perlu mengirim ulang berkas bukti?</div>
                <div style="font-size:12px;color:var(--lp-on-surface-variant);margin-bottom:10px;">
                    Metode tersimpan: <strong>{{ $payMethod === 'cash' ? 'Tunai (Cash di Loket)' : 'Transfer Bank (TF)' }}</strong>. Unggah ulang jika file sebelumnya salah/buram.
                </div>
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
                    Bukti pembayaran sebelumnya ditolak oleh panitia. Silakan periksa kembali dan unggah berkas yang valid.
                </div>
            @endif

            <!-- Pemilihan Metode Pembayaran (Transfer vs Cash) -->
            <div style="margin-bottom: 1.25rem;">
                <label style="font-size: 13px; font-weight: 700; color: var(--lp-on-surface); margin-bottom: 8px; display: block;">
                    1. Pilih Metode Pembayaran: <span class="text-danger">*</span>
                </label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px;">
                    <!-- Opsi Transfer Bank -->
                    <div class="pay-method-card {{ $payMethod !== 'cash' ? 'active' : '' }}" data-method="transfer" onclick="selectPaymentMethod('transfer')">
                        <div style="display:flex;gap:12px;align-items:flex-start;">
                            <div class="pay-method-icon tf">
                                <span class="material-symbols-outlined" style="font-size:24px;">account_balance</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    <div style="font-weight:700;font-size:14px;color:var(--lp-on-surface);">Transfer Bank (TF)</div>
                                    <span class="pay-method-radio"></span>
                                </div>
                                <div style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:3px;line-height:1.4;">
                                    Transfer via ATM / m-Banking / Internet Banking ke Rekening Resmi Mandiri a.n. PUTRI MUNAWWAROH.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Opsi Tunai (Cash) -->
                    <div class="pay-method-card {{ $payMethod === 'cash' ? 'active' : '' }}" data-method="cash" onclick="selectPaymentMethod('cash')">
                        <div style="display:flex;gap:12px;align-items:flex-start;">
                            <div class="pay-method-icon cash">
                                <span class="material-symbols-outlined" style="font-size:24px;">payments</span>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;align-items:center;justify-content:space-between;">
                                    <div style="font-weight:700;font-size:14px;color:var(--lp-on-surface);">Bayar Tunai (Cash di Loket)</div>
                                    <span class="pay-method-radio"></span>
                                </div>
                                <div style="font-size:12px;color:var(--lp-on-surface-variant);margin-top:3px;line-height:1.4;">
                                    Bayar langsung ke Ruang TU / Panitia PPDB di sekolah & minta kuitansi bukti resmi.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Ringkas Berdasarkan Metode Terpilih -->
            <div id="payTransferGuide" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:0.85rem 1.15rem;margin-bottom:1.25rem;font-size:13px;color:#1e40af;display:{{ $payMethod === 'cash' ? 'none' : 'flex' }};align-items:flex-start;gap:10px;">
                <span class="material-symbols-outlined" style="font-size:20px;flex-shrink:0;color:#2563eb;margin-top:2px;">info</span>
                <div>
                    <strong>Instruksi Transfer:</strong> Silakan transfer <strong>Rp 400.000</strong> ke Rekening Mandiri <code>1730004960275</code> a.n. <strong>PUTRI MUNAWWAROH</strong>. Simpan struk fisik ATM atau screenshot m-Banking Anda, lalu unggah di bawah ini.
                </div>
            </div>

            <div id="payCashGuide" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:0.85rem 1.15rem;margin-bottom:1.25rem;font-size:13px;color:#166534;display:{{ $payMethod === 'cash' ? 'flex' : 'none' }};align-items:flex-start;gap:10px;">
                <span class="material-symbols-outlined" style="font-size:20px;flex-shrink:0;color:#16a34a;margin-top:2px;">storefront</span>
                <div>
                    <strong>Instruksi Bayar Tunai:</strong> Lakukan pembayaran tunai <strong>Rp 400.000</strong> langsung di Loket Tata Usaha / Panitia PPDB SMPS2 Al-Muhajirin. Pastikan Anda menerima <strong>Kuitansi Fisik Resmi</strong> bertanda tangan bendahara/panitia, lalu foto kuitansi tersebut dan unggah di bawah ini.
                </div>
            </div>

            <!-- Form Upload Bukti Pembayaran -->
            <form action="{{ route('ppdb.upload-payment') }}" method="POST" enctype="multipart/form-data" id="paymentUploadForm">
                @csrf
                <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="{{ $payMethod }}">
                
                <label style="font-size: 13px; font-weight: 700; color: var(--lp-on-surface); margin-bottom: 8px; display: block;">
                    2. Unggah Bukti Pembayaran: <span class="text-danger">*</span> (Wajib Diunggah)
                </label>

                <div class="db-payment-upload-zone" onclick="this.querySelector('input[type=file]').click();" id="paymentDropZone">
                    <div class="db-payment-upload-icon">
                        <span class="material-symbols-outlined" style="font-size:26px;">cloud_upload</span>
                    </div>
                    <div id="payUploadTitle" style="font-size:15px;font-weight:700;color:var(--lp-on-surface);margin-bottom:4px;">
                        {{ $payMethod === 'cash' ? 'Klik atau seret foto kuitansi tunai ke sini' : 'Klik atau seret bukti transfer ke sini' }}
                    </div>
                    <div id="payUploadHint" style="font-size:13px;color:var(--lp-on-surface-variant);">
                        {{ $payMethod === 'cash' ? 'Foto kuitansi fisik resmi pembayaran tunai dari panitia PPDB' : 'Foto struk ATM, screenshot m-Banking, atau PDF slip setoran' }}
                    </div>
                    <div style="font-size:11px;color:var(--lp-outline);margin-top:6px;">Format: JPG, PNG, WEBP, PDF — Maks. 5MB</div>
                    <input type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf" required style="display:none;" onchange="this.closest('form').querySelector('.upload-filename').textContent = this.files[0]?.name || '';">
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:0.75rem;flex-wrap:wrap;gap:8px;">
                    <span class="upload-filename" style="font-size:13px;color:var(--lp-primary);font-weight:600;"></span>
                    <button type="submit" class="db-feature-btn db-feature-btn-primary">
                        <span class="material-symbols-outlined" style="font-size:16px;">upload</span>
                        <span id="paySubmitBtnText">{{ $payMethod === 'cash' ? 'Unggah Kuitansi Tunai' : 'Unggah Bukti Transfer' }}</span>
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
            <div class="db-feature-card {{ $isFormFilled ? 'completed' : '' }} {{ $paymentStatus !== 'paid' ? 'locked' : '' }}">
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
                @elseif($isFormFilled)
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

            {{-- Card 2: Upload Berkas (WAJIB) --}}
            <div class="db-feature-card {{ $isAllDocs ? 'completed' : '' }} {{ $paymentStatus !== 'paid' ? 'locked' : '' }}">
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
                @elseif($isAllDocs)
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <div class="db-feature-status" style="background:var(--lp-green-light);color:var(--lp-green);">
                            <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
                            Lengkap (4/4 Berkas)
                        </div>
                        <a href="{{ route('ppdb.upload') }}" class="db-feature-btn db-feature-btn-outline">
                            <span class="material-symbols-outlined" style="font-size:16px;">upload_file</span>
                            Lihat / Kelola
                        </a>
                    </div>
                @elseif($hasDocs)
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <div class="db-feature-status" style="background:#fff7ed;color:#c2410c;">
                            <span class="material-symbols-outlined" style="font-size:16px;">pending</span>
                            Sebagian ({{ $docCount }}/4 Berkas)
                        </div>
                        <a href="{{ route('ppdb.upload') }}" class="db-feature-btn db-feature-btn-primary" style="background:linear-gradient(135deg,var(--lp-yellow-dark),#78350f);">
                            <span class="material-symbols-outlined" style="font-size:16px;">upload_file</span>
                            Lengkapi Berkas
                        </a>
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
            @elseif($isFormFilled)
                <span class="db-action-status" style="background:var(--lp-green-light);color:var(--lp-green);">Lengkap ✓</span>
            @else
                <span class="db-action-status" style="background:rgba(214,227,255,0.6);color:var(--lp-primary);">Siap Diisi</span>
            @endif
        </a>
        <a href="{{ route('ppdb.test-card') }}" class="db-action-card {{ ($paymentStatus !== 'paid' || !$isFormFilled) ? 'locked' : '' }}">
            <div class="db-action-icon" style="background:var(--lp-red);color:#fff;"><span class="material-symbols-outlined">badge</span></div>
            <h5>Kartu Tes</h5>
            @if($paymentStatus === 'paid' && $isFormFilled)
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
            <span class="db-info-row-value">2027/2028</span>
        </div>

        @if($isFormFilled)
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
                        <span class="db-info-row-label" style="font-size:12px;">Jurusan</span>
                        <span class="db-info-row-value" style="font-size:13px;text-transform:uppercase;font-weight:700;color:var(--lp-primary);">{{ $formData['major'] ?? '-' }}</span>
                    </div>
                    <div class="db-info-row" style="border-bottom:none;padding:0.4rem 0;">
                        <span class="db-info-row-label" style="font-size:12px;">Jenis Kelamin</span>
                        <span class="db-info-row-value" style="font-size:13px;">{{ $formData['identity']['gender'] ?? '-' }}</span>
                    </div>
                    <div class="db-info-row" style="border-bottom:none;padding:0.4rem 0;">
                        <span class="db-info-row-label" style="font-size:12px;">Asal Sekolah</span>
                        <span class="db-info-row-value" style="font-size:13px;">{{ $formData['school_origin'] ?? '-' }}</span>
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

@push('scripts')
<script>
    function selectPaymentMethod(method) {
        var inputMethod = document.getElementById('selectedPaymentMethod');
        if (inputMethod) inputMethod.value = method;

        var cards = document.querySelectorAll('.pay-method-card');
        cards.forEach(function(card) {
            if (card.getAttribute('data-method') === method) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });

        var infoTf = document.getElementById('payInfoTransfer');
        var infoCash = document.getElementById('payInfoCash');
        var guideTf = document.getElementById('payTransferGuide');
        var guideCash = document.getElementById('payCashGuide');
        var uploadTitle = document.getElementById('payUploadTitle');
        var uploadHint = document.getElementById('payUploadHint');
        var submitBtnText = document.getElementById('paySubmitBtnText');

        if (method === 'cash') {
            if (infoTf) infoTf.style.display = 'none';
            if (infoCash) infoCash.style.display = 'block';
            if (guideTf) guideTf.style.display = 'none';
            if (guideCash) guideCash.style.display = 'flex';
            if (uploadTitle) uploadTitle.textContent = 'Klik atau seret foto kuitansi tunai ke sini';
            if (uploadHint) uploadHint.textContent = 'Foto kuitansi fisik resmi pembayaran tunai dari panitia PPDB';
            if (submitBtnText) submitBtnText.textContent = 'Unggah Kuitansi Tunai (Cash)';
        } else {
            if (infoTf) infoTf.style.display = 'block';
            if (infoCash) infoCash.style.display = 'none';
            if (guideTf) guideTf.style.display = 'flex';
            if (guideCash) guideCash.style.display = 'none';
            if (uploadTitle) uploadTitle.textContent = 'Klik atau seret bukti transfer ke sini';
            if (uploadHint) uploadHint.textContent = 'Foto struk ATM, screenshot m-Banking, atau PDF slip setoran';
            if (submitBtnText) submitBtnText.textContent = 'Unggah Bukti Transfer (TF)';
        }
    }
</script>
@endpush
