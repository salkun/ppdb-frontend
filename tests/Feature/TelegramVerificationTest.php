<?php

namespace Tests\Feature;

use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use App\Services\TelegramNotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class TelegramVerificationTest extends TestCase
{
    /**
     * Test notification sends photo with inline keyboard buttons.
     */
    public function test_telegram_notification_includes_inline_keyboard(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => []], 200),
        ]);

        $service = new TelegramNotificationService();

        $file = UploadedFile::fake()->image('bukti_transfer.jpg', 600, 800);
        $studentData = [
            'full_name' => 'Fauzan Santri Test',
            'nik' => '3201018899220001',
            'email' => 'fauzan@example.com',
        ];

        $regId = (string) Str::uuid();
        $result = $service->sendPaymentProofNotification($file, $studentData, $regId, 'transfer');

        $this->assertTrue($result);

        Http::assertSent(function ($request) use ($regId) {
            $isPhoto = str_contains($request->url(), '/sendPhoto');
            $body = $request->body();
            $hasButtons = str_contains($body, 'verif_' . $regId) && str_contains($body, 'tolak_' . $regId);
            return $isPhoto && $hasButtons;
        });
    }

    /**
     * Test admin can verify payment directly via Telegram callback query.
     */
    public function test_telegram_webhook_verifies_payment_to_paid(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true], 200),
            '*/api/ppdb/verify-payment/*' => Http::response(['message' => 'Synced'], 200),
        ]);

        $account = PpdbAccount::create([
            'id' => (string) Str::uuid(),
            'email' => 'test.verif.' . time() . '@example.com',
            'full_name' => 'Santri Siap Verif',
            'nik' => '3201017788990001',
            'password' => Hash::make('secret123'),
        ]);

        $reg = PpdbRegistration::create([
            'id' => (string) Str::uuid(),
            'account_id' => $account->id,
            'payment_status' => 'pending_verification',
            'payment_method' => 'transfer',
            'payment_amount' => 400000.0,
            'registration_status' => 'pending',
            'form_data' => [
                'full_name' => 'Santri Siap Verif',
            ],
        ]);

        try {
            $payload = [
                'update_id' => 123456,
                'callback_query' => [
                    'id' => 'cb-query-999',
                    'data' => 'verif_' . $reg->id,
                    'from' => [
                        'id' => 987654321,
                        'first_name' => 'Ustadz Panitia',
                        'username' => 'panitia_ppdb',
                    ],
                    'message' => [
                        'message_id' => 4455,
                        'chat' => [
                            'id' => -1004416679598,
                        ],
                        'caption' => '📢 NOTIFIKASI BUKTI PEMBAYARAN PPDB',
                    ],
                ],
            ];

            $response = $this->postJson('/api/telegram/webhook', $payload);

            $response->assertStatus(200);
            $response->assertJsonPath('ok', true);

            $reg->refresh();
            $this->assertEquals('paid', $reg->payment_status);
            $this->assertNotNull($reg->payment_verified_at);
            $this->assertStringContainsString('Telegram: @panitia_ppdb', $reg->payment_verified_by);

            // Verifikasi bahwa answerCallbackQuery dan editMessageCaption dipanggil
            Http::assertSent(function ($request) {
                return str_contains($request->url(), '/answerCallbackQuery');
            });
            Http::assertSent(function ($request) {
                return str_contains($request->url(), '/editMessageCaption');
            });

        } finally {
            $reg->forceDelete();
            $account->forceDelete();
        }
    }

    /**
     * Test admin can reject payment via Telegram callback query.
     */
    public function test_telegram_webhook_rejects_payment(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true], 200),
            '*/api/ppdb/verify-payment/*' => Http::response(['message' => 'Synced'], 200),
        ]);

        $account = PpdbAccount::create([
            'id' => (string) Str::uuid(),
            'email' => 'test.tolak.' . time() . '@example.com',
            'full_name' => 'Santri Bukti Buram',
            'nik' => '3201017788990002',
            'password' => Hash::make('secret123'),
        ]);

        $reg = PpdbRegistration::create([
            'id' => (string) Str::uuid(),
            'account_id' => $account->id,
            'payment_status' => 'pending_verification',
            'payment_method' => 'transfer',
            'payment_amount' => 400000.0,
            'registration_status' => 'pending',
            'form_data' => [
                'full_name' => 'Santri Bukti Buram',
            ],
        ]);

        try {
            $payload = [
                'update_id' => 123457,
                'callback_query' => [
                    'id' => 'cb-query-998',
                    'data' => 'tolak_' . $reg->id,
                    'from' => [
                        'id' => 987654321,
                        'first_name' => 'Ustadz Panitia',
                        'username' => 'panitia_ppdb',
                    ],
                    'message' => [
                        'message_id' => 4456,
                        'chat' => [
                            'id' => -1004416679598,
                        ],
                        'caption' => '📢 NOTIFIKASI BUKTI PEMBAYARAN PPDB',
                    ],
                ],
            ];

            $response = $this->postJson('/api/telegram/webhook', $payload);

            $response->assertStatus(200);
            $response->assertJsonPath('ok', true);

            $reg->refresh();
            $this->assertEquals('rejected', $reg->payment_status);
            $this->assertNotNull($reg->payment_verified_at);
            $this->assertStringContainsString('Telegram (Ditolak): @panitia_ppdb', $reg->payment_verified_by);

        } finally {
            $reg->forceDelete();
            $account->forceDelete();
        }
    }

    /**
     * Test telegram webhook handles /stats command.
     */
    public function test_telegram_webhook_handles_stats_command(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true], 200),
        ]);

        $payload = [
            'update_id' => 123458,
            'message' => [
                'message_id' => 101,
                'chat' => [
                    'id' => -1004416679598,
                ],
                'from' => [
                    'id' => 987654321,
                    'first_name' => 'Ustadz Panitia',
                ],
                'text' => '/stats',
            ],
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/sendMessage') && str_contains($request->body(), 'STATISTIK PEMBAYARAN PPDB');
        });
    }
}
