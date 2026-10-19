@extends('admin.layout')

@section('title', 'Data Berkas SPMB')
@section('header_title', 'Monitoring Kelengkapan Berkas Calon Siswa')

@section('content')
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">TOTAL SISWA</span>
                <span class="material-symbols-outlined text-primary" style="font-size: 20px;">folder_open</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['total'] ?? 0 }}</h3>
            <span class="text-muted small">Pendaftar dalam pemantauan</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">BERKAS LENGKAP</span>
                <span class="material-symbols-outlined text-success" style="font-size: 20px;">task_alt</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['complete'] ?? 0 }}</h3>
            <span class="text-muted small">Semua dokumen terpenuhi</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">BELUM LENGKAP</span>
                <span class="material-symbols-outlined text-warning" style="font-size: 20px;">pending</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['incomplete'] ?? 0 }}</h3>
            <span class="text-muted small">Perlu melengkapi dokumen</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-bold text-uppercase" style="font-size: 11px;">BUKTI BAYAR</span>
                <span class="material-symbols-outlined text-info" style="font-size: 20px;">receipt_long</span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $stats['has_proof'] ?? 0 }}</h3>
            <span class="text-muted small">Bukti transfer terunggah</span>
        </div>
    </div>
</div>

<!-- Filter Toolbar Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white p-3 p-md-4">
    <form method="GET" action="{{ route('admin.ppdb.documents') }}" class="row g-2 align-items-center">
        <input type="hidden" name="per_page" value="{{ $filters['perPage'] ?? 20 }}">
        <!-- Search -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <span class="material-symbols-outlined" style="font-size:18px;">search</span>
                </span>
                <input type="text" 
                       class="form-control border-start-0 ps-0" 
                       name="search" 
                       value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Cari Nama Calon Siswa atau NIK...">
            </div>
        </div>

        <!-- Filter Status Dokumen -->
        <div class="col-6 col-md-3 col-lg-3">
            <select class="form-select" name="doc_status">
                <option value="">-- Semua Status Dokumen --</option>
                <option value="complete" {{ ($filters['docStatus'] ?? '') === 'complete' ? 'selected' : '' }}>Berkas Lengkap</option>
                <option value="incomplete" {{ ($filters['docStatus'] ?? '') === 'incomplete' ? 'selected' : '' }}>Belum Lengkap</option>
                <option value="has_payment" {{ ($filters['docStatus'] ?? '') === 'has_payment' ? 'selected' : '' }}>Ada Bukti Bayar</option>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="col-6 col-md-3 col-lg-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1 fw-semibold">
                <span class="material-symbols-outlined" style="font-size:18px;">filter_alt</span>
                <span>Filter</span>
            </button>
            @if(!empty($filters['search']) || !empty($filters['docStatus']))
                <a href="{{ route('admin.ppdb.documents') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-2" title="Reset Filter">
                    <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill" style="font-size: 12px;">
                {{ $documents->total() }} Data Berkas Siswa
            </span>
            <span class="text-muted small d-none d-sm-inline">Matriks kelengkapan dokumen administratif calon siswa</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead class="table-light">
                <tr>
                    <th style="width: 48px;" class="ps-3 ps-md-4 text-center">No</th>
                    <th>Nama Calon Siswa</th>
                    <th class="text-center">Bukti Bayar</th>
                    <th class="text-center">Kartu Keluarga</th>
                    <th class="text-center">Akta Kelahiran</th>
                    <th class="text-center">NISN / Ijazah</th>
                    <th class="text-center">Pas Foto 3x4</th>
                    <th class="text-center">Status Berkas</th>
                    <th style="text-align: right; width: 100px;" class="pe-3 pe-md-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $index => $doc)
                    @php
                        $docData = $doc['student_docs'] ?? ppdb_get_student_documents($doc['raw_reg'] ?? $doc, $backendUrl);
                        $dItems = $docData['items'] ?? [];
                        $kk = $dItems['kk'] ?? null;
                        $akta = $dItems['akta'] ?? null;
                        $nisn = $dItems['nisn'] ?? null;
                        $foto = $dItems['foto'] ?? null;
                        $proof = $dItems['bukti_bayar'] ?? null;
                    @endphp
                    <tr>
                        <td class="ps-3 ps-md-4 text-center text-muted fw-semibold">
                            {{ ($documents->firstItem() ?? 1) + $index }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $doc['full_name'] }}</div>
                            <div class="text-muted small">NIK: {{ $doc['nik'] }}</div>
                        </td>
                        <!-- Bukti Bayar -->
                        <!-- Bukti Bayar -->
                        <td class="text-center">
                            @if(!empty($proof['has_file']))
                                <button type="button" class="btn btn-sm btn-light {{ !empty($proof['is_missing']) ? 'text-danger border-danger-subtle' : 'text-success border' }} p-1" data-bs-toggle="modal" data-bs-target="#proofModal{{ $doc['id'] }}" title="{{ !empty($proof['is_missing']) ? 'File Bukti Hilang di Server!' : 'Lihat Bukti Transfer' }}">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">{{ !empty($proof['is_missing']) ? 'warning' : 'receipt_long' }}</span>
                                </button>

                                <!-- Modal Preview Bukti Bayar -->
                                <div class="modal fade" id="proofModal{{ $doc['id'] }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content border-0 shadow rounded-3">
                                            <div class="modal-header border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark">Bukti Pembayaran — {{ $doc['full_name'] }}</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>
                                            <div class="modal-body text-center p-3">
                                                @if(!empty($proof['is_missing']))
                                                    <div class="alert alert-danger text-start py-2 px-3 small d-flex align-items-center gap-2 mb-3 rounded-3">
                                                        <span class="material-symbols-outlined text-danger">warning</span>
                                                        <div>File fisik bukti bayar tidak ditemukan di penyimpanan server.</div>
                                                    </div>
                                                @else
                                                    <img src="{{ $proof['url'] }}" alt="Bukti Transfer" style="max-height: 380px; max-width: 100%; object-fit: contain;" class="rounded border">
                                                @endif
                                            </div>
                                            <div class="modal-footer border-top py-2">
                                                @if(empty($proof['is_missing']))
                                                    <a href="{{ $proof['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka Ukuran Penuh</a>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Diunggah">cancel</span>
                            @endif
                        </td>
                        <!-- KK -->
                        <td class="text-center">
                            @if(!empty($kk['is_missing']))
                                <button type="button" class="btn btn-sm btn-light text-danger border border-danger-subtle p-1" data-bs-toggle="modal" data-bs-target="#docsModalDoc{{ $doc['id'] }}" title="File KK Hilang dari Server! Klik untuk hapus/reset">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">error</span>
                                </button>
                            @elseif(!empty($kk['has_file']))
                                <a href="{{ $kk['url'] }}" target="_blank" class="btn btn-sm btn-light text-success border p-1" title="Lihat Kartu Keluarga (KK)">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">check_circle</span>
                                </a>
                            @elseif($doc['has_kk'])
                                <span class="material-symbols-outlined text-warning" style="font-size:18px;" title="Terdata di formulir (berkas belum diunggah)">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Ada">cancel</span>
                            @endif
                        </td>
                        <!-- Akta -->
                        <td class="text-center">
                            @if(!empty($akta['is_missing']))
                                <button type="button" class="btn btn-sm btn-light text-danger border border-danger-subtle p-1" data-bs-toggle="modal" data-bs-target="#docsModalDoc{{ $doc['id'] }}" title="File Akta Hilang dari Server! Klik untuk hapus/reset">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">error</span>
                                </button>
                            @elseif(!empty($akta['has_file']))
                                <a href="{{ $akta['url'] }}" target="_blank" class="btn btn-sm btn-light text-success border p-1" title="Lihat Akta Kelahiran">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">check_circle</span>
                                </a>
                            @elseif($doc['has_akta'])
                                <span class="material-symbols-outlined text-warning" style="font-size:18px;" title="Terdata di formulir (berkas belum diunggah)">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Ada">cancel</span>
                            @endif
                        </td>
                        <!-- NISN -->
                        <td class="text-center">
                            @if(!empty($nisn['is_missing']))
                                <button type="button" class="btn btn-sm btn-light text-danger border border-danger-subtle p-1" data-bs-toggle="modal" data-bs-target="#docsModalDoc{{ $doc['id'] }}" title="File NISN Hilang dari Server! Klik untuk hapus/reset">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">error</span>
                                </button>
                            @elseif(!empty($nisn['has_file']))
                                <a href="{{ $nisn['url'] }}" target="_blank" class="btn btn-sm btn-light text-success border p-1" title="Lihat Berkas NISN">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">check_circle</span>
                                </a>
                            @elseif($doc['has_nisn'])
                                <span class="material-symbols-outlined text-warning" style="font-size:18px;" title="Terdata di formulir (berkas belum diunggah)">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Ada">cancel</span>
                            @endif
                        </td>
                        <!-- Foto -->
                        <td class="text-center">
                            @if(!empty($foto['is_missing']))
                                <button type="button" class="btn btn-sm btn-light text-danger border border-danger-subtle p-1" data-bs-toggle="modal" data-bs-target="#docsModalDoc{{ $doc['id'] }}" title="File Foto Hilang dari Server! Klik untuk hapus/reset">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">error</span>
                                </button>
                            @elseif(!empty($foto['has_file']))
                                <a href="{{ $foto['url'] }}" target="_blank" class="btn btn-sm btn-light text-success border p-1" title="Lihat Pas Foto 3x4">
                                    <span class="material-symbols-outlined align-middle" style="font-size:18px;">check_circle</span>
                                </a>
                            @elseif($doc['has_foto'])
                                <span class="material-symbols-outlined text-warning" style="font-size:18px;" title="Terdata di formulir (berkas belum diunggah)">check_circle</span>
                            @else
                                <span class="material-symbols-outlined text-muted" style="font-size:18px;" title="Belum Ada">cancel</span>
                            @endif
                        </td>
                        <!-- Status Kelengkapan -->
                        <td class="text-center">
                            @if($docData['is_complete'] ?? $doc['is_complete'])
                                <span class="badge bg-success-subtle text-success py-1 px-2">Lengkap ({{ $docData['uploaded_count'] ?? $doc['uploaded_count'] }}/4)</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning py-1 px-2">Sebagian ({{ $docData['uploaded_count'] ?? $doc['uploaded_count'] }}/4)</span>
                            @endif
                        </td>
                        <!-- Aksi -->
                        <td class="pe-3 pe-md-4 text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-primary py-1 px-2 fw-semibold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#docsModalDoc{{ $doc['id'] }}" title="Lihat Semua Berkas Calon Siswa">
                                <span class="material-symbols-outlined" style="font-size: 15px;">folder_open</span>
                                <span>Berkas</span>
                            </button>
                            <a href="{{ route('admin.ppdb.show', $doc['id']) }}?tab=dokumen" class="btn btn-sm btn-outline-secondary py-1 px-2 fw-semibold" style="font-size: 12px;" title="Buka Detail Dossier Siswa">
                                Dossier
                            </a>

                            <!-- Modal Pratinjau Seluruh Berkas Dokumen Siswa -->
                            <div class="modal fade" id="docsModalDoc{{ $doc['id'] }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg text-start">
                                    <div class="modal-content border-0 shadow rounded-3">
                                        <div class="modal-header border-bottom py-3">
                                            <div>
                                                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                                    <span class="material-symbols-outlined text-primary">folder_shared</span>
                                                    Berkas Dokumen Calon Siswa
                                                </h6>
                                                <div class="text-muted small mt-1">
                                                    <strong class="text-dark">{{ $doc['full_name'] }}</strong> &bull; NIK: {{ $doc['nik'] }}
                                                </div>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body p-3 p-md-4 bg-light">
                                            @php
                                                // Ekstraksi nomor WA dan buat template chat santun
                                                $rawReg = $doc['raw_reg'] ?? [];
                                                $rawForm = is_array($rawReg) ? ($rawReg['form_data'] ?? []) : ($rawReg->form_data ?? []);
                                                if (!is_array($rawForm)) {
                                                    $rawForm = json_decode((string)$rawForm, true) ?: [];
                                                }
                                                $studentWa = $rawForm['contact']['whatsapp_number'] ?? ($rawForm['contact']['mobile_number'] ?? ($rawReg['account']['phone'] ?? ''));
                                                $cleanWa = preg_replace('/[^0-9]/', '', (string)$studentWa);
                                                if (str_starts_with($cleanWa, '0')) {
                                                    $cleanWa = '62' . substr($cleanWa, 1);
                                                }

                                                $missingDocsNames = [];
                                                foreach(['kk', 'akta', 'nisn', 'foto'] as $chkKey) {
                                                    if (!empty($dItems[$chkKey]['is_missing']) || empty($dItems[$chkKey]['has_file'])) {
                                                        $missingDocsNames[] = '• ' . $dItems[$chkKey]['label'];
                                                    }
                                                }
                                                $missingDocsStr = !empty($missingDocsNames) ? implode("\n", $missingDocsNames) : "• Berkas Persyaratan PPDB";

                                                $politeWaText = "Assalamu'alaikum Wr. Wb. / Salam Sejahtera Bapak/Ibu,\n\n"
                                                    . "Semoga Ayah/Bunda dan ananda *" . ($doc['full_name'] ?? 'Calon Siswa') . "* senantiasa dalam keadaan sehat walafiat.\n\n"
                                                    . "Sehubungan dengan proses validasi dan peningkatan kualitas arsip digital dokumen PPDB, kami memohon bantuan dan kesediaan Ayah/Bunda untuk dapat melakukan unggah/pembaruan berkas berikut:\n"
                                                    . $missingDocsStr . "\n\n"
                                                    . "Melalui dashboard pendaftaran masing-masing:\n"
                                                    . route('login') . "\n\n"
                                                    . "(Catatan: Isian data biodata formulir dan bukti pembayaran Anda telah tersimpan dengan sangat baik di sistem kami).\n\n"
                                                    . "Apabila Ayah/Bunda membutuhkan bantuan atau mengalami kendala, panitia PPDB siap melayani. Mohon maaf sebesar-besarnya atas ketidaknyamanan ini dan terima kasih banyak atas kerja sama serta kesabaran Ayah/Bunda.\n\n"
                                                    . "Salam hormat,\n*Panitia PPDB*";
                                            @endphp

                                            <div class="row g-3">
                                                @foreach(['kk', 'akta', 'nisn', 'foto', 'bukti_bayar'] as $dKey)
                                                    @php $d = $dItems[$dKey] ?? null; @endphp
                                                    @if($d)
                                                        <div class="col-md-6 {{ $dKey === 'bukti_bayar' ? 'col-12' : '' }}">
                                                            <div class="p-3 bg-white rounded-3 border h-100 d-flex flex-column justify-content-between shadow-sm">
                                                                <div>
                                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                                        <span class="fw-bold text-dark small d-flex align-items-center gap-1">
                                                                            <span class="material-symbols-outlined text-primary" style="font-size: 16px;">
                                                                                {{ $dKey === 'foto' ? 'account_box' : ($dKey === 'bukti_bayar' ? 'receipt_long' : 'description') }}
                                                                            </span>
                                                                            {{ $d['label'] }}
                                                                        </span>
                                                                        <span class="badge {{ !empty($d['is_missing']) ? 'bg-danger-subtle text-danger border border-danger-subtle' : ($d['has_file'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary') }}" style="font-size: 10px;">
                                                                            @if(!empty($d['is_missing']))
                                                                                ⚠️ File Fisik Hilang / 404
                                                                            @elseif($d['has_file'])
                                                                                ✓ File Tersedia
                                                                            @else
                                                                                Belum Diunggah
                                                                            @endif
                                                                        </span>
                                                                    </div>

                                                                    @if(!empty($d['is_missing']))
                                                                        <div class="alert alert-danger py-2 px-2 small mb-2 d-flex align-items-center gap-2 rounded-2">
                                                                            <span class="material-symbols-outlined text-danger" style="font-size: 18px;">error</span>
                                                                            <div style="font-size: 11px; line-height: 1.35;">
                                                                                Tautan ada di database namun file fisiknya <strong>tidak ditemukan di server hosting</strong>. Silakan klik tombol <em>Hapus Berkas</em> agar siswa dapat mengunggah ulang.
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                                    @if($d['has_file'])
                                                                        @if(empty($d['is_missing']))
                                                                            @if($d['is_image'])
                                                                                <div class="text-center my-2 p-2 bg-light rounded border">
                                                                                    <a href="{{ $d['url'] }}" target="_blank">
                                                                                        <img src="{{ $d['url'] }}" alt="{{ $d['label'] }}" style="max-height: 140px; max-width: 100%; object-fit: contain;" class="rounded">
                                                                                    </a>
                                                                                </div>
                                                                            @else
                                                                                <div class="py-3 text-center text-primary bg-light rounded border my-2">
                                                                                    <span class="material-symbols-outlined text-danger d-block fs-3">picture_as_pdf</span>
                                                                                    <span class="small fw-semibold">Dokumen PDF Terlampir</span>
                                                                                </div>
                                                                            @endif
                                                                        @endif
                                                                    @else
                                                                        <div class="py-3 text-center text-muted small bg-light rounded border my-2">
                                                                            Belum diunggah calon siswa
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                @if($d['has_file'])
                                                                    <div class="mt-2 pt-2 border-top d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                                                        @if(empty($d['is_missing']))
                                                                            <a href="{{ $d['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1 py-1 d-inline-flex align-items-center justify-content-center gap-1" style="font-size: 11.5px;">
                                                                                <span class="material-symbols-outlined" style="font-size: 14px;">open_in_new</span>
                                                                                <span>Buka File</span>
                                                                            </a>
                                                                        @endif

                                                                        @if($dKey !== 'bukti_bayar')
                                                                            <form action="{{ route('admin.ppdb.documents.delete', ['id' => $doc['id'], 'documentType' => $dKey]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas {{ $d['label'] }} untuk {{ addslashes($doc['full_name']) }}? Tindakan ini akan mengosongkan berkas ini agar siswa dapat mengunggahnya kembali.')">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size: 11px;" title="Hapus berkas ini agar siswa bisa upload ulang">
                                                                                    <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
                                                                                    <span>Hapus Berkas</span>
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top bg-white py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                @if(!empty($cleanWa))
                                                    <a href="https://wa.me/{{ $cleanWa }}?text={{ rawurlencode($politeWaText) }}" target="_blank" class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center gap-1" title="Kirim Pesan Santun ke WhatsApp Orang Tua/Siswa">
                                                        <span class="material-symbols-outlined" style="font-size: 16px;">chat</span>
                                                        <span>Kirim WA Sopan</span>
                                                    </a>
                                                @endif

                                                @if(!empty($kk['has_file']) || !empty($akta['has_file']) || !empty($nisn['has_file']) || !empty($foto['has_file']))
                                                    <form action="{{ route('admin.ppdb.documents.delete', ['id' => $doc['id'], 'documentType' => 'all']) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS SEMUA 4 BERKAS PERSYARATAN (KK, Akta, NISN, Pas Foto) untuk {{ addslashes($doc['full_name']) }}?\n\nPerhatian:\n- Bukti transfer pembayaran dan data formulir tetap AMAN tersimpan.\n- Seluruh 4 berkas persyaratan akan dikosongkan agar siswa dapat mengunggah ulang.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold d-inline-flex align-items-center gap-1" title="Hapus ke-4 berkas persyaratan siswa ini sekaligus">
                                                            <span class="material-symbols-outlined" style="font-size: 16px;">delete_sweep</span>
                                                            <span>Hapus Semua 4 Berkas</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.ppdb.show', $doc['id']) }}?tab=dokumen" class="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">feed</span>
                                                    <span>Buka Dossier &rarr;</span>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 36px;">folder_off</span>
                            <div class="small">Tidak ada data berkas yang cocok dengan filter.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('admin.ppdb.partials.pagination', ['paginator' => $documents])
</div>
@endsection
