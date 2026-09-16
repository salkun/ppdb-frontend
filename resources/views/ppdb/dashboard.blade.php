@extends('layouts.app')

@section('title', 'Dashboard Calon Siswa')

@section('content')
<div class="container-xl py-3">
    <!-- Header Dashboard -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3" style="border-color: var(--border-light) !important;">
        <div>
            <div class="d-inline-flex align-items-center gap-2 mb-1">
                <span class="badge-pastel badge-pastel-neutral">PORTAL SISWA</span>
                <span class="font-mono-meta small text-secondary">TAHUN AJARAN {{ date('Y') }}/{{ date('Y') + 1 }}</span>
            </div>
            <h2 class="font-serif-heading fs-2 text-dark mb-0">Dashboard Pendaftaran</h2>
            <p class="text-secondary small mb-0 mt-1">
                Selamat datang, <strong class="text-dark">{{ $user['full_name'] ?? 'Calon Siswa' }}</strong>. Pantau status berkas dan pembayaran Anda.
            </p>
        </div>
        <div>
            <span class="font-mono-meta small text-secondary p-2 bg-white rounded-1 border d-inline-flex align-items-center gap-1" style="border-color: var(--border-light) !important;">
                <i class="ph-bold ph-calendar-blank"></i> {{ date('d F Y') }}
            </span>
        </div>
    </div>

    @if(isset($error) && !empty($error))
        <div class="alert-document alert-danger mb-4 d-flex align-items-center">
            <i class="ph-bold ph-warning-circle fs-5 me-2 flex-shrink-0"></i>
            <div>{{ $error }}</div>
        </div>
    @endif

    @php
        $paymentStatus = $registration['payment_status'] ?? 'unpaid';
        $registrationStatus = $registration['registration_status'] ?? 'pending';
        $paymentProof = $registration['payment_proof_path'] ?? null;
        $formData = $registration['form_data'] ?? null;
    @endphp

    <!-- 3 Bento Status Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Status Pembayaran -->
        <div class="col-md-4">
            <div class="bento-card h-100 fade-in-entry d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="font-mono-meta text-secondary" style="font-size: 0.75rem;">PEMBAYARAN</span>
                        @if($paymentStatus === 'paid')
                            <span class="badge-pastel badge-pastel-green">LUNAS</span>
                        @elseif($paymentStatus === 'pending_verification')
                            <span class="badge-pastel badge-pastel-yellow">VERIFIKASI</span>
                        @elseif($paymentStatus === 'rejected')
                            <span class="badge-pastel badge-pastel-red">DITOLAK</span>
                        @else
                            <span class="badge-pastel badge-pastel-red">BELUM BAYAR</span>
                        @endif
                    </div>
                    <h5 class="fw-semibold text-dark mb-1">
                        @if($paymentStatus === 'paid')
                            Terverifikasi Lunas
                        @elseif($paymentStatus === 'pending_verification')
                            Menunggu Verifikasi
                        @elseif($paymentStatus === 'rejected')
                            Pembayaran Ditolak
                        @else
                            Menunggu Pembayaran
                        @endif
                    </h5>
                    <p class="text-secondary small mb-0">Biaya Administrasi: Rp 250.000</p>
                </div>
                <div class="pt-3 mt-3 border-top d-flex align-items-center justify-content-between small text-secondary" style="border-color: var(--border-light) !important;">
                    <span>Tujuan Transfer</span>
                    <span class="font-mono-meta fw-medium text-dark">BANK BRI</span>
                </div>
            </div>
        </div>

        <!-- 2. Status Formulir Pendaftaran -->
        <div class="col-md-4">
            <div class="bento-card h-100 fade-in-entry d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="font-mono-meta text-secondary" style="font-size: 0.75rem;">FORMULIR PENDAFTARAN</span>
                        @if($paymentStatus !== 'paid')
                            <span class="badge-pastel badge-pastel-neutral">TERKUNCI</span>
                        @elseif(!empty($formData))
                            <span class="badge-pastel badge-pastel-green">LENGKAP</span>
                        @else
                            <span class="badge-pastel badge-pastel-blue">SIAP DIISI</span>
                        @endif
                    </div>
                    <h5 class="fw-semibold text-dark mb-1">
                        @if($paymentStatus !== 'paid')
                            Formulir Terkunci
                        @elseif(!empty($formData))
                            Biodata Tersimpan
                        @else
                            Siap Dilengkapi
                        @endif
                    </h5>
                    <p class="text-secondary small mb-0">
                        @if($paymentStatus !== 'paid')
                            Terbuka otomatis setelah pembayaran lunas
                        @elseif(!empty($formData))
                            Seluruh data tersimpan di sistem
                        @else
                            Silakan mulai melengkapi biodata Anda
                        @endif
                    </p>
                </div>
                <div class="pt-3 mt-3 border-top d-flex align-items-center justify-content-between small text-secondary" style="border-color: var(--border-light) !important;">
                    <span>Standar Data</span>
                    <span class="font-mono-meta fw-medium text-dark">Data Pokok Siswa</span>
                </div>
            </div>
        </div>

        <!-- 3. Status Penerimaan -->
        <div class="col-md-4">
            <div class="bento-card h-100 fade-in-entry d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="font-mono-meta text-secondary" style="font-size: 0.75rem;">SELEKSI AKADEMIK</span>
                        @if($registrationStatus === 'accepted')
                            <span class="badge-pastel badge-pastel-green">DITERIMA</span>
                        @elseif($registrationStatus === 'rejected')
                            <span class="badge-pastel badge-pastel-red">TIDAK LOLOS</span>
                        @else
                            <span class="badge-pastel badge-pastel-blue">PROSES SELEKSI</span>
                        @endif
                    </div>
                    <h5 class="fw-semibold text-dark mb-1">
                        @if($registrationStatus === 'accepted')
                            Resmi Diterima
                        @elseif($registrationStatus === 'rejected')
                            Tidak Lolos Seleksi
                        @else
                            Dalam Peninjauan
                        @endif
                    </h5>
                    <p class="text-secondary small mb-0">
                        @if($registrationStatus === 'accepted')
                            Akun siswa resmi otomatis diterbitkan
                        @else
                            Keputusan resmi dewan panitia PPDB
                        @endif
                    </p>
                </div>
                <div class="pt-3 mt-3 border-top d-flex align-items-center justify-content-between small text-secondary" style="border-color: var(--border-light) !important;">
                    <span>Tahun Ajaran</span>
                    <span class="font-mono-meta fw-medium text-dark">{{ date('Y') }}/{{ date('Y') + 1 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Action Notice -->
    @if($paymentStatus === 'unpaid' || $paymentStatus === 'rejected')
        <div class="bento-card mb-4 border-1 p-4 fade-in-entry" style="border-color: rgba(159, 47, 45, 0.25) !important; background-color: #FFFDFD;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge-pastel badge-pastel-red">PERHATIAN</span>
                        <strong class="text-dark">Selesaikan Pembayaran Biaya Pendaftaran</strong>
                    </div>
                    <p class="text-secondary small mb-0" style="max-width: 680px;">
                        @if($paymentStatus === 'rejected')
                            Bukti transfer sebelumnya ditolak panitia karena tidak valid atau nominal tidak sesuai. Silakan kirimkan kembali bukti transfer yang sah.
                        @else
                            Untuk membuka pengisian formulir data pokok dan orang tua, silakan lakukan transfer sebesar <strong>Rp 250.000,-</strong> ke rekening sekolah dan unggah bukti transfer pada panel di samping.
                        @endif
                    </p>
                </div>
                <div class="text-md-end flex-shrink-0">
                    <span class="font-mono-meta small text-secondary d-block">REKENING RESMI SEKOLAH</span>
                    <kbd class="kbd-key mt-1 d-inline-block">BRI 0123-01-000456-53-8</kbd>
                </div>
            </div>
        </div>
    @elseif($paymentStatus === 'pending_verification')
        <div class="bento-card mb-4 border-1 p-4 fade-in-entry" style="border-color: rgba(149, 100, 0, 0.25) !important; background-color: #FFFEFA;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge-pastel badge-pastel-yellow">DALAM PENINJAUAN</span>
                        <strong class="text-dark">Bukti Pembayaran Sedang Diverifikasi</strong>
                    </div>
                    <p class="text-secondary small mb-0" style="max-width: 700px;">
                        Bukti transfer Anda telah diterima sistem dan saat ini sedang dalam proses verifikasi oleh panitia administrasi sekolah. Setelah berstatus lunas, pengisian formulir akan terbuka secara otomatis.
                    </p>
                </div>
                <div class="font-mono-meta small text-secondary flex-shrink-0">
                    ESTIMASI: MAKS. 1X24 JAM
                </div>
            </div>
        </div>
    @elseif($paymentStatus === 'paid')
        <div class="bento-card mb-4 border-1 p-4 fade-in-entry" style="border-color: rgba(52, 101, 56, 0.25) !important; background-color: #FAFCFA;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge-pastel badge-pastel-green">LUNAS</span>
                        <strong class="text-dark">Pembayaran Terverifikasi — Akses Formulir Terbuka</strong>
                    </div>
                    <p class="text-secondary small mb-0" style="max-width: 680px;">
                        Akses pengisian formulir pendaftaran telah dibuka. Pastikan Anda melengkapi seluruh data calon siswa dan orang tua dengan data yang valid.
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('ppdb.form') }}" class="btn-minimal-primary py-2 px-3 text-decoration-none">
                        <i class="ph-bold ph-pencil-simple me-1"></i>
                        {{ !empty($formData) ? 'Periksa / Ubah Formulir' : 'Isi Formulir Sekarang' }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Grid: Info Akun & Form Bukti Bayar -->
    <div class="row g-4">
        <!-- Kolom Kiri: Informasi Calon Siswa & Form Tersimpan -->
        <div class="col-lg-6">
            <div class="bento-card h-100 fade-in-entry">
                <div class="bento-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ph-bold ph-user-focus fs-5 text-dark"></i>
                        <h6 class="fw-semibold text-dark mb-0">Identitas Calon Siswa</h6>
                    </div>
                    <span class="badge-pastel badge-pastel-neutral">DATA AKUN</span>
                </div>

                <div class="space-y-3 small">
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--border-light) !important;">
                        <span class="text-secondary">NIK Siswa:</span>
                        <span class="font-mono-meta text-dark fw-medium">{{ $user['nik'] ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--border-light) !important;">
                        <span class="text-secondary">Nama Lengkap:</span>
                        <span class="text-dark fw-medium">{{ $user['full_name'] ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--border-light) !important;">
                        <span class="text-secondary">Email Terdaftar:</span>
                        <span class="text-dark fw-medium">{{ $user['email'] ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" style="border-color: var(--border-light) !important;">
                        <span class="text-secondary">Tanggal Registrasi:</span>
                        <span class="font-mono-meta text-dark">{{ isset($registration['created_at']) ? date('d F Y, H:i', strtotime($registration['created_at'])) . ' WIB' : '-' }}</span>
                    </div>
                </div>

                @if(!empty($formData))
                    <div class="mt-4 pt-3 border-top" style="border-color: var(--border-light) !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="font-mono-meta small text-secondary text-uppercase">RINGKASAN FORMULIR TERSIMPAN</span>
                            <span class="badge-pastel badge-pastel-green">LENGKAP</span>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-secondary d-block" style="font-size: 0.78rem;">NISN:</span>
                                <span class="font-mono-meta text-dark fw-medium">{{ $formData['nisn'] ?? '-' }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-secondary d-block" style="font-size: 0.78rem;">Jenis Kelamin:</span>
                                <span class="text-dark fw-medium">{{ $formData['identity']['gender'] ?? '-' }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-secondary d-block" style="font-size: 0.78rem;">Tempat Lahir:</span>
                                <span class="text-dark fw-medium">{{ $formData['identity']['place_of_birth'] ?? '-' }}</span>
                            </div>
                            <div class="col-6">
                                <span class="text-secondary d-block" style="font-size: 0.78rem;">Tanggal Lahir:</span>
                                <span class="font-mono-meta text-dark fw-medium">{{ $formData['identity']['date_of_birth'] ?? '-' }}</span>
                            </div>
                            @php
                                $bOrder = $formData['identity']['birth_order'] ?? ($formData['birth_order'] ?? null);
                                $sCount = $formData['identity']['siblings_count'] ?? ($formData['siblings_count'] ?? '-');
                            @endphp
                            @if(!is_null($bOrder))
                                <div class="col-12 mt-2 pt-2 border-top" style="border-color: var(--border-light) !important;">
                                    <span class="text-secondary d-block" style="font-size: 0.78rem;">Urutan Kelahiran:</span>
                                    <span class="text-dark fw-medium">Anak ke-{{ $bOrder }} dari {{ $sCount }} bersaudara</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Upload / Status Bukti Bayar -->
        <div class="col-lg-6">
            <div class="bento-card h-100 fade-in-entry">
                <div class="bento-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ph-bold ph-receipt fs-5 text-dark"></i>
                        <h6 class="fw-semibold text-dark mb-0">Bukti Pembayaran</h6>
                    </div>
                    @if($paymentStatus === 'paid')
                        <span class="badge-pastel badge-pastel-green">TERVERIFIKASI</span>
                    @endif
                </div>

                @if($paymentStatus === 'paid')
                    <div class="text-center py-4">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center p-3 rounded-circle" style="background: var(--pastel-green); color: var(--pastel-green-text); width: 56px; height: 56px;">
                            <i class="ph-bold ph-check fs-2"></i>
                        </div>
                        <h6 class="fw-semibold text-dark mb-1">Bukti Pembayaran Telah Divalidasi</h6>
                        <p class="text-secondary small mb-3" style="max-width: 360px; margin-inline: auto;">
                            Pembayaran Anda telah diverifikasi resmi oleh panitia PPDB. Silakan lanjutkan pengisian formulir pendaftaran.
                        </p>
                        @if(!empty($paymentProof))
                            <a href="{{ $backendUrl . $paymentProof }}" target="_blank" class="btn-minimal-secondary py-2 px-3 text-decoration-none" style="font-size: 0.82rem;">
                                <i class="ph-bold ph-file-arrow-up me-1"></i> Lihat Berkas Bukti Transfer
                            </a>
                        @endif
                    </div>
                @elseif($paymentStatus === 'pending_verification')
                    <div class="text-center py-3">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center p-3 rounded-circle" style="background: var(--pastel-yellow); color: var(--pastel-yellow-text); width: 52px; height: 52px;">
                            <i class="ph-bold ph-clock fs-3"></i>
                        </div>
                        <h6 class="fw-semibold text-dark mb-1">Berkas Sedang Ditinjau Panitia</h6>
                        <p class="text-secondary small mb-3">
                            Bukti transfer Anda telah tersimpan di sistem. Jika diperlukan perbaikan berkas, Anda dapat mengunggah kembali di bawah ini:
                        </p>
                        @if(!empty($paymentProof))
                            <div class="mb-3">
                                <a href="{{ $backendUrl . $paymentProof }}" target="_blank" class="btn-minimal-secondary py-1 px-3 d-inline-flex text-decoration-none" style="font-size: 0.8rem;">
                                    <i class="ph-bold ph-eye me-1"></i> Buka Berkas Terakhir
                                </a>
                            </div>
                        @endif

                        <form action="{{ route('ppdb.upload-payment') }}" method="POST" enctype="multipart/form-data" class="text-start mt-4 pt-3 border-top" style="border-color: var(--border-light) !important;">
                            @csrf
                            <label for="file" class="form-label">Unggah Ulang Berkas Pengganti:</label>
                            <input type="file" class="form-control mb-2" id="file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="text-secondary small" style="font-size: 0.76rem;">JPG, PNG, WEBP, PDF (Maks. 5MB)</span>
                                <button class="btn-minimal-primary py-1 px-3" type="submit" style="font-size: 0.82rem;">Kirim Ulang</button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Form Upload Aktif Saat Unpaid / Rejected -->
                    <form action="{{ route('ppdb.upload-payment') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <p class="text-secondary small mb-3">
                            Unggah foto struk ATM, bukti transfer mobile banking, atau bukti setoran bank (format JPG, PNG, WEBP, atau PDF, maks. 5MB).
                        </p>

                        <div class="mb-4">
                            <label for="file" class="form-label">
                                Pilih Berkas Bukti Transfer <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('file') is-invalid @enderror" 
                                   id="file" 
                                   name="file" 
                                   accept=".jpg,.jpeg,.png,.webp,.pdf" 
                                   required>
                            @error('file')
                                <div class="text-danger small mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-2">
                                Pastikan tanggal, nominal (Rp 250.000), dan nomor referensi transaksi terlihat dengan jelas.
                            </div>
                        </div>

                        <button type="submit" class="btn-minimal-primary w-100 py-2">
                            <i class="ph-bold ph-upload-simple me-1"></i> Unggah Bukti Pembayaran
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
