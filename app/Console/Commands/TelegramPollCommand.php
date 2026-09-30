<?php

namespace App\Console\Commands;

use App\Services\TelegramNotificationService;
use Illuminate\Console\Command;

class TelegramPollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ppdb:telegram-poll {--timeout=10 : Long polling timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan Telegram Bot Listener (Long Polling) untuk verifikasi pembayaran PPDB di lingkungan lokal/Laragon';

    /**
     * Execute the console command.
     */
    public function handle(TelegramNotificationService $telegramService): int
    {
        $this->info("====================================================");
        $this->info("     TELEGRAM BOT PPDB LISTENER (LONG POLLING)     ");
        $this->info("====================================================");

        if (!$telegramService->isConfigured()) {
            $this->error("Kredensial TELEGRAM_BOT_TOKEN atau TELEGRAM_PANITIA_CHAT_ID belum disetel di .env!");
            return Command::FAILURE;
        }

        // Cek koneksi getMe
        $conn = $telegramService->testConnection();
        if (!($conn['ok'] ?? false)) {
            $this->warn("Perhatian: " . ($conn['message'] ?? 'Gagal terhubung ke Telegram API.'));
        } else {
            $bot = $conn['bot'] ?? [];
            $this->info("✓ Bot Terhubung: @" . ($bot['username'] ?? '-') . " (ID: " . ($bot['id'] ?? '-') . ")");
        }

        // Cek jika webhook masih aktif di Telegram
        $webhookInfo = $telegramService->getWebhookInfo();
        if (!empty($webhookInfo['url'])) {
            $this->warn("Webhook aktif terdeteksi ke URL: " . $webhookInfo['url']);
            $this->line("Menghapus webhook sementara agar Long Polling dapat menerima update...");
            $telegramService->deleteWebhook();
            $this->info("✓ Webhook berhasil dihapus sementara.");
        }

        $this->info("Bot sedang aktif mendengarkan aksi tombol & perintah di Telegram...");
        $this->line("Tekan Ctrl+C untuk menghentikan.\n");

        $offset = 0;
        $timeout = (int) $this->option('timeout') ?: 10;

        while (true) {
            try {
                $updates = $telegramService->getUpdates($offset, 50, $timeout);

                foreach ($updates as $update) {
                    $updateId = (int) ($update['update_id'] ?? 0);
                    $offset = max($offset, $updateId + 1);

                    // Proses callback query atau pesan
                    $type = isset($update['callback_query']) ? 'Tombol (Callback)' : (isset($update['message']) ? 'Pesan Teks' : 'Update');
                    $user = $update['callback_query']['from']['first_name'] ?? ($update['message']['from']['first_name'] ?? 'User');
                    $data = $update['callback_query']['data'] ?? ($update['message']['text'] ?? '-');

                    $time = now()->setTimezone('Asia/Jakarta')->format('H:i:s');
                    $this->line("[{$time}] <fg=cyan>{$type}</> dari <fg=yellow>{$user}</>: <fg=white>{$data}</>");

                    $result = $telegramService->processUpdate($update);

                    if ($result['success'] ?? false) {
                        $this->info("   ↳ Sukses: " . ($result['message'] ?? json_encode($result)));
                    } else {
                        $this->line("   ↳ Info: " . ($result['message'] ?? 'Diabaikan'));
                    }
                }

                // Beri sedikit jeda agar tidak membebani CPU
                usleep(300000); // 0.3 detik

            } catch (\Throwable $e) {
                $this->error("Error saat polling: " . $e->getMessage());
                sleep(2);
            }
        }

        return Command::SUCCESS;
    }
}
