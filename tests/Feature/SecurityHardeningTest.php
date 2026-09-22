<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class SecurityHardeningTest extends TestCase
{
    /**
     * Test HTTP responses contain all expected security headers.
     */
    public function test_responses_contain_security_headers(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertTrue($response->headers->has('Permissions-Policy'));
    }

    /**
     * Test admin middleware rejects session that lacks is_admin flag.
     */
    public function test_admin_middleware_rejects_missing_is_admin_flag(): void
    {
        // Punya admin_api_token tapi is_admin = false atau tidak ada
        $response = $this->withSession([
            'admin_api_token' => 'some-token-value',
            'is_admin' => false,
        ])->get('/admin/ppdb');

        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('warning');
    }

    /**
     * Test delete_doc with invalid key does not corrupt or delete unexpected keys.
     */
    public function test_delete_doc_with_arbitrary_key_is_ignored(): void
    {
        $uniqueEmail = 'sec_test_' . uniqid() . '@example.com';

        $account = PpdbAccount::create([
            'email' => $uniqueEmail,
            'full_name' => 'Security Test Student',
            'password' => Hash::make('password123'),
        ]);

        $registration = PpdbRegistration::create([
            'account_id' => $account->id,
            'payment_status' => 'paid',
            'form_data' => [
                'full_name' => 'Security Test Student',
                'documents' => [
                    'kk' => '/uploads/ppdb_documents/kk_test.jpg',
                    'sensitive_internal_config' => 'config_value',
                ],
            ],
        ]);

        $response = $this->withSession([
            'api_token' => 'student-token-sec',
            'account_id' => $account->id,
            'email' => $account->email,
        ])->post('/ppdb/upload-berkas', [
            'delete_doc' => 'sensitive_internal_config',
        ]);

        $registration->refresh();
        $this->assertEquals('config_value', $registration->form_data['documents']['sensitive_internal_config'] ?? null);

        // Cleanup
        $registration->delete();
        $account->delete();
    }
}
