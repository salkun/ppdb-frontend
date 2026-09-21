<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class PpdbFlowTest extends TestCase
{
    /**
     * Test public pages accessibility.
     */
    public function test_public_pages_can_be_rendered(): void
    {
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);

        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);

        $registerResponse = $this->get('/register');
        $registerResponse->assertStatus(200);
    }

    /**
     * Test middleware blocks unauthenticated requests to protected PPDB routes.
     */
    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
        $response->assertSessionHas('warning');
    }

    public function test_guest_is_redirected_to_login_from_form(): void
    {
        $response = $this->get('/ppdb/form');
        $response->assertRedirect('/login');
        $response->assertSessionHas('warning');
    }

    /**
     * Test register sends email, full_name, and password without NIK.
     */
    public function test_register_creates_account_and_redirects_to_login(): void
    {
        Http::fake([
            '*/api/ppdb/register-account' => Http::response([
                'id' => 'mock-uuid-1234',
                'email' => 'ahmad@example.com',
                'full_name' => 'Ahmad Fauzi',
            ], 201),
        ]);

        $response = $this->post('/register', [
            'email' => 'ahmad@example.com',
            'full_name' => 'Ahmad Fauzi',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');
    }

    /**
     * Test login sets session token and redirects to dashboard.
     */
    public function test_successful_login_stores_token_in_session(): void
    {
        Http::fake([
            '*/api/ppdb/login' => Http::response([
                'access_token' => 'mocked-jwt-token-xyz',
                'token_type' => 'bearer',
                'account_id' => 'mock-uuid-1234',
                'full_name' => 'Ahmad Fauzi',
                'email' => 'ahmad@example.com',
            ], 200),
        ]);

        $response = $this->post('/login', [
            'email' => 'ahmad@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertEquals('mocked-jwt-token-xyz', session('api_token'));
        $this->assertEquals('Ahmad Fauzi', session('full_name'));
        $this->assertEquals('ahmad@example.com', session('email'));
    }

    /**
     * Test authenticated dashboard loads with my-registration data.
     */
    public function test_authenticated_dashboard_renders_with_mocked_backend_data(): void
    {
        Http::fake([
            '*/api/ppdb/my-registration' => Http::response([
                'id' => 'mock-reg-id-999',
                'account_id' => 'mock-uuid-1234',
                'payment_status' => 'unpaid',
                'payment_amount' => 0.0,
                'payment_proof_path' => null,
                'registration_status' => 'pending',
                'form_data' => null,
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'mocked-jwt-token-xyz',
            'account_id' => 'mock-uuid-1234',
            'full_name' => 'Ahmad Fauzi',
            'email' => 'ahmad@example.com',
        ])->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('BELUM BAYAR');
        $response->assertSee('Rp 400.000');
        $response->assertSee('ahmad@example.com');
    }

    /**
     * Test form is locked if payment status is not paid.
     */
    public function test_form_is_locked_if_payment_status_is_unpaid(): void
    {
        Http::fake([
            '*/api/ppdb/my-registration' => Http::response([
                'id' => 'mock-reg-id-999',
                'payment_status' => 'unpaid',
                'registration_status' => 'pending',
                'form_data' => null,
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'mocked-jwt-token-xyz',
        ])->get('/ppdb/form');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('warning');
    }

    /**
     * Test form is accessible when payment status is paid.
     */
    public function test_form_is_accessible_when_payment_status_is_paid(): void
    {
        Http::fake([
            '*/api/ppdb/my-registration' => Http::response([
                'id' => 'mock-reg-id-999',
                'payment_status' => 'paid',
                'registration_status' => 'pending',
                'form_data' => null,
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'mocked-jwt-token-xyz',
            'full_name' => 'Ahmad Fauzi',
            'email' => 'ahmad@example.com',
        ])->get('/ppdb/form');

        $response->assertStatus(200);
        $response->assertSee('Formulir Pendaftaran Calon Siswa');
    }

    /**
     * Test form submission sends properly structured nested JSON to PUT /api/ppdb/registration-form.
     */
    public function test_submit_form_sends_nested_json_to_backend(): void
    {
        Http::fake([
            '*/api/ppdb/registration-form' => Http::response([
                'message' => 'Form data saved',
            ], 200),
        ]);

        $postData = [
            // Tahap 1
            'nik' => '3201012345670001',
            'nisn' => '0051234567',
            'full_name' => 'Ahmad Fauzi Rahman',
            'first_name' => 'Ahmad',
            'last_name' => 'Fauzi Rahman',
            'major' => 'reguler',
            'school_origin' => 'SMP Negeri 1 Purwakarta',
            'school_origin_address' => 'Jl. Veteran No. 12, Purwakarta',

            // Tahap 2
            'family_card_number' => '3201010000000001',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'place_of_birth' => 'Jakarta',
            'date_of_birth' => '2008-05-14',
            'birth_order' => 2,
            'siblings_count' => 3,

            // Tahap 3
            'street_address' => 'Jl. Merdeka No. 45',
            'rt' => '002',
            'rw' => '005',
            'village' => 'Sukamaju',
            'district' => 'Cilodong',
            'postal_code' => '16415',
            'residence_type' => 'Bersama Orang Tua',
            'transportation_mode' => 'Sepeda Motor',

            // Tahap 4
            'phone_number' => '021-77889900',
            'mobile_number' => '081234567890',
            'whatsapp_number' => '081234567890',
            'email' => 'ahmad.fauzi@student.sch.id',

            // Tahap 5 - Ayah
            'father_nik' => '3201011122330001',
            'father_name' => 'Bambang Sudarsono',
            'father_birth_year' => '1978',
            'father_education' => '05',
            'father_occupation' => '02',
            'father_income' => '03',
            'father_phone' => '081311223344',
            'father_whatsapp' => '081311223344',
            'father_email' => 'bambang.sudarsono@example.com',

            // Tahap 5 - Ibu
            'mother_nik' => '3201011122330002',
            'mother_name' => 'Siti Aminah',
            'mother_birth_year' => '1982',
            'mother_education' => '04',
            'mother_occupation' => '01',
            'mother_income' => '06',
            'mother_phone' => '081311223355',
            'mother_whatsapp' => '081311223355',
            'mother_email' => 'siti.aminah@example.com',
        ];

        $response = $this->withSession([
            'api_token' => 'mocked-jwt-token-xyz',
        ])->post('/ppdb/form', $postData);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        // Verify sent payload structure
        Http::assertSent(function ($request) {
            $data = $request->data();
            return $request->url() === config('ppdb.api_url') . '/api/ppdb/registration-form'
                && $request->method() === 'PUT'
                && isset($data['identity']['family_card_number'])
                && isset($data['identity']['birth_order'])
                && $data['identity']['birth_order'] === 2
                && isset($data['identity']['siblings_count'])
                && $data['identity']['siblings_count'] === 3
                && isset($data['address']['street_address'])
                && isset($data['contact']['mobile_number'])
                && isset($data['student_parents'][0]['relationship_type'])
                && $data['student_parents'][0]['relationship_type'] === 1
                && ($data['student_parents'][0]['parent']['email'] ?? '') === 'bambang.sudarsono@example.com'
                && $data['student_parents'][1]['relationship_type'] === 2
                && ($data['student_parents'][1]['parent']['email'] ?? '') === 'siti.aminah@example.com';
        });
    }

    /**
     * Test logout clears session.
     */
    public function test_logout_clears_session_and_redirects_to_login(): void
    {
        $response = $this->withSession([
            'api_token' => 'mocked-jwt-token-xyz',
        ])->post('/logout');

        $response->assertRedirect('/login');
        $this->assertNull(session('api_token'));
    }

    /**
     * Test test-card is locked when payment is unpaid.
     */
    public function test_test_card_is_locked_if_unpaid(): void
    {
        Http::fake([
            '*/api/ppdb/my-registration' => Http::response([
                'id' => 'mock-reg-id-999',
                'payment_status' => 'unpaid',
                'registration_status' => 'pending',
                'form_data' => null,
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'mocked-jwt-token-xyz',
        ])->get('/ppdb/test-card');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('warning');
    }

    /**
     * Test test-card is locked when form data is empty even if paid.
     */
    public function test_test_card_is_locked_if_form_is_empty(): void
    {
        Http::fake([
            '*/api/ppdb/my-registration' => Http::response([
                'id' => 'mock-reg-id-999',
                'payment_status' => 'paid',
                'registration_status' => 'pending',
                'form_data' => null,
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'mocked-jwt-token-xyz',
        ])->get('/ppdb/test-card');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('warning');
    }

    /**
     * Test test-card renders properly when paid and form is filled.
     */
    public function test_test_card_renders_when_paid_and_form_filled(): void
    {
        Http::fake([
            '*/api/ppdb/my-registration' => Http::response([
                'id' => 'REG-2026-001',
                'payment_status' => 'paid',
                'registration_status' => 'pending',
                'form_data' => [
                    'full_name' => 'Ahmad Fauzi',
                    'nik' => '3201012345670001',
                    'nisn' => '0051234567',
                    'school_origin' => 'SMP Negeri 1',
                    'major' => 'reguler',
                    'identity' => [
                        'gender' => 'Laki-laki',
                        'place_of_birth' => 'Purwakarta',
                        'date_of_birth' => '2008-05-14',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession([
            'api_token' => 'mocked-jwt-token-xyz',
            'full_name' => 'Ahmad Fauzi',
            'email' => 'ahmad@example.com',
        ])->get('/ppdb/test-card');

        $response->assertStatus(200);
        $response->assertSee('Kartu Tes Pendaftaran');
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('REG-2026-001');
        $response->assertSee('3201012345670001');
        $response->assertSee('Reguler');
    }
}
