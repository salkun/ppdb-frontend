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
}
