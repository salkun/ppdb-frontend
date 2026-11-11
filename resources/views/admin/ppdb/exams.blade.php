@extends('admin.layout')

@section('title', 'Pengelolaan Ujian')
@section('header_title', 'Pengelolaan Ujian')

@push('styles')
<style>
    .exam-management { --exam-ink: #14213d; --exam-muted: #64748b; --exam-line: #e7edf5; }
    .exam-hero {
        position: relative;
        overflow: hidden;
        border: 0;
        border-radius: 20px;
        color: #fff;
        background: linear-gradient(120deg, #073b78 0%, #075bb3 58%, #1783d5 100%);
        box-shadow: 0 14px 34px rgba(11, 76, 142, .16);
    }
    .exam-hero::after {
        position: absolute;
        top: -110px;
        right: -40px;
        width: 300px;
        height: 300px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 50%;
        box-shadow: 0 0 0 35px rgba(255,255,255,.04), 0 0 0 75px rgba(255,255,255,.035);
        content: "";
        pointer-events: none;
    }
    .exam-hero-content { position: relative; z-index: 1; }
    .exam-hero-icon {
        display: grid;
        width: 52px;
        height: 52px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 16px;
        background: rgba(255,255,255,.14);
    }
    .exam-stat {
        height: 100%;
        padding: 18px;
        border: 1px solid var(--exam-line);
        border-radius: 15px;
        background: #fff;
    }
    .exam-stat-icon {
        display: grid;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 12px;
        background: #eff6ff;
        color: #1766b1;
    }
    .exam-card {
        overflow: hidden;
        border: 1px solid var(--exam-line);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 5px 18px rgba(15, 23, 42, .04);
    }
    .exam-card-head {
        padding: 20px 22px;
        border-bottom: 1px solid var(--exam-line);
        background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
    }
    .exam-type-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 14px;
        background: #eaf3ff;
        color: #1264b1;
    }
    .exam-card-body { padding: 22px; }
    .exam-section-label {
        margin-bottom: 14px;
        color: var(--exam-ink);
        font-size: .9rem;
        font-weight: 800;
    }
    .exam-options {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 18px;
        padding: 13px 15px;
        border: 1px solid var(--exam-line);
        border-radius: 12px;
        background: #f8fafc;
    }
    .exam-options .form-check { margin: 0; }
    .exam-import-panel {
        padding: 18px;
        border: 1px dashed #bfd1e5;
        border-radius: 14px;
        background: #f8fbff;
    }
    .exam-results {
        overflow: hidden;
        border: 1px solid var(--exam-line);
        border-radius: 14px;
    }
    .exam-results .table { min-width: 650px; }
    .exam-results thead th {
        padding-top: 12px;
        padding-bottom: 12px;
        border-bottom-color: var(--exam-line);
        background: #f8fafc;
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .exam-results tbody td { padding-top: 13px; padding-bottom: 13px; }
    .exam-answer-review {
        padding: 14px 16px;
        border-radius: 12px;
        background: #f8fafc;
    }
    .exam-answer-review summary { font-weight: 700; }
    .exam-management .form-control,
    .exam-management .form-select { min-height: 43px; border-color: #dce4ee; }
    .exam-management textarea.form-control { min-height: auto; }
    .exam-management .form-control:focus,
    .exam-management .form-select:focus {
        border-color: #80b7ec;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .1);
    }
    @media (max-width: 575.98px) {
        .exam-card-head, .exam-card-body { padding: 17px; }
        .exam-hero { border-radius: 16px; }
    }
</style>
@endpush

@section('content')
@php
    $preview = session('exam_import_preview');
    $totalQuestions = $exams->sum('questions_count');
    $totalAttempts = $exams->sum(fn ($exam) => $exam->attempts->count());
    $publishedExams = $exams->where('published', true)->count();
    $examIcons = ['general' => 'menu_book', 'language' => 'translate', 'ict' => 'computer'];
    $attemptStatuses = [
        'in_progress' => 'Sedang mengerjakan',
        'suspended' => 'Ditangguhkan',
        'submitted' => 'Selesai',
        'terminated' => 'Dihentikan',
    ];
@endphp

<div class="exam-management">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2" role="alert">
            <span class="material-symbols-outlined">check_circle</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3 d-flex align-items-center gap-2" role="alert">
            <span class="material-symbols-outlined">error</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3" role="alert">
            <div class="fw-bold mb-1">Periksa kembali data yang diisi</div>
            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="exam-hero p-4 p-lg-5 mb-4">
        <div class="exam-hero-content d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
            <div class="d-flex align-items-start gap-3">
                <div class="exam-hero-icon"><span class="material-symbols-outlined" style="font-size:28px;">quiz</span></div>
                <div>
                    <div class="small fw-bold text-uppercase mb-2" style="letter-spacing:.1em;color:#c7e4ff;">Pusat Pengaturan</div>
                    <h2 class="h3 fw-bold mb-2">Kelola ujian calon siswa</h2>
                    <p class="mb-0 text-white-50">Atur jadwal, bank soal, dan pantau progres peserta dari satu halaman.</p>
                </div>
            </div>
            <a class="btn btn-light text-primary fw-bold d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2" href="{{ route('admin.ppdb.exams.template') }}">
                <span class="material-symbols-outlined" style="font-size:20px;">description</span>
                Unduh Template Soal
            </a>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="exam-stat d-flex align-items-center gap-3">
                <div class="exam-stat-icon"><span class="material-symbols-outlined">quiz</span></div>
                <div><div class="small text-muted">Jenis ujian</div><div class="fs-4 fw-bold text-dark">{{ count($types) }}</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="exam-stat d-flex align-items-center gap-3">
                <div class="exam-stat-icon" style="background:#ecfdf5;color:#13875a;"><span class="material-symbols-outlined">campaign</span></div>
                <div><div class="small text-muted">Dipublikasikan</div><div class="fs-4 fw-bold text-dark">{{ $publishedExams }}<span class="fs-6 fw-normal text-muted"> / {{ count($types) }}</span></div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="exam-stat d-flex align-items-center gap-3">
                <div class="exam-stat-icon" style="background:#fff7ed;color:#c56a16;"><span class="material-symbols-outlined">library_books</span></div>
                <div><div class="small text-muted">Total soal</div><div class="fs-4 fw-bold text-dark">{{ $totalQuestions }}</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="exam-stat d-flex align-items-center gap-3">
                <div class="exam-stat-icon" style="background:#f5f3ff;color:#7048b8;"><span class="material-symbols-outlined">groups</span></div>
                <div><div class="small text-muted">Total peserta tes</div><div class="fs-4 fw-bold text-dark">{{ $totalAttempts }}</div></div>
            </div>
        </div>
    </div>

    <div class="alert border-0 rounded-3 d-flex align-items-start gap-3 mb-4" style="background:#eff6ff;color:#21476d;">
        <span class="material-symbols-outlined text-primary">info</span>
        <div class="small">
            <strong>Format soal Word (.docx)</strong>
            <div class="mt-1">Gunakan tabel dengan kolom Nomor, Pertanyaan, A, B, C, D, dan Kunci. Soal akan ditinjau sebelum diimpor; bank soal yang sudah memiliki peserta tidak dapat diganti.</div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h3 class="h5 fw-bold text-dark mb-1">Daftar ujian</h3>
            <p class="small text-muted mb-0">Pengaturan setiap jenis ujian dapat dikelola secara terpisah.</p>
        </div>
    </div>

    <div class="d-flex flex-column gap-4">
        @foreach($types as $type => $label)
            @php($exam = $exams->firstWhere('type', $type))
            <section class="exam-card">
                <div class="exam-card-head d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="exam-type-icon"><span class="material-symbols-outlined">{{ $examIcons[$type] ?? 'quiz' }}</span></div>
                        <div>
                            <h4 class="h6 fw-bold text-dark mb-1">{{ $label }}</h4>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                @if($exam?->published)
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1">Dipublikasikan</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border px-2 py-1">Belum dipublikasikan</span>
                                @endif
                                @if($exam)
                                    <span class="small text-muted">{{ $exam->questions_count }} soal</span>
                                    <span class="text-muted">·</span>
                                    <span class="small text-muted">{{ $exam->attempts->count() }} peserta</span>
                                @else
                                    <span class="small text-muted">Belum dikonfigurasi</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($exam?->results_released)
                        <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                            <span class="material-symbols-outlined align-middle me-1" style="font-size:16px;">visibility</span>Nilai dirilis
                        </span>
                    @elseif($exam)
                        <span class="badge rounded-pill bg-light text-muted border px-3 py-2">Nilai belum dirilis</span>
                    @endif
                </div>

                <div class="exam-card-body">
                    <form method="POST" action="{{ route('admin.ppdb.exams.save') }}">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">

                        <div class="exam-section-label d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-primary" style="font-size:20px;">tune</span>
                            Pengaturan ujian
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-5">
                                <label class="form-label fw-semibold small" for="exam-title-{{ $type }}">Judul ujian</label>
                                <input id="exam-title-{{ $type }}" name="title" class="form-control" maxlength="150" required value="{{ old('title', $exam?->title ?? $label) }}">
                            </div>
                            <div class="col-sm-6 col-lg-2">
                                <label class="form-label fw-semibold small" for="exam-duration-{{ $type }}">Durasi (menit)</label>
                                <input id="exam-duration-{{ $type }}" type="number" name="duration_minutes" class="form-control" min="1" max="300" required value="{{ old('duration_minutes', $exam?->duration_minutes ?? 60) }}">
                            </div>
                            <div class="col-sm-6 col-lg-2">
                                <label class="form-label fw-semibold small" for="exam-start-{{ $type }}">Jadwal mulai</label>
                                <input id="exam-start-{{ $type }}" type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $exam?->starts_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label fw-semibold small" for="exam-end-{{ $type }}">Jadwal selesai</label>
                                <input id="exam-end-{{ $type }}" type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $exam?->ends_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small" for="exam-instructions-{{ $type }}">Petunjuk untuk siswa <span class="text-muted fw-normal">(opsional)</span></label>
                                <textarea id="exam-instructions-{{ $type }}" name="instructions" class="form-control" rows="2" maxlength="5000" placeholder="Tuliskan petunjuk pengerjaan ujian...">{{ old('instructions', $exam?->instructions) }}</textarea>
                            </div>
                        </div>

                        <div class="exam-options mt-3">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="published" value="1" @checked(old('published', $exam?->published ?? false))>
                                <span class="form-check-label small fw-semibold">Publikasikan ujian</span>
                            </label>
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions', $exam?->shuffle_questions ?? true))>
                                <span class="form-check-label small">Acak urutan soal</span>
                            </label>
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="shuffle_options" value="1" @checked(old('shuffle_options', $exam?->shuffle_options ?? true))>
                                <span class="form-check-label small">Acak pilihan jawaban</span>
                            </label>
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="results_released" value="1" @checked(old('results_released', $exam?->results_released ?? false))>
                                <span class="form-check-label small fw-semibold">Rilis nilai ke siswa</span>
                            </label>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-4">
                            <p class="small text-muted mb-0">
                                <span class="material-symbols-outlined align-middle me-1" style="font-size:17px;">schedule</span>
                                Jadwal diperlukan agar ujian dapat dipublikasikan.
                            </p>
                            <button class="btn btn-primary fw-semibold d-inline-flex align-items-center justify-content-center gap-2 px-4" type="submit">
                                <span class="material-symbols-outlined" style="font-size:19px;">save</span>
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>

                    @if($exam)
                        <hr class="my-4" style="border-color:var(--exam-line);opacity:1;">

                        <div class="exam-section-label d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-primary" style="font-size:20px;">upload_file</span>
                            Bank soal
                        </div>
                        @if($exam->attempts->isNotEmpty())
                            <div class="alert alert-warning border-0 rounded-3 d-flex align-items-start gap-2 mb-0">
                                <span class="material-symbols-outlined">lock</span>
                                <div class="small"><strong>Bank soal terkunci.</strong> Soal tidak dapat diganti setelah peserta mulai ujian.</div>
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.ppdb.exams.preview', $exam) }}" enctype="multipart/form-data" class="exam-import-panel">
                                @csrf
                                <div class="row g-3 align-items-end">
                                    <div class="col-lg-8">
                                        <label class="form-label fw-semibold small" for="questions-file-{{ $exam->id }}">Pilih dokumen Word (.docx)</label>
                                        <input id="questions-file-{{ $exam->id }}" type="file" name="questions_file" class="form-control" accept=".docx" required>
                                        <div class="form-text">Maksimal 500 soal per ujian. Pastikan format tabel mengikuti template.</div>
                                    </div>
                                    <div class="col-lg-4">
                                        <button class="btn btn-outline-primary fw-semibold w-100 d-inline-flex align-items-center justify-content-center gap-2" type="submit">
                                            <span class="material-symbols-outlined" style="font-size:19px;">preview</span>
                                            Baca &amp; Pratinjau Soal
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif

                        @if($preview && (int)($preview['exam_id'] ?? 0) === $exam->id && session('exam_import_preview_key'))
                            <div class="mt-4 p-3 p-md-4 border rounded-3">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                                    <div>
                                        <h5 class="h6 fw-bold mb-1">Pratinjau impor</h5>
                                        <p class="small text-muted mb-0">{{ count($preview['questions']) }} soal siap ditinjau sebelum dimasukkan.</p>
                                    </div>
                                    <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">{{ count($preview['questions']) }} soal</span>
                                </div>
                                <div class="table-responsive exam-results mb-3" style="max-height:360px;">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="sticky-top"><tr><th class="ps-3">No.</th><th>Pertanyaan</th><th>Pilihan jawaban</th><th>Kunci</th></tr></thead>
                                        <tbody>
                                        @foreach($preview['questions'] as $index => $question)
                                            <tr>
                                                <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                                <td>{{ $question['question'] }}</td>
                                                <td>@foreach($question['options'] as $key => $option)<div><strong>{{ $key }}.</strong> {{ $option }}</div>@endforeach</td>
                                                <td><span class="badge bg-success-subtle text-success">{{ $question['correct_option'] }}</span></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <form method="POST" action="{{ route('admin.ppdb.exams.import', $exam) }}" onsubmit="return confirm('Bank soal saat ini akan diganti. Lanjutkan impor?');">
                                    @csrf
                                    <input type="hidden" name="preview_key" value="{{ session('exam_import_preview_key') }}">
                                    <button class="btn btn-success fw-semibold d-inline-flex align-items-center gap-2" type="submit">
                                        <span class="material-symbols-outlined" style="font-size:19px;">check_circle</span>
                                        Konfirmasi &amp; Impor Soal
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if($exam->attempts->isNotEmpty())
                            <hr class="my-4" style="border-color:var(--exam-line);opacity:1;">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3">
                                <div>
                                    <div class="exam-section-label mb-1">Hasil &amp; progres peserta</div>
                                    <p class="small text-muted mb-0">Pantau status pengerjaan dan tinjau jawaban siswa.</p>
                                </div>
                                <a class="btn btn-outline-success fw-semibold d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('admin.ppdb.exams.results.export', $exam) }}">
                                    <span class="material-symbols-outlined" style="font-size:19px;">download</span>
                                    Export Excel
                                </a>
                            </div>
                            <div class="table-responsive exam-results">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr><th class="ps-3">Peserta</th><th>Status</th><th class="text-center">Pelanggaran</th><th class="text-end">Nilai</th><th class="text-end pe-3">Tinjauan</th></tr>
                                    </thead>
                                    <tbody>
                                    @foreach($exam->attempts as $attempt)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-dark">{{ $attempt->account?->full_name ?? $attempt->account_id }}</div>
                                                @if($attempt->account?->email)<div class="small text-muted">{{ $attempt->account->email }}</div>@endif
                                            </td>
                                            <td>
                                                @php($statusClass = match($attempt->status) { 'submitted' => 'bg-success-subtle text-success', 'in_progress' => 'bg-primary-subtle text-primary', 'suspended' => 'bg-warning-subtle text-warning-emphasis', 'terminated' => 'bg-danger-subtle text-danger', default => 'bg-secondary-subtle text-secondary' })
                                                <span class="badge rounded-pill {{ $statusClass }}">{{ $attemptStatuses[$attempt->status] ?? ucfirst($attempt->status) }}</span>
                                                @if($attempt->status === 'terminated')
                                                    <form method="POST" action="{{ route('admin.ppdb.exams.attempts.reopen', $attempt) }}" class="mt-2" onsubmit="return confirm('Buka kembali ujian ini dengan durasi penuh? Jawaban yang sudah tersimpan tetap ada.');">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-warning" type="submit">Buka Kembali</button>
                                                    </form>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $attempt->violations }}</td>
                                            <td class="text-end fw-bold">{{ $attempt->score === null ? '—' : number_format((float)$attempt->score, 2) }}</td>
                                            <td class="text-end pe-3">
                                                <details class="d-inline-block text-start">
                                                    <summary class="small text-primary fw-semibold" style="cursor:pointer;">Lihat jawaban</summary>
                                                    <div class="exam-answer-review mt-2" style="min-width:min(420px, 80vw);">
                                                        <ol class="small mb-0 ps-3">
                                                            @foreach($attempt->question_order ?? [] as $questionId)
                                                                @php($answeredQuestion = $exam->questions->firstWhere('id', $questionId))
                                                                @if($answeredQuestion)
                                                                    @php($studentAnswer = $attempt->answers[$questionId] ?? null)
                                                                    @php($displayedOptionOrder = $attempt->option_orders[$questionId] ?? array_keys($answeredQuestion->options))
                                                                    <li class="mb-3">
                                                                        <div class="fw-semibold mb-1">{{ $answeredQuestion->question }}</div>
                                                                        <ul class="list-unstyled mb-1">
                                                                            @foreach($displayedOptionOrder as $optionIndex => $optionKey)
                                                                                <li>
                                                                                    <span class="text-muted">{{ chr(65 + $optionIndex) }}.</span>
                                                                                    {{ $answeredQuestion->options[$optionKey] ?? '' }}
                                                                                    @if($studentAnswer === $optionKey)<span class="badge bg-primary-subtle text-primary ms-1">Jawaban peserta</span>@endif
                                                                                    @if($answeredQuestion->correct_option === $optionKey)<span class="badge bg-success-subtle text-success ms-1">Kunci</span>@endif
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                        @if(!$studentAnswer)<span class="text-muted fst-italic">Belum dijawab</span>@endif
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ol>
                                                    </div>
                                                </details>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection
