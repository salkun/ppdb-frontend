@extends('admin.layout')

@section('title', 'Edit Data Pendaftar — ' . ($account['full_name'] ?? 'Calon Siswa'))
@section('header_title', 'Edit Data Calon Peserta Didik')

@section('content')
@php
    $pStatus = old('payment_status', $registration['payment_status'] ?? 'unpaid');
    $rStatus = old('registration_status', $registration['registration_status'] ?? 'pending');
    $pAmount = old('payment_amount', $registration['payment_amount'] ?? 250000);
    $major = old('major', $formData['major'] ?? '');
    $gender = old('gender', $formData['identity']['gender'] ?? ($formData['gender'] ?? ''));
    $dob = old('date_of_birth', $formData['identity']['date_of_birth'] ?? ($formData['date_of_birth'] ?? ''));
    $pob = old('place_of_birth', $formData['identity']['place_of_birth'] ?? ($formData['place_of_birth'] ?? ''));
    $phone = old('whatsapp_number', $formData['contact']['whatsapp_number'] ?? ($formData['whatsapp_number'] ?? ($formData['contact']['mobile_number'] ?? '')));
    $street = old('street_address', $formData['address']['street_address'] ?? ($formData['street_address'] ?? ''));
@endphp

<div class="mb-3 d-flex justify-content-between align-items-center">
    <a href="{{ route('admin.ppdb.show', $registration['id']) }}" class="btn-minimal-secondary d-inline-flex align-items-center gap-1">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Dossier
    </a>
    <div class="d-flex align-items-center gap-2">
        <span class="font-mono-meta text-secondary small">REG ID: {{ substr($registration['id'], 0, 8) }}...</span>
    </div>
</div>

