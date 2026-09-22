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

<!-- Navigation Bar -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="{{ route('admin.ppdb.students') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
        <span>Kembali ke Data Siswa</span>
    </a>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.ppdb.edit', $registration['id']) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
            <span>Edit Siswa</span>
        </a>
        <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#deleteShowModal">
            <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
            <span>Hapus</span>
        </button>
        @if($rStatus !== 'accepted' && $pStatus === 'paid' && $hasForm)
            <form action="{{ route('admin.ppdb.accept', $registration['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerima calon siswa ini? Seluruh data akan dimigrasikan permanen ke sistem sekolah.');" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:18px;">verified</span>
                    <span>Terima Siswa</span>
                </button>
            </form>
        @endif
    </div>
</div>

<!-- ============================================== -->
<!-- 1. HERO IDENTITY CARD (BOOTSTRAP 5 CLEAN)      -->
<!-- ============================================== -->
<div class="card border-0 shadow-sm rounded-3 bg-white p-3 p-md-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-4 flex-shrink-0 shadow-sm" style="width: 60px; height: 60px; background: linear-gradient(135deg, #005ab4, #003770);">
                {{ $initials }}
            </div>
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="badge bg-light text-secondary border fw-semibold">CALON SISWA</span>

                    @if($pStatus === 'paid')
                        <span class="badge bg-success-subtle text-success fw-semibold">BIAYA LUNAS</span>
                    @elseif($pStatus === 'pending_verification')
                        <span class="badge bg-warning-subtle text-warning fw-semibold">BUTUH VERIFIKASI BAYAR</span>
                    @elseif($pStatus === 'rejected')
                        <span class="badge bg-danger-subtle text-danger fw-semibold">PEMBAYARAN DITOLAK</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary fw-semibold">BELUM BAYAR</span>
                    @endif

                    @if($rStatus === 'accepted')
                        <span class="badge bg-success-subtle text-success fw-semibold">RESMI DITERIMA</span>
                    @elseif($rStatus === 'rejected')
                        <span class="badge bg-danger-subtle text-danger fw-semibold">TIDAK LOLOS</span>
                    @else
                        <span class="badge bg-primary-subtle text-primary fw-semibold">MENUNGGU SELEKSI</span>
                    @endif

                    @php
                        $rawMajor = $formData['major'] ?? ($registration['student']['major'] ?? null);
                        $majorLabel = match(strtolower((string)$rawMajor)) {
                            'reguler' => 'JURUSAN REGULER',
                            'bahasa' => 'JURUSAN BAHASA',
                            'tahfidz' => 'JURUSAN TAHFIDZ',
                            'ict' => 'JURUSAN ICT (IT)',
                            default => null
                        };
                    @endphp
                    @if($majorLabel)
                        <span class="badge bg-light text-primary border fw-semibold">
                            {{ $majorLabel }}
                        </span>
                    @endif
                </div>

                <h4 class="fw-bold text-dark mb-1">{{ $name }}</h4>

                <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                    <span>NIK: <strong class="text-dark">{{ $account['nik'] ?? ($formData['nik'] ?? '-') }}</strong></span>
                    <span>&bull;</span>
                    <span>Email: <strong class="text-dark">{{ $account['email'] ?? '-' }}</strong></span>
                    <span>&bull;</span>
                    <span>Daftar: {{ isset($registration['created_at']) ? date('d M Y, H:i', strtotime($registration['created_at'])) . ' WIB' : '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 2. MAIN 2-COLUMN SPLIT WORKSPACE               -->
<!-- ============================================== -->
<div class="row g-4">
    <!-- Left Column: Pembayaran & Kelulusan -->
    <div class="col-lg-4">
        <!-- Panel 1: Biaya & Pembayaran -->
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-size:20px;">payments</span>
                    Biaya & Pembayaran
                </h6>
                <span class="badge bg-primary-subtle text-primary fw-semibold">Rp 400.000</span>
            </div>
            <div class="card-body p-3 px-md-4">
                @php
                    $payMethod = $registration['payment_method'] ?? 'transfer';
                @endphp

                <!-- Info Metode Pembayaran -->
                <div class="mb-3 d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                    <span class="text-muted small fw-bold text-uppercase">Metode:</span>
                    @if($payMethod === 'cash')
                        <span class="badge py-1 px-2" style="background:#ccfbf1;color:#0f766e;border:1px solid #99f6e4;">
                            <span class="material-symbols-outlined align-middle me-1" style="font-size:13px;">payments</span>
                            Tunai (Cash di Loket)
                        </span>
                    @else
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2">
                            <span class="material-symbols-outlined align-middle me-1" style="font-size:13px;">account_balance</span>
                            Transfer Bank (TF)
                        </span>
                    @endif
                </div>

                <!-- Struk Transfer / Kuitansi Tunai Preview -->
                <div class="mb-3">
                    <label class="text-muted small fw-bold text-uppercase d-block mb-2">
                        Berkas {{ $payMethod === 'cash' ? 'Kuitansi Tunai (Cash)' : 'Bukti Transfer (TF)' }}
                    </label>
                    @if($hasProof)
                        <div class="p-2 border rounded-3 bg-light text-center">
                            @php
                                $ext = pathinfo($paymentProof, PATHINFO_EXTENSION);
                            @endphp
                            @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']))
                                <img src="{{ ppdb_proof_url($paymentProof, $backendUrl) }}" 
                                     alt="Bukti Pembayaran" 
                                     class="img-fluid rounded mb-2 border cursor-pointer"
                                     style="max-height: 180px; object-fit: contain; cursor: pointer;"
                                     data-bs-toggle="modal"
                                     data-bs-target="#proofModal">
                            @else
                                <div class="py-3 text-muted">
                                    <span class="material-symbols-outlined fs-1 text-secondary">description</span>
                                    <div class="small fw-semibold mt-1">Dokumen PDF Terlampir</div>
                                </div>
                            @endif
                            <div>
                                <a href="{{ ppdb_proof_url($paymentProof, $backendUrl) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 py-1" style="font-size: 12px;">
                                    Buka Ukuran Penuh &rarr;
                                </a>
                            </div>
                        </div>

                        <!-- Modal Zoom Struk -->
                        <div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow rounded-3">
                                    <div class="modal-header border-bottom py-3">
                                        <h6 class="modal-title fw-bold text-dark">
                                            Pratinjau Berkas {{ $payMethod === 'cash' ? 'Kuitansi Tunai (Cash)' : 'Bukti Transfer (TF)' }}
                                        </h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body p-3 text-center bg-light">
                                        <img src="{{ ppdb_proof_url($paymentProof, $backendUrl) }}" alt="Bukti Pembayaran" class="img-fluid rounded shadow-sm" style="max-height: 75vh;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-3 border rounded-3 text-center text-muted bg-light small">
                            <span class="material-symbols-outlined d-block mb-1 text-secondary" style="font-size: 28px;">receipt_long</span>
                            Pendaftar belum mengunggah berkas bukti pembayaran.
                        </div>
                    @endif
                </div>

                <!-- Form Verifikasi Pembayaran -->
                <form action="{{ route('admin.ppdb.verify-payment', $registration['id']) }}" method="POST" class="pt-3 border-top">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Metode Pembayaran</label>
                        <select class="form-select form-select-sm" name="payment_method">
                            <option value="transfer" {{ $payMethod === 'transfer' ? 'selected' : '' }}>Transfer Bank (TF)</option>
                            <option value="cash" {{ $payMethod === 'cash' ? 'selected' : '' }}>Tunai / Cash di Loket</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Status Verifikasi</label>
                        <select class="form-select form-select-sm" name="payment_status" required>
                            <option value="paid" {{ $pStatus === 'paid' ? 'selected' : '' }}>Setujui: Lunas (Tervalidasi)</option>
                            <option value="rejected" {{ $pStatus === 'rejected' ? 'selected' : '' }}>Tolak: Bukti Tidak Valid</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1">Nominal Terverifikasi (Rp)</label>
                        <input type="number" class="form-control form-control-sm" name="payment_amount" value="{{ (int)($registration['payment_amount'] ?: 400000) }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        Simpan Status Pembayaran
                    </button>
                </form>
            </div>
        </div>

        <!-- Panel: Kelengkapan Berkas Persyaratan Siswa -->
        @php
            $docInfo = ppdb_get_student_documents($registration, $backendUrl);
            $docItems = $docInfo['items'];
            $uploadedDocsCount = $docInfo['uploaded_count'];
        @endphp
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-size:20px;">folder_open</span>
                    Berkas Persyaratan
                </h6>
                <span class="badge {{ $uploadedDocsCount >= 4 ? 'bg-success-subtle text-success' : ($uploadedDocsCount > 0 ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary') }} fw-semibold">
                    {{ $uploadedDocsCount }}/4 Berkas
                </span>
            </div>
            <div class="card-body p-3 px-md-4">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="text-muted fw-semibold">Kelengkapan Berkas</span>
                        <span class="fw-bold {{ $uploadedDocsCount >= 4 ? 'text-success' : 'text-warning' }}">{{ round(($uploadedDocsCount / 4) * 100) }}%</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar {{ $uploadedDocsCount >= 4 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ ($uploadedDocsCount / 4) * 100 }}%"></div>
                    </div>
                </div>

                <div class="list-group list-group-flush mb-3">
                    @foreach(['kk', 'akta', 'nisn', 'foto'] as $dKey)
                        @php $dItem = $docItems[$dKey]; @endphp
                        <div class="list-group-item px-0 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <span class="material-symbols-outlined {{ $dItem['has_file'] ? 'text-success' : 'text-muted' }}" style="font-size: 18px;">
                                    {{ $dItem['has_file'] ? 'task_alt' : 'radio_button_unchecked' }}
                                </span>
                                <div>
                                    <div class="fw-semibold text-dark text-truncate" style="font-size: 12.5px;">{{ $dItem['label'] }}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        @if($dItem['has_file'])
                                            <span class="text-success fw-medium">{{ strtoupper($dItem['ext']) }} Terunggah</span>
                                        @else
                                            <span class="text-muted">Belum ada file</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($dItem['has_file'])
                                <a href="{{ $dItem['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                    <span class="material-symbols-outlined" style="font-size: 13px;">open_in_new</span>
                                    <span>Buka</span>
                                </a>
                            @else
                                <span class="badge bg-light text-muted border" style="font-size: 10px;">Kosong</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary w-100 fw-semibold d-inline-flex align-items-center justify-content-center gap-1" onclick="const b = document.getElementById('tab-dokumen-btn'); if(b){ new bootstrap.Tab(b).show(); b.scrollIntoView({behavior:'smooth', block:'center'}); }">
                    <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                    <span>Lihat di Tab Berkas Lampiran &rarr;</span>
                </button>
            </div>
        </div>

        <!-- Panel 3: Status Penerimaan -->
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-size:20px;">verified</span>
                    Status Penerimaan
                </h6>
                <span class="badge {{ $rStatus === 'accepted' ? 'bg-success-subtle text-success' : ($rStatus === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary') }} fw-semibold">
                    {{ strtoupper($rStatus) }}
                </span>
            </div>
            <div class="card-body p-3 px-md-4">
                @if($rStatus === 'accepted')
                    <div class="alert alert-success d-flex align-items-center gap-2 p-2 mb-3 rounded-3" style="font-size: 13px;">
                        <span class="material-symbols-outlined text-success">check_circle</span>
                        <div><strong>Siswa Resmi Diterima:</strong> Data telah tercatat ke dalam sistem sekolah.</div>
                    </div>
                @elseif($rStatus === 'rejected')
                    <div class="alert alert-danger d-flex align-items-center gap-2 p-2 mb-3 rounded-3" style="font-size: 13px;">
                        <span class="material-symbols-outlined text-danger">cancel</span>
                        <div><strong>Tidak Lolos Seleksi:</strong> Calon siswa tidak memenuhi kriteria penerimaan.</div>
                    </div>
                @else
                    <p class="text-muted small mb-3">
                        Menerima calon siswa akan otomatis menerbitkan akun siswa resmi dan mencatat seluruh berkas pendaftaran ke pangkalan data sekolah.
                    </p>
                @endif

                @if($rStatus !== 'accepted')
                    @if($pStatus !== 'paid')
                        <button type="button" class="btn btn-light text-muted btn-sm w-100 border" disabled>
                            Pembayaran Belum Lunas
                        </button>
                    @elseif(!$hasForm)
                        <button type="button" class="btn btn-light text-muted btn-sm w-100 border" disabled>
                            Formulir Belum Diisi Siswa
                        </button>
                    @else
                        <form action="{{ route('admin.ppdb.accept', $registration['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerima calon siswa ini? Data akan diterbitkan resmi ke pangkalan data sekolah.');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold py-2">
                                Terima & Terbitkan Data Siswa
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Dossier Inspector -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden mb-4">
            <!-- Tabs Navigation -->
            <div class="card-header bg-white border-bottom py-2 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 14px;">
                    <span class="material-symbols-outlined text-primary" style="font-size:20px;">folder_open</span>
                    Lembar Berkas Calon Siswa
                </div>

                <ul class="nav nav-pills gap-1" id="dossierTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $hasForm ? 'active' : '' }} py-1 px-3 fw-semibold" id="tab-biodata-btn" data-bs-toggle="pill" data-bs-target="#tab-biodata" type="button" role="tab" style="font-size: 12.5px;">
                            Biodata
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 fw-semibold" id="tab-alamat-btn" data-bs-toggle="pill" data-bs-target="#tab-alamat" type="button" role="tab" style="font-size: 12.5px;">
                            Domisili & Kontak
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 fw-semibold" id="tab-ortu-btn" data-bs-toggle="pill" data-bs-target="#tab-ortu" type="button" role="tab" style="font-size: 12.5px;">
                            Orang Tua / Wali
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ !$hasForm ? 'active' : '' }} py-1 px-3 fw-semibold d-inline-flex align-items-center gap-1" id="tab-dokumen-btn" data-bs-toggle="pill" data-bs-target="#tab-dokumen" type="button" role="tab" style="font-size: 12.5px;">
                            <span>Berkas Lampiran</span>
                            <span class="badge {{ $uploadedDocsCount >= 4 ? 'bg-success' : 'bg-primary' }} rounded-pill" style="font-size: 10px;">{{ $uploadedDocsCount }}/4</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 fw-semibold" id="tab-all-btn" data-bs-toggle="pill" data-bs-target="#tab-all" type="button" role="tab" style="font-size: 12.5px;">
                            Semua Data
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-3 p-md-4">
                <div class="tab-content" id="dossierTabContent">
                    <!-- ========================================== -->
                    <!-- TAB 1: BIODATA & IDENTITAS                 -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade {{ $hasForm ? 'show active' : '' }}" id="tab-biodata" role="tabpanel">
                        @if(!$hasForm)
                            <div class="text-center py-5 text-muted">
                                <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 40px;">feed</span>
                                <h6 class="fw-bold text-dark">Pendaftar Belum Mengisi Formulir Biodata</h6>
                                <p class="small text-muted mb-3" style="max-width: 480px; margin: 0 auto;">
                                    Calon siswa belum melengkapi isian biodata pokok, domisili tempat tinggal, dan data orang tua/wali. Namun Anda tetap dapat melihat berkas persyaratan pada tab <strong>Berkas Lampiran</strong>.
                                </p>
                                <button type="button" class="btn btn-sm btn-primary" onclick="new bootstrap.Tab(document.getElementById('tab-dokumen-btn')).show();">
                                    Buka Tab Berkas Lampiran Siswa &rarr;
                                </button>
                            </div>
                        @else
                            <!-- Section: Biodata Pokok -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 14px;">1. Biodata Pokok Calon Siswa</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nomor Induk Kependudukan (NIK)</div>
                                            <div class="fw-bold text-dark fs-6">{{ $formData['nik'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nomor Induk Siswa Nasional (NISN)</div>
                                            <div class="fw-bold text-dark fs-6">{{ $formData['nisn'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nama Lengkap Siswa</div>
                                            <div class="fw-bold text-dark fs-6">{{ $formData['full_name'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nama Depan</div>
                                            <div class="fw-semibold text-dark">{{ $formData['first_name'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nama Belakang</div>
                                            <div class="fw-semibold text-dark">{{ $formData['last_name'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Peminatan Jurusan & Sekolah Asal -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 14px;">2. Peminatan Jurusan &amp; Asal Sekolah</h6>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Pilihan Jurusan</div>
                                            <div class="fw-bold text-primary">{{ strtoupper($formData['major'] ?? ($registration['student']['major'] ?? '-')) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nama Asal Sekolah</div>
                                            <div class="fw-semibold text-dark">{{ $formData['school_origin'] ?? ($registration['student']['school_origin'] ?? '-') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Alamat Sekolah Asal</div>
                                            <div class="text-dark">{{ $formData['school_origin_address'] ?? ($registration['student']['school_origin_address'] ?? '-') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Identitas Tambahan -->
                            <div>
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 14px;">3. Identitas Tambahan &amp; Keluarga</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nomor Kartu Keluarga (KK)</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['family_card_number'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Jenis Kelamin</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['gender'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Agama</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['religion'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Tempat Lahir</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['place_of_birth'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Tanggal Lahir</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['date_of_birth'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Susunan Saudara Kandung</div>
                                            <div class="text-dark">
                                                Anak ke-<strong>{{ $formData['identity']['birth_order'] ?? ($formData['birth_order'] ?? '-') }}</strong> dari total <strong>{{ $formData['identity']['siblings_count'] ?? ($formData['siblings_count'] ?? '-') }}</strong> bersaudara
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 2: DOMISILI & KONTAK                   -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade" id="tab-alamat" role="tabpanel">
                        @if(!$hasForm)
                            <div class="text-center py-5 text-muted">
                                <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 40px;">location_on</span>
                                <h6 class="fw-bold text-dark">Data Domisili &amp; Kontak Belum Diisi</h6>
                                <p class="small text-muted mb-0">Calon siswa belum melengkapi isian alamat domisili dan kontak keluarga.</p>
                            </div>
                        @else
                            <!-- Section: Tempat Tinggal -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 14px;">1. Alamat Tempat Tinggal &amp; Domisili</h6>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Alamat Lengkap / Jalan</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['street_address'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">RT / RW</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['rt'] ?? '-' }} / {{ $formData['address']['rw'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Kelurahan / Desa</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['village'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Kecamatan</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['district'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Kode Pos</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['postal_code'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Jenis Tempat Tinggal</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['residence_type'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Moda Transportasi</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['transportation_mode'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Kontak Siswa -->
                            <div>
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 14px;">2. Kontak &amp; Komunikasi Siswa</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nomor WhatsApp Siswa</div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="fw-semibold text-dark">{{ $formData['contact']['whatsapp_number'] ?? '-' }}</span>
                                                @if(!empty($formData['contact']['whatsapp_number']))
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $formData['contact']['whatsapp_number']) }}" target="_blank" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size: 12px;">
                                                        Chat WA
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nomor Handphone (HP)</div>
                                            <div class="fw-semibold text-dark">{{ $formData['contact']['mobile_number'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nomor Telepon Rumah</div>
                                            <div class="fw-semibold text-dark">{{ $formData['contact']['phone_number'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Email Calon Siswa</div>
                                            <div class="fw-semibold text-dark">{{ $formData['contact']['email'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                        <!-- ========================================== -->
                        <!-- TAB 3: ORANG TUA / WALI                    -->
                        <!-- ========================================== -->
                        <div class="tab-pane fade" id="tab-ortu" role="tabpanel">
                            @if(!$hasForm)
                                <div class="text-center py-5 text-muted">
                                    <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 40px;">family_restroom</span>
                                    <h6 class="fw-bold text-dark">Data Orang Tua / Wali Belum Diisi</h6>
                                    <p class="small text-muted mb-0">Calon siswa belum melengkapi isian data orang tua atau wali.</p>
                                </div>
                            @else
                                <!-- Sub Nav Pills Ayah / Ibu / Wali -->
                                <ul class="nav nav-pills gap-1 mb-3 pb-2 border-bottom" id="parentSubTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active py-1 px-3 fw-semibold" id="subtab-ayah-btn" data-bs-toggle="pill" data-bs-target="#subtab-ayah" type="button" role="tab" style="font-size: 12.5px;">
                                            Ayah Kandung
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1 px-3 fw-semibold" id="subtab-ibu-btn" data-bs-toggle="pill" data-bs-target="#subtab-ibu" type="button" role="tab" style="font-size: 12.5px;">
                                            Ibu Kandung
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1 px-3 fw-semibold" id="subtab-wali-btn" data-bs-toggle="pill" data-bs-target="#subtab-wali" type="button" role="tab" style="font-size: 12.5px;">
                                            Wali (Opsional)
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="parentSubContent">
                                    <!-- Sub Pane Ayah -->
                                    <div class="tab-pane fade show active" id="subtab-ayah" role="tabpanel">
                                        @if(!empty($father))
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Nama Lengkap Ayah</div>
                                                        <div class="fw-bold text-dark">{{ $father['full_name'] ?? ($father['name'] ?? '-') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">NIK Ayah</div>
                                                        <div class="fw-semibold text-dark">{{ $father['nik'] ?? '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Tahun Lahir</div>
                                                        <div class="fw-semibold text-dark">{{ $father['birth_year'] ?? '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Pendidikan Terakhir</div>
                                                        <div class="fw-semibold text-dark">{{ $educationList[$father['education_code'] ?? ''] ?? ($father['education'] ?? ($father['education_code'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Pekerjaan Utama</div>
                                                        <div class="fw-semibold text-dark">{{ $occupationList[$father['occupation_code'] ?? ''] ?? ($father['occupation'] ?? ($father['occupation_code'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Penghasilan Bulanan</div>
                                                        <div class="fw-semibold text-dark">{{ $incomeList[$father['income_code'] ?? ''] ?? ($father['monthly_income'] ?? ($father['income_code'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Nomor HP / WhatsApp</div>
                                                        <div class="fw-semibold text-dark">{{ $father['phone_number'] ?? ($father['whatsapp_number'] ?? ($father['phone'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Email Ayah</div>
                                                        <div class="fw-semibold text-dark">{{ $father['email'] ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-4 text-center border rounded-3 bg-light text-muted small">
                                                Data ayah kandung belum tercatat.
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Sub Pane Ibu -->
                                    <div class="tab-pane fade" id="subtab-ibu" role="tabpanel">
                                        @if(!empty($mother))
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Nama Lengkap Ibu</div>
                                                        <div class="fw-bold text-dark">{{ $mother['full_name'] ?? ($mother['name'] ?? '-') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">NIK Ibu</div>
                                                        <div class="fw-semibold text-dark">{{ $mother['nik'] ?? '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Tahun Lahir</div>
                                                        <div class="fw-semibold text-dark">{{ $mother['birth_year'] ?? '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Pendidikan Terakhir</div>
                                                        <div class="fw-semibold text-dark">{{ $educationList[$mother['education_code'] ?? ''] ?? ($mother['education'] ?? ($mother['education_code'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Pekerjaan Utama</div>
                                                        <div class="fw-semibold text-dark">{{ $occupationList[$mother['occupation_code'] ?? ''] ?? ($mother['occupation'] ?? ($mother['occupation_code'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Penghasilan Bulanan</div>
                                                        <div class="fw-semibold text-dark">{{ $incomeList[$mother['income_code'] ?? ''] ?? ($mother['monthly_income'] ?? ($mother['income_code'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Nomor HP / WhatsApp</div>
                                                        <div class="fw-semibold text-dark">{{ $mother['phone_number'] ?? ($mother['whatsapp_number'] ?? ($mother['phone'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Email Ibu</div>
                                                        <div class="fw-semibold text-dark">{{ $mother['email'] ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-4 text-center border rounded-3 bg-light text-muted small">
                                                Data ibu kandung belum tercatat.
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Sub Pane Wali -->
                                    <div class="tab-pane fade" id="subtab-wali" role="tabpanel">
                                        @if(!empty($guardian))
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Nama Lengkap Wali</div>
                                                        <div class="fw-bold text-dark">{{ $guardian['full_name'] ?? ($guardian['name'] ?? '-') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">NIK Wali</div>
                                                        <div class="fw-semibold text-dark">{{ $guardian['nik'] ?? '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Tahun Lahir</div>
                                                        <div class="fw-semibold text-dark">{{ $guardian['birth_year'] ?? '-' }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Pendidikan Terakhir</div>
                                                        <div class="fw-semibold text-dark">{{ $educationList[$guardian['education_code'] ?? ''] ?? ($guardian['education'] ?? ($guardian['education_code'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Pekerjaan</div>
                                                        <div class="fw-semibold text-dark">{{ $occupationList[$guardian['occupation_code'] ?? ''] ?? ($guardian['occupation'] ?? ($guardian['occupation_code'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Penghasilan Bulanan</div>
                                                        <div class="fw-semibold text-dark">{{ $incomeList[$guardian['income_code'] ?? ''] ?? ($guardian['monthly_income'] ?? ($guardian['income_code'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Nomor HP / WhatsApp</div>
                                                        <div class="fw-semibold text-dark">{{ $guardian['phone_number'] ?? ($guardian['whatsapp_number'] ?? ($guardian['phone'] ?? '-')) }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3 bg-light rounded-3 border h-100">
                                                        <div class="text-muted small fw-bold text-uppercase mb-1">Email Wali</div>
                                                        <div class="fw-semibold text-dark">{{ $guardian['email'] ?? '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-4 text-center border rounded-3 bg-light text-muted small">
                                                Data wali tidak diisi (opsional).
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- ========================================== -->
                        <!-- TAB: BERKAS LAMPIRAN SISWA                 -->
                        <!-- ========================================== -->
                        <div class="tab-pane fade {{ !$hasForm ? 'show active' : '' }}" id="tab-dokumen" role="tabpanel">
                            <div class="mb-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom pb-2 mb-3">
                                    <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">
                                        Berkas Dokumen Persyaratan Calon Siswa
                                    </h6>
                                    <span class="badge {{ $uploadedDocsCount >= 4 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} fw-semibold px-2 py-1">
                                        {{ $uploadedDocsCount }} dari 4 Berkas Persyaratan Terunggah
                                    </span>
                                </div>

                                <div class="row g-3">
                                    @foreach(['kk', 'akta', 'nisn', 'foto', 'bukti_bayar'] as $dKey)
                                        @php $d = $docItems[$dKey]; @endphp
                                        <div class="col-md-6 {{ $dKey === 'bukti_bayar' ? 'col-12' : '' }}">
                                            <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                                            <span class="material-symbols-outlined text-primary" style="font-size: 18px;">
                                                                {{ $dKey === 'foto' ? 'account_box' : ($dKey === 'bukti_bayar' ? 'receipt_long' : 'description') }}
                                                            </span>
                                                            <span>{{ $d['label'] }}</span>
                                                        </div>
                                                        <span class="badge {{ $d['has_file'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                            {{ $d['has_file'] ? '✓ Tersedia' : 'Belum Ada' }}
                                                        </span>
                                                    </div>

                                                    @if($d['has_file'])
                                                        @if($d['is_image'])
                                                            <div class="text-center my-2 p-2 bg-white rounded border">
                                                                <a href="{{ $d['url'] }}" target="_blank" class="d-inline-block" data-bs-toggle="modal" data-bs-target="#previewModal_{{ $dKey }}">
                                                                    <img src="{{ $d['url'] }}" alt="{{ $d['label'] }}" style="max-height: 150px; max-width: 100%; object-fit: contain;" class="rounded">
                                                                </a>
                                                                <div class="mt-1">
                                                                    <button type="button" class="btn btn-link btn-sm text-primary text-decoration-none p-0 fw-semibold" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#previewModal_{{ $dKey }}">
                                                                        Perbesar Pratinjau &rarr;
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <!-- Modal Zoom Berkas -->
                                                            <div class="modal fade" id="previewModal_{{ $dKey }}" tabindex="-1" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                                    <div class="modal-content border-0 shadow rounded-3">
                                                                        <div class="modal-header border-bottom py-3">
                                                                            <h6 class="modal-title fw-bold text-dark">{{ $d['label'] }} — {{ $name }}</h6>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                                        </div>
                                                                        <div class="modal-body text-center bg-light p-3">
                                                                            <img src="{{ $d['url'] }}" alt="{{ $d['label'] }}" class="img-fluid rounded shadow-sm" style="max-height: 75vh;">
                                                                        </div>
                                                                        <div class="modal-footer border-top py-2">
                                                                            <a href="{{ $d['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka di Tab Baru</a>
                                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="py-4 text-center text-primary bg-white rounded border my-2">
                                                                <span class="material-symbols-outlined d-block text-danger" style="font-size: 38px;">picture_as_pdf</span>
                                                                <div class="small fw-semibold mt-1">Dokumen PDF Terlampir</div>
                                                                <div class="text-muted" style="font-size: 11px;">Format dokumen resmi (.pdf)</div>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="text-muted small py-4 text-center bg-white rounded border my-2">
                                                            <span class="material-symbols-outlined text-secondary d-block mb-1" style="font-size: 28px;">upload_file</span>
                                                            Pendaftar belum mengunggah dokumen {{ strtolower($d['label']) }}.
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($d['has_file'])
                                                    <div class="d-flex gap-2 mt-2">
                                                        <a href="{{ $d['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1" style="font-size: 12px;">
                                                            <span class="material-symbols-outlined" style="font-size: 15px;">open_in_new</span>
                                                            <span>Buka Ukuran Penuh</span>
                                                        </a>
                                                        <a href="{{ $d['url'] }}" download class="btn btn-sm btn-light border text-secondary px-2" title="Unduh Berkas">
                                                            <span class="material-symbols-outlined" style="font-size: 15px;">download</span>
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- TAB 4: SEMUA DATA LENGKAP                  -->
                        <!-- ========================================== -->
                        <div class="tab-pane fade" id="tab-all" role="tabpanel">
                            @if(!$hasForm)
                                <div class="text-center py-5 text-muted">
                                    <span class="material-symbols-outlined text-secondary mb-2" style="font-size: 40px;">feed</span>
                                    <h6 class="fw-bold text-dark">Data Formulir Belum Diisi</h6>
                                    <p class="small text-muted mb-0">Calon siswa belum melengkapi isian formulir pendaftaran.</p>
                                </div>
                            @else
                                <!-- All Section 1 -->
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 14px;">1. Biodata Pokok Siswa</h6>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">NIK Siswa</div>
                                            <div class="fw-bold text-dark fs-6">{{ $formData['nik'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">NISN Siswa</div>
                                            <div class="fw-bold text-dark fs-6">{{ $formData['nisn'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nama Lengkap</div>
                                            <div class="fw-bold text-dark">{{ $formData['full_name'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nama Depan</div>
                                            <div class="fw-semibold text-dark">{{ $formData['first_name'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nama Belakang</div>
                                            <div class="fw-semibold text-dark">{{ $formData['last_name'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Section 2 -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 14px;">2. Identitas Tambahan</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nomor Kartu Keluarga (KK)</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['family_card_number'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Jenis Kelamin</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['gender'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Agama</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['religion'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Tempat Lahir</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['place_of_birth'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Tanggal Lahir</div>
                                            <div class="fw-semibold text-dark">{{ $formData['identity']['date_of_birth'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Susunan Saudara</div>
                                            <div class="fw-semibold text-dark">Ke-{{ $formData['identity']['birth_order'] ?? ($formData['birth_order'] ?? '-') }} dari {{ $formData['identity']['siblings_count'] ?? ($formData['siblings_count'] ?? '-') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Section 3 -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 14px;">3. Alamat Tempat Tinggal</h6>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Alamat / Jalan</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['street_address'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">RT / RW</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['rt'] ?? '-' }} / {{ $formData['address']['rw'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Kelurahan / Kecamatan</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['village'] ?? '-' }}, {{ $formData['address']['district'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Kode Pos &amp; Tinggal</div>
                                            <div class="fw-semibold text-dark">{{ $formData['address']['postal_code'] ?? '-' }} ({{ $formData['address']['residence_type'] ?? '-' }})</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Section 4 -->
                            <div>
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3" style="font-size: 14px;">4. Kontak &amp; Ringkasan Orang Tua</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Kontak WhatsApp Siswa</div>
                                            <div class="fw-semibold text-dark">{{ $formData['contact']['whatsapp_number'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Email Calon Siswa</div>
                                            <div class="fw-semibold text-dark">{{ $formData['contact']['email'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nama &amp; Kontak Ayah</div>
                                            <div class="fw-semibold text-dark">{{ $father['full_name'] ?? ($father['name'] ?? '-') }} ({{ $father['phone_number'] ?? ($father['whatsapp_number'] ?? ($father['phone'] ?? '-')) }})</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 border h-100">
                                            <div class="text-muted small fw-bold text-uppercase mb-1">Nama &amp; Kontak Ibu</div>
                                            <div class="fw-semibold text-dark">{{ $mother['full_name'] ?? ($mother['name'] ?? '-') }} ({{ $mother['phone_number'] ?? ($mother['whatsapp_number'] ?? ($mother['phone'] ?? '-')) }})</div>
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
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteShowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">warning</span>
                    Hapus Data Calon Siswa
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <p class="text-dark small mb-2">
                    Apakah Anda yakin ingin menghapus data calon siswa ini secara permanen?
                </p>
                <div class="p-3 mb-2 rounded-3 bg-light border">
                    <div class="fw-bold text-dark">{{ $name }}</div>
                    <div class="text-muted small">NIK: {{ $account['nik'] ?? ($formData['nik'] ?? '-') }}</div>
                </div>
                <span class="text-danger small">
                    Seluruh data pendaftaran, akun akses calon siswa, dan berkas terkait akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                </span>
            </div>
            <div class="modal-footer border-top bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('admin.ppdb.destroy', $registration['id']) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold">Hapus Permanen</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'dokumen' || window.location.hash === '#tab-dokumen') {
            const tabBtn = document.getElementById('tab-dokumen-btn');
            if (tabBtn) {
                const tabInstance = new bootstrap.Tab(tabBtn);
                tabInstance.show();
                tabBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>
@endpush
