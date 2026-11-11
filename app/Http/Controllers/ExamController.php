<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use App\Services\ExamEligibilityService;
use App\Services\ExamResultExportService;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class ExamController extends Controller
{
    private const TYPES = [
        'general' => 'Tes Umum',
        'language' => 'Tes Bahasa',
        'ict' => 'Tes ICT',
    ];

    public function adminIndex()
    {
        return view('admin.ppdb.exams', [
            'exams' => Exam::withCount('questions')->with('attempts.account')->orderBy('id')->get(),
            'types' => self::TYPES,
        ]);
    }

    public function exportResults(Exam $exam, ExamResultExportService $exportService)
    {
        try {
            $path = $exportService->generateXlsx($exam);

            return response()->download($path, $exportService->filename($exam), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-store, no-cache',
            ])->deleteFileAfterSend(true);
        } catch (\RuntimeException $exception) {
            Log::error('Exam results export failed.', [
                'exam_id' => $exam->id,
                'exception' => $exception,
            ]);

            return redirect()->route('admin.ppdb.exams')
                ->with('error', 'Gagal mengekspor laporan hasil ujian. Silakan coba kembali.');
        }
    }

    public function saveExam(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:general,language,ict'],
            'title' => ['required', 'string', 'max:150'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['required_if:published,1', 'nullable', 'date'],
            'ends_at' => ['required_if:published,1', 'nullable', 'date', 'after:starts_at'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:300'],
            'published' => ['nullable', 'boolean'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_options' => ['nullable', 'boolean'],
            'results_released' => ['nullable', 'boolean'],
        ]);

        Exam::updateOrCreate(
            ['type' => $data['type']],
            [
                'title' => $data['title'],
                'instructions' => $data['instructions'] ?? '',
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'duration_minutes' => $data['duration_minutes'],
                'published' => $request->boolean('published'),
                'shuffle_questions' => $request->boolean('shuffle_questions'),
                'shuffle_options' => $request->boolean('shuffle_options'),
                'results_released' => $request->boolean('results_released'),
            ]
        );

        return redirect()->route('admin.ppdb.exams')->with('success', 'Pengaturan ujian berhasil disimpan.');
    }

    public function questionTemplate()
    {
        $path = tempnam(sys_get_temp_dir(), 'ppdb-exam-template-');
        if ($path === false) {
            abort(500, 'Gagal menyiapkan template Word.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            abort(500, 'Gagal membuat template Word.');
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>');
        $zip->addFromString('word/document.xml', $this->templateDocumentXml());
        $zip->close();

        return response()->download($path, 'template-soal-ujian-ppdb.docx')->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request, Exam $exam)
    {
        $request->validate([
            'questions_file' => ['required', 'file', 'extensions:docx', 'max:10240'],
        ]);

        try {
            $questions = $this->readQuestionsFromDocx($request->file('questions_file')->getRealPath());
        } catch (\RuntimeException $exception) {
            return back()->withInput()->withErrors(['questions_file' => $exception->getMessage()]);
        }

        if (count($questions) > 500) {
            return back()->withErrors(['questions_file' => 'Maksimal 500 soal per ujian.']);
        }

        $key = (string) Str::uuid();
        session()->put('exam_import_preview', [
            'key' => $key,
            'exam_id' => $exam->id,
            'questions' => $questions,
        ]);

        return redirect()->route('admin.ppdb.exams')
            ->with('exam_import_preview_key', $key)
            ->with('success', count($questions) . ' soal berhasil dibaca. Periksa pratinjau sebelum mengganti bank soal.');
    }

    public function confirmImport(Request $request, Exam $exam)
    {
        $request->validate(['preview_key' => ['required', 'string', 'uuid']]);
        $preview = session('exam_import_preview');

        if (!is_array($preview)
            || !hash_equals((string) ($preview['key'] ?? ''), $request->input('preview_key'))
            || (int) ($preview['exam_id'] ?? 0) !== $exam->id
            || empty($preview['questions'])) {
            return redirect()->route('admin.ppdb.exams')->with('error', 'Pratinjau impor tidak ditemukan atau sudah kedaluwarsa. Silakan unggah ulang.');
        }

        if ($exam->attempts()->exists()) {
            return redirect()->route('admin.ppdb.exams')->with('error', 'Bank soal tidak dapat diganti setelah peserta mulai mengerjakan ujian.');
        }

        DB::transaction(function () use ($exam, $preview) {
            $exam->questions()->delete();
            foreach ($preview['questions'] as $index => $question) {
                $exam->questions()->create([
                    'position' => $index + 1,
                    'question' => $question['question'],
                    'options' => $question['options'],
                    'correct_option' => $question['correct_option'],
                ]);
            }
        });

        session()->forget('exam_import_preview');

        return redirect()->route('admin.ppdb.exams')->with('success', count($preview['questions']) . ' soal berhasil diimpor.');
    }

    public function reopenAttempt(ExamAttempt $attempt)
    {
        if ($attempt->status !== 'terminated') {
            return back()->with('error', 'Hanya ujian yang dihentikan sistem yang dapat dibuka kembali.');
        }

        $history = $attempt->violation_log ?? [];
        $history[] = [
            'event' => 'reopened_by_admin',
            'occurred_at' => now()->toIso8601String(),
            'admin' => session('admin_user.username', 'Administrator'),
        ];
        $attempt->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'ends_at' => now()->addMinutes($attempt->exam->duration_minutes),
            'completed_at' => null,
            'violations' => 0,
            'last_violation_at' => null,
            'violation_log' => $history,
            'score' => null,
        ]);

        return redirect()->route('admin.ppdb.exams')->with('success', 'Ujian dibuka kembali dengan durasi penuh. Jawaban sebelumnya tetap tersimpan.');
    }

    public function studentIndex(ExamEligibilityService $eligibility)
    {
        [$account, $registration] = $this->studentRecords();
        $missing = $eligibility->missingRequirements($registration);
        $major = $registration?->form_data['major'] ?? null;
        $allowedTypes = match ($major) {
            'reguler', 'tahfidz' => ['general'],
            'bahasa' => ['general', 'language'],
            'ict' => ['general', 'ict'],
            default => [],
        };

        $exams = Exam::withCount('questions')
            ->whereIn('type', $allowedTypes)
            ->orderBy('id')
            ->get()
            ->map(function (Exam $exam) use ($account) {
                $exam->attempt = $account
                    ? $exam->attempts()->where('account_id', $account->id)->first()
                    : null;
                return $exam;
            });

        return response()->view('ppdb.exams', [
            'exams' => $exams,
            'missingRequirements' => $missing,
            'types' => self::TYPES,
            'eligible' => empty($missing),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function start(Request $request, Exam $exam, ExamEligibilityService $eligibility)
    {
        [$account, $registration] = $this->studentRecords();
        abort_unless($account && $registration, 403);
        abort_unless(in_array($exam->type, $this->allowedTypes($registration), true), 403);

        $missing = $eligibility->missingRequirements($registration);
        if ($missing) {
            return redirect()->route('exams.index')->with('warning', 'Selesaikan semua persyaratan PPDB sebelum memulai ujian.');
        }
        if (!$exam->published || !$exam->questions()->exists()) {
            return redirect()->route('exams.index')->with('warning', 'Ujian belum tersedia.');
        }
        if (!$exam->starts_at || !$exam->ends_at || now()->lt($exam->starts_at) || now()->gt($exam->ends_at)) {
            return redirect()->route('exams.index')->with('warning', 'Ujian hanya dapat dimulai pada jadwal yang ditentukan.');
        }

        $attempt = ExamAttempt::firstOrCreate(
            ['exam_id' => $exam->id, 'account_id' => $account->id],
            $this->newAttemptData($exam)
        );

        if ($attempt->ends_at->isPast() && in_array($attempt->status, ['in_progress', 'suspended'], true)) {
            $this->finishAttempt($attempt, 'submitted');
        }
        if (in_array($attempt->status, ['submitted', 'terminated'], true)) {
            return redirect()->route('exams.index')->with('info', 'Ujian ini sudah selesai.');
        }

        session(['exam_mode' => true]);
        return redirect()->route('exams.take', $exam);
    }

    public function take(Exam $exam, ExamEligibilityService $eligibility)
    {
        [$account, $registration] = $this->studentRecords();
        abort_unless($account && $registration, 403);
        abort_unless(in_array($exam->type, $this->allowedTypes($registration), true), 403);

        $missing = $eligibility->missingRequirements($registration);
        if ($missing) {
            return redirect()->route('exams.index')->with('warning', 'Persyaratan ujian belum lengkap.');
        }

        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('account_id', $account->id)->firstOrFail();
        if ($attempt->ends_at->isPast() && in_array($attempt->status, ['in_progress', 'suspended'], true)) {
            $this->finishAttempt($attempt, 'submitted');
            $attempt->refresh();
        }

        if ($attempt->status === 'submitted' || $attempt->status === 'terminated') {
            return redirect()->route('exams.index')->with('info', 'Ujian ini sudah berakhir.');
        }

        session(['exam_mode' => true]);
        $questionsById = $exam->questions()->whereIn('id', $attempt->question_order ?? [])->get()->keyBy('id');
        $questions = collect($attempt->question_order ?? [])
            ->map(function ($id) use ($questionsById, $attempt) {
                $question = $questionsById->get($id);
                if (!$question) {
                    return null;
                }
                $options = [];
                foreach ($attempt->option_orders[$question->id] ?? array_keys($question->options) as $index => $optionKey) {
                    if (isset($question->options[$optionKey])) {
                        $options[] = [
                            'key' => $optionKey,
                            'label' => chr(65 + $index),
                            'text' => $question->options[$optionKey],
                        ];
                    }
                }
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'options' => $options,
                ];
            })
            ->filter()
            ->values();

        return response()->view('ppdb.exam-take', [
            'exam' => $exam,
            'attempt' => $attempt,
            'questions' => $questions,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function saveAnswer(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'answer' => ['nullable', 'in:A,B,C,D'],
        ]);
        $attempt = $this->ownedAttempt($exam);
        if (!$this->ensureActive($attempt)) {
            return response()->json(['message' => 'Sesi ujian tidak aktif atau waktu telah habis.'], 409);
        }

        $questionId = (string) $data['question_id'];
        abort_unless(in_array($questionId, array_map('strval', $attempt->question_order ?? []), true), 422);
        $question = ExamQuestion::where('exam_id', $exam->id)->findOrFail($data['question_id']);
        $answer = $data['answer'] ?? null;
        abort_if($answer !== null && !array_key_exists($answer, $question->options), 422);

        $answers = $attempt->answers ?? [];
        if ($answer === null) {
            unset($answers[$questionId]);
        } else {
            $answers[$questionId] = $answer;
        }
        $attempt->update(['answers' => $answers]);

        return response()->json(['saved' => true, 'saved_at' => now()->toIso8601String()]);
    }

    public function violation(Exam $exam)
    {
        $attempt = $this->ownedAttempt($exam);
        if (!$this->ensureActive($attempt)) {
            return response()->json(['status' => $attempt->status], 409);
        }
        if ($attempt->last_violation_at && $attempt->last_violation_at->diffInSeconds(now()) < 2) {
            return response()->json(['status' => $attempt->status, 'violations' => $attempt->violations]);
        }

        $violations = $attempt->violations + 1;
        $status = $violations > 3 ? 'terminated' : 'suspended';
        $violationLog = $attempt->violation_log ?? [];
        $violationLog[] = [
            'event' => 'visibility_hidden',
            'occurred_at' => now()->toIso8601String(),
            'violation' => $violations,
        ];
        $attempt->update([
            'violations' => $violations,
            'last_violation_at' => now(),
            'violation_log' => $violationLog,
            'status' => $status,
            'completed_at' => $status === 'terminated' ? now() : null,
        ]);
        if ($status === 'terminated') {
            $this->calculateScore($attempt);
            session()->forget('exam_mode');
        }

        return response()->json(['status' => $status, 'violations' => $violations]);
    }

    public function resume(Exam $exam)
    {
        $attempt = $this->ownedAttempt($exam);
        if ($attempt->status !== 'suspended' || $attempt->violations > 3 || $attempt->ends_at->isPast()) {
            return redirect()->route('exams.index')->with('warning', 'Sesi ujian tidak dapat dilanjutkan.');
        }
        $attempt->update(['status' => 'in_progress']);
        session(['exam_mode' => true]);

        return redirect()->route('exams.take', $exam);
    }

    public function submit(Exam $exam)
    {
        $attempt = $this->ownedAttempt($exam);
        if (in_array($attempt->status, ['in_progress', 'suspended'], true) && $attempt->ends_at->isPast()) {
            $this->finishAttempt($attempt, 'submitted');
            session()->forget('exam_mode');
            return redirect()->route('exams.index')->with('success', 'Waktu ujian berakhir. Jawaban yang tersimpan telah dikirim.');
        }
        if (!$this->ensureActive($attempt)) {
            return redirect()->route('exams.index')->with('warning', 'Sesi ujian sudah tidak aktif.');
        }
        $this->finishAttempt($attempt, 'submitted');
        session()->forget('exam_mode');

        return redirect()->route('exams.index')->with('success', 'Jawaban ujian berhasil dikirim.');
    }

    private function studentRecords(): array
    {
        $accountId = session('account_id');
        $email = session('email');
        $account = $accountId
            ? PpdbAccount::with('registration')->where('id', $accountId)->orWhere('remote_id', $accountId)->first()
            : PpdbAccount::with('registration')->where('email', $email)->first();

        return [$account, $account?->registration];
    }

    private function allowedTypes(PpdbRegistration $registration): array
    {
        return match ($registration->form_data['major'] ?? null) {
            'reguler', 'tahfidz' => ['general'],
            'bahasa' => ['general', 'language'],
            'ict' => ['general', 'ict'],
            default => [],
        };
    }

    private function newAttemptData(Exam $exam): array
    {
        $questions = $exam->questions()->get();
        if ($exam->shuffle_questions) {
            $questions = $questions->shuffle()->values();
        }
        $optionOrders = [];
        foreach ($questions as $question) {
            $keys = array_keys($question->options);
            if ($exam->shuffle_options) {
                shuffle($keys);
            }
            $optionOrders[$question->id] = $keys;
        }
        $startedAt = now();
        $endsAt = $startedAt->copy()->addMinutes($exam->duration_minutes);
        if ($exam->ends_at && $exam->ends_at->lt($endsAt)) {
            $endsAt = $exam->ends_at;
        }

        return [
            'status' => 'in_progress',
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
            'question_order' => $questions->pluck('id')->all(),
            'option_orders' => $optionOrders,
            'answers' => [],
        ];
    }

    private function ownedAttempt(Exam $exam): ExamAttempt
    {
        $accountId = session('account_id');
        abort_unless($accountId, 403);

        return ExamAttempt::where('exam_id', $exam->id)
            ->where('account_id', $accountId)
            ->firstOrFail();
    }

    private function ensureActive(ExamAttempt $attempt): bool
    {
        if ($attempt->status !== 'in_progress') {
            return false;
        }
        if ($attempt->ends_at->isPast()) {
            $this->finishAttempt($attempt, 'submitted');
            return false;
        }
        return true;
    }

    private function finishAttempt(ExamAttempt $attempt, string $status): void
    {
        $attempt->update(['status' => $status, 'completed_at' => now()]);
        $this->calculateScore($attempt);
    }

    private function calculateScore(ExamAttempt $attempt): void
    {
        $questions = ExamQuestion::where('exam_id', $attempt->exam_id)
            ->whereIn('id', $attempt->question_order ?? [])
            ->get();
        $answers = $attempt->answers ?? [];
        $correct = $questions->filter(fn ($question) => ($answers[$question->id] ?? null) === $question->correct_option)->count();
        $score = $questions->isEmpty() ? 0 : round(($correct / $questions->count()) * 100, 2);
        $attempt->update(['score' => $score]);
    }

    private function readQuestionsFromDocx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('File Word tidak dapat dibuka. Gunakan template .docx yang disediakan.');
        }

        $stat = $zip->statName('word/document.xml');
        if (!$stat || $stat['size'] > 10 * 1024 * 1024) {
            $zip->close();
            throw new \RuntimeException('Dokumen Word tidak valid atau ukurannya terlalu besar.');
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (!is_string($xml)) {
            throw new \RuntimeException('Isi dokumen Word tidak dapat dibaca.');
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (!$loaded) {
            throw new \RuntimeException('Struktur dokumen Word tidak valid.');
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $tables = $xpath->query('//w:tbl');
        $questions = [];
        $foundTemplateTable = false;

        foreach ($tables as $table) {
            $rows = $xpath->query('./w:tr', $table);
            if (!$rows || $rows->length < 2) {
                continue;
            }
            $header = $this->tableRow($xpath, $rows->item(0));
            $normalized = array_map(fn ($value) => Str::lower(preg_replace('/[^a-z0-9]/i', '', $value)), $header);
            $expected = ['nomor', 'pertanyaan', 'a', 'b', 'c', 'd', 'kunci'];
            if (array_slice($normalized, 0, 7) !== $expected) {
                continue;
            }
            $foundTemplateTable = true;

            $seenQuestions = [];
            for ($rowIndex = 1; $rowIndex < $rows->length; $rowIndex++) {
                $cells = $this->tableRow($xpath, $rows->item($rowIndex));
                if (count(array_filter($cells, fn ($cell) => trim($cell) !== '')) === 0) {
                    continue;
                }
                if (count($cells) < 7) {
                    throw new \RuntimeException('Baris ' . ($rowIndex + 1) . ' tidak memiliki 7 kolom lengkap.');
                }
                $number = trim($cells[0]);
                $questionText = trim($cells[1]);
                if (!ctype_digit($number) || (int) $number !== count($questions) + 1) {
                    throw new \RuntimeException('Kolom Nomor harus berurutan mulai dari 1 dan tidak boleh duplikat.');
                }
                $normalizedQuestion = Str::lower(preg_replace('/\s+/', ' ', $questionText));
                if (isset($seenQuestions[$normalizedQuestion])) {
                    throw new \RuntimeException('Soal duplikat ditemukan pada baris ' . ($rowIndex + 1) . '.');
                }
                $seenQuestions[$normalizedQuestion] = true;
                $options = ['A' => trim($cells[2]), 'B' => trim($cells[3]), 'C' => trim($cells[4]), 'D' => trim($cells[5])];
                $correct = strtoupper(trim($cells[6]));
                if ($questionText === '' || in_array('', $options, true) || !array_key_exists($correct, $options)) {
                    throw new \RuntimeException('Baris ' . ($rowIndex + 1) . ' harus berisi soal, empat pilihan, dan kunci A/B/C/D.');
                }
                $questions[] = [
                    'question' => $questionText,
                    'options' => $options,
                    'correct_option' => $correct,
                ];
            }
        }

        if (!$foundTemplateTable || !$questions) {
            throw new \RuntimeException('Tabel soal tidak ditemukan atau belum berisi soal. Unduh dan gunakan template Word resmi.');
        }

        return $questions;
    }

    private function tableRow(DOMXPath $xpath, \DOMNode $row): array
    {
        $cells = $xpath->query('./w:tc', $row);
        $values = [];
        foreach ($cells as $cell) {
            $texts = $xpath->query('.//w:t', $cell);
            $value = '';
            foreach ($texts as $text) {
                $value .= $text->textContent;
            }
            $values[] = $value;
        }
        return $values;
    }

    private function templateDocumentXml(): string
    {
        $escape = fn ($value) => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $headers = ['Nomor', 'Pertanyaan', 'A', 'B', 'C', 'D', 'Kunci'];
        $rows = [$headers, ['1', 'Tulis pertanyaan di sini', 'Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'A']];
        $tableRows = '';
        foreach ($rows as $row) {
            $cells = '';
            foreach ($row as $value) {
                $cells .= '<w:tc><w:tcPr><w:tcW w:w="1800" w:type="dxa"/></w:tcPr><w:p><w:r><w:t xml:space="preserve">' . $escape($value) . '</w:t></w:r></w:p></w:tc>';
            }
            $tableRows .= '<w:tr>' . $cells . '</w:tr>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
            . '<w:p><w:r><w:t>Template Import Soal Ujian PPDB</w:t></w:r></w:p>'
            . '<w:p><w:r><w:t>Isi satu baris untuk setiap soal. Jangan mengubah urutan atau nama kolom. Pilihan wajib A-D dan kunci berisi A, B, C, atau D.</w:t></w:r></w:p>'
            . '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/></w:tblPr>' . $tableRows . '</w:tbl>'
            . '<w:sectPr><w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/><w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720"/></w:sectPr>'
            . '</w:body></w:document>';
    }
}
