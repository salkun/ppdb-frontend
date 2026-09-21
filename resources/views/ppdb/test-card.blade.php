@extends('layouts.app')

@section('title', 'Kartu Tes Pendaftaran (A6)')

@section('content')
<div class="db-content">

    {{-- Screen Header (Hidden on Print) --}}
    <div class="db-page-header d-print-none">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:0.5rem;">
            <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:4px;font-size:13px;font-weight:600;color:var(--lp-primary);text-decoration:none;">
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Dashboard
            </a>
            <span style="color:var(--lp-outline);font-size:13px;">/</span>
            <span style="font-size:13px;font-weight:600;color:var(--lp-on-surface);">Kartu Tes Peserta</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div>
                <h1 class="db-page-title">Kartu Tes Pendaftaran Siswa Baru</h1>
                <p class="db-page-subtitle">Format cetak telah disesuaikan dengan ukuran standar <strong>A6 (105 × 148 mm)</strong>. Cetak dan bawa saat tes seleksi.</p>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <button onclick="window.print()" class="db-form-btn-next" style="cursor:pointer;padding:10px 20px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">print</span>
                    <span>Cetak Kartu (A6)</span>
                </button>
                <a href="{{ route('dashboard') }}" class="db-form-btn-prev" style="padding:10px 16px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Info Banner Ukuran A6 (Hidden on Print) --}}
    <div class="d-print-none" style="max-width:500px;margin:0 auto 1.5rem;background:rgba(214,227,255,0.4);border:1px solid rgba(0,90,180,0.18);border-radius:12px;padding:0.75rem 1rem;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--lp-primary);">
        <span class="material-symbols-outlined" style="font-size:20px;flex-shrink:0;">info</span>
        <span>Ukuran cetak otomatis diatur ke <strong>A6 Portrait</strong>. Gunakan opsi <em>"Save as PDF"</em> atau pilih kertas A6 saat mencetak.</span>
    </div>

    @php
        $majorLabels = [
            'reguler' => 'Reguler (Umum)',
            'bahasa' => 'Bahasa & Literasi',
            'tahfidz' => 'Tahfidzul Qur\'an',
            'ict' => 'ICT (Teknologi)',
        ];
        $selectedMajor = $majorLabels[$formData['major'] ?? ''] ?? ($formData['major'] ?? '-');
        $regId = $registration['id'] ?? 'PPDB-'.date('Y').'-001';
    @endphp

    {{-- ======================================================== --}}
    {{-- A6 PRINTABLE TEST CARD CONTAINER                         --}}
    {{-- ======================================================== --}}
    <div class="test-card-a6-wrapper">
        <div class="test-card-a6" id="printableTestCard">

            {{-- 1. Kop Sekolah --}}
            <div class="tc-header">
                <div class="tc-brand">
                    <div class="tc-logo">
                        <span class="material-symbols-outlined">school</span>
                    </div>
                    <div class="tc-school-info">
                        <div class="tc-school-name">SMPS2 AL-MUHAJIRIN</div>
                        <div class="tc-school-sub">PURWAKARTA — TERAKREDITASI A</div>
                        <div class="tc-school-addr">Jl. Ipik Gandamanah No. 33, Ciseureuh, Purwakarta</div>
                    </div>
                </div>
            </div>

            {{-- Accent Color Line --}}
            <div class="tc-accent-bar"></div>

            {{-- 2. Title Ribbon --}}
            <div class="tc-title-ribbon">
                <div class="tc-title-text">KARTU PESERTA TES SELEKSI</div>
                <div class="tc-title-reg">NO. REG: <strong>{{ $regId }}</strong></div>
            </div>

            {{-- 3. Body: Foto & Data Siswa --}}
            <div class="tc-body">
                {{-- Foto 3x4 Frame --}}
                <div class="tc-photo-col">
                    <div class="tc-photo-box">
                        <span class="material-symbols-outlined tc-photo-icon">person</span>
                        <span class="tc-photo-label">PAS FOTO<br>3 × 4</span>
                    </div>
                </div>

                {{-- Data Siswa Table --}}
                <div class="tc-data-col">
                    <table class="tc-table">
                        <tr>
                            <td class="tc-lbl">Nama Siswa</td>
                            <td class="tc-sep">:</td>
                            <td class="tc-val tc-name"><strong>{{ $formData['full_name'] ?? session('full_name', '-') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="tc-lbl">NIK / NISN</td>
                            <td class="tc-sep">:</td>
                            <td class="tc-val tc-mono">{{ $formData['nik'] ?? '-' }} / {{ $formData['nisn'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="tc-lbl">Jenis Kelamin</td>
                            <td class="tc-sep">:</td>
                            <td class="tc-val">{{ $formData['identity']['gender'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="tc-lbl">TTL</td>
                            <td class="tc-sep">:</td>
                            <td class="tc-val">
                                {{ $formData['identity']['place_of_birth'] ?? '-' }}, 
                                {{ isset($formData['identity']['date_of_birth']) ? date('d/m/Y', strtotime($formData['identity']['date_of_birth'])) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="tc-lbl">Peminatan</td>
                            <td class="tc-sep">:</td>
                            <td class="tc-val tc-major"><strong>{{ $selectedMajor }}</strong></td>
                        </tr>
                        <tr>
                            <td class="tc-lbl">Asal Sekolah</td>
                            <td class="tc-sep">:</td>
                            <td class="tc-val">{{ $formData['school_origin'] ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- 4. Jadwal & Ruang Tes --}}
            <div class="tc-schedule-box">
                <div class="tc-schedule-header">
                    <span class="material-symbols-outlined" style="font-size:13px;">event_available</span>
                    JADWAL & LOKASI PELAKSANAAN TES
                </div>
                <div class="tc-schedule-grid">
                    <div class="tc-sch-item">
                        <span class="tc-sch-label">GELOMBANG / TP</span>
                        <span class="tc-sch-val">TP {{ date('Y') }}/{{ date('Y') + 1 }}</span>
                    </div>
                    <div class="tc-sch-item">
                        <span class="tc-sch-label">WAKTU TES</span>
                        <span class="tc-sch-val">08.00 - 11.30 WIB</span>
                    </div>
                    <div class="tc-sch-item">
                        <span class="tc-sch-label">LOKASI TES</span>
                        <span class="tc-sch-val">Kampus 2 SMPS2</span>
                    </div>
                </div>
            </div>

            {{-- 5. Footer: Tata Tertib & Tanda Tangan --}}
            <div class="tc-footer">
                <div class="tc-rules">
                    <div class="tc-rules-title">Ketentuan Peserta:</div>
                    <ol class="tc-rules-list">
                        <li>Wajib membawa kartu ini saat tes seleksi.</li>
                        <li>Hadir 30 menit sebelum ujian dimulai.</li>
                        <li>Membawa alat tulis (pensil 2B, pulpen, penghapus).</li>
                        <li>Mengenakan pakaian seragam sekolah asal yang rapi.</li>
                    </ol>
                </div>
                <div class="tc-sign">
                    <div class="tc-sign-date">Purwakarta, {{ date('d M Y') }}</div>
                    <div class="tc-sign-role">Ketua Panitia PPDB</div>
                    <div class="tc-sign-space"></div>
                    <div class="tc-sign-name">( Panitia PPDB )</div>
                    <div class="tc-sign-badge">VERIFIED DIGITAL</div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    /* ========================================================
       A6 TEST CARD — SCREEN STYLES
       ======================================================== */
    .test-card-a6-wrapper {
        display: flex;
        justify-content: center;
        margin: 0 auto 3rem;
    }

    .test-card-a6 {
        width: 100%;
        max-width: 440px; /* Proportional to A6 105x148mm */
        background: #ffffff;
        border: 1.5px solid var(--lp-surface-container);
        border-radius: 16px;
        box-shadow: 0 12px 36px rgba(0, 43, 90, 0.08), 0 2px 8px rgba(0, 43, 90, 0.04);
        overflow: hidden;
        font-family: var(--font-landing);
        color: var(--lp-on-surface);
        position: relative;
    }

    /* Kop Surat */
    .tc-header {
        padding: 14px 18px 10px;
        background: #ffffff;
    }

    .tc-brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tc-logo {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--lp-primary), var(--lp-primary-dark));
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0, 90, 180, 0.25);
        flex-shrink: 0;
    }

    .tc-logo .material-symbols-outlined {
        font-size: 24px;
    }

    .tc-school-info {
        flex: 1;
        min-width: 0;
    }

    .tc-school-name {
        font-family: var(--font-headline);
        font-size: 15px;
        font-weight: 800;
        color: var(--lp-primary);
        letter-spacing: -0.01em;
        line-height: 1.2;
    }

    .tc-school-sub {
        font-size: 10.5px;
        font-weight: 700;
        color: var(--lp-on-surface);
        letter-spacing: 0.03em;
        margin-top: 1px;
    }

    .tc-school-addr {
        font-size: 9.5px;
        color: var(--lp-outline);
        margin-top: 1px;
        line-height: 1.2;
    }

    /* Accent Bar */
    .tc-accent-bar {
        height: 3px;
        background: linear-gradient(90deg, var(--lp-primary), var(--lp-yellow), var(--lp-green));
    }

    /* Title Ribbon */
    .tc-title-ribbon {
        background: linear-gradient(135deg, var(--lp-primary), var(--lp-primary-dark));
        color: #ffffff;
        padding: 7px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    .tc-title-text {
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .tc-title-reg {
        font-size: 11px;
        opacity: 0.95;
    }

    .tc-title-reg strong {
        color: var(--lp-yellow);
        letter-spacing: 0.04em;
    }

    /* Body */
    .tc-body {
        padding: 14px 18px;
        display: flex;
        gap: 14px;
        align-items: flex-start;
    }

    /* Pas Foto */
    .tc-photo-col {
        flex-shrink: 0;
    }

    .tc-photo-box {
        width: 82px;
        height: 108px; /* Proportional 3x4 */
        border: 1.5px dashed var(--lp-outline);
        border-radius: 8px;
        background: var(--lp-surface-subtle);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--lp-outline);
        padding: 4px;
    }

    .tc-photo-icon {
        font-size: 32px;
        margin-bottom: 2px;
        opacity: 0.6;
    }

    .tc-photo-label {
        font-size: 8.5px;
        font-weight: 700;
        letter-spacing: 0.04em;
        line-height: 1.3;
    }

    /* Data Table */
    .tc-data-col {
        flex: 1;
        min-width: 0;
    }

    .tc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .tc-table tr {
        border-bottom: 1px dashed var(--lp-surface-container);
    }

    .tc-table tr:last-child {
        border-bottom: none;
    }

    .tc-lbl {
        width: 85px;
        color: var(--lp-on-surface-variant);
        padding: 4px 0;
        font-size: 10.5px;
        vertical-align: top;
        font-weight: 500;
    }

    .tc-sep {
        width: 10px;
        color: var(--lp-outline);
        padding: 4px 2px;
        vertical-align: top;
        text-align: center;
    }

    .tc-val {
        padding: 4px 0;
        color: var(--lp-on-surface);
        vertical-align: top;
        font-size: 11px;
        word-break: break-word;
    }

    .tc-name {
        color: var(--lp-primary);
        font-size: 11.5px;
    }

    .tc-mono {
        font-size: 10px;
        letter-spacing: 0.02em;
    }

    .tc-major {
        color: #0284c7;
    }

    /* Schedule Box */
    .tc-schedule-box {
        margin: 0 18px 12px;
        background: rgba(214, 227, 255, 0.35);
        border: 1px solid rgba(0, 90, 180, 0.15);
        border-radius: 10px;
        padding: 8px 12px;
    }

    .tc-schedule-header {
        font-size: 9.5px;
        font-weight: 800;
        color: var(--lp-primary);
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 6px;
    }

    .tc-schedule-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
    }

    .tc-sch-item {
        display: flex;
        flex-direction: column;
    }

    .tc-sch-label {
        font-size: 8px;
        font-weight: 600;
        color: var(--lp-outline);
        letter-spacing: 0.02em;
    }

    .tc-sch-val {
        font-size: 10px;
        font-weight: 700;
        color: var(--lp-on-surface);
        margin-top: 1px;
    }

    /* Footer */
    .tc-footer {
        padding: 10px 18px 14px;
        background: var(--lp-surface-subtle);
        border-top: 1px solid var(--lp-surface-container);
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-end;
    }

    .tc-rules {
        flex: 1;
        min-width: 0;
    }

    .tc-rules-title {
        font-size: 9px;
        font-weight: 800;
        color: var(--lp-on-surface);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 3px;
    }

    .tc-rules-list {
        margin: 0;
        padding-left: 12px;
        font-size: 8.5px;
        color: var(--lp-on-surface-variant);
        line-height: 1.45;
    }

    .tc-sign {
        width: 120px;
        text-align: center;
        flex-shrink: 0;
    }

    .tc-sign-date {
        font-size: 8.5px;
        color: var(--lp-outline);
    }

    .tc-sign-role {
        font-size: 9px;
        font-weight: 700;
        color: var(--lp-on-surface);
        margin-top: 1px;
    }

    .tc-sign-space {
        height: 28px;
    }

    .tc-sign-name {
        font-size: 9px;
        font-weight: 800;
        color: var(--lp-on-surface);
        border-top: 1px solid #cbd5e1;
        padding-top: 2px;
    }

    .tc-sign-badge {
        display: inline-block;
        font-size: 7px;
        font-weight: 800;
        color: var(--lp-green);
        background: var(--lp-green-light);
        padding: 1px 5px;
        border-radius: 4px;
        margin-top: 2px;
        letter-spacing: 0.04em;
    }

    /* ========================================================
       A6 PRINT SPECIFICATION (EXACT 105 × 148 MM)
       ======================================================== */
    @media print {
        @page {
            size: 105mm 148mm portrait; /* Standard A6 */
            margin: 4mm;
        }

        html, body {
            width: 105mm !important;
            height: 148mm !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            font-size: 8pt;
        }

        /* Hide all layout elements except test card */
        .db-sidebar,
        .db-topbar,
        .db-mobile-tab-bar,
        .db-page-header,
        .d-print-none,
        header,
        aside,
        nav,
        .offcanvas {
            display: none !important;
        }

        .db-shell {
            display: block !important;
            background: #ffffff !important;
        }

        .db-viewport {
            margin-left: 0 !important;
            padding: 0 !important;
            min-height: auto !important;
        }

        .db-content {
            padding: 0 !important;
            margin: 0 !important;
            max-width: none !important;
        }

        .test-card-a6-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            display: block !important;
        }

        .test-card-a6 {
            width: 97mm !important;
            max-height: 140mm !important;
            margin: 0 auto !important;
            border: 1.5px solid #000000 !important;
            border-radius: 4px !important;
            box-shadow: none !important;
            page-break-inside: avoid !important;
            page-break-after: avoid !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .tc-header {
            padding: 3mm 4mm 2mm !important;
        }

        .tc-logo {
            width: 9mm !important;
            height: 9mm !important;
            background: #005ab4 !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .tc-school-name {
            font-size: 10pt !important;
            color: #005ab4 !important;
        }

        .tc-school-sub {
            font-size: 7pt !important;
        }

        .tc-school-addr {
            font-size: 6.5pt !important;
        }

        .tc-title-ribbon {
            background: #005ab4 !important;
            color: #ffffff !important;
            padding: 1.8mm 4mm !important;
            font-size: 7.5pt !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .tc-body {
            padding: 3mm 4mm !important;
            gap: 3mm !important;
        }

        .tc-photo-box {
            width: 25mm !important;
            height: 33mm !important; /* Pas foto 3x4 pada A6 */
            border: 1px dashed #777 !important;
        }

        .tc-photo-icon {
            font-size: 16pt !important;
        }

        .tc-photo-label {
            font-size: 5.5pt !important;
        }

        .tc-table {
            font-size: 7.5pt !important;
        }

        .tc-lbl {
            width: 23mm !important;
            font-size: 7pt !important;
            padding: 1mm 0 !important;
        }

        .tc-val {
            font-size: 7.5pt !important;
            padding: 1mm 0 !important;
        }

        .tc-name {
            font-size: 8pt !important;
            color: #000000 !important;
        }

        .tc-schedule-box {
            margin: 0 4mm 2.5mm !important;
            padding: 2mm 3mm !important;
            background: #f0f4f9 !important;
            border: 1px solid #c8d7ea !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .tc-schedule-header {
            font-size: 6.5pt !important;
            margin-bottom: 1mm !important;
        }

        .tc-sch-label {
            font-size: 5.5pt !important;
        }

        .tc-sch-val {
            font-size: 7pt !important;
        }

        .tc-footer {
            padding: 2.5mm 4mm 3mm !important;
            background: #f9fbfd !important;
            border-top: 1px solid #d2dbe5 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .tc-rules-title {
            font-size: 6.5pt !important;
        }

        .tc-rules-list {
            font-size: 6pt !important;
            line-height: 1.35 !important;
            padding-left: 3mm !important;
        }

        .tc-sign {
            width: 30mm !important;
        }

        .tc-sign-date {
            font-size: 6pt !important;
        }

        .tc-sign-role {
            font-size: 6.5pt !important;
        }

        .tc-sign-space {
            height: 8mm !important;
        }

        .tc-sign-name {
            font-size: 6.5pt !important;
        }

        .tc-sign-badge {
            font-size: 5pt !important;
        }
    }

    /* Mobile adjustments for screen view */
    @media (max-width: 480px) {
        .test-card-a6 {
            border-radius: 12px;
        }

        .tc-header {
            padding: 12px 14px 8px;
        }

        .tc-body {
            padding: 12px 14px;
            gap: 10px;
        }

        .tc-photo-box {
            width: 72px;
            height: 96px;
        }

        .tc-lbl {
            width: 75px;
            font-size: 9.5px;
        }

        .tc-val {
            font-size: 10px;
        }

        .tc-schedule-box {
            margin: 0 14px 10px;
            padding: 6px 10px;
        }

        .tc-footer {
            padding: 8px 14px 12px;
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        .tc-sign {
            width: 100%;
            text-align: right;
        }

        .tc-sign-name {
            display: inline-block;
            min-width: 100px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-print jika parameter ?print=1
    @if($autoPrint)
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    @endif
</script>
@endpush