<form action="{{ route('admin.ppdb.update', $registration['id']) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Kolom Kiri: Data Siswa & Akademik -->
        <div class="col-lg-8">
            <!-- 1. Identitas Pokok Siswa -->
            <div class="bento-card mb-4 p-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: var(--border-light) !important;">
                    <i class="ph-bold ph-user-circle fs-4 text-primary"></i>
                    <h5 class="fw-semibold text-dark mb-0">1. Identitas Pokok Calon Siswa</h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold text-dark">
                            Nama Lengkap Calon Siswa <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('full_name') is-invalid @enderror" 
                               name="full_name" 
                               value="{{ old('full_name', $account['full_name'] ?? ($formData['full_name'] ?? '')) }}" 
                               required>
                        @error('full_name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">
                            NIK Calon Siswa (16 Digit) <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control font-mono-meta @error('nik') is-invalid @enderror" 
                               name="nik" 
                               value="{{ old('nik', $account['nik'] ?? ($formData['nik'] ?? '')) }}" 
                               maxlength="16" 
                               pattern="\d{16}"
                               required>
                        @error('nik')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">
                            Nomor Induk Siswa Nasional (NISN)
                        </label>
                        <input type="text" 
                               class="form-control font-mono-meta @error('nisn') is-invalid @enderror" 
                               name="nisn" 
                               value="{{ old('nisn', $formData['nisn'] ?? '') }}" 
                               placeholder="10 digit NISN">
                        @error('nisn')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">
                            Alamat Email Akun <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               name="email" 
                               value="{{ old('email', $account['email'] ?? ($formData['contact']['email'] ?? '')) }}" 
                               required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">
                            No. WhatsApp / HP Aktif
                        </label>
                        <input type="text" 
                               class="form-control font-mono-meta @error('whatsapp_number') is-invalid @enderror" 
                               name="whatsapp_number" 
                               value="{{ $phone }}" 
                               placeholder="08xxxxxxxxxx">
                        @error('whatsapp_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-dark">Jenis Kelamin</label>
                        <select class="form-select" name="gender">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki" {{ $gender === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ $gender === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-dark">Tempat Lahir</label>
                        <input type="text" class="form-control" name="place_of_birth" value="{{ $pob }}" placeholder="Kota Kelahiran">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-dark">Tanggal Lahir</label>
                        <input type="date" class="form-control font-mono-meta" name="date_of_birth" value="{{ $dob }}">
                    </div>
                </div>
            </div>

            <!-- 2. Peminatan & Asal Sekolah -->
            <div class="bento-card mb-4 p-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: var(--border-light) !important;">
                    <i class="ph-bold ph-graduation-cap fs-4 text-primary"></i>
                    <h5 class="fw-semibold text-dark mb-0">2. Peminatan Jurusan &amp; Asal Sekolah</h5>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">Jurusan / Track Pilihan</label>
                        <select class="form-select" name="major">
                            <option value="">-- Pilih Jurusan --</option>
                            <option value="reguler" {{ strtolower($major) === 'reguler' ? 'selected' : '' }}>Kelas Reguler</option>
                            <option value="bahasa" {{ strtolower($major) === 'bahasa' ? 'selected' : '' }}>Kelas Bahasa (Oxford TeachCast)</option>
                            <option value="tahfidz" {{ strtolower($major) === 'tahfidz' ? 'selected' : '' }}>Kelas Tahfizh &amp; Qur'ani</option>
                            <option value="ict" {{ strtolower($major) === 'ict' ? 'selected' : '' }}>Kelas ICT / Komputer</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-dark">Nama Asal Sekolah (SD/MI)</label>
                        <input type="text" 
                               class="form-control" 
                               name="school_origin" 
                               value="{{ old('school_origin', $formData['school_origin'] ?? '') }}" 
                               placeholder="Contoh: SDN 1 Ciseureuh Purwakarta">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-semibold text-dark">Alamat Asal Sekolah</label>
                        <input type="text" 
                               class="form-control" 
                               name="school_origin_address" 
                               value="{{ old('school_origin_address', $formData['school_origin_address'] ?? '') }}" 
                               placeholder="Alamat lengkap SD/MI asal">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-semibold text-dark">Alamat Tempat Tinggal / Domisili</label>
                        <textarea class="form-control" 
                                  name="street_address" 
                                  rows="2" 
                                  placeholder="Nama jalan, nomor rumah, RT/RW, dsb.">{{ $street }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Status Administrasi & Pembayaran -->
        <div class="col-lg-4">
            <!-- Card Kontrol Status -->
            <div class="bento-card mb-4 p-4" style="border-left: 4px solid var(--primary-accent) !important;">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom" style="border-color: var(--border-light) !important;">
                    <i class="ph-bold ph-sliders fs-4 text-primary"></i>
                    <h5 class="fw-semibold text-dark mb-0">Kontrol Status (Admin)</h5>
                </div>

                <!-- Status Pembayaran -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">
                        Status Pembayaran Biaya <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('payment_status') is-invalid @enderror" name="payment_status" required>
                        <option value="unpaid" {{ $pStatus === 'unpaid' ? 'selected' : '' }}>Belum Bayar (UNPAID)</option>
                        <option value="pending_verification" {{ $pStatus === 'pending_verification' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="paid" {{ $pStatus === 'paid' ? 'selected' : '' }}>Lunas / Terverifikasi (PAID)</option>
                        <option value="rejected" {{ $pStatus === 'rejected' ? 'selected' : '' }}>Ditolak (REJECTED)</option>
                    </select>
                    @error('payment_status')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nominal Pembayaran -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-dark">
                        Nominal Biaya Pendaftaran (Rp)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text font-mono-meta">Rp</span>
                        <input type="number" 
                               class="form-control font-mono-meta" 
                               name="payment_amount" 
                               value="{{ (int) $pAmount }}" 
                               step="1000" 
                               min="0">
                    </div>
                </div>

                <!-- Status Seleksi Pendaftaran -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-dark">
                        Status Seleksi Pendaftaran <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('registration_status') is-invalid @enderror" name="registration_status" required>
                        <option value="pending" {{ $rStatus === 'pending' ? 'selected' : '' }}>Dalam Proses Seleksi (PENDING)</option>
                        <option value="accepted" {{ $rStatus === 'accepted' ? 'selected' : '' }}>Diterima (ACCEPTED)</option>
                        <option value="rejected" {{ $rStatus === 'rejected' ? 'selected' : '' }}>Tidak Lolos (REJECTED)</option>
                    </select>
                    <div class="form-text" style="font-size: 0.75rem;">
                        Catatan: Mengubah ke 'accepted' secara manual di sini hanya merubah status staging, tidak otomatis men-trigger migrasi akun SIAKAD. Gunakan tombol resmi di Dossier untuk migrasi penuh.
                    </div>
                </div>

                <div class="pt-2 border-top" style="border-color: var(--border-light) !important;">
                    <button type="submit" class="btn-minimal-primary w-100 py-2 fs-6 mb-2">
                        <i class="ph-bold ph-floppy-disk me-1"></i> Simpan Perubahan Data
                    </button>
                    <a href="{{ route('admin.ppdb.show', $registration['id']) }}" class="btn-minimal-secondary w-100 py-2 text-center text-decoration-none">
                        Batal
                    </a>
                </div>
            </div>

            <!-- Danger Zone: Hapus Pendaftar -->
            <div class="bento-card p-4" style="border-color: rgba(220, 38, 38, 0.2) !important; background: #fffbfb;">
                <div class="d-flex align-items-center gap-2 mb-2 text-danger">
                    <i class="ph-bold ph-warning-circle fs-4"></i>
                    <h6 class="fw-bold mb-0">Zona Bahaya</h6>
                </div>
                <p class="text-secondary small mb-3" style="font-size: 0.8rem;">
                    Menghapus calon siswa akan menghapus akun pendaftaran dan staging data secara permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
                <button type="button" 
                        class="btn-minimal-secondary text-danger w-100 py-2 border-danger" 
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteModal">
                    <i class="ph-bold ph-trash me-1"></i> Hapus Data Pendaftar Ini
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bento-card p-0 shadow-sm border" style="border-color: var(--border-light) !important;">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom" style="border-color: var(--border-light) !important;">
                <div class="d-flex align-items-center gap-2 text-danger">
                    <i class="ph-bold ph-warning-octagon fs-4"></i>
                    <h6 class="fw-bold mb-0">Konfirmasi Hapus Pendaftar</h6>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="p-4">
                <p class="text-dark mb-2">
                    Apakah Anda yakin ingin menghapus data calon siswa:
                </p>
                <div class="p-3 mb-3 rounded" style="background: var(--surface-muted); border: 1px solid var(--border-light);">
                    <strong class="text-dark d-block fs-6">{{ $account['full_name'] ?? 'Calon Siswa' }}</strong>
                    <span class="font-mono-meta text-secondary small">NIK: {{ $account['nik'] ?? '-' }}</span> &bull; 
                    <span class="text-secondary small">{{ $account['email'] ?? '-' }}</span>
                </div>
                <p class="text-danger small mb-0 fw-semibold">
                    <i class="ph-bold ph-warning me-1"></i> Peringatan: Seluruh data formulir, berkas bukti transfer, dan akun pendaftaran akan dihapus permanen.
                </p>
            </div>
            <div class="p-3 border-top d-flex justify-content-end gap-2" style="border-color: var(--border-light) !important; background-color: var(--surface-muted);">
                <button type="button" class="btn-minimal-secondary" data-bs-dismiss="modal">Batalkan</button>
                <form action="{{ route('admin.ppdb.destroy', $registration['id']) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-1">
                        <i class="ph-bold ph-trash"></i> Ya, Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
