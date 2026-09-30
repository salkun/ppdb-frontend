<?php

namespace App\Http\Controllers;

use App\Services\TelegramNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    /**
     * Endpoint Webhook yang menerima update dari Telegram Bot API.
     * URL: POST /api/telegram/webhook
     */
    public function handleWebhook(Request $request, TelegramNotificationService $telegramService): JsonResponse
    {
        // 1. Verifikasi Secret Token jika dikonfigurasi
        $configuredSecret = config('services.telegram.webhook_secret');
        if (!empty($configuredSecret)) {
            $receivedSecret = $request->header('X-Telegram-Bot-Api-Secret-Token');
            if ($receivedSecret !== $configuredSecret) {
                Log::warning('Telegram Webhook: Invalid Secret Token received.');
                return response()->json(['ok' => false, 'message' => 'Unauthorized token'], 401);
            }
        }

        $payload = $request->all();

        if (empty($payload)) {
            return response()->json(['ok' => true, 'message' => 'Empty payload']);
        }

        try {
            $result = $telegramService->processUpdate($payload);
            return response()->json([
                'ok' => true,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Telegram Webhook Exception: ' . $e->getMessage(), ['payload' => $payload]);
            return response()->json(['ok' => true, 'error' => $e->getMessage()]);
        }
    }
}
