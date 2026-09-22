<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    protected ?string $botToken;
    protected ?string $chatId;

    public function __construct()
    {
        $this->botToken = trim((string) config('services.telegram.bot_token', ''));
        $this->chatId = trim((string) config('services.telegram.panitia_chat_id', ''));
    }

    /**
     * Mengecek apakah kredensial Telegram bot telah dikonfigurasi.
     */
    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->chatId);
    }

    /**
     * Mengirim notifikasi bukti transfer ke Grup Telegram Panitia PPDB.
     * Mendukung lampiran gambar (sendPhoto) dan PDF (sendDocument).
     *
     * @param UploadedFile $file
     * @param array $studentData (berisi full_name, nik, email)
     * @param string|null $registrationId
     * @return bool
     */
    public function sendPaymentProofNotification(UploadedFile $file, array $studentData, ?string $registrationId = null, string $paymentMethod = 'transfer'): bool
    {
        if (!$this->isConfigured()) {
            Log::info('Telegram Notification diabaikan: TELEGRAM_BOT_TOKEN atau TELEGRAM_PANITIA_CHAT_ID belum disetel di .env.');
            return false;
        }

        try {
            $fullName = htmlspecialchars($studentData['full_name'] ?? 'Calon Siswa', ENT_QUOTES, 'UTF-8');
            $nik = htmlspecialchars($studentData['nik'] ?? '-', ENT_QUOTES, 'UTF-8');
            $email = htmlspecialchars($studentData['email'] ?? '-', ENT_QUOTES, 'UTF-8');
            $uploadTime = now()->translatedFormat('d M Y, H:i') . ' WIB';
            $nominal = 'Rp 400.000';
            $methodLabel = ($paymentMethod === 'cash') ? 'Tunai (Cash di Loket)' : 'Transfer Bank (TF)';

            // URL langsung ke detail pendaftar di admin panel
            $adminUrl = !empty($registrationId)
                ? url('/admin/ppdb/registrations/' . $registrationId)
                : url('/admin/ppdb');

            $nikLine = (!empty($studentData['nik']) && $studentData['nik'] !== '-')
                ? "• <b>NIK:</b> <code>" . htmlspecialchars($studentData['nik'], ENT_QUOTES, 'UTF-8') . "</code>\n"
                : "";

            $caption = "📢 <b>NOTIFIKASI BUKTI PEMBAYARAN PPDB</b>\n\n"
                . "Telah diterima unggahan bukti pembayaran pendaftaran:\n"
                . "• <b>Nama Siswa:</b> {$fullName}\n"
                . $nikLine
                . "• <b>Email:</b> {$email}\n"
                . "• <b>Metode:</b> <b>{$methodLabel}</b>\n"
                . "• <b>Nominal:</b> <b>{$nominal}</b> (Biaya Registrasi)\n"
                . "• <b>Waktu:</b> {$uploadTime}\n\n"
                . "🔗 <b>Tindakan Panitia:</b>\n"
                . "👉 <a href=\"{$adminUrl}\">Buka &amp; Verifikasi di Panel Admin</a>";

            // Deteksi tipe berkas: Gambar vs PDF
            $extension = strtolower($file->getClientOriginalExtension());
            $mime = $file->getClientMimeType();
            $isPdf = ($extension === 'pdf' || $mime === 'application/pdf');

            $method = $isPdf ? 'sendDocument' : 'sendPhoto';
            $attachKey = $isPdf ? 'document' : 'photo';
            $apiUrl = "https://api.telegram.org/bot{$this->botToken}/{$method}";

            $response = Http::timeout(12)
                ->attach(
                    $attachKey,
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($apiUrl, [
                    'chat_id' => $this->chatId,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]);

            if ($response->successful()) {
                Log::info("Notifikasi Telegram PPDB berhasil terkirim ke Chat ID {$this->chatId} untuk siswa {$fullName}");
                return true;
            }

            Log::error("Telegram API Error [{$response->status()}]: " . $response->body());
            return false;

        } catch (\Throwable $e) {
            Log::error('Exception saat mengirim notifikasi Telegram: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Diagnostik: Memeriksa bot token via getMe dan mengirim pesan uji coba ke chat ID panitia.
     *
     * @return array
     */
    public function testConnection(): array
    {
        if (empty($this->botToken)) {
            return [
                'ok' => false,
                'message' => 'TELEGRAM_BOT_TOKEN belum disetel di .env',
            ];
        }

        // 1. Uji Bot Token via getMe
        try {
            $meResponse = Http::timeout(8)->get("https://api.telegram.org/bot{$this->botToken}/getMe");
            if (!$meResponse->successful()) {
                return [
                    'ok' => false,
                    'message' => 'Bot Token tidak valid atau tidak dapat terhubung ke Telegram API: ' . $meResponse->body(),
                ];
            }
            $botData = $meResponse->json('result');
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Koneksi ke api.telegram.org gagal: ' . $e->getMessage(),
            ];
        }

        if (empty($this->chatId)) {
            return [
                'ok' => false,
                'message' => 'TELEGRAM_PANITIA_CHAT_ID belum disetel di .env. Bot valid: @' . ($botData['username'] ?? '-'),
                'bot' => $botData,
            ];
        }

        // 2. Uji kirim pesan teks ke Grup Panitia
        try {
            $testMsg = "✅ <b>UJI COBA INTEGRASI TELEGRAM PPDB</b>\n\n"
                . "Sistem PPDB Online berhasil terhubung dengan grup panitia ini.\n"
                . "• <b>Bot:</b> @" . ($botData['username'] ?? '-') . "\n"
                . "• <b>Waktu:</b> " . now()->format('Y-m-d H:i:s') . "\n"
                . "• <b>Status:</b> Siap menerima notifikasi bukti transfer.";

            $sendResponse = Http::timeout(8)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $testMsg,
                'parse_mode' => 'HTML',
            ]);

            if ($sendResponse->successful()) {
                return [
                    'ok' => true,
                    'message' => 'Pesan uji coba berhasil dikirim ke grup chat ' . $this->chatId,
                    'bot' => $botData,
                ];
            }

            return [
                'ok' => false,
                'message' => "Bot valid (@{$botData['username']}), namun gagal mengirim pesan ke Chat ID {$this->chatId}: " . $sendResponse->body(),
                'bot' => $botData,
            ];

        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Gagal mengirim pesan uji coba: ' . $e->getMessage(),
                'bot' => $botData,
            ];
        }
    }
}
