@extends('layouts.app')

@section('title', 'Upload Berkas Persyaratan')

@section('content')
<div class="db-content">

    {{-- Page Header --}}
    <div class="db-page-header">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:0.5rem;">
            <a href="{{ route('dashboard') }}" style="display:inline-flex;align-items:center;gap:4px;font-size:13px;font-weight:600;color:var(--lp-primary);text-decoration:none;">
                <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Dashboard
            </a>
            <span style="color:var(--lp-outline);font-size:13px;">/</span>
            <span style="font-size:13px;font-weight:600;color:var(--lp-on-surface);">Upload Berkas</span>
        </div>
        <h1 class="db-page-title">Upload Berkas Persyaratan</h1>
        <p class="db-page-subtitle">Unggah scan atau foto jelas dokumen berikut dalam format JPG, PNG, atau PDF. Maksimal 5MB per file.</p>
    </div>

    {{-- Info Banner --}}
    <div style="background:rgba(214,227,255,0.4);border:1px solid rgba(0,90,180,0.15);border-radius:14px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:12px;font-size:13px;">
        <span class="material-symbols-outlined" style="font-size:22px;color:var(--lp-primary);flex-shrink:0;margin-top:2px;">info</span>
        <div>
            <div style="font-weight:700;color:var(--lp-primary);margin-bottom:4px;">Panduan Upload Berkas</div>
            <ul style="margin:0;padding-left:1.25rem;color:var(--lp-on-surface-variant);line-height:1.7;">
                <li>Pastikan scan/foto <strong>tidak blur</strong> dan semua teks terbaca jelas</li>
                <li>Format yang diterima: <strong>JPG, PNG, PDF</strong> — maksimal <strong>5MB</strong> per file</li>
                <li>Pas Foto ukuran <strong>3x4 cm</strong> dengan latar belakang <strong>merah</strong></li>
                <li>Fitur ini masih dalam <strong>tahap pengembangan</strong> — data tersimpan lokal sementara</li>
            </ul>
        </div>
    </div>

    {{-- Upload Cards Grid --}}
    <div class="db-upload-grid">
        {{-- 1. Kartu Keluarga --}}
        <div class="db-upload-card" id="card-kk">
            <div class="db-upload-card-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="db-upload-card-icon" style="background:var(--lp-primary);color:#fff;">
                        <span class="material-symbols-outlined" style="font-size:22px;">family_restroom</span>
                    </div>
                    <div>
                        <h4>Kartu Keluarga (KK)</h4>
                        <p style="margin:0;">Scan/foto halaman utama KK yang memuat data siswa</p>
                    </div>
                </div>
                <span class="db-badge db-badge-yellow" id="status-kk">BELUM</span>
            </div>
            <div class="db-upload-dropzone" onclick="this.querySelector('input').click();" id="dropzone-kk">
                <span class="material-symbols-outlined">cloud_upload</span>
                <span>Klik atau seret file ke sini</span>
                <small>JPG, PNG, PDF — maks. 5MB</small>
                <input type="file" accept=".jpg,.jpeg,.png,.pdf" style="display:none;" onchange="handleMockUpload(this, 'kk')">
            </div>
            <div class="db-upload-preview" id="preview-kk" style="display:none;margin-top:0.75rem;">
                <img id="preview-img-kk" src="" alt="Preview KK">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:0.5rem;">
                    <span id="filename-kk" style="font-size:12px;font-weight:600;color:var(--lp-on-surface);"></span>
                    <button onclick="removeMockUpload('kk')" style="background:none;border:none;cursor:pointer;color:var(--lp-red);font-size:12px;font-weight:700;display:flex;align-items:center;gap:3px;">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span> Hapus
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. Akta Kelahiran --}}
        <div class="db-upload-card" id="card-akta">
            <div class="db-upload-card-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="db-upload-card-icon" style="background:var(--lp-green);color:#fff;">
                        <span class="material-symbols-outlined" style="font-size:22px;">description</span>
                    </div>
                    <div>
                        <h4>Akta Kelahiran</h4>
                        <p style="margin:0;">Scan/foto akta kelahiran asli yang masih berlaku</p>
                    </div>
                </div>
                <span class="db-badge db-badge-yellow" id="status-akta">BELUM</span>
            </div>
            <div class="db-upload-dropzone" onclick="this.querySelector('input').click();" id="dropzone-akta">
                <span class="material-symbols-outlined">cloud_upload</span>
                <span>Klik atau seret file ke sini</span>
                <small>JPG, PNG, PDF — maks. 5MB</small>
                <input type="file" accept=".jpg,.jpeg,.png,.pdf" style="display:none;" onchange="handleMockUpload(this, 'akta')">
            </div>
            <div class="db-upload-preview" id="preview-akta" style="display:none;margin-top:0.75rem;">
                <img id="preview-img-akta" src="" alt="Preview Akta">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:0.5rem;">
                    <span id="filename-akta" style="font-size:12px;font-weight:600;color:var(--lp-on-surface);"></span>
                    <button onclick="removeMockUpload('akta')" style="background:none;border:none;cursor:pointer;color:var(--lp-red);font-size:12px;font-weight:700;display:flex;align-items:center;gap:3px;">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span> Hapus
                    </button>
                </div>
            </div>
        </div>

        {{-- 3. Kartu NISN --}}
        <div class="db-upload-card" id="card-nisn">
            <div class="db-upload-card-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="db-upload-card-icon" style="background:var(--lp-yellow);color:#451a03;">
                        <span class="material-symbols-outlined" style="font-size:22px;">credit_card</span>
                    </div>
                    <div>
                        <h4>Kartu / Cetak NISN</h4>
                        <p style="margin:0;">Screenshot NISN dari situs Data Pokok Pendidikan (Dapodik)</p>
                    </div>
                </div>
                <span class="db-badge db-badge-yellow" id="status-nisn">BELUM</span>
            </div>
            <div class="db-upload-dropzone" onclick="this.querySelector('input').click();" id="dropzone-nisn">
                <span class="material-symbols-outlined">cloud_upload</span>
                <span>Klik atau seret file ke sini</span>
                <small>JPG, PNG, PDF — maks. 5MB</small>
                <input type="file" accept=".jpg,.jpeg,.png,.pdf" style="display:none;" onchange="handleMockUpload(this, 'nisn')">
            </div>
            <div class="db-upload-preview" id="preview-nisn" style="display:none;margin-top:0.75rem;">
                <img id="preview-img-nisn" src="" alt="Preview NISN">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:0.5rem;">
                    <span id="filename-nisn" style="font-size:12px;font-weight:600;color:var(--lp-on-surface);"></span>
                    <button onclick="removeMockUpload('nisn')" style="background:none;border:none;cursor:pointer;color:var(--lp-red);font-size:12px;font-weight:700;display:flex;align-items:center;gap:3px;">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span> Hapus
                    </button>
                </div>
            </div>
        </div>

        {{-- 4. Pas Foto --}}
        <div class="db-upload-card" id="card-foto">
            <div class="db-upload-card-header">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="db-upload-card-icon" style="background:var(--lp-red);color:#fff;">
                        <span class="material-symbols-outlined" style="font-size:22px;">photo_camera</span>
                    </div>
                    <div>
                        <h4>Pas Foto 3x4</h4>
                        <p style="margin:0;">Pas foto digital ukuran 3x4 cm, latar belakang merah</p>
                    </div>
                </div>
                <span class="db-badge db-badge-yellow" id="status-foto">BELUM</span>
            </div>
            <div class="db-upload-dropzone" onclick="this.querySelector('input').click();" id="dropzone-foto">
                <span class="material-symbols-outlined">cloud_upload</span>
                <span>Klik atau seret file ke sini</span>
                <small>JPG, PNG — maks. 5MB</small>
                <input type="file" accept=".jpg,.jpeg,.png" style="display:none;" onchange="handleMockUpload(this, 'foto')">
            </div>
            <div class="db-upload-preview" id="preview-foto" style="display:none;margin-top:0.75rem;">
                <img id="preview-img-foto" src="" alt="Preview Foto">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:0.5rem;">
                    <span id="filename-foto" style="font-size:12px;font-weight:600;color:var(--lp-on-surface);"></span>
                    <button onclick="removeMockUpload('foto')" style="background:none;border:none;cursor:pointer;color:var(--lp-red);font-size:12px;font-weight:700;display:flex;align-items:center;gap:3px;">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Note --}}
    <div style="background:var(--lp-yellow-light);border:1px solid rgba(234,179,8,0.3);border-radius:14px;padding:1rem 1.25rem;display:flex;align-items:flex-start;gap:12px;font-size:13px;margin-bottom:1.5rem;">
        <span class="material-symbols-outlined" style="font-size:22px;color:var(--lp-yellow-dark);flex-shrink:0;margin-top:2px;">construction</span>
        <div>
            <div style="font-weight:700;color:var(--lp-yellow-dark);margin-bottom:4px;">Fitur Dalam Tahap Pengembangan</div>
            <p style="color:var(--lp-on-surface-variant);margin:0;line-height:1.6;">Upload berkas saat ini hanya menampilkan preview lokal di browser. Data belum dikirim ke server. Fitur penyimpanan permanen akan segera tersedia setelah update sistem backend.</p>
        </div>
    </div>

