@extends('admin.layout')

@section('title', 'Pencadangan Cloud Google Drive (4 TB)')

@section('content')
<div class="container-fluid px-0">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.ppdb.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                    Dashboard
                </a>
                <span class="text-muted small">/</span>
                <span class="text-dark small fw-semibold">Backup Google Drive</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <span class="material-symbols-outlined text-primary fs-2">cloud_sync</span>
                Pencadangan Cloud Google Drive (4 TB)
            </h1>
            <p class="text-muted mb-0 small">
                Cadangkan seluruh database pendaftaran siswa dan dokumen fisik (KK, Akta, NISN, Foto, Bukti Bayar) langsung ke Google Drive 4 TB Anda secara otomatis dan aman.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if(!empty($folderId))
                <a href="https://drive.google.com/drive/folders/{{ $folderId }}" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center gap-1 fw-semibold shadow-sm">
                    <span class="material-symbols-outlined" style="font-size: 18px;">folder_shared</span>
                    <span>Buka Folder di Drive</span>
                    <span class="material-symbols-outlined" style="font-size: 14px;">open_in_new</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3 mb-4" role="alert">
            <span class="material-symbols-outlined fs-4 text-success">check_circle</span>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm rounded-3 mb-4" role="alert">
            <span class="material-symbols-outlined fs-4 text-danger">error</span>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Kolom Kiri: Status Koneksi & Tombol Backup --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary">cloud_done</span>
                        Status Koneksi Google Drive
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle {{ $isConnected ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} mb-3" style="width: 80px; height: 80px;">
                            <span class="material-symbols-outlined" style="font-size: 44px;">
                                {{ $isConnected ? 'cloud_done' : 'cloud_off' }}
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">
                            {{ $isConnected ? 'Google Drive 4 TB Terhubung' : 'Belum Terhubung' }}
                        </h5>
                        <p class="text-muted small mb-0">
                            {{ $isConnected ? 'Aplikasi telah berhasil terautentikasi dan siap melakukan pencadangan otomatis.' : 'Harap lakukan otorisasi akun Google Anda untuk mengaktifkan fitur pencadangan.' }}
                        </p>
                    </div>

                    @if($isConnected)
                        <div class="p-3 bg-light rounded-3 border mb-4">
                            <div class="d-flex justify-content-between align-items-center small mb-2">
                                <span class="text-muted">Target Folder:</span>
                                <span class="badge bg-primary-subtle text-primary">Kapasitas 4 TB</span>
                            </div>
                            <div class="fw-bold text-truncate text-dark small" title="{{ $folderId }}">
                                📁 ID: {{ $folderId }}
                            </div>
                        </div>

                        <form action="{{ route('admin.ppdb.backup.run') }}" method="POST" id="formRunBackup">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 shadow" id="btnSubmitBackup">
                                <span class="material-symbols-outlined" id="backupIcon">backup</span>
                                <span id="backupText">Mulai Pencadangan Sekarang</span>
                            </button>
                        </form>
                        <div class="text-muted text-center small mt-2" style="font-size: 11.5px;">
                            *Proses ini akan mengarsipkan seluruh database MySQL &amp; berkas upload ke Google Drive.
                        </div>
                    @else
                        {{-- Opsi Otorisasi --}}
                        <div class="border rounded-3 p-3 bg-light mb-3">
                            <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                                <span class="badge bg-primary rounded-circle">1</span>
                                <span>Otorisasi Akun Google</span>
                            </div>
                            <p class="text-muted small mb-3">
                                Klik tombol di bawah ini untuk membuka halaman izin Google akun 4 TB Anda:
                            </p>
                            <a href="{{ $authUrl }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                                <span class="material-symbols-outlined" style="font-size: 18px;">key</span>
                                <span>Buka Halaman Izin Google &rarr;</span>
                            </a>
                        </div>

                        <div class="border rounded-3 p-3 bg-light">
                            <div class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                                <span class="badge bg-primary rounded-circle">2</span>
                                <span>Masukkan Kode Otorisasi</span>
                            </div>
                            <p class="text-muted small mb-2">
                                Setelah Anda klik "Izinkan", browser akan diarahkan ke link localhost/127.0.0.1. <strong>Salin teks kode</strong> dari address bar (setelah tanda <code>code=</code>) lalu tempelkan di bawah ini:
                            </p>
                            <form action="{{ route('admin.ppdb.backup.save-code') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="code" class="form-control form-control-sm" placeholder="Contoh: 4/0AcvDMr..." required>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold d-inline-flex align-items-center justify-content-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">link</span>
                                    <span>Hubungkan Sekarang</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card Jadwal Otomatis Cron Job --}}
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                        Jadwal Otomatis di cPanel
                    </h6>
                </div>
                <div class="card-body p-3">
                    <p class="text-muted small mb-2">
                        Agar sistem mencadangkan otomatis setiap malam tanpa perlu menekan tombol manual, pasang baris perintah ini di menu <strong>Cron Jobs</strong> cPanel hosting:
                    </p>
                    <div class="p-2 bg-dark text-light rounded font-monospace small mb-2 text-break" style="font-size: 11.5px;">
                        php /home/username_cpanel/public_html/artisan ppdb:backup-gdrive &gt;&gt; /dev/null 2&gt;&amp;1
                    </div>
                    <div class="text-muted small" style="font-size: 11px;">
                        Setel waktu: <code>0 0 * * *</code> (Setiap tengah malam jam 00:00).
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Riwayat File Cadangan di Google Drive --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary">history</span>
                        Riwayat Arsip di Google Drive 4 TB
                    </h6>
                    <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                        {{ count($backups) }} File Tersimpan
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-2">Nama Berkas Cadangan</th>
                                    <th class="py-2">Ukuran</th>
                                    <th class="py-2">Waktu Pembuatan</th>
                                    <th class="pe-3 py-2 text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $b)
                                    @php
                                        $sizeMB = isset($b['size']) ? round($b['size'] / (1024 * 1024), 2) . ' MB' : '-';
                                        $created = isset($b['createdTime']) ? \Carbon\Carbon::parse($b['createdTime'])->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : '-';
                                    @endphp
                                    <tr>
                                        <td class="ps-3 py-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined text-danger" style="font-size: 24px;">folder_zip</span>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $b['name'] ?? 'Backup' }}</div>
                                                    <div class="text-muted" style="font-size: 11px;">Arsip ZIP Database &amp; Berkas Siswa</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-nowrap fw-semibold text-secondary">
                                            {{ $sizeMB }}
                                        </td>
                                        <td class="text-nowrap text-muted">
                                            {{ $created }} WIB
                                        </td>
                                        <td class="pe-3 text-end text-nowrap">
                                            @if(!empty($b['webViewLink']))
                                                <a href="{{ $b['webViewLink'] }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 11.5px;" title="Buka / Download dari Google Drive">
                                                    <span class="material-symbols-outlined" style="font-size: 14px;">cloud_download</span>
                                                    <span>Unduh</span>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 40px;">cloud_off</span>
                                            @if($isConnected)
                                                <div class="fw-semibold text-dark">Belum ada file cadangan di folder Google Drive</div>
                                                <div class="small text-muted mt-1">Klik tombol "Mulai Pencadangan Sekarang" di samping untuk membuat arsip cadangan perdana.</div>
                                            @else
                                                <div class="fw-semibold text-dark">Google Drive Belum Terhubung</div>
                                                <div class="small text-muted mt-1">Selesaikan otorisasi di sebelah kiri untuk melihat arsip cadangan.</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const formRunBackup = document.getElementById('formRunBackup');
    if (formRunBackup) {
        formRunBackup.addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmitBackup');
            const icon = document.getElementById('backupIcon');
            const text = document.getElementById('backupText');
            if (btn) {
                btn.disabled = true;
                if (icon) icon.textContent = 'hourglass_top';
                if (text) text.textContent = 'Sedang Mengarsipkan & Mengunggah...';
            }
        });
    }
</script>
@endsection
