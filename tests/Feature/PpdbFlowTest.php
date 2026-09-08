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
     * Test login sets session token and redirects to dashboard.
     */
    public function test_successful_login_stores_token_in_session(): void
    {
        Http::fake([
            '*/api/ppdb/login' => Http::response([
                'access_token' => 'mocked-jwt-token-xyz',
                'token_type' => 'bearer',
                'account_id' => 'mock-uuid-1234',
                'nik' => '3201012345670001',
                'full_name' => 'Ahmad Fauzi',
                'email' => 'ahmad@example.com',
            ], 200),
        ]);

        $response = $this->post('/login', [
            'nik' => '3201012345670001',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertEquals('mocked-jwt-token-xyz', session('api_token'));
        $this->assertEquals('Ahmad Fauzi', session('full_name'));
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
            'nik' => '3201012345670001',
            'full_name' => 'Ahmad Fauzi',
            'email' => 'ahmad@example.com',
        ])->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('BELUM BAYAR');
        $response->assertSee('3201012345670001');
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
            'nik' => '3201012345670001',
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

            // Tahap 2
            'family_card_number' => '3201010000000001',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'place_of_birth' => 'Jakarta',
            'date_of_birth' => '2008-05-14',

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

            // Tahap 5 - Ibu
            'mother_nik' => '3201011122330002',
            'mother_name' => 'Siti Aminah',
            'mother_birth_year' => '1982',
            'mother_education' => '04',
            'mother_occupation' => '01',
            'mother_income' => '06',
            'mother_phone' => '081311223355',
            'mother_whatsapp' => '081311223355',
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
                && isset($data['address']['street_address'])
                && isset($data['contact']['mobile_number'])
                && isset($data['student_parents'][0]['relationship_type'])
                && $data['student_parents'][0]['relationship_type'] === 1
                && $data['student_parents'][1]['relationship_type'] === 2;
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
}
