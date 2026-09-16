<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class AdminPpdbTest extends TestCase
{
    /**
     * Test admin login page can be rendered.
     */
    public function test_admin_login_page_renders_successfully(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('Admin Portal PPDB');
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
}
