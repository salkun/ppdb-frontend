@extends('admin.layout')

@section('title', 'Dossier Pendaftar — ' . ($account['full_name'] ?? 'Calon Siswa'))
@section('header_title', 'Dossier Calon Peserta Didik')

@section('content')

@php
    $pStatus = $registration['payment_status'] ?? 'unpaid';
    $rStatus = $registration['registration_status'] ?? 'pending';
    $paymentProof = $registration['payment_proof_path'] ?? null;
    $hasProof = !empty($paymentProof);
    $hasForm = !empty($formData);

    // Dictionaries for human-readable labels
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

    // Extract parents
    $father = null;
    $mother = null;
    $guardian = null;
    $rawParents = $formData['student_parents'] ?? ($formData['parents'] ?? []);
    if (!empty($rawParents) && is_array($rawParents)) {
        foreach ($rawParents as $sp) {
            $relType = (int) ($sp['relationship_type'] ?? 0);
            $pObj = $sp['parent'] ?? $sp;
            if ($relType === 1) $father = $pObj;
            elseif ($relType === 2) $mother = $pObj;
            elseif ($relType === 3) $guardian = $pObj;
        }
    }

    // Name initials
    $name = $account['full_name'] ?? ($formData['full_name'] ?? 'Calon Siswa');
    $parts = explode(' ', trim($name));
    $initials = '';
    if (count($parts) >= 2) {
        $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }
@endphp

