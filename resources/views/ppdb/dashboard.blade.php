@extends('layouts.app')

@section('title', 'Dashboard Calon Siswa')

@section('content')
<div class="container py-4">
    <!-- Header Dashboard -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Pendaftaran
            </h3>
            <p class="text-muted mb-0">Selamat datang di portal pendaftaran peserta didik baru, <strong>{{ $user['full_name'] ?? 'Calon Siswa' }}</strong>.</p>
        </div>
        <div>
            <span class="badge bg-light text-dark border px-3 py-2 fs-6 rounded-pill">
                <i class="bi bi-calendar3 me-1 text-primary"></i> {{ date('d F Y') }}
            </span>
        </div>
    </div>

    @if(isset($error) && !empty($error))
        <div class="alert alert-danger shadow-sm border-0 rounded-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $error }}
        </div>
    @endif

    @php
        $paymentStatus = $registration['payment_status'] ?? 'unpaid';
        $registrationStatus = $registration['registration_status'] ?? 'pending';
        $paymentProof = $registration['payment_proof_path'] ?? null;
        $formData = $registration['form_data'] ?? null;
    @endphp

    <!-- Status Cards Row -->
    <div class="row g-4 mb-4">
        <!-- 1. Status Pembayaran Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase">Status Pembayaran</span>
                    @if($paymentStatus === 'paid')
                        <span class="badge bg-success badge-status"><i class="bi bi-check-circle-fill me-1"></i> LUNAS</span>
                    @elseif($paymentStatus === 'pending_verification')
                        <span class="badge bg-warning text-dark badge-status"><i class="bi bi-clock-history me-1"></i> VERIFIKASI</span>
                    @elseif($paymentStatus === 'rejected')
                        <span class="badge bg-danger badge-status"><i class="bi bi-x-circle-fill me-1"></i> DITOLAK</span>
                    @else
                        <span class="badge bg-danger bg-opacity-75 text-white badge-status"><i class="bi bi-exclamation-octagon-fill me-1"></i> BELUM BAYAR</span>
                    @endif
                </div>

                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 me-3 
                        {{ $paymentStatus === 'paid' ? 'bg-success bg-opacity-10 text-success' : ($paymentStatus === 'pending_verification' ? 'bg-warning bg-opacity-10 text-warning' : 'bg-danger bg-opacity-10 text-danger') }}">
                        <i class="bi bi-cash-stack fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            @if($paymentStatus === 'paid')
                                Terverifikasi
                            @elseif($paymentStatus === 'pending_verification')
                                Menunggu Verifikasi
                            @elseif($paymentStatus === 'rejected')
                                Pembayaran Ditolak
                            @else
                                Belum Dibayar
                            @endif
                        </h5>
                        <small class="text-muted">Biaya Administrasi: Rp 250.000</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Status Formulir Dapodik Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase">Formulir Dapodik</span>
                    @if($paymentStatus !== 'paid')
                        <span class="badge bg-secondary badge-status"><i class="bi bi-lock-fill me-1"></i> TERKUNCI</span>
                    @elseif(!empty($formData))
                        <span class="badge bg-success badge-status"><i class="bi bi-check-all me-1"></i> LENGKAP</span>
                    @else
                        <span class="badge bg-primary badge-status"><i class="bi bi-pencil-fill me-1"></i> BELUM DIISI</span>
                    @endif
                </div>

                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 me-3 
                        {{ $paymentStatus !== 'paid' ? 'bg-secondary bg-opacity-10 text-secondary' : (!empty($formData) ? 'bg-success bg-opacity-10 text-success' : 'bg-primary bg-opacity-10 text-primary') }}">
                        <i class="bi bi-file-earmark-text fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            @if($paymentStatus !== 'paid')
                                Terkunci
                            @elseif(!empty($formData))
                                Sudah Diisi
                            @else
                                Siap Diisi
                            @endif
                        </h5>
                        <small class="text-muted">
                            @if($paymentStatus !== 'paid')
                                Selesaikan pembayaran dahulu
                            @elseif(!empty($formData))
                                Data tersimpan di sistem
                            @else
                                Segera lengkapi data Anda
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Status Seleksi Card -->
        <div class="col-md-12 col-lg-4">
            <div class="card card-custom h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold text-uppercase">Status Penerimaan</span>
                    @if($registrationStatus === 'accepted')
                        <span class="badge bg-success badge-status"><i class="bi bi-patch-check-fill me-1"></i> DITERIMA</span>
                    @elseif($registrationStatus === 'rejected')
                        <span class="badge bg-danger badge-status"><i class="bi bi-x-octagon-fill me-1"></i> TIDAK LOLOS</span>
                    @else
                        <span class="badge bg-info text-dark badge-status"><i class="bi bi-hourglass-split me-1"></i> SELEKSI</span>
                    @endif
                </div>

                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 me-3 
                        {{ $registrationStatus === 'accepted' ? 'bg-success bg-opacity-10 text-success' : ($registrationStatus === 'rejected' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-info bg-opacity-10 text-info') }}">
                        <i class="bi bi-mortarboard fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            @if($registrationStatus === 'accepted')
                                Resmi Diterima
                            @elseif($registrationStatus === 'rejected')
                                Tidak Lolos Seleksi
                            @else
                                Dalam Peninjauan
                            @endif
                        </h5>
                        <small class="text-muted">
                            @if($registrationStatus === 'accepted')
                                Akun SIAKAD otomatis dibuat
                            @else
                                Keputusan panitia PPDB
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Locking System Alert & Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            @if($paymentStatus === 'unpaid' || $paymentStatus === 'rejected')
                <!-- Kasus 1 & 4: UNPAID / REJECTED - Alert Merah & Form Upload Aktif -->
                <div class="alert alert-danger border-0 shadow-sm p-4 rounded-4 mb-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill fs-2 text-danger me-3"></i>
                        <div>
                            <h5 class="alert-heading fw-bold">Perhatian: Biaya Pendaftaran Belum Terverifikasi</h5>
                            <p class="mb-2">
                                @if($paymentStatus === 'rejected')
                                    Bukti transfer yang Anda kirimkan sebelumnya <strong>ditolak</strong> oleh panitia verifikasi. Silakan unggah bukti transfer yang valid.
                                @else
                                    Untuk membuka akses ke pengisian formulir biodata pokok, alamat, dan data orang tua (Dapodik), Anda diwajibkan melakukan pembayaran biaya pendaftaran sebesar <strong>Rp 250.000,-</strong> dan mengunggah bukti transfer pada form di bawah.
                                @endif
                            </p>
                            <div class="small bg-white bg-opacity-75 p-2 rounded-2 border border-danger-subtle text-dark">
                                Rekening Tujuan: <strong>BANK BRI 0123-01-000456-53-8 (PPDB SEKOLAH UNGGULAN)</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($paymentStatus === 'pending_verification')
                <!-- Kasus 2: PENDING VERIFICATION - Alert Kuning -->
                <div class="alert alert-warning border-0 shadow-sm p-4 rounded-4 mb-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-clock-history fs-2 text-warning me-3"></i>
                        <div>
                            <h5 class="alert-heading fw-bold">Bukti Pembayaran Sedang Diverifikasi Panitia</h5>
                            <p class="mb-0">
                                Bukti transfer Anda telah berhasil dikirimkan ke sistem kami dan saat ini sedang ditinjau oleh staf administrasi sekolah. Proses ini membutuhkan waktu paling lambat 1x24 jam kerja. Setelah status berubah menjadi <strong>LUNAS (PAID)</strong>, seluruh menu formulir pendaftaran akan terbuka otomatis.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($paymentStatus === 'paid')
                <!-- Kasus 3: PAID - Alert Hijau & Buka Akses Formulir -->
                <div class="alert alert-success border-0 shadow-sm p-4 rounded-4 mb-4">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-check-circle-fill fs-2 text-success me-3"></i>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading fw-bold">Pembayaran Anda Telah Terverifikasi (Lunas)!</h5>
                            <p class="mb-3">
                                Seluruh hak akses formulir pendaftaran telah terbuka. Silakan melengkapi seluruh isian data pokok calon siswa, identitas tambahan, alamat, kontak, dan data orang tua sesuai standar Dapodik Kemendikbudristek.
                            </p>
                            <a href="{{ route('ppdb.form') }}" class="btn btn-teal fw-bold px-4 py-2">
                                <i class="bi bi-file-earmark-person-fill me-2"></i> 
                                {{ !empty($formData) ? 'Perbarui / Lihat Formulir Pendaftaran' : 'Buka & Isi Formulir Pendaftaran Sekarang' }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Content Section: Profil & Upload Pembayaran -->
    <div class="row g-4">
        <!-- Kolom Kiri: Profil Akun & Data Pendaftar -->
        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-custom-header d-flex align-items-center">
                    <i class="bi bi-person-badge-fill text-primary fs-5 me-2"></i>
                    <span>Informasi Akun Calon Siswa</span>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <td class="text-muted" style="width: 40%;">ID Pendaftaran</td>
                                    <td class="fw-bold text-dark font-monospace small">{{ $registration['id'] ?? '-' }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted">NIK Calon Siswa</td>
                                    <td class="fw-bold text-dark">{{ $user['nik'] ?? '-' }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted">Nama Lengkap</td>
                                    <td class="fw-bold text-dark">{{ $user['full_name'] ?? '-' }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted">Email Terdaftar</td>
                                    <td class="text-dark">{{ $user['email'] ?? '-' }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted">Tanggal Registrasi</td>
                                    <td class="text-dark">{{ isset($registration['created_at']) ? date('d F Y, H:i', strtotime($registration['created_at'])) . ' WIB' : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status Akun</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Aktif</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if(!empty($formData))
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-check2-all text-success me-1"></i> Data Formulir Tersimpan:</h6>
                            <p class="small text-muted mb-3">Formulir Dapodik Anda telah tersimpan dengan rincian:</p>
                            <div class="row g-2 small">
                                <div class="col-6"><strong>NISN:</strong> {{ $formData['nisn'] ?? '-' }}</div>
                                <div class="col-6"><strong>Jenis Kelamin:</strong> {{ $formData['identity']['gender'] ?? '-' }}</div>
                                <div class="col-6"><strong>Tempat Lahir:</strong> {{ $formData['identity']['place_of_birth'] ?? '-' }}</div>
                                <div class="col-6"><strong>Tanggal Lahir:</strong> {{ $formData['identity']['date_of_birth'] ?? '-' }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Upload Pembayaran (Unlocked saat unpaid/rejected) -->
        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-custom-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-upload text-primary fs-5 me-2"></i>
                        <span>Unggah Bukti Pembayaran</span>
                    </div>
                    @if($paymentStatus === 'paid')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Terverifikasi</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($paymentStatus === 'paid')
                        <!-- Saat Paid: Tampilkan konfirmasi & pratinjau bukti bayar -->
                        <div class="text-center py-4">
                            <div class="text-success mb-3">
                                <i class="bi bi-patch-check-fill" style="font-size: 3.5rem;"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Bukti Pembayaran Terverifikasi</h5>
                            <p class="text-muted small mb-3">
                                Pembayaran Anda telah disetujui oleh panitia PPDB. Tidak diperlukan pengunggahan berkas bukti bayar ulang.
                            </p>
                            @if(!empty($paymentProof))
                                <a href="{{ $backendUrl . $paymentProof }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-file-earmark-image me-1"></i> Lihat Bukti Bayar yang Diunggah
                                </a>
                            @endif
                        </div>
                    @elseif($paymentStatus === 'pending_verification')
                        <!-- Saat Pending Verification: Tampilkan status menunggu & pratinjau berkas -->
                        <div class="text-center py-4">
                            <div class="text-warning mb-3">
                                <i class="bi bi-hourglass-top" style="font-size: 3.5rem;"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Sedang Dalam Pemeriksaan</h5>
                            <p class="text-muted small mb-3">
                                Berkas bukti transfer telah kami terima. Jika ada perbaikan bukti bayar, Anda dapat mengunggah kembali di bawah ini:
                            </p>
                            @if(!empty($paymentProof))
                                <div class="mb-3">
                                    <a href="{{ $backendUrl . $paymentProof }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i> Periksa Berkas Terakhir
                                    </a>
                                </div>
                            @endif

                            <!-- Form Upload Ulang jika diperlukan -->
                            <form action="{{ route('ppdb.upload-payment') }}" method="POST" enctype="multipart/form-data" class="text-start mt-4 pt-3 border-top">
                                @csrf
                                <label for="file" class="form-label small fw-semibold text-dark">Ganti / Unggah Ulang Bukti Bayar:</label>
                                <div class="input-group mb-2">
                                    <input type="file" class="form-control form-control-sm" id="file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                                    <button class="btn btn-navy btn-sm" type="submit">Unggah Ulang</button>
                                </div>
                                <div class="form-text small text-muted">Format: JPG, PNG, WEBP, PDF (Maks 5MB)</div>
                            </form>
                        </div>
                    @else
                        <!-- Saat Unpaid / Rejected: Form Aktif untuk Upload -->
                        <form action="{{ route('ppdb.upload-payment') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <p class="text-muted small mb-3">
                                Silakan unggah foto struk ATM, bukti transfer mobile banking, atau bukti setoran tunai bank (format JPG, PNG, WEBP, atau PDF, maks. 5MB).
                            </p>

                            <div class="mb-4">
                                <label for="file" class="form-label fw-semibold text-dark">
                                    Pilih Berkas Bukti Transfer <span class="text-danger">*</span>
                                </label>
                                <input type="file" 
                                       class="form-control @error('file') is-invalid @enderror" 
                                       id="file" 
                                       name="file" 
                                       accept=".jpg,.jpeg,.png,.webp,.pdf" 
                                       required>
                                @error('file')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text small text-muted mt-2">
                                    <i class="bi bi-info-circle me-1"></i> Pastikan foto bukti transfer terlihat jelas mencakup tanggal, nominal (Rp 250.000), dan nomor referensi transaksi.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-teal w-100 py-2 fw-semibold">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Kirim Bukti Pembayaran
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
