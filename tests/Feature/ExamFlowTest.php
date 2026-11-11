<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use App\Services\ExamResultExportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class ExamFlowTest extends TestCase
{
    public function test_admin_can_configure_exam_and_import_docx_questions(): void
    {
        $exam = null;
        $docx = $this->makeDocx();
        $duplicateDocx = null;
        try {
            $this->withSession(['admin_api_token' => 'admin-token', 'is_admin' => true])
                ->get('/admin/ppdb/exams/template')
                ->assertOk()
                ->assertDownload('template-soal-ujian-ppdb.docx');

            $this->withSession(['admin_api_token' => 'admin-token', 'is_admin' => true])
                ->post('/admin/ppdb/exams', [
                    'type' => 'general',
                    'title' => 'Tes Umum',
                    'instructions' => 'Kerjakan dengan teliti.',
                    'duration_minutes' => 45,
                    'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
                    'ends_at' => now()->addHour()->format('Y-m-d H:i:s'),
                    'published' => '1',
                    'shuffle_questions' => '1',
                    'shuffle_options' => '1',
                ])
                ->assertRedirect('/admin/ppdb/exams');

            $exam = Exam::where('type', 'general')->firstOrFail();
            $upload = new UploadedFile(
                $docx,
                'questions.docx',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                null,
                true
            );

            $this->withSession(['admin_api_token' => 'admin-token', 'is_admin' => true])
                ->post(route('admin.ppdb.exams.preview', $exam), ['questions_file' => $upload])
                ->assertRedirect('/admin/ppdb/exams');

            $previewKey = session('exam_import_preview_key');
            $this->assertNotEmpty($previewKey);
            $this->assertSame('Apa warna langit cerah?', session('exam_import_preview.questions.0.question'));

            $this->withSession(['admin_api_token' => 'admin-token', 'is_admin' => true])
                ->post(route('admin.ppdb.exams.import', $exam), ['preview_key' => $previewKey])
                ->assertRedirect('/admin/ppdb/exams')
                ->assertSessionHas('success');

            $this->assertSame(1, $exam->questions()->count());
            $this->assertSame('B', $exam->questions()->first()->correct_option);

            $duplicateDocx = $this->makeDocx([
                ['Nomor', 'Pertanyaan', 'A', 'B', 'C', 'D', 'Kunci'],
                ['1', 'Soal sama?', 'Satu', 'Dua', 'Tiga', 'Empat', 'A'],
                ['2', 'soal   SAMA?', 'Satu', 'Dua', 'Tiga', 'Empat', 'B'],
            ]);
            $invalidUpload = new UploadedFile(
                $duplicateDocx,
                'duplicate-questions.docx',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                null,
                true
            );
            $this->withSession(['admin_api_token' => 'admin-token', 'is_admin' => true])
                ->post(route('admin.ppdb.exams.preview', $exam), ['questions_file' => $invalidUpload])
                ->assertSessionHasErrors('questions_file');
            $this->assertSame(1, $exam->questions()->count());
        } finally {
            if ($exam) {
                $exam->delete();
            }
            if (File::exists($docx)) {
                File::delete($docx);
            }
            if ($duplicateDocx && File::exists($duplicateDocx)) {
                File::delete($duplicateDocx);
            }
        }
    }

    public function test_student_gets_only_major_specific_exams_after_meeting_requirements(): void
    {
        [$account, $registration] = $this->createEligibleStudent('bahasa');
        $exams = collect();
        try {
            foreach (['general' => 'Tes Umum', 'language' => 'Tes Bahasa', 'ict' => 'Tes ICT'] as $type => $title) {
                $exam = Exam::create([
                    'type' => $type,
                    'title' => $title,
                    'duration_minutes' => 30,
                    'published' => true,
                    'starts_at' => now()->subMinute(),
                    'ends_at' => now()->addHour(),
                    'shuffle_questions' => false,
                    'shuffle_options' => false,
                ]);
                $exam->questions()->create([
                    'position' => 1,
                    'question' => 'Contoh?',
                    'options' => ['A' => 'Satu', 'B' => 'Dua', 'C' => 'Tiga', 'D' => 'Empat'],
                    'correct_option' => 'A',
                ]);
                $exams->push($exam);
            }

            $response = $this->withSession([
                'api_token' => 'local_auth_' . $account->id,
                'account_id' => $account->id,
                'email' => $account->email,
            ])->get('/exams');

            $response->assertOk();
            $response->assertSee('Tes Umum');
            $response->assertSee('Tes Bahasa');
            $response->assertDontSee('Tes ICT');

            $this->withSession([
                'api_token' => 'local_auth_' . $account->id,
                'account_id' => $account->id,
                'email' => $account->email,
            ])->get('/dashboard')->assertRedirect('/exams');

            $formData = $registration->form_data;
            $formData['identity']['family_card_number'] = '';
            $registration->update(['form_data' => $formData]);
            $this->withSession([
                'api_token' => 'local_auth_' . $account->id,
                'account_id' => $account->id,
                'email' => $account->email,
            ])->post(route('exams.start', $exams->firstWhere('type', 'general')))
                ->assertRedirect(route('exams.index'));
            $this->assertDatabaseMissing('exam_attempts', [
                'exam_id' => $exams->firstWhere('type', 'general')->id,
                'account_id' => $account->id,
            ]);
        } finally {
            $exams->each->delete();
            $registration->delete();
            $account->delete();
        }
    }

    public function test_admin_can_export_exam_results_to_excel(): void
    {
        [$account, $registration] = $this->createEligibleStudent('bahasa');
        $exam = Exam::create([
            'type' => 'language',
            'title' => 'Tes Bahasa',
            'duration_minutes' => 30,
            'published' => true,
            'shuffle_questions' => false,
            'shuffle_options' => false,
        ]);
        $exportPath = null;

        try {
            $account->update(['phone' => '081234567890']);
            ExamAttempt::create([
                'exam_id' => $exam->id,
                'account_id' => $account->id,
                'status' => 'submitted',
                'started_at' => now()->subMinutes(10),
                'ends_at' => now()->addMinutes(20),
                'completed_at' => now(),
                'violations' => 1,
                'question_order' => [],
                'option_orders' => [],
                'answers' => [],
                'score' => 85,
            ]);

            $this->withSession(['admin_api_token' => 'admin-token', 'is_admin' => true])
                ->get(route('admin.ppdb.exams.results.export', $exam))
                ->assertOk()
                ->assertDownload();

            $exportPath = app(ExamResultExportService::class)->generateXlsx($exam);
            $zip = new ZipArchive();
            $this->assertTrue($zip->open($exportPath));
            $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();

            $this->assertStringContainsString('Siswa Ujian', $worksheet);
            $this->assertStringContainsString($account->email, $worksheet);
            $this->assertStringContainsString('081234567890', $worksheet);
            $this->assertStringContainsString('Bahasa', $worksheet);
            $this->assertStringContainsString('Tes Bahasa', $worksheet);
            $this->assertStringContainsString('Selesai', $worksheet);
            $this->assertStringContainsString('85.00', $worksheet);
        } finally {
            if ($exportPath && File::exists($exportPath)) {
                File::delete($exportPath);
            }
            $exam->delete();
            $registration->delete();
            $account->delete();
        }
    }

    public function test_student_returns_to_normal_portal_after_submitting_all_required_exams(): void
    {
        [$account, $registration] = $this->createEligibleStudent('bahasa');
        $exams = collect();
        try {
            foreach (['general' => 'Tes Umum', 'language' => 'Tes Bahasa'] as $type => $title) {
                $exam = Exam::create([
                    'type' => $type,
                    'title' => $title,
                    'duration_minutes' => 30,
                    'published' => true,
                    'starts_at' => now()->subMinute(),
                    'ends_at' => now()->addHour(),
                    'shuffle_questions' => false,
                    'shuffle_options' => false,
                ]);
                $question = $exam->questions()->create([
                    'position' => 1,
                    'question' => 'Contoh?',
                    'options' => ['A' => 'Satu', 'B' => 'Dua', 'C' => 'Tiga', 'D' => 'Empat'],
                    'correct_option' => 'A',
                ]);
                ExamAttempt::create([
                    'exam_id' => $exam->id,
                    'account_id' => $account->id,
                    'status' => 'submitted',
                    'started_at' => now()->subMinutes(10),
                    'ends_at' => now()->addMinutes(20),
                    'completed_at' => now(),
                    'question_order' => [$question->id],
                    'option_orders' => [$question->id => ['A', 'B', 'C', 'D']],
                    'answers' => [$question->id => 'A'],
                    'score' => 100,
                ]);
                $exams->push($exam);
            }

            $this->withSession([
                'api_token' => 'local_auth_' . $account->id,
                'account_id' => $account->id,
                'email' => $account->email,
                'exam_mode' => true,
            ])->get('/dashboard')
                ->assertOk()
                ->assertSessionMissing('exam_mode');
        } finally {
            $exams->each->delete();
            $registration->delete();
            $account->delete();
        }
    }

    public function test_student_answers_are_saved_and_departure_suspends_then_terminates_after_fourth_violation(): void
    {
        [$account, $registration] = $this->createEligibleStudent('ict');
        $exam = null;
        try {
            $exam = Exam::create([
                'type' => 'general',
                'title' => 'Tes Umum',
                'duration_minutes' => 30,
                'published' => true,
                'starts_at' => now()->subMinute(),
                'ends_at' => now()->addHour(),
                'shuffle_questions' => false,
                'shuffle_options' => false,
            ]);
            $question = $exam->questions()->create([
                'position' => 1,
                'question' => 'Dua tambah dua?',
                'options' => ['A' => '3', 'B' => '4', 'C' => '5', 'D' => '6'],
                'correct_option' => 'B',
            ]);
            $session = [
                'api_token' => 'local_auth_' . $account->id,
                'account_id' => $account->id,
                'email' => $account->email,
                'full_name' => $account->full_name,
            ];

            $this->withSession($session)
                ->post(route('exams.start', $exam))
                ->assertRedirect(route('exams.take', $exam));
            $this->withSession($session)->get(route('exams.take', $exam))
                ->assertOk()
                ->assertSee('Dua tambah dua?')
                ->assertDontSee('correct_option');
            $this->withSession($session)->get('/dashboard')->assertRedirect(route('exams.take', $exam));

            $this->withSession($session)->postJson(route('exams.answers', $exam), [
                'question_id' => $question->id,
                'answer' => 'B',
            ])->assertOk()->assertJsonPath('saved', true);

            for ($violation = 1; $violation <= 4; $violation++) {
                $response = $this->withSession($session)->postJson(route('exams.violation', $exam));
                $expectedStatus = $violation > 3 ? 'terminated' : 'suspended';
                $response->assertOk()->assertJsonPath('status', $expectedStatus);

                if ($violation < 4) {
                    $this->withSession($session)->post(route('exams.resume', $exam))
                        ->assertRedirect(route('exams.take', $exam));
                    $attempt = ExamAttempt::where('exam_id', $exam->id)->firstOrFail();
                    $attempt->update(['last_violation_at' => now()->subSeconds(3)]);
                }
            }

            $attempt = ExamAttempt::where('exam_id', $exam->id)->firstOrFail();
            $this->assertSame('terminated', $attempt->status);
            $this->assertSame(4, $attempt->violations);
            $this->assertSame('B', $attempt->answers[(string) $question->id] ?? $attempt->answers[$question->id]);
            $this->assertSame(100.0, (float) $attempt->score);

            $this->withSession($session)->get('/dashboard')
                ->assertRedirect('/exams');
            $this->withSession($session)->get('/exams')->assertDontSee('Nilai:');
            $this->withSession(['admin_api_token' => 'admin-token', 'is_admin' => true])
                ->get(route('admin.ppdb.exams'))
                ->assertOk()
                ->assertSee('Dua tambah dua?')
                ->assertSee('Jawaban peserta');

            $exam->update(['results_released' => true]);
            $this->withSession($session)->get('/exams')->assertSee('Nilai: 100.00');

            $this->withSession(['admin_api_token' => 'admin-token', 'is_admin' => true])
                ->post(route('admin.ppdb.exams.attempts.reopen', $attempt))
                ->assertRedirect('/admin/ppdb/exams')
                ->assertSessionHas('success');

            $attempt->refresh();
            $this->assertSame('in_progress', $attempt->status);
            $this->assertSame(0, $attempt->violations);
            $this->assertNull($attempt->score);
            $this->assertSame('B', $attempt->answers[(string) $question->id] ?? $attempt->answers[$question->id]);
            $violationLog = $attempt->violation_log;
            $this->assertSame('reopened_by_admin', $violationLog[count($violationLog) - 1]['event']);
            $this->assertTrue($attempt->ends_at->isFuture());
        } finally {
            if ($exam) {
                $exam->delete();
            }
            $registration->delete();
            $account->delete();
        }
    }

    private function createEligibleStudent(string $major): array
    {
        $email = 'exam.' . Str::uuid() . '@example.com';
        $account = PpdbAccount::create([
            'full_name' => 'Siswa Ujian',
            'email' => $email,
            'password' => Hash::make('secret123'),
        ]);
        $registration = PpdbRegistration::create([
            'account_id' => $account->id,
            'payment_status' => 'paid',
            'payment_amount' => 400000,
            'registration_status' => 'pending',
            'form_data' => [
                'full_name' => 'Siswa Ujian',
                'first_name' => 'Siswa',
                'last_name' => 'Ujian',
                'nik' => '3201012345678901',
                'nisn' => '1234567890',
                'major' => $major,
                'school_origin' => 'SMP Uji',
                'school_origin_address' => 'Jl. Sekolah 1',
                'identity' => [
                    'family_card_number' => '3201012345678901',
                    'gender' => 'Laki-laki',
                    'religion' => 'Islam',
                    'place_of_birth' => 'Bandung',
                    'date_of_birth' => '2008-05-01',
                    'birth_order' => 1,
                    'siblings_count' => 2,
                ],
                'address' => [
                    'street_address' => 'Jl. Ujian 1',
                    'rt' => '01',
                    'rw' => '02',
                    'village' => 'Desa Uji',
                    'district' => 'Kecamatan Uji',
                    'postal_code' => '12345',
                    'residence_type' => 'Rumah sendiri',
                    'transportation_mode' => 'Kendaraan pribadi',
                ],
                'contact' => [
                    'mobile_number' => '081234567890',
                    'whatsapp_number' => '081234567890',
                    'email' => $email,
                ],
                'student_parents' => [
                    [
                        'relationship_type' => 1,
                        'parent' => [
                            'status' => 'ada',
                            'full_name' => 'Ayah Uji',
                            'nik' => '3201012345678902',
                            'birth_year' => '1980',
                            'education_code' => 'SMA',
                            'occupation_code' => 'Karyawan',
                            'income_code' => 'Sedang',
                        ],
                    ],
                    [
                        'relationship_type' => 2,
                        'parent' => [
                            'status' => 'ada',
                            'full_name' => 'Ibu Uji',
                            'nik' => '3201012345678903',
                            'birth_year' => '1982',
                            'education_code' => 'SMA',
                            'occupation_code' => 'Wiraswasta',
                            'income_code' => 'Sedang',
                        ],
                    ],
                ],
                'documents' => [
                    'kk' => 'uploads/kk.pdf',
                    'akta' => 'uploads/akta.pdf',
                    'nisn' => 'uploads/nisn.pdf',
                    'foto' => 'uploads/foto.jpg',
                ],
            ],
        ]);

        return [$account, $registration];
    }

    private function makeDocx(?array $rows = null): string
    {
        $path = tempnam(sys_get_temp_dir(), 'ppdb-exam-test-');
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        $rows ??= [
            ['Nomor', 'Pertanyaan', 'A', 'B', 'C', 'D', 'Kunci'],
            ['1', 'Apa warna langit cerah?', 'Merah', 'Biru', 'Hijau', 'Hitam', 'B'],
        ];
        $table = '';
        foreach ($rows as $row) {
            $cells = '';
            foreach ($row as $value) {
                $cells .= '<w:tc><w:p><w:r><w:t>' . htmlspecialchars($value, ENT_XML1, 'UTF-8') . '</w:t></w:r></w:p></w:tc>';
            }
            $table .= '<w:tr>' . $cells . '</w:tr>';
        }
        $document = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
            . '<w:tbl>' . $table . '</w:tbl><w:sectPr/></w:body></w:document>';
        $zip->addFromString('word/document.xml', $document);
        $zip->close();
        return $path;
    }
}
