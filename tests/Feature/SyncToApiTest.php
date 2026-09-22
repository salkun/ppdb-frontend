<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use App\Models\SyncLog;
use App\Services\ApiSyncService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncToApiTest extends TestCase
{
    /**
     * Test admin can trigger one-click bulk sync to Master Data API.
     */
    public function test_admin_can_trigger_one_click_sync_to_master_api(): void
    {
        // 1. Siapkan akun lokal yang pending sync
        $randomEmail = 'pending_' . Str::random(8) . '@example.com';
        $account = PpdbAccount::create([
            'nik' => '320101' . rand(1000000000, 9999999999),
            'full_name' => 'Siswa Pending Sync',
            'email' => $randomEmail,
            'password' => Hash::make('Secret123!'),
            'phone' => '081234567890',
            'sync_status' => 'pending',
            'sync_message' => 'Menunggu sinkronisasi',
        ]);

        $registration = PpdbRegistration::create([
            'account_id' => $account->id,
            'payment_status' => 'paid',
            'payment_amount' => 400000.0,
            'registration_status' => 'pending',
            'form_data' => [
                'full_name' => 'Siswa Pending Sync',
                'major' => 'reguler',
            ],
            'sync_status' => 'pending',
            'sync_message' => 'Menunggu sinkronisasi',
        ]);

        // 2. Mocking response backend API (FastAPI)
        Http::fake([
            '*/api/ppdb/register-account' => Http::response([
                'id' => 'remote-acc-uuid-888',
                'email' => $randomEmail,
                'full_name' => 'Siswa Pending Sync',
            ], 200),
            '*/api/ppdb/verify-payment*' => Http::response([
                'message' => 'Payment verified',
            ], 200),
            '*/api/ppdb/registrations*' => function ($request) use ($randomEmail) {
                if ($request->method() === 'PUT') {
                    return Http::response([
                        'id' => 'remote-reg-uuid-999',
                        'payment_status' => 'paid',
                        'registration_status' => 'pending',
                    ], 200);
                }
                return Http::response([
                    [
                        'id' => 'remote-reg-uuid-999',
                        'account_id' => 'remote-acc-uuid-888',
                        'account' => [
                            'id' => 'remote-acc-uuid-888',
                            'email' => $randomEmail,
                            'full_name' => 'Siswa Pending Sync',
                        ],
                        'payment_status' => 'paid',
                        'registration_status' => 'pending',
                    ]
                ], 200);
            },
        ]);

        // 3. Request POST /admin/ppdb/sync dengan sesi admin
        $response = $this->withSession([
            'admin_api_token' => 'admin-jwt-token-xyz',
            'is_admin' => true,
        ])->post('/admin/ppdb/sync');

        $response->assertRedirect();
        $response->assertSessionHas('sync_results');

        // 4. Pastikan status lokal terupdate menjadi 'synced'
        $account->refresh();
        $registration->refresh();

        $this->assertEquals('synced', $account->sync_status);
        $this->assertEquals('remote-acc-uuid-888', $account->remote_id);
        $this->assertNotNull($account->last_synced_at);

        $this->assertEquals('synced', $registration->sync_status);
        $this->assertNotNull($registration->last_synced_at);

        // 5. Pastikan audit sync log tercatat di sync_logs
        $this->assertGreaterThanOrEqual(1, SyncLog::where('syncable_id', $account->id)->count());

        // Cleanup
        $registration->forceDelete();
        $account->forceDelete();
    }

    /**
     * Test sync handles API errors gracefully without breaking local data.
     */
    public function test_sync_handles_backend_errors_and_marks_failed(): void
    {
        $randomEmail = 'fail_' . Str::random(8) . '@example.com';
        $account = PpdbAccount::create([
            'nik' => '320101' . rand(1000000000, 9999999999),
            'full_name' => 'Siswa Error Sync',
            'email' => $randomEmail,
            'password' => Hash::make('Secret123!'),
            'phone' => '081234567890',
            'sync_status' => 'pending',
            'sync_message' => 'Menunggu sinkronisasi',
        ]);

        Http::fake([
            '*/api/ppdb/register-account' => Http::response([
                'detail' => 'Internal Server Error pada Master API',
            ], 500),
        ]);

        $service = app(ApiSyncService::class);
        $result = $service->syncAccount($account, 'admin-token');

        $account->refresh();
        $this->assertEquals('failed', $account->sync_status);
        $this->assertFalse($result);

        // Data lokal tetap aman
        $this->assertEquals('Siswa Error Sync', $account->full_name);

        // Cleanup
        $account->forceDelete();
    }
}
