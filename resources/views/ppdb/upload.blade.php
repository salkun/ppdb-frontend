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
        <p class="db-page-subtitle">Unggah scan atau foto jelas dokumen pendaftaran berikut dalam format JPG, PNG, atau PDF. Maksimal 5MB per file.</p>
    </div>

    {{-- Session Alerts --}}
    @if(session('success'))
        <div style="background:var(--lp-green-light);border:1px solid rgba(22,163,74,0.3);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:12px;font-size:14px;color:var(--lp-green);font-weight:600;">
            <span class="material-symbols-outlined" style="font-size:24px;">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div style="background:var(--lp-yellow-light);border:1px solid rgba(234,179,8,0.4);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:12px;font-size:14px;color:var(--lp-yellow-dark);font-weight:600;">
            <span class="material-symbols-outlined" style="font-size:24px;">warning</span>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div style="background:var(--lp-red-light);border:1px solid rgba(220,38,38,0.3);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:12px;font-size:14px;color:var(--lp-red);font-weight:600;">
            <span class="material-symbols-outlined" style="font-size:24px;">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div style="background:var(--lp-red-light);border:1px solid rgba(220,38,38,0.3);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:13px;color:var(--lp-red);">
            <div style="font-weight:700;display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                <span class="material-symbols-outlined" style="font-size:20px;">error</span>
                <span>Terdapat kendala pada berkas yang Anda pilih:</span>
            </div>
            <ul style="margin:0;padding-left:1.5rem;line-height:1.6;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $existingKK = $documents['kk'] ?? null;
        $existingAkta = $documents['akta'] ?? null;
        $existingNISN = $documents['nisn'] ?? null;
        $existingFoto = $documents['foto'] ?? null;

        $uploadedCount = (!empty($existingKK) ? 1 : 0) + (!empty($existingAkta) ? 1 : 0) + (!empty($existingNISN) ? 1 : 0) + (!empty($existingFoto) ? 1 : 0);
    @endphp

    {{-- Status Ringkasan Kelengkapan --}}
    <div style="background:rgba(214,227,255,0.35);border:1px solid rgba(0,90,180,0.2);border-radius:14px;padding:1.15rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <span class="material-symbols-outlined" style="font-size:28px;color:var(--lp-primary);">folder_shared</span>
            <div>
                <div style="font-weight:700;font-size:15px;color:var(--lp-primary);">Status Kelengkapan Dokumen</div>
                <div style="font-size:13px;color:var(--lp-on-surface-variant);margin-top:2px;">
                    @if($uploadedCount === 4)
                        <strong style="color:var(--lp-green);">Lengkap (4 dari 4 berkas tersimpan).</strong> Seluruh dokumen persyaratan wajib telah Anda unggah.
                    @elseif($uploadedCount > 0)
                        <strong style="color:var(--lp-yellow-dark);">Sebagian ({{ $uploadedCount }} dari 4 berkas tersimpan).</strong> Anda dapat melengkapi sisa berkas secara bertahap.
                    @else
                        Belum ada berkas yang diunggah. Silakan pilih berkas di bawah lalu klik tombol <strong>Simpan & Unggah</strong>.
                    @endif
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span class="db-badge {{ $uploadedCount === 4 ? 'db-badge-green' : ($uploadedCount > 0 ? 'db-badge-yellow' : 'db-badge-red') }}" style="font-size:13px;padding:6px 14px;">
                {{ $uploadedCount }}/4 Berkas
            </span>
        </div>
    </div>

    {{-- Formulir Upload Berkas Dokumen --}}
    <form action="{{ route('ppdb.upload.submit') }}" method="POST" enctype="multipart/form-data" id="formUploadDocs">
        @csrf

        {{-- Upload Cards Grid --}}
        <div class="db-upload-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;margin-bottom:2rem;">

            {{-- 1. Kartu Keluarga (KK) --}}
            <div class="db-upload-card {{ !empty($existingKK) ? 'uploaded' : '' }}" id="card-kk" style="background:#fff;border:1.5px solid {{ !empty($existingKK) ? 'rgba(22,163,74,0.35)' : 'var(--lp-surface-container)' }};border-radius:16px;padding:1.25rem;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div>
                    <div class="db-upload-card-header" style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:1rem;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="db-upload-card-icon" style="width:42px;height:42px;border-radius:12px;background:var(--lp-primary);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span class="material-symbols-outlined" style="font-size:22px;">family_restroom</span>
                            </div>
                            <div>
                                <h4 style="margin:0;font-size:15px;font-weight:700;color:var(--lp-on-surface);">Kartu Keluarga (KK)</h4>
                                <p style="margin:2px 0 0;font-size:12px;color:var(--lp-on-surface-variant);">Scan/foto halaman utama KK data siswa</p>
                            </div>
                        </div>
                        <span class="db-badge {{ !empty($existingKK) ? 'db-badge-green' : 'db-badge-yellow' }}" id="status-kk">
                            {{ !empty($existingKK) ? '✓ TERSIMPAN' : 'BELUM' }}
                        </span>
                    </div>

                    {{-- Jika berkas sudah pernah diunggah --}}
                    @if(!empty($existingKK))
                        <div class="db-existing-file" id="existing-kk" style="background:var(--lp-surface-subtle);border:1px solid rgba(22,163,74,0.2);border-radius:12px;padding:0.75rem;margin-bottom:0.75rem;">
                            @php
                                $ext = strtolower(pathinfo($existingKK, PATHINFO_EXTENSION));
                            @endphp
                            @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                <div style="text-align:center;margin-bottom:8px;">
                                    <a href="{{ ppdb_doc_url($existingKK, $backendUrl) }}" target="_blank">
                                        <img src="{{ ppdb_doc_url($existingKK, $backendUrl) }}" alt="Pratinjau KK" style="max-height:140px;max-width:100%;object-fit:contain;border-radius:8px;border:1px solid #ddd;">
                                    </a>
                                </div>
                            @else
                                <div style="display:flex;align-items:center;gap:8px;color:var(--lp-primary);font-size:13px;font-weight:600;margin-bottom:6px;">
                                    <span class="material-symbols-outlined">picture_as_pdf</span>
                                    <span>Dokumen PDF Terlampir</span>
                                </div>
                            @endif
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;font-size:12px;">
                                <a href="{{ ppdb_doc_url($existingKK, $backendUrl) }}" target="_blank" style="color:var(--lp-primary);font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Buka Berkas
                                </a>
                                <button type="submit" name="delete_doc" value="kk" onclick="return confirm('Hapus berkas Kartu Keluarga ini?')" style="background:none;border:none;color:var(--lp-red);font-weight:700;cursor:pointer;font-size:12px;display:inline-flex;align-items:center;gap:2px;padding:0;">
                                    <span class="material-symbols-outlined" style="font-size:15px;">delete</span> Hapus
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Dropzone Input File --}}
                    <div class="db-upload-dropzone" onclick="document.getElementById('input-kk').click();" id="dropzone-kk" style="border:2px dashed {{ !empty($existingKK) ? 'rgba(0,90,180,0.25)' : 'var(--lp-outline)' }};border-radius:12px;padding:1rem;text-align:center;cursor:pointer;background:var(--lp-surface-subtle);transition:all 0.2s ease;">
                        <span class="material-symbols-outlined" style="font-size:32px;color:var(--lp-primary);display:block;margin-bottom:4px;">cloud_upload</span>
                        <span style="font-size:13px;font-weight:600;color:var(--lp-on-surface);display:block;">
                            {{ !empty($existingKK) ? 'Klik untuk mengganti berkas KK' : 'Pilih atau seret berkas KK ke sini' }}
                        </span>
                        <small style="font-size:11px;color:var(--lp-on-surface-variant);display:block;margin-top:2px;">Format: JPG, PNG, PDF (Maks. 5MB)</small>
                        <input type="file" name="kk" id="input-kk" accept=".jpg,.jpeg,.png,.webp,.pdf" style="display:none;" onchange="handleFileSelect(this, 'kk')">
                    </div>

                    {{-- Pratinjau Seleksi Baru --}}
                    <div class="db-upload-preview" id="preview-kk" style="display:none;margin-top:0.75rem;padding:0.65rem;border-radius:10px;background:#f0fdf4;border:1px solid #86efac;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="material-symbols-outlined" style="color:var(--lp-green);font-size:20px;">file_present</span>
                            <div style="flex:1;overflow:hidden;">
                                <div id="filename-kk" style="font-size:12px;font-weight:700;color:#166534;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;"></div>
                                <div style="font-size:11px;color:#15803d;">Siap diunggah saat klik Simpan</div>
                            </div>
                            <button type="button" onclick="cancelFileSelect('kk')" style="background:none;border:none;cursor:pointer;color:var(--lp-red);font-size:11px;font-weight:700;">Batal</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Akta Kelahiran --}}
            <div class="db-upload-card {{ !empty($existingAkta) ? 'uploaded' : '' }}" id="card-akta" style="background:#fff;border:1.5px solid {{ !empty($existingAkta) ? 'rgba(22,163,74,0.35)' : 'var(--lp-surface-container)' }};border-radius:16px;padding:1.25rem;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div>
                    <div class="db-upload-card-header" style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:1rem;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="db-upload-card-icon" style="width:42px;height:42px;border-radius:12px;background:var(--lp-green);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span class="material-symbols-outlined" style="font-size:22px;">description</span>
                            </div>
                            <div>
                                <h4 style="margin:0;font-size:15px;font-weight:700;color:var(--lp-on-surface);">Akta Kelahiran</h4>
                                <p style="margin:2px 0 0;font-size:12px;color:var(--lp-on-surface-variant);">Scan/foto akta kelahiran siswa</p>
                            </div>
                        </div>
                        <span class="db-badge {{ !empty($existingAkta) ? 'db-badge-green' : 'db-badge-yellow' }}" id="status-akta">
                            {{ !empty($existingAkta) ? '✓ TERSIMPAN' : 'BELUM' }}
                        </span>
                    </div>

                    @if(!empty($existingAkta))
                        <div class="db-existing-file" id="existing-akta" style="background:var(--lp-surface-subtle);border:1px solid rgba(22,163,74,0.2);border-radius:12px;padding:0.75rem;margin-bottom:0.75rem;">
                            @php
                                $ext = strtolower(pathinfo($existingAkta, PATHINFO_EXTENSION));
                            @endphp
                            @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                <div style="text-align:center;margin-bottom:8px;">
                                    <a href="{{ ppdb_doc_url($existingAkta, $backendUrl) }}" target="_blank">
                                        <img src="{{ ppdb_doc_url($existingAkta, $backendUrl) }}" alt="Pratinjau Akta" style="max-height:140px;max-width:100%;object-fit:contain;border-radius:8px;border:1px solid #ddd;">
                                    </a>
                                </div>
                            @else
                                <div style="display:flex;align-items:center;gap:8px;color:var(--lp-primary);font-size:13px;font-weight:600;margin-bottom:6px;">
                                    <span class="material-symbols-outlined">picture_as_pdf</span>
                                    <span>Dokumen PDF Terlampir</span>
                                </div>
                            @endif
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;font-size:12px;">
                                <a href="{{ ppdb_doc_url($existingAkta, $backendUrl) }}" target="_blank" style="color:var(--lp-primary);font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Buka Berkas
                                </a>
                                <button type="submit" name="delete_doc" value="akta" onclick="return confirm('Hapus berkas Akta Kelahiran ini?')" style="background:none;border:none;color:var(--lp-red);font-weight:700;cursor:pointer;font-size:12px;display:inline-flex;align-items:center;gap:2px;padding:0;">
                                    <span class="material-symbols-outlined" style="font-size:15px;">delete</span> Hapus
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="db-upload-dropzone" onclick="document.getElementById('input-akta').click();" id="dropzone-akta" style="border:2px dashed {{ !empty($existingAkta) ? 'rgba(0,90,180,0.25)' : 'var(--lp-outline)' }};border-radius:12px;padding:1rem;text-align:center;cursor:background:var(--lp-surface-subtle);transition:all 0.2s ease;">
                        <span class="material-symbols-outlined" style="font-size:32px;color:var(--lp-green);display:block;margin-bottom:4px;">cloud_upload</span>
                        <span style="font-size:13px;font-weight:600;color:var(--lp-on-surface);display:block;">
                            {{ !empty($existingAkta) ? 'Klik untuk mengganti berkas Akta' : 'Pilih atau seret berkas Akta ke sini' }}
                        </span>
                        <small style="font-size:11px;color:var(--lp-on-surface-variant);display:block;margin-top:2px;">Format: JPG, PNG, PDF (Maks. 5MB)</small>
                        <input type="file" name="akta" id="input-akta" accept=".jpg,.jpeg,.png,.webp,.pdf" style="display:none;" onchange="handleFileSelect(this, 'akta')">
                    </div>

                    <div class="db-upload-preview" id="preview-akta" style="display:none;margin-top:0.75rem;padding:0.65rem;border-radius:10px;background:#f0fdf4;border:1px solid #86efac;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="material-symbols-outlined" style="color:var(--lp-green);font-size:20px;">file_present</span>
                            <div style="flex:1;overflow:hidden;">
                                <div id="filename-akta" style="font-size:12px;font-weight:700;color:#166534;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;"></div>
                                <div style="font-size:11px;color:#15803d;">Siap diunggah saat klik Simpan</div>
                            </div>
                            <button type="button" onclick="cancelFileSelect('akta')" style="background:none;border:none;cursor:pointer;color:var(--lp-red);font-size:11px;font-weight:700;">Batal</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Kartu / Cetak NISN --}}
            <div class="db-upload-card {{ !empty($existingNISN) ? 'uploaded' : '' }}" id="card-nisn" style="background:#fff;border:1.5px solid {{ !empty($existingNISN) ? 'rgba(22,163,74,0.35)' : 'var(--lp-surface-container)' }};border-radius:16px;padding:1.25rem;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div>
                    <div class="db-upload-card-header" style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:1rem;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="db-upload-card-icon" style="width:42px;height:42px;border-radius:12px;background:var(--lp-yellow);color:#451a03;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span class="material-symbols-outlined" style="font-size:22px;">credit_card</span>
                            </div>
                            <div>
                                <h4 style="margin:0;font-size:15px;font-weight:700;color:var(--lp-on-surface);">Kartu / Cetak NISN</h4>
                                <p style="margin:2px 0 0;font-size:12px;color:var(--lp-on-surface-variant);">Screenshot NISN dari Kemdikbud / Dapodik</p>
                            </div>
                        </div>
                        <span class="db-badge {{ !empty($existingNISN) ? 'db-badge-green' : 'db-badge-yellow' }}" id="status-nisn">
                            {{ !empty($existingNISN) ? '✓ TERSIMPAN' : 'BELUM' }}
                        </span>
                    </div>

                    @if(!empty($existingNISN))
                        <div class="db-existing-file" id="existing-nisn" style="background:var(--lp-surface-subtle);border:1px solid rgba(22,163,74,0.2);border-radius:12px;padding:0.75rem;margin-bottom:0.75rem;">
                            @php
                                $ext = strtolower(pathinfo($existingNISN, PATHINFO_EXTENSION));
                            @endphp
                            @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                <div style="text-align:center;margin-bottom:8px;">
                                    <a href="{{ ppdb_doc_url($existingNISN, $backendUrl) }}" target="_blank">
                                        <img src="{{ ppdb_doc_url($existingNISN, $backendUrl) }}" alt="Pratinjau NISN" style="max-height:140px;max-width:100%;object-fit:contain;border-radius:8px;border:1px solid #ddd;">
                                    </a>
                                </div>
                            @else
                                <div style="display:flex;align-items:center;gap:8px;color:var(--lp-primary);font-size:13px;font-weight:600;margin-bottom:6px;">
                                    <span class="material-symbols-outlined">picture_as_pdf</span>
                                    <span>Dokumen PDF Terlampir</span>
                                </div>
                            @endif
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;font-size:12px;">
                                <a href="{{ ppdb_doc_url($existingNISN, $backendUrl) }}" target="_blank" style="color:var(--lp-primary);font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Buka Berkas
                                </a>
                                <button type="submit" name="delete_doc" value="nisn" onclick="return confirm('Hapus berkas NISN ini?')" style="background:none;border:none;color:var(--lp-red);font-weight:700;cursor:pointer;font-size:12px;display:inline-flex;align-items:center;gap:2px;padding:0;">
                                    <span class="material-symbols-outlined" style="font-size:15px;">delete</span> Hapus
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="db-upload-dropzone" onclick="document.getElementById('input-nisn').click();" id="dropzone-nisn" style="border:2px dashed {{ !empty($existingNISN) ? 'rgba(0,90,180,0.25)' : 'var(--lp-outline)' }};border-radius:12px;padding:1rem;text-align:center;cursor:pointer;background:var(--lp-surface-subtle);transition:all 0.2s ease;">
                        <span class="material-symbols-outlined" style="font-size:32px;color:var(--lp-yellow-dark);display:block;margin-bottom:4px;">cloud_upload</span>
                        <span style="font-size:13px;font-weight:600;color:var(--lp-on-surface);display:block;">
                            {{ !empty($existingNISN) ? 'Klik untuk mengganti berkas NISN' : 'Pilih atau seret berkas NISN ke sini' }}
                        </span>
                        <small style="font-size:11px;color:var(--lp-on-surface-variant);display:block;margin-top:2px;">Format: JPG, PNG, PDF (Maks. 5MB)</small>
                        <input type="file" name="nisn" id="input-nisn" accept=".jpg,.jpeg,.png,.webp,.pdf" style="display:none;" onchange="handleFileSelect(this, 'nisn')">
                    </div>

                    <div class="db-upload-preview" id="preview-nisn" style="display:none;margin-top:0.75rem;padding:0.65rem;border-radius:10px;background:#f0fdf4;border:1px solid #86efac;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="material-symbols-outlined" style="color:var(--lp-green);font-size:20px;">file_present</span>
                            <div style="flex:1;overflow:hidden;">
                                <div id="filename-nisn" style="font-size:12px;font-weight:700;color:#166534;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;"></div>
                                <div style="font-size:11px;color:#15803d;">Siap diunggah saat klik Simpan</div>
                            </div>
                            <button type="button" onclick="cancelFileSelect('nisn')" style="background:none;border:none;cursor:pointer;color:var(--lp-red);font-size:11px;font-weight:700;">Batal</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Pas Foto 3x4 --}}
            <div class="db-upload-card {{ !empty($existingFoto) ? 'uploaded' : '' }}" id="card-foto" style="background:#fff;border:1.5px solid {{ !empty($existingFoto) ? 'rgba(22,163,74,0.35)' : 'var(--lp-surface-container)' }};border-radius:16px;padding:1.25rem;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div>
                    <div class="db-upload-card-header" style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:1rem;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="db-upload-card-icon" style="width:42px;height:42px;border-radius:12px;background:var(--lp-red);color:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span class="material-symbols-outlined" style="font-size:22px;">photo_camera</span>
                            </div>
                            <div>
                                <h4 style="margin:0;font-size:15px;font-weight:700;color:var(--lp-on-surface);">Pas Foto 3x4</h4>
                                <p style="margin:2px 0 0;font-size:12px;color:var(--lp-on-surface-variant);">Latar belakang merah, wajah tampak jelas</p>
                            </div>
                        </div>
                        <span class="db-badge {{ !empty($existingFoto) ? 'db-badge-green' : 'db-badge-yellow' }}" id="status-foto">
                            {{ !empty($existingFoto) ? '✓ TERSIMPAN' : 'BELUM' }}
                        </span>
                    </div>

                    @if(!empty($existingFoto))
                        <div class="db-existing-file" id="existing-foto" style="background:var(--lp-surface-subtle);border:1px solid rgba(22,163,74,0.2);border-radius:12px;padding:0.75rem;margin-bottom:0.75rem;">
                            <div style="text-align:center;margin-bottom:8px;">
                                <a href="{{ ppdb_doc_url($existingFoto, $backendUrl) }}" target="_blank">
                                    <img src="{{ ppdb_doc_url($existingFoto, $backendUrl) }}" alt="Pratinjau Foto" style="max-height:140px;max-width:100%;object-fit:contain;border-radius:8px;border:1px solid #ddd;">
                                </a>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;font-size:12px;">
                                <a href="{{ ppdb_doc_url($existingFoto, $backendUrl) }}" target="_blank" style="color:var(--lp-primary);font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span> Buka Foto
                                </a>
                                <button type="submit" name="delete_doc" value="foto" onclick="return confirm('Hapus pas foto ini?')" style="background:none;border:none;color:var(--lp-red);font-weight:700;cursor:pointer;font-size:12px;display:inline-flex;align-items:center;gap:2px;padding:0;">
                                    <span class="material-symbols-outlined" style="font-size:15px;">delete</span> Hapus
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="db-upload-dropzone" onclick="document.getElementById('input-foto').click();" id="dropzone-foto" style="border:2px dashed {{ !empty($existingFoto) ? 'rgba(0,90,180,0.25)' : 'var(--lp-outline)' }};border-radius:12px;padding:1rem;text-align:center;cursor:pointer;background:var(--lp-surface-subtle);transition:all 0.2s ease;">
                        <span class="material-symbols-outlined" style="font-size:32px;color:var(--lp-red);display:block;margin-bottom:4px;">cloud_upload</span>
                        <span style="font-size:13px;font-weight:600;color:var(--lp-on-surface);display:block;">
                            {{ !empty($existingFoto) ? 'Klik untuk mengganti Pas Foto' : 'Pilih atau seret Pas Foto ke sini' }}
                        </span>
                        <small style="font-size:11px;color:var(--lp-on-surface-variant);display:block;margin-top:2px;">Format: JPG, PNG, WEBP (Maks. 5MB)</small>
                        <input type="file" name="foto" id="input-foto" accept=".jpg,.jpeg,.png,.webp" style="display:none;" onchange="handleFileSelect(this, 'foto')">
                    </div>

                    <div class="db-upload-preview" id="preview-foto" style="display:none;margin-top:0.75rem;padding:0.65rem;border-radius:10px;background:#f0fdf4;border:1px solid #86efac;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="material-symbols-outlined" style="color:var(--lp-green);font-size:20px;">file_present</span>
                            <div style="flex:1;overflow:hidden;">
                                <div id="filename-foto" style="font-size:12px;font-weight:700;color:#166534;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;"></div>
                                <div style="font-size:11px;color:#15803d;">Siap diunggah saat klik Simpan</div>
                            </div>
                            <button type="button" onclick="cancelFileSelect('foto')" style="background:none;border:none;cursor:pointer;color:var(--lp-red);font-size:11px;font-weight:700;">Batal</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Action Bar: Submit & Back Buttons --}}
        <div style="background:#fff;border:1.5px solid var(--lp-surface-container);border-radius:16px;padding:1.25rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);margin-bottom:2rem;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="material-symbols-outlined" style="font-size:24px;color:var(--lp-primary);">info</span>
                <span style="font-size:13px;color:var(--lp-on-surface-variant);">
                    Anda dapat menyimpan berkas yang sudah ada terlebih dahulu dan mengunggah sisanya kemudian.
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('dashboard') }}" class="db-feature-btn db-feature-btn-outline" style="text-decoration:none;padding:10px 18px;font-size:13.5px;font-weight:600;">
                    <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span> Dashboard
                </a>
                <button type="submit" id="btnSubmitDocs" class="db-feature-btn db-feature-btn-primary" style="padding:10px 24px;font-size:14px;font-weight:700;background:linear-gradient(135deg,var(--lp-primary),#003d80);border:none;border-radius:10px;color:#fff;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(0,90,180,0.3);cursor:pointer;">
                    <span class="material-symbols-outlined" style="font-size:18px;">cloud_done</span>
                    <span>Simpan &amp; Unggah Berkas</span>
                </button>
            </div>
        </div>

    </form>

