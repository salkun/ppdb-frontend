<?php

namespace App\Services;

use App\Models\PpdbRegistration;
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
     * Mengirim notifikasi bukti transfer ke Grup Telegram Panitia PPDB dengan Inline Keyboard Verifikasi 1-Klik.
     * Mendukung lampiran gambar (sendPhoto) dan PDF (sendDocument).
     *
     * @param UploadedFile $file
     * @param array $studentData (berisi full_name, nik, email)
     * @param string|null $registrationId
     * @param string $paymentMethod
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
            $uploadTime = now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB';
            $nominal = 'Rp 400.000';
            $methodLabel = ($paymentMethod === 'cash') ? 'Tunai (Cash di Loket)' : 'Transfer Bank (TF)';

            // URL langsung ke detail pendaftar di admin panel
            $adminUrl = !empty($registrationId)
                ? url('/admin/ppdb/registrations/' . $registrationId)
                : url('/admin/ppdb');

            $nikLine = (!empty($studentData['nik']) && $studentData['nik'] !== '-')
                ? "• <b>NIK:</b> <code>" . htmlspecialchars($studentData['nik'], ENT_QUOTES, 'UTF-8') . "</code>\n"
                : "";

            $regIdParam = $registrationId ?: 'unknown';

            $caption = "📢 <b>NOTIFIKASI BUKTI PEMBAYARAN PPDB</b>\n\n"
                . "Telah diterima unggahan bukti pembayaran pendaftaran:\n"
                . "• <b>Nama Siswa:</b> {$fullName}\n"
                . $nikLine
                . "• <b>Email:</b> {$email}\n"
                . "• <b>Metode:</b> <b>{$methodLabel}</b>\n"
                . "• <b>Nominal:</b> <b>{$nominal}</b> (Biaya Registrasi)\n"
                . "• <b>Waktu:</b> {$uploadTime}\n"
                . "• <b>Status:</b> ⏳ <b>Menunggu Verifikasi</b>\n\n"
                . "👇 <b>Tekan tombol di bawah untuk verifikasi langsung:</b>";

            // Inline Keyboard Buttons untuk aksi cepat di Telegram
            $inlineKeyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '✅ Verifikasi Lunas',
                            'callback_data' => 'verif_' . $regIdParam,
                        ],
                        [
                            'text' => '❌ Tolak Bukti',
                            'callback_data' => 'tolak_' . $regIdParam,
                        ],
                    ],
                    [
                        [
                            'text' => '🔍 Buka Dossier Siswa di Web Admin',
                            'url' => $adminUrl,
                        ],
                    ],
                ],
            ];

            // Deteksi tipe berkas: Gambar vs PDF
            $extension = strtolower($file->getClientOriginalExtension());
            $mime = $file->getClientMimeType();
            $isPdf = ($extension === 'pdf' || $mime === 'application/pdf');

            $method = $isPdf ? 'sendDocument' : 'sendPhoto';
            $attachKey = $isPdf ? 'document' : 'photo';
            $apiUrl = "https://api.telegram.org/bot{$this->botToken}/{$method}";

            $postData = [
                'chat_id' => $this->chatId,
                'caption' => $caption,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($inlineKeyboard),
            ];

            $response = Http::timeout(15)
                ->attach(
                    $attachKey,
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($apiUrl, $postData);

            if ($response->successful()) {
                Log::info("Notifikasi Telegram PPDB berhasil terkirim ke Chat ID {$this->chatId} untuk siswa {$fullName} (Reg ID: {$regIdParam})");
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
     * Memproses payload Update dari Telegram (baik via Webhook maupun Long Polling).
     *
     * @param array $update
     * @return array ['success' => bool, 'type' => string, 'message' => string]
     */
    public function processUpdate(array $update): array
    {
        if (isset($update['callback_query'])) {
            return $this->handleCallbackQuery($update['callback_query']);
        }

        if (isset($update['message'])) {
            return $this->handleMessage($update['message']);
        }

        return ['success' => false, 'type' => 'unknown', 'message' => 'No handled update type.'];
    }

    /**
     * Menangani callback_query saat admin menekan tombol inline di Telegram.
     */
    public function handleCallbackQuery(array $callbackQuery): array
    {
        $callbackId = $callbackQuery['id'] ?? '';
        $data = $callbackQuery['data'] ?? '';
        $from = $callbackQuery['from'] ?? [];
        $message = $callbackQuery['message'] ?? [];
        $chatId = $message['chat']['id'] ?? $this->chatId;
        $messageId = $message['message_id'] ?? null;
        $currentCaption = $message['caption'] ?? ($message['text'] ?? '');

        // Format nama admin yang menekan tombol
        $adminFirstName = $from['first_name'] ?? 'Admin';
        $adminUsername = !empty($from['username']) ? "@" . $from['username'] : $adminFirstName;
        $adminDisplay = $adminUsername;

        if ($data === 'noop') {
            $this->answerCallbackQuery($callbackId, 'ℹ️ Tindakan ini telah selesai diproses.', false);
            return ['success' => true, 'type' => 'noop', 'message' => 'No action required'];
        }

        // 1. Verifikasi Lunas
        if (str_starts_with($data, 'verif_')) {
            $regId = substr($data, 6);
            return $this->executePaymentAction($regId, 'paid', $adminDisplay, $callbackId, $chatId, $messageId, $currentCaption);
        }

        // 2. Tolak Bukti Pembayaran
        if (str_starts_with($data, 'tolak_')) {
            $regId = substr($data, 6);
            return $this->executePaymentAction($regId, 'rejected', $adminDisplay, $callbackId, $chatId, $messageId, $currentCaption);
        }

        $this->answerCallbackQuery($callbackId, 'Perintah tidak dikenali.', false);
        return ['success' => false, 'type' => 'unknown_callback', 'message' => 'Unknown callback query: ' . $data];
    }

    /**
     * Eksekusi update status pembayaran (paid / rejected) di MySQL dan kirim balasan ke Telegram.
     */
    protected function executePaymentAction(
        string $regId,
        string $targetStatus,
        string $adminDisplay,
        string $callbackId,
        $chatId,
        $messageId,
        string $currentCaption
    ): array {
        // Cari pendaftar di MySQL lokal
        $reg = PpdbRegistration::with('account')
            ->where('id', $regId)
            ->orWhere('remote_id', $regId)
            ->first();

        if (!$reg) {
            $this->answerCallbackQuery($callbackId, '❌ Data pendaftar tidak ditemukan di sistem PPDB.', true);
            return ['success' => false, 'message' => "Registration {$regId} not found."];
        }

        $studentName = $reg->account?->full_name ?? ($reg->form_data['full_name'] ?? 'Calon Siswa');
        $processedTime = now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB';

        // Cek jika status sudah sama sebelumnya
        if ($reg->payment_status === $targetStatus) {
            $statusText = ($targetStatus === 'paid') ? 'LUNAS' : 'DITOLAK';
            $this->answerCallbackQuery($callbackId, "ℹ️ Pembayaran a.n. {$studentName} sudah berstatus {$statusText}.", true);
            return ['success' => true, 'message' => "Already {$targetStatus}."];
        }

        // Update database MySQL lokal
        $isPaid = ($targetStatus === 'paid');
        $reg->payment_status = $targetStatus;
        if ($isPaid) {
            $reg->payment_amount = $reg->payment_amount ?: 400000.00;
        }
        $reg->payment_verified_at = now();
        $reg->payment_verified_by = "Telegram" . ($isPaid ? "" : " (Ditolak)") . ": {$adminDisplay}";
        $reg->sync_status = 'pending';
        $reg->sync_message = "Status pembayaran {$targetStatus} diverifikasi via Telegram oleh {$adminDisplay}";
        $reg->save();

        // Coba sinkronkan ke Master FastAPI jika online
        $this->syncPaymentToBackend($reg, $targetStatus);

        // Jawaban Popup Alert ke pengguna yang menekan tombol di Telegram
        $alertMsg = $isPaid
            ? "✅ SUKSES: Pembayaran a.n. {$studentName} berhasil diverifikasi LUNAS!"
            : "❌ Pembayaran a.n. {$studentName} telah DITOLAK.";
        $this->answerCallbackQuery($callbackId, $alertMsg, false);

        // Perbarui Tampilan Pesan / Caption di Telegram agar jelas & mencegah double click
        $adminUrl = url('/admin/ppdb/registrations/' . $reg->id);
        $statusBadge = $isPaid ? "✅ <b>STATUS: DIVERIFIKASI LUNAS</b>" : "❌ <b>STATUS: BUKTI PEMBAYARAN DITOLAK</b>";

        $cleanedCaption = preg_replace('/• <b>Status:<\/b>.*$/m', '', $currentCaption);
        $cleanedCaption = trim($cleanedCaption);

        $newCaption = $cleanedCaption . "\n\n"
            . "═══════════════════════════\n"
            . "{$statusBadge}\n"
            . "• <b>Diproses Oleh:</b> {$adminDisplay}\n"
            . "• <b>Waktu:</b> {$processedTime}\n"
            . "• <b>Sistem:</b> MySQL Database Updated ✓";

        // Keyboard baru yang tombol verifikasinya dinonaktifkan
        $updatedKeyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => $isPaid ? '✓ Telah DIVERIFIKASI LUNAS' : '✗ Bukti Telah DITOLAK',
                        'callback_data' => 'noop',
                    ],
                ],
                [
                    [
                        'text' => '🔍 Buka Lembar Dossier Siswa',
                        'url' => $adminUrl,
                    ],
                ],
            ],
        ];

        if ($messageId && !empty($chatId)) {
            $this->editMessageCaption((string) $chatId, (int) $messageId, $newCaption, $updatedKeyboard);

            // Kirim pesan rangkuman ke grup
            $summaryAction = $isPaid
                ? "🎉 <b>VERIFIKASI PEMBAYARAN BERHASIL</b>\n\nCalon siswa <b>{$studentName}</b> telah diverifikasi <b>LUNAS</b> oleh {$adminDisplay}.\nSiswa sekarang dapat melanjutkan ke pengisian formulir lengkap & cetak kartu ujian PPDB."
                : "⚠️ <b>BUKTI PEMBAYARAN DITOLAK</b>\n\nBukti pembayaran calon siswa <b>{$studentName}</b> telah ditolak oleh {$adminDisplay}.\nSiswa diminta mengunggah ulang bukti pembayaran yang sah.";

            $this->sendMessage((string) $chatId, $summaryAction);
        }

        Log::info("Verifikasi Telegram PPDB sukses: {$regId} menjadi {$targetStatus} oleh {$adminDisplay}");
        return ['success' => true, 'type' => 'payment_verified', 'status' => $targetStatus, 'student' => $studentName];
    }

    /**
     * Menangani pesan teks / perintah (command) dari grup atau private chat.
     */
    public function handleMessage(array $message): array
    {
        $text = trim($message['text'] ?? '');
        $chatId = (string) ($message['chat']['id'] ?? $this->chatId);

        if (empty($text)) {
            return ['success' => false, 'message' => 'Empty text'];
        }

        // /start atau /help
        if ($text === '/start' || $text === '/help' || str_starts_with($text, '/start@') || str_starts_with($text, '/help@')) {
            $helpMsg = "🤖 <b>BOT VERIFIKASI PEMBAYARAN PPDB ONLINE</b>\n"
                . "<b>SMP Al-Muhajirin Purwakarta</b>\n\n"
                . "Bot ini bertugas mengirimkan notifikasi bukti transfer calon santri serta memproses verifikasi 1-klik secara instan.\n\n"
                . "📌 <b>Daftar Perintah Panitia:</b>\n"
                . "• /pending — Tampilkan pendaftar yang belum diverifikasi\n"
                . "• /stats — Ringkasan statistik pembayaran PPDB\n"
                . "• /verif &lt;nik_atau_id&gt; — Verifikasi lunas secara langsung\n"
                . "• /tolak &lt;nik_atau_id&gt; — Tolak bukti pembayaran\n"
                . "• /help — Tampilkan pesan bantuan ini\n\n"
                . "💡 <i>Tips: Saat ada pendaftar baru yang transfer, bot akan otomatis mengirim foto struk beserta tombol ✅ Verifikasi Lunas.</i>";

            $this->sendMessage($chatId, $helpMsg);
            return ['success' => true, 'command' => 'help'];
        }

        // /stats
        if ($text === '/stats' || str_starts_with($text, '/stats@')) {
            $total = PpdbRegistration::count();
            $paid = PpdbRegistration::where('payment_status', 'paid')->count();
            $pending = PpdbRegistration::whereIn('payment_status', ['pending', 'pending_verification'])->count();
            $unpaid = PpdbRegistration::whereNotIn('payment_status', ['paid', 'pending', 'pending_verification', 'rejected'])->count();
            $rejected = PpdbRegistration::where('payment_status', 'rejected')->count();

            $statsMsg = "📊 <b>STATISTIK PEMBAYARAN PPDB</b>\n"
                . "📅 " . now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . " WIB\n\n"
                . "• <b>Total Akun Pendaftar:</b> {$total} siswa\n"
                . "• <b>✅ Lunas (Terverifikasi):</b> {$paid} siswa\n"
                . "• <b>⏳ Menunggu Verifikasi:</b> {$pending} siswa\n"
                . "• <b>❌ Ditolak:</b> {$rejected} siswa\n"
                . "• <b>⚪ Belum Bayar:</b> {$unpaid} siswa\n\n"
                . "🔗 <a href=\"" . url('/admin/ppdb/payments') . "\">Buka Modul Verifikasi di Web Admin</a>";

            $this->sendMessage($chatId, $statsMsg);
            return ['success' => true, 'command' => 'stats'];
        }

        // /pending
        if ($text === '/pending' || str_starts_with($text, '/pending@')) {
            $pendingList = PpdbRegistration::with('account')
                ->whereIn('payment_status', ['pending', 'pending_verification'])
                ->orWhere(function ($q) {
                    $q->where('payment_status', '!=', 'paid')
                      ->whereNotNull('payment_proof_path');
                })
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            if ($pendingList->isEmpty()) {
                $this->sendMessage($chatId, "✅ <b>Alhamdulillah!</b> Tidak ada pendaftar yang menunggu verifikasi saat ini. Semua pembayaran telah diproses.");
                return ['success' => true, 'command' => 'pending', 'count' => 0];
            }

            $count = $pendingList->count();
            $msg = "📋 <b>DAFTAR PENDAFTAR MENUNGGU VERIFIKASI ({$count} Teratas)</b>\n\n";

            foreach ($pendingList as $idx => $r) {
                $name = $r->account?->full_name ?? ($r->form_data['full_name'] ?? 'Calon Siswa');
                $nik = $r->account?->nik ?? '-';
                $method = ($r->payment_method === 'cash') ? 'Tunai' : 'Transfer';
                $time = $r->updated_at ? $r->updated_at->setTimezone('Asia/Jakarta')->translatedFormat('d M, H:i') : '-';

                $msg .= ($idx + 1) . ". <b>{$name}</b>\n"
                    . "   • NIK: <code>{$nik}</code> | Metode: {$method}\n"
                    . "   • Waktu: {$time} WIB\n"
                    . "   • Aksi Cepat: /verif_{$r->id}\n\n";
            }

            $msg .= "<i>Gunakan link /verif_ID di atas atau buka Dossier untuk memproses.</i>";
            $this->sendMessage($chatId, $msg);
            return ['success' => true, 'command' => 'pending', 'count' => $count];
        }

        // /verif_{id} atau /verif <nik_atau_id>
        if (preg_match('/^\/verif(?:_([a-zA-Z0-9\-]+)|\s+([a-zA-Z0-9\-]+))/', $text, $matches)) {
            $target = !empty($matches[1]) ? $matches[1] : ($matches[2] ?? '');
            return $this->manualVerifyCommand($target, 'paid', $message);
        }

        // /tolak_{id} atau /tolak <nik_atau_id>
        if (preg_match('/^\/tolak(?:_([a-zA-Z0-9\-]+)|\s+([a-zA-Z0-9\-]+))/', $text, $matches)) {
            $target = !empty($matches[1]) ? $matches[1] : ($matches[2] ?? '');
            return $this->manualVerifyCommand($target, 'rejected', $message);
        }

        return ['success' => false, 'message' => 'Ignored message.'];
    }

    /**
     * Memproses perintah verifikasi manual lewat ketikan teks (misal: /verif 3201019988770001).
     */
    protected function manualVerifyCommand(string $query, string $targetStatus, array $message): array
    {
        $chatId = (string) ($message['chat']['id'] ?? $this->chatId);
        $from = $message['from'] ?? [];
        $adminDisplay = !empty($from['username']) ? "@" . $from['username'] : ($from['first_name'] ?? 'Admin Panitia');

        $reg = PpdbRegistration::with('account')
            ->where('id', $query)
            ->orWhere('remote_id', $query)
            ->orWhereHas('account', function ($q) use ($query) {
                $q->where('nik', $query)->orWhere('email', $query);
            })
            ->first();

        if (!$reg) {
            $this->sendMessage($chatId, "❌ Calon siswa dengan ID/NIK <code>{$query}</code> tidak ditemukan.");
            return ['success' => false, 'message' => 'Student not found'];
        }

        $studentName = $reg->account?->full_name ?? ($reg->form_data['full_name'] ?? 'Calon Siswa');
        $isPaid = ($targetStatus === 'paid');

        $reg->payment_status = $targetStatus;
        if ($isPaid) {
            $reg->payment_amount = $reg->payment_amount ?: 400000.00;
        }
        $reg->payment_verified_at = now();
        $reg->payment_verified_by = "Telegram: {$adminDisplay}";
        $reg->sync_status = 'pending';
        $reg->save();

        $this->syncPaymentToBackend($reg, $targetStatus);

        $badge = $isPaid ? 'LUNAS (PAID) ✅' : 'DITOLAK ❌';
        $reply = "✓ <b>STATUS PEMBAYARAN DIPERBARUI</b>\n\n"
            . "• <b>Siswa:</b> {$studentName}\n"
            . "• <b>NIK:</b> <code>" . ($reg->account?->nik ?? '-') . "</code>\n"
            . "• <b>Status:</b> <b>{$badge}</b>\n"
            . "• <b>Admin:</b> {$adminDisplay}\n"
            . "• <b>Waktu:</b> " . now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . " WIB\n\n"
            . "🔗 <a href=\"" . url('/admin/ppdb/registrations/' . $reg->id) . "\">Buka Dossier Siswa</a>";

        $this->sendMessage($chatId, $reply);
        return ['success' => true, 'status' => $targetStatus, 'student' => $studentName];
    }

    /**
     * Sinkronisasi status pembayaran ke FastAPI Backend jika online.
     */
    protected function syncPaymentToBackend(PpdbRegistration $reg, string $status): void
    {
        try {
            $targetRemoteId = $reg->remote_id ?: $reg->id;
            $backendUrl = rtrim((string) config('ppdb.api_url', 'http://127.0.0.1:8001'), '/');

            Http::timeout(5)->put("{$backendUrl}/api/ppdb/verify-payment/{$targetRemoteId}", [
                'payment_status' => $status,
                'payment_amount' => (float) ($reg->payment_amount ?? 400000.00),
            ]);
        } catch (\Throwable $e) {
            // Offline fallback
        }
    }

    /**
     * Menjawab callback_query (menghilangkan loader di aplikasi Telegram).
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text, bool $showAlert = false): bool
    {
        if (empty($this->botToken) || empty($callbackQueryId)) {
            return false;
        }

        try {
            $res = Http::timeout(6)->post("https://api.telegram.org/bot{$this->botToken}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $showAlert,
            ]);
            return $res->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Memperbarui caption pada foto/dokumen yang sudah terkirim di Telegram.
     */
    public function editMessageCaption(string $chatId, int $messageId, string $newCaption, ?array $replyMarkup = null): bool
    {
        if (empty($this->botToken)) return false;

        try {
            $payload = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'caption' => $newCaption,
                'parse_mode' => 'HTML',
            ];

            if ($replyMarkup !== null) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $res = Http::timeout(8)->post("https://api.telegram.org/bot{$this->botToken}/editMessageCaption", $payload);
            return $res->successful();
        } catch (\Throwable $e) {
            Log::warning('Gagal editMessageCaption Telegram: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengirim pesan teks baru ke chat / grup Telegram.
     */
    public function sendMessage(string $chatId, string $text, ?array $replyMarkup = null): bool
    {
        if (empty($this->botToken)) return false;

        try {
            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false,
            ];

            if ($replyMarkup !== null) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            $res = Http::timeout(8)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", $payload);
            return $res->successful();
        } catch (\Throwable $e) {
            Log::warning('Gagal sendMessage Telegram: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil updates terbaru via Long Polling (cocok untuk pengujian lokal di Laragon).
     */
    public function getUpdates(int $offset = 0, int $limit = 50, int $timeout = 10): array
    {
        if (empty($this->botToken)) return [];

        try {
            $res = Http::timeout($timeout + 5)->get("https://api.telegram.org/bot{$this->botToken}/getUpdates", [
                'offset' => $offset,
                'limit' => $limit,
                'timeout' => $timeout,
            ]);

            if ($res->successful()) {
                return $res->json('result') ?? [];
            }
            return [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Mendaftarkan URL Webhook ke Telegram API.
     */
    public function setWebhook(string $url, ?string $secretToken = null): array
    {
        if (empty($this->botToken)) {
            return ['ok' => false, 'description' => 'TELEGRAM_BOT_TOKEN belum disetel'];
        }

        try {
            $payload = ['url' => $url];
            if (!empty($secretToken)) {
                $payload['secret_token'] = $secretToken;
            }

            $res = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/setWebhook", $payload);
            return $res->json() ?? ['ok' => false, 'description' => $res->body()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    /**
     * Mendapatkan info status Webhook saat ini.
     */
    public function getWebhookInfo(): array
    {
        if (empty($this->botToken)) {
            return ['ok' => false, 'description' => 'TELEGRAM_BOT_TOKEN belum disetel'];
        }

        try {
            $res = Http::timeout(8)->get("https://api.telegram.org/bot{$this->botToken}/getWebhookInfo");
            return $res->json('result') ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Menghapus Webhook Telegram (berguna saat beralih ke Long Polling lokal).
     */
    public function deleteWebhook(): array
    {
        if (empty($this->botToken)) {
            return ['ok' => false, 'description' => 'TELEGRAM_BOT_TOKEN belum disetel'];
        }

        try {
            $res = Http::timeout(8)->post("https://api.telegram.org/bot{$this->botToken}/deleteWebhook");
            return $res->json() ?? ['ok' => false, 'description' => $res->body()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    /**
     * Diagnostik: Memeriksa bot token via getMe dan mengirim pesan uji coba ke chat ID panitia.
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
                . "• <b>Waktu:</b> " . now()->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . " WIB\n"
                . "• <b>Status:</b> Siap menerima notifikasi bukti transfer & verifikasi 1-klik.";

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
