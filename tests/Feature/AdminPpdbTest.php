<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class AdminPpdbTest extends TestCase
{
    /**
     * Test admin login page can be rendered.
     */
    public function test_admin_login_page_renders_successfully(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('SMPS2 Al-Muhajirin');
        $response->assertSee('panitia PPDB');
    }

    /**
     * Test unauthenticated access to admin dashboard redirects to admin login.
     */
    public function test_unauthenticated_admin_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/ppdb');
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('warning');
    }

    /**
     * Test successful admin login stores token and redirects to admin dashboard.
     */
    public function test_admin_login_success(): void
    {
        Http::fake([
            '*/api/auth/login' => Http::response([
                'access_token' => 'admin-jwt-token-12345',
                'token_type' => 'bearer',
            ], 200),
            '*/api/profile/me' => Http::response([
                'id' => 'admin-uuid-1',
                'username' => 'admin',
                'role' => 'admin',
                'roles' => ['admin'],
            ], 200),
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin/ppdb');
        $this->assertEquals('admin-jwt-token-12345', session('admin_api_token'));
        $this->assertTrue(session('is_admin'));
    }

    /**
     * Test admin can login using local seeded MySQL account when API backend is offline.
     */
    public function test_admin_can_login_with_local_seeded_account(): void
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@almuhajirin.sch.id'],
            [
                'name' => 'admin',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        Http::fake([
            '*/api/auth/login' => Http::response(['detail' => 'Service Unavailable'], 503),
        ]);

        $response = $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin/ppdb');
        $this->assertTrue(session('is_admin'));
        $this->assertStringStartsWith('local_admin_', session('admin_api_token'));
    }

    /**
     * Test admin dashboard renders with mock registrations.
     */
    public function test_admin_dashboard_renders_registrations_table(): void
    {
        Http::fake([
            '*/api/ppdb/registrations*' => Http::response([
                [
                    'id' => 'reg-uuid-101',
                    'account_id' => 'acc-uuid-101',
                    'payment_status' => 'pending_verification',
                    'payment_amount' => 250000.0,
                    'payment_proof_path' => '/uploads/ppdb_payments/test.jpg',
                    'registration_status' => 'pending',
                    'form_data' => null,
                    'created_at' => '2026-09-08T10:00:00Z',
                    'account' => [
                        'nik' => '3201012345670001',
                        'full_name' => 'Ahmad Fauzi',
                        'email' => 'ahmad@example.com',
                    ],
                ]
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->get('/admin/ppdb');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('3201012345670001');
    }

    /**
     * Test admin can verify payment.
     */
    public function test_admin_verify_payment(): void
    {
        Http::fake([
            '*/api/ppdb/verify-payment/reg-uuid-101' => Http::response([
                'id' => 'reg-uuid-101',
                'payment_status' => 'paid',
                'payment_amount' => 250000.0,
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->post('/admin/ppdb/registrations/reg-uuid-101/verify-payment', [
            'payment_status' => 'paid',
            'payment_amount' => 250000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/ppdb/verify-payment/reg-uuid-101')
                && $request->method() === 'PUT'
                && $request['payment_status'] === 'paid';
        });
    }

    /**
     * Test admin can accept student and migrate to SIAKAD master.
     */
    public function test_admin_accept_student(): void
    {
        Http::fake([
            '*/api/ppdb/accept/reg-uuid-101' => Http::response([
                'message' => 'Calon siswa berhasil diterima',
                'registration_id' => 'reg-uuid-101',
                'student_id' => 'student-uuid-999',
                'user_id' => 'user-uuid-999',
                'username' => '0051234567',
                'full_name' => 'Ahmad Fauzi',
                'role' => 'student',
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->post('/admin/ppdb/registrations/reg-uuid-101/accept');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/ppdb/accept/reg-uuid-101')
                && $request->method() === 'POST';
        });
    }

    /**
     * Test admin logout.
     */
    public function test_admin_logout(): void
    {
        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertNull(session('admin_api_token'));
    }

    /**
     * Test admin dossier show page renders complete parents data, email, and birth order.
     */
    public function test_admin_dossier_renders_parents_and_birth_order(): void
    {
        Http::fake([
            '*/api/ppdb/registrations/reg-uuid-101' => Http::response([
                'id' => 'reg-uuid-101',
                'account_id' => 'acc-uuid-101',
                'payment_status' => 'paid',
                'payment_amount' => 250000.0,
                'payment_proof_path' => '/uploads/ppdb_payments/test.jpg',
                'registration_status' => 'pending',
                'created_at' => '2026-09-08T10:00:00Z',
                'account' => [
                    'nik' => '3201012345670001',
                    'full_name' => 'Ahmad Fauzi Rahman',
                    'email' => 'ahmad@example.com',
                ],
                'form_data' => [
                    'nik' => '3201012345670001',
                    'nisn' => '0051234567',
                    'full_name' => 'Ahmad Fauzi Rahman',
                    'first_name' => 'Ahmad',
                    'last_name' => 'Fauzi Rahman',
                    'identity' => [
                        'family_card_number' => '3201010000000001',
                        'gender' => 'Laki-laki',
                        'religion' => 'Islam',
                        'place_of_birth' => 'Jakarta',
                        'date_of_birth' => '2008-05-14',
                        'birth_order' => 2,
                        'siblings_count' => 3,
                    ],
                    'address' => [
                        'street_address' => 'Jl. Merdeka No. 45',
                        'rt' => '002',
                        'rw' => '005',
                        'village' => 'Sukamaju',
                        'district' => 'Cilodong',
                        'postal_code' => '16415',
                        'residence_type' => 'Bersama Orang Tua',
                        'transportation_mode' => 'Sepeda Motor',
                    ],
                    'contact' => [
                        'phone_number' => '021-77889900',
                        'mobile_number' => '081234567890',
                        'whatsapp_number' => '081234567890',
                        'email' => 'ahmad.fauzi@student.sch.id',
                    ],
                    'student_parents' => [
                        [
                            'relationship_type' => 1,
                            'parent' => [
                                'nik' => '3201011122330001',
                                'full_name' => 'Bambang Sudarsono',
                                'birth_year' => '1978',
                                'education_code' => '05',
                                'occupation_code' => '02',
                                'income_code' => '03',
                                'phone_number' => '081311223344',
                                'whatsapp_number' => '081311223344',
                                'email' => 'bambang.sudarsono@example.com',
                            ],
                        ],
                        [
                            'relationship_type' => 2,
                            'parent' => [
                                'nik' => '3201012233440002',
                                'full_name' => 'Siti Aminah',
                                'birth_year' => '1982',
                                'education_code' => '04',
                                'occupation_code' => '03',
                                'income_code' => '02',
                                'phone_number' => '081399887766',
                                'whatsapp_number' => '081399887766',
                                'email' => 'siti.aminah@example.com',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->get('/admin/ppdb/registrations/reg-uuid-101');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi Rahman');
        $response->assertSee('3201012345670001');
        $response->assertSee('Anak ke-');
        $response->assertSee('2');
        $response->assertSee('3');
        $response->assertSee('Bambang Sudarsono');
        $response->assertSee('bambang.sudarsono@example.com');
        $response->assertSee('PNS / TNI / Polri');
        $response->assertSee('D1 / D2 / D3');
        $response->assertSee('Siti Aminah');
        $response->assertSee('siti.aminah@example.com');
    }

    /**
     * Test admin students module renders.
     */
    public function test_admin_students_module_renders(): void
    {
        Http::fake([
            '*/api/ppdb/registrations*' => Http::response([
                [
                    'id' => 'reg-uuid-101',
                    'payment_status' => 'paid',
                    'registration_status' => 'pending',
                    'account' => [
                        'nik' => '3201012345670001',
                        'full_name' => 'Ahmad Fauzi',
                        'email' => 'ahmad@example.com',
                    ],
                    'form_data' => [
                        'major' => 'tahfidz',
                        'school_origin' => 'SD IT Al-Muhajirin',
                    ],
                ]
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->get('/admin/ppdb/students');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('tahfidz');
    }

    /**
     * Test admin users module renders.
     */
    public function test_admin_users_module_renders(): void
    {
        Http::fake([
            '*/api/ppdb/registrations*' => Http::response([
                [
                    'id' => 'reg-uuid-101',
                    'account_id' => 'acc-101',
                    'payment_status' => 'paid',
                    'registration_status' => 'pending',
                    'account' => [
                        'nik' => '3201012345670001',
                        'full_name' => 'Ahmad Fauzi',
                        'email' => 'ahmad@example.com',
                        'phone' => '081234567890',
                    ],
                    'form_data' => null,
                ]
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->get('/admin/ppdb/users');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('ahmad@example.com');
    }

    /**
     * Test admin documents module renders.
     */
    public function test_admin_documents_module_renders(): void
    {
        Http::fake([
            '*/api/ppdb/registrations*' => Http::response([
                [
                    'id' => 'reg-uuid-101',
                    'payment_status' => 'paid',
                    'payment_proof_path' => '/uploads/proof.jpg',
                    'account' => [
                        'nik' => '3201012345670001',
                        'full_name' => 'Ahmad Fauzi',
                    ],
                    'form_data' => [
                        'major' => 'reguler',
                    ],
                ]
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->get('/admin/ppdb/documents');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('Bukti Pembayaran');
    }

    /**
     * Test admin can view student uploaded documents in dossier and modal.
     */
    public function test_admin_can_view_student_uploaded_documents_in_dossier_and_modal(): void
    {
        $accId = (string) \Illuminate\Support\Str::uuid();
        $regId = (string) \Illuminate\Support\Str::uuid();

        $account = \App\Models\PpdbAccount::create([
            'id' => $accId,
            'nik' => '3201019988776655',
            'full_name' => 'Siti Berkas Nurhaliza',
            'email' => 'siti.berkas.' . uniqid() . '@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $reg = \App\Models\PpdbRegistration::create([
            'id' => $regId,
            'account_id' => $account->id,
            'payment_status' => 'paid',
            'payment_proof_path' => 'uploads/ppdb_payments/proof-sample.jpg',
            'registration_status' => 'pending',
            'sync_status' => 'pending',
            'form_data' => [
                'full_name' => 'Siti Berkas Nurhaliza',
                'nik' => '3201019988776655',
                'documents' => [
                    'kk' => 'uploads/ppdb_documents/kk_sample.pdf',
                    'akta' => 'uploads/ppdb_documents/akta_sample.jpg',
                    'nisn' => 'uploads/ppdb_documents/nisn_sample.jpg',
                    'foto' => 'uploads/ppdb_documents/foto_sample.jpg',
                ],
            ],
        ]);

        try {
            // 1. Cek pada halaman Data Berkas (/admin/ppdb/documents)
            $docResponse = $this->withSession([
                'admin_api_token' => 'admin-jwt-token-12345',
                'is_admin' => true,
            ])->get('/admin/ppdb/documents');

            $docResponse->assertStatus(200);
            $docResponse->assertSee('Siti Berkas Nurhaliza');
            $docResponse->assertSee('docsModalDoc' . $regId);
            $docResponse->assertSee('Kartu Keluarga (KK)');
            $docResponse->assertSee('Akta Kelahiran');
            $docResponse->assertSee('Pas Foto 3x4');

            // 2. Cek pada halaman Detail Siswa / Dossier (/admin/ppdb/registrations/{id})
            $showResponse = $this->withSession([
                'admin_api_token' => 'admin-jwt-token-12345',
                'is_admin' => true,
            ])->get('/admin/ppdb/registrations/' . $reg->id);

            $showResponse->assertStatus(200);
            $showResponse->assertSee('Berkas Persyaratan');
            $showResponse->assertSee('Berkas Lampiran');
            $showResponse->assertSee('Kartu Keluarga (KK)');
            $showResponse->assertSee('Dokumen PDF Terlampir');
            $showResponse->assertSee('uploads/ppdb_documents/kk_sample.pdf');
            $showResponse->assertSee('uploads/ppdb_documents/akta_sample.jpg');
        } finally {
            $reg->forceDelete();
            $account->forceDelete();
        }
    }

    /**
     * Test admin payments module renders.
     */
    public function test_admin_payments_module_renders(): void
    {
        Http::fake([
            '*/api/ppdb/registrations*' => Http::response([
                [
                    'id' => 'reg-uuid-101',
                    'payment_status' => 'pending_verification',
                    'payment_amount' => 400000.0,
                    'payment_proof_path' => '/uploads/proof.jpg',
                    'account' => [
                        'nik' => '3201012345670001',
                        'full_name' => 'Ahmad Fauzi',
                        'email' => 'ahmad@example.com',
                    ],
                ]
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->get('/admin/ppdb/payments?status=pending');

        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('400.000');
    }

    /**
     * Test admin can manually store a new applicant account.
     */
    public function test_admin_can_store_user_manually(): void
    {
        Http::fake([
            '*/api/ppdb/register-account' => Http::response([
                'id' => 'acc-uuid-201',
                'full_name' => 'Dimas Arya',
                'email' => 'dimas.arya@example.com',
                'nik' => '3201019999990001',
            ], 201),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->post('/admin/ppdb/users', [
            'full_name' => 'Dimas Arya',
            'email' => 'dimas.arya@example.com',
            'nik' => '3201019999990001',
            'password' => 'Siswa2026!',
        ]);

        $response->assertRedirect('/admin/ppdb/users');
        $response->assertSessionHas('success');
    }

    /**
     * Test admin can manually store user and mark paid instantly.
     */
    public function test_admin_can_store_user_manually_and_mark_paid(): void
    {
        Http::fake([
            '*/api/ppdb/register-account' => Http::response([
                'id' => 'acc-uuid-202',
                'full_name' => 'Siti Fatimah',
                'email' => 'siti.fatimah@example.com',
            ], 201),
            '*/api/ppdb/registrations*' => Http::response([
                [
                    'id' => 'reg-uuid-202',
                    'account_id' => 'acc-uuid-202',
                    'payment_status' => 'unpaid',
                    'account' => [
                        'email' => 'siti.fatimah@example.com',
                    ],
                ]
            ], 200),
            '*/api/ppdb/verify-payment/reg-uuid-202' => Http::response([
                'id' => 'reg-uuid-202',
                'payment_status' => 'paid',
                'payment_amount' => 400000.0,
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->post('/admin/ppdb/users', [
            'full_name' => 'Siti Fatimah',
            'email' => 'siti.fatimah@example.com',
            'password' => 'Siswa2026!',
            'mark_as_paid' => '1',
        ]);

        $response->assertRedirect('/admin/ppdb/users');
        $response->assertSessionHas('success');
    }

    /**
     * Test admin can download import templates in XLSX and CSV.
     */
    public function test_admin_can_download_user_import_templates(): void
    {
        // Test CSV
        $responseCsv = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->get('/admin/ppdb/users/template?format=csv');

        $responseCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $responseCsv->headers->get('content-type'));
        $this->assertStringContainsString('Nama Lengkap', $responseCsv->getContent());

        // Test XLSX
        $responseXlsx = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->get('/admin/ppdb/users/template?format=xlsx');

        $responseXlsx->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $responseXlsx->headers->get('content-type'));
    }

    /**
     * Test admin can import users via CSV file.
     */
    public function test_admin_can_import_users_via_csv(): void
    {
        Http::fake([
            '*/api/ppdb/register-account' => Http::response([
                'id' => 'mock-imported-acc',
                'full_name' => 'Calon Siswa Import',
            ], 201),
        ]);

        $csvData = "NIK,Nama Lengkap,Email,Password\n" .
                   "3201015555550001,Budi Santoso,budi.santoso@example.com,Pass1234!\n" .
                   "3201015555550002,Citra Lestari,citra.lestari@example.com,Pass1234!\n";

        $file = UploadedFile::fake()->createWithContent('calon_siswa.csv', $csvData);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->post('/admin/ppdb/users/import', [
            'file' => $file,
            'default_password' => 'Ppdb2026!',
        ]);

        $response->assertRedirect('/admin/ppdb/users');
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_summary');

        $summary = session('import_summary');
        $this->assertEquals(2, $summary['success_count']);
        $this->assertEquals(0, $summary['failed_count']);
    }

    /**
     * Test admin can import users via XLSX file.
     */
    public function test_admin_can_import_users_via_xlsx(): void
    {
        Http::fake([
            '*/api/ppdb/register-account' => Http::response([
                'id' => 'mock-imported-acc-xlsx',
                'full_name' => 'Calon Siswa Excel',
            ], 201),
        ]);

        $importService = new \App\Services\PpdbImportService();
        $xlsxPath = $importService->generateTemplateXlsx();

        $file = new UploadedFile(
            $xlsxPath,
            'calon_siswa.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->post('/admin/ppdb/users/import', [
            'file' => $file,
            'default_password' => 'Ppdb2026!',
        ]);

        if (file_exists($xlsxPath)) {
            @unlink($xlsxPath);
        }

        $response->assertRedirect('/admin/ppdb/users');
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_summary');

        $summary = session('import_summary');
        $this->assertEquals(3, $summary['success_count']);
        $this->assertEquals(0, $summary['failed_count']);
    }

    /**
     * Test admin can update a user account.
     */
    public function test_admin_can_update_user_account(): void
    {
        Http::fake([
            '*/api/ppdb/registrations/reg-uuid-101' => Http::response([
                'id' => 'reg-uuid-101',
                'account_id' => 'acc-uuid-101',
                'payment_status' => 'unpaid',
                'payment_amount' => 0.0,
                'form_data' => [
                    'full_name' => 'Ahmad Fauzi',
                ],
                'account' => [
                    'nik' => '3201012345670001',
                    'email' => 'ahmad@example.com',
                ],
            ], 200),
            '*/api/ppdb/registrations/reg-uuid-101*' => Http::response([
                'id' => 'reg-uuid-101',
                'payment_status' => 'paid',
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->put('/admin/ppdb/users/reg-uuid-101', [
            'full_name' => 'Ahmad Fauzi Updated',
            'email' => 'ahmad.new@example.com',
            'nik' => '3201012345679999',
            'phone' => '081299998888',
            'password' => 'NewPassword123!',
            'payment_status' => 'paid',
        ]);

        $response->assertRedirect('/admin/ppdb/users');
        $response->assertSessionHas('success');
        $this->assertStringContainsString('Password baru berhasil disetel', session('success'));
        $this->assertStringNotContainsString('NewPassword123!', session('success'));
    }

    /**
     * Test admin can delete a user account.
     */
    public function test_admin_can_delete_user_account(): void
    {
        Http::fake([
            '*/api/ppdb/registrations/reg-uuid-101' => Http::response([
                'message' => 'Registration deleted successfully',
            ], 200),
        ]);

        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-12345',
            'is_admin' => true,
        ])->delete('/admin/ppdb/users/reg-uuid-101');

        $response->assertRedirect('/admin/ppdb/users');
        $response->assertSessionHas('success');
    }
}