</div>

@push('scripts')
<script>
function handleFileSelect(input, type) {
    const file = input.files[0];
    if (!file) return;

    // Validate size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file terlalu besar. Maksimal 5MB.');
        input.value = '';
        return;
    }

    const preview = document.getElementById('preview-' + type);
    const filename = document.getElementById('filename-' + type);
    const dropzone = document.getElementById('dropzone-' + type);
    const status = document.getElementById('status-' + type);

    if (filename) filename.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    if (preview) preview.style.display = 'block';
    if (status) {
        status.className = 'db-badge db-badge-yellow';
        status.textContent = 'SIAP SIMPAN';
    }
    if (dropzone) {
        dropzone.style.borderColor = 'var(--lp-primary)';
        dropzone.style.background = 'rgba(214,227,255,0.2)';
    }
}

function cancelFileSelect(type) {
    const input = document.getElementById('input-' + type);
    if (input) input.value = '';

    const preview = document.getElementById('preview-' + type);
    if (preview) preview.style.display = 'none';

    const card = document.getElementById('card-' + type);
    const status = document.getElementById('status-' + type);
    const dropzone = document.getElementById('dropzone-' + type);

    if (card && card.classList.contains('uploaded')) {
        if (status) {
            status.className = 'db-badge db-badge-green';
            status.textContent = '✓ TERSIMPAN';
        }
    } else {
        if (status) {
            status.className = 'db-badge db-badge-yellow';
            status.textContent = 'BELUM';
        }
    }

    if (dropzone) {
        dropzone.style.borderColor = '';
        dropzone.style.background = '';
    }
}

document.getElementById('formUploadDocs')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnSubmitDocs');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width:16px;height:16px;"></span> Menyimpan...';
    }
});
</script>
@endpush
@endsection
