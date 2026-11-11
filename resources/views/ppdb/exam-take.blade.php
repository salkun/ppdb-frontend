@extends('layouts.app')

@section('title', $exam->title)

@push('styles')
<style>
    .exam-mode .db-viewport { width: 100%; min-height: 100vh; }
    .exam-shell { max-width: 1000px; margin: auto; padding: 1.25rem; }
    .exam-question { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem; margin-bottom: 1rem; }
    .exam-option { display: flex; gap: .75rem; align-items: flex-start; padding: .7rem; border: 1px solid #e2e8f0; border-radius: 10px; margin-top: .5rem; cursor: pointer; }
    .exam-option:has(input:checked) { border-color: #005ab4; background: #eff6ff; }
    .exam-status { position: sticky; top: 0; z-index: 4; background: #f8fafc; padding: .75rem 0; }
    .exam-suspend { position: fixed; inset: 0; z-index: 3000; display: none; place-items: center; padding: 1rem; background: rgba(15,23,42,.75); }
    .exam-suspend.is-open { display: grid; }
</style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div id="examRuntimeConfig" hidden
     data-violation-url="{{ route('exams.violation', $exam) }}"
     data-answer-url="{{ route('exams.answers', $exam) }}"
     data-exam-index-url="{{ route('exams.index') }}"
     data-pending-storage-key="ppdb-exam-pending-{{ $attempt->id }}"
     data-attempt-active="{{ $attempt->status === 'in_progress' ? 'true' : 'false' }}"></div>
<div class="exam-shell">
    <div class="exam-status d-flex justify-content-between align-items-center border-bottom mb-3">
        <div><h1 class="h4 fw-bold mb-1">{{ $exam->title }}</h1><span id="saveStatus" class="small text-muted">Jawaban tersimpan otomatis</span></div>
        <div class="badge bg-primary fs-6" id="examTimer" data-ends-at="{{ $attempt->ends_at->toIso8601String() }}" data-server-now="{{ now()->toIso8601String() }}">--:--:--</div>
    </div>

    @if($attempt->status === 'suspended')
        <div class="alert alert-warning">
            Ujian ditangguhkan karena halaman ujian ditinggalkan ({{ $attempt->violations }}/3). Jawaban tersimpan dan timer tetap berjalan.
            <form method="POST" action="{{ route('exams.resume', $exam) }}" class="mt-2">@csrf<button class="btn btn-warning" type="submit">Lanjutkan Ujian</button></form>
        </div>
    @endif
    <p class="text-secondary">{{ $exam->instructions }}</p>

    <form id="examForm" method="POST" action="{{ route('exams.submit', $exam) }}">
        @csrf
        @foreach($questions as $index => $question)
            <section class="exam-question">
                <h2 class="h6 fw-bold">{{ $index + 1 }}. {{ $question['question'] }}</h2>
                @foreach($question['options'] as $option)
                    <label class="exam-option">
                        <input type="radio" name="answer-{{ $question['id'] }}" value="{{ $option['key'] }}" data-question-id="{{ $question['id'] }}" @checked(($attempt->answers[$question['id']] ?? null) === $option['key']) @disabled($attempt->status !== 'in_progress')>
                        <span><strong>{{ $option['label'] }}.</strong> {{ $option['text'] }}</span>
                    </label>
                @endforeach
            </section>
        @endforeach

        @if($attempt->status === 'in_progress')
            <div class="text-end mb-5">
                <button type="submit" class="btn btn-success btn-lg">Kirim & Akhiri Ujian</button>
            </div>
        @endif
    </form>
</div>

<div class="exam-suspend {{ $attempt->status === 'suspended' ? 'is-open' : '' }}" id="examSuspendOverlay">
    <div class="bg-white rounded-4 shadow p-4" style="max-width:480px;">
        <h2 class="h5 fw-bold">Ujian Ditangguhkan</h2>
        <p id="suspendMessage" class="text-secondary">Halaman ujian terdeteksi ditinggalkan. Jawaban Anda tersimpan. Pelanggaran: {{ $attempt->violations }}/3.</p>
        <form method="POST" action="{{ route('exams.resume', $exam) }}">@csrf<button class="btn btn-primary" id="resumeExamButton" type="submit">Lanjutkan Ujian</button></form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var timer = document.getElementById('examTimer');
    var form = document.getElementById('examForm');
    var status = document.getElementById('saveStatus');
    var overlay = document.getElementById('examSuspendOverlay');
    var config = document.getElementById('examRuntimeConfig');
    var deadline = new Date(timer.dataset.endsAt).getTime();
    var serverTimeOffset = new Date(timer.dataset.serverNow).getTime() - Date.now();
    var violationUrl = config.dataset.violationUrl;
    var answerUrl = config.dataset.answerUrl;
    var examIndexUrl = config.dataset.examIndexUrl;
    var pendingStorageKey = config.dataset.pendingStorageKey;
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var violationPending = false;
    var unreportedDeparture = false;
    var pendingAnswers = {};
    var savingAnswers = {};
    var submitting = false;
    var submitPending = false;
    var navigationInProgress = false;
    var automaticSubmit = false;
    var attemptActive = config.dataset.attemptActive === 'true';

    function persistPendingAnswers() {
        try {
            if (Object.keys(pendingAnswers).length) {
                localStorage.setItem(pendingStorageKey, JSON.stringify(pendingAnswers));
            } else {
                localStorage.removeItem(pendingStorageKey);
            }
        } catch (error) {
            status.textContent = 'Penyimpanan lokal browser tidak tersedia. Jawaban tetap dicoba simpan ke server.';
        }
    }

    try {
        pendingAnswers = JSON.parse(localStorage.getItem(pendingStorageKey) || '{}');
        if (!pendingAnswers || typeof pendingAnswers !== 'object' || Array.isArray(pendingAnswers)) {
            throw new Error('Data jawaban lokal tidak valid.');
        }
    } catch (error) {
        pendingAnswers = {};
        status.textContent = 'Jawaban lokal sebelumnya tidak dapat dipulihkan. Periksa jawaban sebelum melanjutkan.';
    }

    function tick() {
        var remaining = Math.max(0, deadline - (Date.now() + serverTimeOffset));
        var seconds = Math.floor(remaining / 1000);
        timer.textContent = [Math.floor(seconds / 3600), Math.floor((seconds % 3600) / 60), seconds % 60]
            .map(function (value) { return String(value).padStart(2, '0'); }).join(':');
        if (remaining <= 0 && attemptActive && !submitting) {
            automaticSubmit = true;
            form.requestSubmit();
        }
    }
    var timerInterval = setInterval(tick, 1000);
    tick();

    function savePendingAnswer(questionId) {
        if (savingAnswers[questionId]) {
            return savingAnswers[questionId].then(function (saved) {
                if (!saved) return false;
                return pendingAnswers[questionId] === undefined
                    ? true
                    : savePendingAnswer(questionId);
            });
        }
        var answer = pendingAnswers[questionId];
        if (answer === undefined) return Promise.resolve(true);
        status.textContent = 'Menyimpan...';
        var saving = fetch(answerUrl, {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ question_id: questionId, answer: answer })
            }).then(function (response) {
                if (!response.ok) throw new Error('Jawaban belum tersimpan.');
                if (pendingAnswers[questionId] === answer) delete pendingAnswers[questionId];
                persistPendingAnswers();
                status.textContent = 'Tersimpan ' + new Date().toLocaleTimeString();
                return true;
            }).catch(function () {
                status.textContent = 'Koneksi terganggu. Jawaban lokal akan dicoba simpan kembali saat tersambung.';
                return false;
            });
        savingAnswers[questionId] = saving;
        return saving.then(function (saved) {
            if (savingAnswers[questionId] === saving) delete savingAnswers[questionId];
            if (saved && pendingAnswers[questionId] !== undefined) {
                return savePendingAnswer(questionId);
            }
            return saved;
        });
    }

    function flushPendingAnswers() {
        return Promise.all(Object.keys(pendingAnswers).map(savePendingAnswer))
            .then(function (results) {
                if (!results.every(Boolean)) return false;
                return Object.keys(pendingAnswers).length ? flushPendingAnswers() : true;
            });
    }

    var restoredAnswers = {};
    form.querySelectorAll('input[type="radio"]').forEach(function (input) {
        if (pendingAnswers[input.dataset.questionId] === input.value) {
            restoredAnswers[input.dataset.questionId] = input.value;
            if (attemptActive) input.checked = true;
        }
    });
    pendingAnswers = restoredAnswers;
    persistPendingAnswers();

    form.querySelectorAll('input[type="radio"]').forEach(function (input) {
        input.addEventListener('change', function () {
            pendingAnswers[input.dataset.questionId] = input.value;
            persistPendingAnswers();
            savePendingAnswer(input.dataset.questionId);
        });
    });
    document.querySelectorAll('form[action$="/resume"]').forEach(function (resumeForm) {
        resumeForm.addEventListener('submit', function () { navigationInProgress = true; });
    });
    window.addEventListener('online', flushPendingAnswers);
    setInterval(function () {
        form.querySelectorAll('input[type="radio"]:checked').forEach(function (input) {
            pendingAnswers[input.dataset.questionId] = input.value;
        });
        persistPendingAnswers();
        flushPendingAnswers();
    }, 20000);

    form.addEventListener('submit', function (event) {
        if (submitting || submitPending) {
            event.preventDefault();
            return;
        }
        event.preventDefault();
        if (!automaticSubmit && Date.now() < deadline && !window.confirm('Kirim jawaban dan akhiri ujian?')) return;
        if (automaticSubmit) {
            submitting = true;
            clearInterval(timerInterval);
            form.submit();
            return;
        }
        submitPending = true;
        form.querySelectorAll('input[type="radio"]').forEach(function (input) { input.disabled = true; });
        flushPendingAnswers().then(function (saved) {
            if (!saved) {
                submitPending = false;
                form.querySelectorAll('input[type="radio"]').forEach(function (input) { input.disabled = false; });
                automaticSubmit = false;
                status.textContent = 'Jawaban belum tersinkron. Periksa koneksi sebelum mengakhiri ujian.';
                return;
            }
            submitting = true;
            clearInterval(timerInterval);
            form.submit();
        });
    });

    function reportLeaving(retry) {
        if (violationPending || submitting || navigationInProgress || (!document.hidden && !retry)) return;
        violationPending = true;
        fetch(violationUrl, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
        }).then(function (response) {
            if (!response.ok) throw new Error('Pelanggaran belum tercatat.');
            return response.json();
        }).then(function (result) {
            unreportedDeparture = false;
            if (result.status === 'terminated') {
                clearInterval(timerInterval);
                window.location.href = examIndexUrl;
                return;
            }
            if (result.status !== 'suspended') return;
            form.querySelectorAll('input[type="radio"]').forEach(function (input) { input.disabled = true; });
            overlay.classList.add('is-open');
            document.getElementById('suspendMessage').textContent =
                'Halaman ujian terdeteksi ditinggalkan. Jawaban Anda tersimpan. Pelanggaran: ' + result.violations + '/3.';
        }).catch(function () {
            unreportedDeparture = true;
            status.textContent = 'Tidak dapat memverifikasi sesi. Tetap di halaman sampai koneksi pulih.';
        }).finally(function () { violationPending = false; });
    }
    document.addEventListener('visibilitychange', reportLeaving);
    window.addEventListener('online', function () {
        flushPendingAnswers();
        if (unreportedDeparture) reportLeaving(true);
    });
})();
</script>
@endpush