</div>

@push('scripts')
<script>
function handleMockUpload(input, type) {
    const file = input.files[0];
    if (!file) return;

    // Validate size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file terlalu besar. Maksimal 5MB.');
        input.value = '';
        return;
    }

    const card = document.getElementById('card-' + type);
    const dropzone = document.getElementById('dropzone-' + type);
    const preview = document.getElementById('preview-' + type);
    const previewImg = document.getElementById('preview-img-' + type);
    const filename = document.getElementById('filename-' + type);
    const status = document.getElementById('status-' + type);

    // Show preview for images
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        // PDF - show icon instead
        previewImg.style.display = 'none';
    }

    filename.textContent = file.name;
    dropzone.style.display = 'none';
    preview.style.display = 'block';
    card.classList.add('uploaded');

    // Update status badge
    status.className = 'db-badge db-badge-green';
    status.textContent = '✓ SIAP';
}

function removeMockUpload(type) {
    const card = document.getElementById('card-' + type);
    const dropzone = document.getElementById('dropzone-' + type);
    const preview = document.getElementById('preview-' + type);
    const previewImg = document.getElementById('preview-img-' + type);
    const status = document.getElementById('status-' + type);

    // Reset file input
    const input = dropzone.querySelector('input[type=file]');
    if (input) input.value = '';

    previewImg.src = '';
    preview.style.display = 'none';
    dropzone.style.display = '';
    card.classList.remove('uploaded');

    // Reset status badge
    status.className = 'db-badge db-badge-yellow';
    status.textContent = 'BELUM';
}
</script>
@endpush
@endsection