<!-- ============================================== -->
<!-- HERO DOSSIER HEADER CARD -->
<!-- ============================================== -->
<div class="dossier-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-start align-items-md-center gap-3">
        <!-- Left: Avatar & Profile Info -->
        <div class="d-flex align-items-center gap-3">
            <div class="dossier-avatar">
                {{ $initials }}
            </div>
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="badge-pastel badge-pastel-neutral">CALON SISWA</span>
                    @if($pStatus === 'paid')
                        <span class="badge-pastel badge-pastel-green"><i class="ph-bold ph-check"></i> BIAYA LUNAS</span>
                    @elseif($pStatus === 'pending_verification')
                        <span class="badge-pastel badge-pastel-yellow"><i class="ph-bold ph-hourglass"></i> BUTUH VERIFIKASI BAYAR</span>
                    @elseif($pStatus === 'rejected')
                        <span class="badge-pastel badge-pastel-red"><i class="ph-bold ph-x"></i> PEMBAYARAN DITOLAK</span>
                    @else
                        <span class="badge-pastel badge-pastel-neutral">BELUM BAYAR</span>
                    @endif

                    @if($rStatus === 'accepted')
                        <span class="badge-pastel badge-pastel-green"><i class="ph-bold ph-seal-check"></i> RESMI DITERIMA</span>
                    @elseif($rStatus === 'rejected')
                        <span class="badge-pastel badge-pastel-red"><i class="ph-bold ph-x-circle"></i> TIDAK LOLOS</span>
                    @else
                        <span class="badge-pastel badge-pastel-blue">MENUNGGU SELEKSI</span>
                    @endif

                    @php
                        $rawMajor = $formData['major'] ?? ($registration['student']['major'] ?? null);
                        $majorBadge = match(strtolower((string)$rawMajor)) {
                            'reguler' => ['label' => 'JURUSAN REGULER', 'class' => 'badge-pastel-neutral', 'icon' => 'ph-book-open'],
                            'bahasa' => ['label' => 'JURUSAN BAHASA', 'class' => 'badge-pastel-blue', 'icon' => 'ph-translate'],
                            'tahfidz' => ['label' => 'JURUSAN TAHFIDZ', 'class' => 'badge-pastel-green', 'icon' => 'ph-scroll'],
                            'ict' => ['label' => 'JURUSAN ICT (IT)', 'class' => 'badge-pastel-yellow', 'icon' => 'ph-laptop'],
                            default => null
                        };
                    @endphp
                    @if($majorBadge)
                        <span class="badge-pastel {{ $majorBadge['class'] }} fw-semibold">
                            <i class="ph-bold {{ $majorBadge['icon'] }}"></i> {{ $majorBadge['label'] }}
                        </span>
                    @endif
                </div>

                <h3 class="font-serif-heading fs-2 text-dark mb-1">{{ $name }}</h3>

                <div class="d-flex flex-wrap align-items-center gap-3 text-secondary small font-mono-meta" style="font-size: 0.78rem;">
                    <span><i class="ph-bold ph-identification-card me-1"></i>NIK: <strong class="text-dark">{{ $account['nik'] ?? ($formData['nik'] ?? '-') }}</strong></span>
                    <span>&bull;</span>
                    <span><i class="ph-bold ph-envelope me-1"></i>{{ $account['email'] ?? '-' }}</span>
                    <span>&bull;</span>
                    <span><i class="ph-bold ph-calendar me-1"></i>Daftar: {{ isset($registration['created_at']) ? date('d M Y, H:i', strtotime($registration['created_at'])) . ' WIB' : '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.ppdb.index') }}" class="btn-minimal-secondary py-2 px-3">
                <i class="ph-bold ph-arrow-left"></i> Kembali
            </a>

            <a href="{{ route('admin.ppdb.edit', $registration['id']) }}" class="btn-minimal-secondary py-2 px-3 text-primary" title="Edit Data Pendaftar">
                <i class="ph-bold ph-pencil-simple"></i> Edit
            </a>

            <button type="button" class="btn-minimal-secondary text-danger py-2 px-3 border-danger-subtle" data-bs-toggle="modal" data-bs-target="#deleteShowModal" title="Hapus Data Pendaftar">
                <i class="ph-bold ph-trash"></i> Hapus
            </button>

            @if($rStatus !== 'accepted' && $pStatus === 'paid' && $hasForm)
                <form action="{{ route('admin.ppdb.accept', $registration['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerima calon siswa ini? Seluruh data akan dimigrasikan permanen ke sistem database sekolah.');">
                    @csrf
                    <button type="submit" class="btn-minimal-primary py-2 px-3">
                        <i class="ph-bold ph-check-circle"></i> Terima Siswa
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MAIN SPLIT WORKSPACE -->
<!-- ============================================== -->
<div class="row g-4">
    <!-- ============================================== -->
    <!-- KOLOM KIRI: VERIFIKASI BIAYA & STATUS -->
    <!-- ============================================== -->
    <div class="col-lg-4 col-xl-4">
        <!-- 1. Panel Verifikasi Pembayaran -->
        <div class="bento-card mb-4">
            <div class="bento-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="ph-bold ph-credit-card fs-5 text-dark"></i>
                    <h6 class="fw-semibold text-dark mb-0">Biaya & Pembayaran</h6>
                </div>
                <span class="font-mono-meta small fw-semibold text-dark">RP 250.000</span>
            </div>

            <!-- Struk / Bukti Transfer Box -->
            <div class="mb-3">
                <span class="text-secondary small d-block mb-2 font-mono-meta" style="font-size: 0.72rem;">BERKAS STRUK / TRANSFER</span>
                @if($hasProof)
                    <div class="proof-preview-box">
                        @php
                            $ext = pathinfo($paymentProof, PATHINFO_EXTENSION);
                        @endphp
                        @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']))
                            <img src="{{ $backendUrl . $paymentProof }}" 
                                 alt="Struk Transfer Calon Siswa" 
                                 class="proof-img mb-2"
                                 data-bs-toggle="modal"
                                 data-bs-target="#proofModal">
                        @else
                            <div class="py-4 text-secondary">
                                <i class="ph-bold ph-file-pdf fs-1 d-block mb-1 text-dark"></i>
                                <span class="small fw-semibold">Dokumen PDF Terlampir</span>
                            </div>
                        @endif
                        <div class="d-flex gap-1">
                            <a href="{{ $backendUrl . $paymentProof }}" target="_blank" class="btn-minimal-secondary w-100 py-1" style="font-size: 0.78rem;">
                                <i class="ph-bold ph-arrow-square-out me-1"></i> Buka Layar Penuh
                            </a>
                        </div>
                    </div>

                    <!-- Modal Zoom Struk -->
                    <div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content bento-card p-0 overflow-hidden border">
                                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                    <h6 class="fw-semibold text-dark mb-0">Pratinjau Berkas Bukti Transfer</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="p-3 text-center bg-light">
                                    <img src="{{ $backendUrl . $paymentProof }}" alt="Bukti Transfer" class="img-fluid rounded shadow-sm" style="max-height: 80vh;">
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-3 border rounded-2 text-center text-secondary small" style="background-color: var(--surface-muted); border-color: var(--border-light) !important;">
                        <i class="ph-bold ph-file-x fs-3 d-block mb-1 text-secondary"></i>
                        Pendaftar belum mengunggah berkas transfer.
                    </div>
                @endif
            </div>

            <!-- Form Ubah Status Pembayaran -->
            <form action="{{ route('admin.ppdb.verify-payment', $registration['id']) }}" method="POST" class="pt-3 border-top" style="border-color: var(--border-light) !important;">
                @csrf
                <div class="mb-2">
                    <label class="form-label font-mono-meta small text-secondary mb-1" style="font-size: 0.72rem;">STATUS VERIFIKASI</label>
                    <select class="form-select" name="payment_status" required>
                        <option value="paid" {{ $pStatus === 'paid' ? 'selected' : '' }}>Setujui: Lunas (Tervalidasi)</option>
                        <option value="rejected" {{ $pStatus === 'rejected' ? 'selected' : '' }}>Tolak: Bukti Tidak Valid</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label font-mono-meta small text-secondary mb-1" style="font-size: 0.72rem;">NOMINAL TERVERIFIKASI (RP)</label>
                    <input type="number" class="form-control font-mono-meta" name="payment_amount" value="{{ $registration['payment_amount'] ?: 250000 }}" required>
                </div>
                <button type="submit" class="btn-minimal-primary w-100">
                    <i class="ph-bold ph-check me-1"></i> Simpan Status Pembayaran
                </button>
            </form>
        </div>

        <!-- 2. Panel Status Seleksi & Penerimaan -->
        <div class="bento-card">
            <div class="bento-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="ph-bold ph-seal-check fs-5 text-dark"></i>
                    <h6 class="fw-semibold text-dark mb-0">Status Penerimaan</h6>
                </div>
                <span class="badge-pastel {{ $rStatus === 'accepted' ? 'badge-pastel-green' : ($rStatus === 'rejected' ? 'badge-pastel-red' : 'badge-pastel-blue') }}">
                    {{ strtoupper($rStatus) }}
                </span>
            </div>

            <div class="mb-3">
                @if($rStatus === 'accepted')
                    <div class="alert-document alert-success mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph-bold ph-check-circle text-success fs-5"></i>
                            <strong class="text-dark">Siswa Diterima Resmi</strong>
                        </div>
                        <p class="small text-secondary mb-0">
                            Data calon peserta didik telah disahkan ke dalam sistem induk sekolah.
                        </p>
                    </div>
                @elseif($rStatus === 'rejected')
                    <div class="alert-document alert-danger mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph-bold ph-x-circle text-danger fs-5"></i>
                            <strong class="text-dark">Tidak Lolos Seleksi</strong>
                        </div>
                        <p class="small text-secondary mb-0">
                            Berkas calon siswa tidak memenuhi ambang batas seleksi penerimaan.
                        </p>
                    </div>
                @else
                    <p class="small text-secondary mb-3">
                        Menerima calon siswa akan otomatis menerbitkan akun siswa resmi dan mencatat seluruh berkas pendaftaran ke pangkalan data sekolah.
                    </p>
                @endif

                @if($rStatus !== 'accepted')
                    @if($pStatus !== 'paid')
                        <button type="button" class="btn-minimal-secondary w-100 text-secondary" disabled>
                            <i class="ph-bold ph-lock-key me-1"></i> Pembayaran Belum Lunas
                        </button>
                    @elseif(!$hasForm)
                        <button type="button" class="btn-minimal-secondary w-100 text-secondary" disabled>
                            <i class="ph-bold ph-file-x me-1"></i> Formulir Belum Diisi Siswa
                        </button>
                    @else
                        <form action="{{ route('admin.ppdb.accept', $registration['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerima calon siswa ini? Data akan diterbitkan resmi ke pangkalan data sekolah.');">
                            @csrf
                            <button type="submit" class="btn-minimal-primary w-100 py-2">
                                <i class="ph-bold ph-check-circle me-1"></i> Terima & Terbitkan Data Siswa
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- KOLOM KANAN: DOSSIER INSPECTOR BERKAS -->
    <!-- ============================================== -->
    <div class="col-lg-8 col-xl-8">
        <div class="bento-card p-0 overflow-hidden">
            <!-- Dossier Inspector Navigation -->
            <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color: var(--border-light) !important; background-color: var(--surface-bg);">
                <div class="d-flex align-items-center gap-2">
                    <i class="ph-bold ph-folder-open fs-5 text-dark"></i>
                    <h6 class="fw-semibold text-dark mb-0">Lembar Berkas Calon Siswa</h6>
                </div>

                @if($hasForm)
                    <!-- Segmented Tabs for Dossier Sections -->
                    <ul class="nav segmented-nav" id="dossierTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-biodata-btn" data-bs-toggle="pill" data-bs-target="#tab-biodata" type="button" role="tab">
                                <i class="ph-bold ph-user"></i> Biodata
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-alamat-btn" data-bs-toggle="pill" data-bs-target="#tab-alamat" type="button" role="tab">
                                <i class="ph-bold ph-map-pin"></i> Domisili & Kontak
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-ortu-btn" data-bs-toggle="pill" data-bs-target="#tab-ortu" type="button" role="tab">
                                <i class="ph-bold ph-users"></i> Orang Tua / Wali
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-all-btn" data-bs-toggle="pill" data-bs-target="#tab-all" type="button" role="tab">
                                <i class="ph-bold ph-list-bullets"></i> Semua
                            </button>
                        </li>
                    </ul>
                @else
                    <span class="badge-pastel badge-pastel-neutral">BELUM MENGISI FORMULIR</span>
                @endif
            </div>

            <div class="p-3 p-md-4">
                @if(!$hasForm)
                    <div class="text-center py-5 text-secondary">
                        <i class="ph-bold ph-file-dashed fs-1 d-block mb-2 text-secondary"></i>
                        <h5 class="fw-semibold text-dark">Pendaftar Belum Mengisi Formulir Pendaftaran</h5>
                        <p class="small text-secondary mb-0" style="max-width: 480px; margin: 0 auto;">
                            Calon siswa belum melengkapi isian biodata pokok, identitas tambahan, domisili tempat tinggal, kontak, dan data orang tua/wali.
                        </p>
                    </div>
                @else
                    <div class="tab-content" id="dossierTabContent">

                        <!-- ========================================== -->
                        <!-- TAB 1: BIODATA & IDENTITAS -->
                        <!-- ========================================== -->
                        <div class="tab-pane fade show active" id="tab-biodata" role="tabpanel">
                            <!-- Section: Biodata Pokok -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-pastel badge-pastel-neutral">01</span>
                                        <h6 class="fw-semibold text-dark mb-0">Biodata Pokok Calon Siswa</h6>
                                    </div>
                                    <span class="badge-pastel badge-pastel-green">TERVERIFIKASI</span>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nomor Induk Kependudukan (NIK)</span>
                                            <span class="data-value font-mono-meta fs-6">{{ $formData['nik'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nomor Induk Siswa Nasional (NISN)</span>
                                            <span class="data-value font-mono-meta fs-6">{{ $formData['nisn'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="data-cell">
                                            <span class="data-label">Nama Lengkap Siswa</span>
                                            <span class="data-value fw-semibold fs-6">{{ $formData['full_name'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nama Depan</span>
                                            <span class="data-value">{{ $formData['first_name'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nama Belakang</span>
                                            <span class="data-value">{{ $formData['last_name'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Peminatan Jurusan & Sekolah Asal -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-pastel badge-pastel-neutral">02</span>
                                        <h6 class="fw-semibold text-dark mb-0">Peminatan Jurusan &amp; Asal Sekolah</h6>
                                    </div>
                                    <span class="badge-pastel badge-pastel-blue">PPDB PILIHAN</span>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Pilihan Jurusan</span>
                                            <span class="data-value fw-bold text-dark">
                                                {{ strtoupper($formData['major'] ?? ($registration['student']['major'] ?? '-')) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="data-cell">
                                            <span class="data-label">Nama Asal Sekolah (SMP/MTs)</span>
                                            <span class="data-value fw-medium">
                                                {{ $formData['school_origin'] ?? ($registration['student']['school_origin'] ?? '-') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="data-cell">
                                            <span class="data-label">Alamat Sekolah Asal</span>
                                            <span class="data-value">
                                                {{ $formData['school_origin_address'] ?? ($registration['student']['school_origin_address'] ?? '-') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Identitas Tambahan -->
                            <div>
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-pastel badge-pastel-neutral">03</span>
                                        <h6 class="fw-semibold text-dark mb-0">Identitas Tambahan &amp; Keluarga</h6>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nomor Kartu Keluarga (KK)</span>
                                            <span class="data-value font-mono-meta">{{ $formData['identity']['family_card_number'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Jenis Kelamin</span>
                                            <span class="data-value">{{ $formData['identity']['gender'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Agama</span>
                                            <span class="data-value">{{ $formData['identity']['religion'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Tempat Lahir</span>
                                            <span class="data-value">{{ $formData['identity']['place_of_birth'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Tanggal Lahir</span>
                                            <span class="data-value font-mono-meta">{{ $formData['identity']['date_of_birth'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="data-cell">
                                            <span class="data-label">Susunan Saudara Kandung</span>
                                            <span class="data-value">
                                                Anak ke-<strong>{{ $formData['identity']['birth_order'] ?? ($formData['birth_order'] ?? '-') }}</strong> dari total <strong>{{ $formData['identity']['siblings_count'] ?? ($formData['siblings_count'] ?? '-') }}</strong> bersaudara
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- TAB 2: DOMISILI & KONTAK -->
                        <!-- ========================================== -->
                        <div class="tab-pane fade" id="tab-alamat" role="tabpanel">
                            <!-- Section: Tempat Tinggal -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-pastel badge-pastel-neutral">03</span>
                                        <h6 class="fw-semibold text-dark mb-0">Alamat Tempat Tinggal & Domisili</h6>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="data-cell">
                                            <span class="data-label">Alamat Lengkap / Jalan</span>
                                            <span class="data-value fw-medium">{{ $formData['address']['street_address'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">RT / RW</span>
                                            <span class="data-value font-mono-meta">{{ $formData['address']['rt'] ?? '-' }} / {{ $formData['address']['rw'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Kelurahan / Desa</span>
                                            <span class="data-value">{{ $formData['address']['village'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Kecamatan</span>
                                            <span class="data-value">{{ $formData['address']['district'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Kode Pos</span>
                                            <span class="data-value font-mono-meta">{{ $formData['address']['postal_code'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Jenis Tempat Tinggal</span>
                                            <span class="data-value">{{ $formData['address']['residence_type'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Moda Transportasi</span>
                                            <span class="data-value">{{ $formData['address']['transportation_mode'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Kontak -->
                            <div>
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-pastel badge-pastel-neutral">04</span>
                                        <h6 class="fw-semibold text-dark mb-0">Kontak & Komunikasi Siswa</h6>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nomor WhatsApp Siswa</span>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="data-value font-mono-meta">{{ $formData['contact']['whatsapp_number'] ?? '-' }}</span>
                                                @if(!empty($formData['contact']['whatsapp_number']))
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $formData['contact']['whatsapp_number']) }}" target="_blank" class="btn-minimal-secondary py-0 px-2" style="font-size: 0.72rem; min-height: 24px;">
                                                        <i class="ph-bold ph-chat-circle-dots"></i> Chat
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nomor Handphone (HP)</span>
                                            <span class="data-value font-mono-meta">{{ $formData['contact']['mobile_number'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nomor Telepon Rumah</span>
                                            <span class="data-value font-mono-meta">{{ $formData['contact']['phone_number'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Email Calon Siswa</span>
                                            <span class="data-value">{{ $formData['contact']['email'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- TAB 3: DATA ORANG TUA / WALI -->
                        <!-- ========================================== -->
                        <div class="tab-pane fade" id="tab-ortu" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge-pastel badge-pastel-neutral">05</span>
                                    <h6 class="fw-semibold text-dark mb-0">Data Orang Tua / Wali Siswa</h6>
                                </div>

                                <!-- Sub tabs for father, mother, guardian -->
                                <ul class="nav segmented-nav" id="parentSubTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="subtab-ayah-btn" data-bs-toggle="pill" data-bs-target="#subtab-ayah" type="button" role="tab">
                                            Ayah Kandung
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="subtab-ibu-btn" data-bs-toggle="pill" data-bs-target="#subtab-ibu" type="button" role="tab">
                                            Ibu Kandung
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="subtab-wali-btn" data-bs-toggle="pill" data-bs-target="#subtab-wali" type="button" role="tab">
                                            Wali (Opsional)
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="tab-content" id="parentSubContent">
                                <!-- Sub Pane Ayah -->
                                <div class="tab-pane fade show active" id="subtab-ayah" role="tabpanel">
                                    @if(!empty($father))
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="data-cell">
                                                    <span class="data-label">Nama Lengkap Ayah</span>
                                                    <span class="data-value fw-semibold">{{ $father['full_name'] ?? ($father['name'] ?? '-') }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="data-cell">
                                                    <span class="data-label">NIK Ayah</span>
                                                    <span class="data-value font-mono-meta">{{ $father['nik'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Tahun Lahir</span>
                                                    <span class="data-value font-mono-meta">{{ $father['birth_year'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Pendidikan Terakhir</span>
                                                    <span class="data-value">{{ $educationList[$father['education_code'] ?? ''] ?? ($father['education'] ?? ($father['education_code'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Pekerjaan Utama</span>
                                                    <span class="data-value">{{ $occupationList[$father['occupation_code'] ?? ''] ?? ($father['occupation'] ?? ($father['occupation_code'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Penghasilan Bulanan</span>
                                                    <span class="data-value">{{ $incomeList[$father['income_code'] ?? ''] ?? ($father['monthly_income'] ?? ($father['income_code'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Nomor HP / WhatsApp</span>
                                                    <span class="data-value font-mono-meta">{{ $father['phone_number'] ?? ($father['whatsapp_number'] ?? ($father['phone'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Email Ayah</span>
                                                    <span class="data-value">{{ $father['email'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-4 text-center border rounded-2 text-secondary small" style="background-color: var(--surface-muted); border-color: var(--border-light) !important;">
                                            Data ayah kandung belum tercatat.
                                        </div>
                                    @endif
                                </div>

                                <!-- Sub Pane Ibu -->
                                <div class="tab-pane fade" id="subtab-ibu" role="tabpanel">
                                    @if(!empty($mother))
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="data-cell">
                                                    <span class="data-label">Nama Lengkap Ibu</span>
                                                    <span class="data-value fw-semibold">{{ $mother['full_name'] ?? ($mother['name'] ?? '-') }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="data-cell">
                                                    <span class="data-label">NIK Ibu</span>
                                                    <span class="data-value font-mono-meta">{{ $mother['nik'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Tahun Lahir</span>
                                                    <span class="data-value font-mono-meta">{{ $mother['birth_year'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Pendidikan Terakhir</span>
                                                    <span class="data-value">{{ $educationList[$mother['education_code'] ?? ''] ?? ($mother['education'] ?? ($mother['education_code'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Pekerjaan Utama</span>
                                                    <span class="data-value">{{ $occupationList[$mother['occupation_code'] ?? ''] ?? ($mother['occupation'] ?? ($mother['occupation_code'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Penghasilan Bulanan</span>
                                                    <span class="data-value">{{ $incomeList[$mother['income_code'] ?? ''] ?? ($mother['monthly_income'] ?? ($mother['income_code'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Nomor HP / WhatsApp</span>
                                                    <span class="data-value font-mono-meta">{{ $mother['phone_number'] ?? ($mother['whatsapp_number'] ?? ($mother['phone'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Email Ibu</span>
                                                    <span class="data-value">{{ $mother['email'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-4 text-center border rounded-2 text-secondary small" style="background-color: var(--surface-muted); border-color: var(--border-light) !important;">
                                            Data ibu kandung belum tercatat.
                                        </div>
                                    @endif
                                </div>

                                <!-- Sub Pane Wali -->
                                <div class="tab-pane fade" id="subtab-wali" role="tabpanel">
                                    @if(!empty($guardian))
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="data-cell">
                                                    <span class="data-label">Nama Lengkap Wali</span>
                                                    <span class="data-value fw-semibold">{{ $guardian['full_name'] ?? ($guardian['name'] ?? '-') }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="data-cell">
                                                    <span class="data-label">NIK Wali</span>
                                                    <span class="data-value font-mono-meta">{{ $guardian['nik'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Tahun Lahir</span>
                                                    <span class="data-value font-mono-meta">{{ $guardian['birth_year'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Pendidikan Terakhir</span>
                                                    <span class="data-value">{{ $educationList[$guardian['education_code'] ?? ''] ?? ($guardian['education'] ?? ($guardian['education_code'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Pekerjaan</span>
                                                    <span class="data-value">{{ $occupationList[$guardian['occupation_code'] ?? ''] ?? ($guardian['occupation'] ?? ($guardian['occupation_code'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Penghasilan Bulanan</span>
                                                    <span class="data-value">{{ $incomeList[$guardian['income_code'] ?? ''] ?? ($guardian['monthly_income'] ?? ($guardian['income_code'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Nomor HP / WhatsApp</span>
                                                    <span class="data-value font-mono-meta">{{ $guardian['phone_number'] ?? ($guardian['whatsapp_number'] ?? ($guardian['phone'] ?? '-')) }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-cell">
                                                    <span class="data-label">Email Wali</span>
                                                    <span class="data-value">{{ $guardian['email'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-4 text-center border rounded-2 text-secondary small" style="background-color: var(--surface-muted); border-color: var(--border-light) !important;">
                                            Data wali tidak diisi (opsional).
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- TAB 4: SEMUA BERKAS LENGKAP -->
                        <!-- ========================================== -->
                        <div class="tab-pane fade" id="tab-all" role="tabpanel">
                            <!-- All Section 1 -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                    <span class="badge-pastel badge-pastel-neutral">01</span>
                                    <h6 class="fw-semibold text-dark mb-0">Biodata Pokok Siswa</h6>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">NIK Siswa</span>
                                            <span class="data-value font-mono-meta fs-6">{{ $formData['nik'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">NISN Siswa</span>
                                            <span class="data-value font-mono-meta fs-6">{{ $formData['nisn'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nama Lengkap</span>
                                            <span class="data-value fw-semibold">{{ $formData['full_name'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="data-cell">
                                            <span class="data-label">Nama Depan</span>
                                            <span class="data-value">{{ $formData['first_name'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="data-cell">
                                            <span class="data-label">Nama Belakang</span>
                                            <span class="data-value">{{ $formData['last_name'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Section 2 -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                    <span class="badge-pastel badge-pastel-neutral">02</span>
                                    <h6 class="fw-semibold text-dark mb-0">Identitas Tambahan</h6>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nomor Kartu Keluarga (KK)</span>
                                            <span class="data-value font-mono-meta">{{ $formData['identity']['family_card_number'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Jenis Kelamin</span>
                                            <span class="data-value">{{ $formData['identity']['gender'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="data-cell">
                                            <span class="data-label">Agama</span>
                                            <span class="data-value">{{ $formData['identity']['religion'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="data-cell">
                                            <span class="data-label">Tempat Lahir</span>
                                            <span class="data-value">{{ $formData['identity']['place_of_birth'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="data-cell">
                                            <span class="data-label">Tanggal Lahir</span>
                                            <span class="data-value font-mono-meta">{{ $formData['identity']['date_of_birth'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="data-cell">
                                            <span class="data-label">Susunan Saudara</span>
                                            <span class="data-value">Ke-{{ $formData['identity']['birth_order'] ?? ($formData['birth_order'] ?? '-') }} dari {{ $formData['identity']['siblings_count'] ?? ($formData['siblings_count'] ?? '-') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Section 3 -->
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                    <span class="badge-pastel badge-pastel-neutral">03</span>
                                    <h6 class="fw-semibold text-dark mb-0">Alamat Tempat Tinggal</h6>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="data-cell">
                                            <span class="data-label">Alamat / Jalan</span>
                                            <span class="data-value">{{ $formData['address']['street_address'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">RT / RW</span>
                                            <span class="data-value font-mono-meta">{{ $formData['address']['rt'] ?? '-' }} / {{ $formData['address']['rw'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Kelurahan / Kecamatan</span>
                                            <span class="data-value">{{ $formData['address']['village'] ?? '-' }}, {{ $formData['address']['district'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="data-cell">
                                            <span class="data-label">Kode Pos & Tinggal</span>
                                            <span class="data-value">{{ $formData['address']['postal_code'] ?? '-' }} ({{ $formData['address']['residence_type'] ?? '-' }})</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Section 4 -->
                            <div>
                                <div class="d-flex align-items-center gap-2 pb-2 mb-3 border-bottom" style="border-color: var(--border-light) !important;">
                                    <span class="badge-pastel badge-pastel-neutral">04</span>
                                    <h6 class="fw-semibold text-dark mb-0">Kontak & Ringkasan Orang Tua</h6>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Kontak WhatsApp Siswa</span>
                                            <span class="data-value font-mono-meta">{{ $formData['contact']['whatsapp_number'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Email Calon Siswa</span>
                                            <span class="data-value">{{ $formData['contact']['email'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nama & Kontak Ayah</span>
                                            <span class="data-value">{{ $father['full_name'] ?? ($father['name'] ?? '-') }} ({{ $father['phone_number'] ?? ($father['whatsapp_number'] ?? ($father['phone'] ?? '-')) }})</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="data-cell">
                                            <span class="data-label">Nama & Kontak Ibu</span>
                                            <span class="data-value">{{ $mother['full_name'] ?? ($mother['name'] ?? '-') }} ({{ $mother['phone_number'] ?? ($mother['whatsapp_number'] ?? ($mother['phone'] ?? '-')) }})</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                @endif
            </div>
        </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteShowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content bento-card p-0 shadow-sm border" style="border-color: var(--border-light) !important;">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="border-color: var(--border-light) !important;">
                <div class="d-flex align-items-center gap-2 text-danger">
                    <i class="ph-bold ph-warning-octagon fs-5"></i>
                    <h6 class="fw-bold mb-0">Hapus Data Calon Siswa</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="p-3">
                <p class="text-dark small mb-2">
                    Apakah Anda yakin ingin menghapus data calon siswa ini secara permanen?
                </p>
                <div class="p-2 mb-2 rounded bg-light border">
                    <strong class="text-dark d-block" style="font-size: 0.88rem;">{{ $name }}</strong>
                    <span class="font-mono-meta text-secondary" style="font-size: 0.78rem;">NIK: {{ $account['nik'] ?? ($formData['nik'] ?? '-') }}</span>
                </div>
                <span class="text-danger small" style="font-size: 0.78rem;">
                    <i class="ph-bold ph-info me-1"></i> Seluruh data pendaftaran, akun akses calon siswa, dan berkas terkait akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                </span>
            </div>
            <div class="p-3 border-top d-flex justify-content-end gap-2" style="border-color: var(--border-light) !important; background-color: var(--surface-muted);">
                <button type="button" class="btn-minimal-secondary py-1 px-3" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('admin.ppdb.destroy', $registration['id']) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm py-1 px-3 d-inline-flex align-items-center gap-1">
                        <i class="ph-bold ph-trash"></i> Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
