<?php

namespace App\Console\Commands;

use App\Services\TelegramNotificationService;
use Illuminate\Console\Command;

class TelegramWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ppdb:telegram-webhook 
                            {--set= : Pasang URL Webhook ke Telegram API (misal: https://domain-anda.com/api/telegram/webhook)}
                            {--info : Cek status konfigurasi Webhook saat ini di Telegram}
                            {--delete : Hapus Webhook aktif di Telegram}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kelola pendaftaran Webhook Telegram Bot PPDB (Set, Info, Delete)';

    /**
     * Execute the console command.
     */
    public function handle(TelegramNotificationService $telegramService): int
    {
        $this->info("====================================================");
        $this->info("       PENGATURAN WEBHOOK TELEGRAM BOT PPDB        ");
        $this->info("====================================================");

        if (!$telegramService->isConfigured()) {
            $this->error("Kredensial TELEGRAM_BOT_TOKEN belum disetel di .env!");
            return Command::FAILURE;
        }

        // 1. Opsi Pasang Webhook Baru
        if ($url = $this->option('set')) {
            $this->info("Mendaftarkan URL Webhook ke Telegram API...");
            $this->line("Target URL: {$url}");

            $secret = config('services.telegram.webhook_secret');
            $res = $telegramService->setWebhook($url, $secret);

            if ($res['ok'] ?? false) {
                $this->info("✓ SUKSES: Webhook berhasil didaftarkan ke Telegram API!");
                $this->line("Deskripsi: " . ($res['description'] ?? 'OK'));
            } else {
                $this->error("✗ GAGAL: " . ($res['description'] ?? 'Terjadi kesalahan'));
            }
            return Command::SUCCESS;
        }

        // 2. Opsi Hapus Webhook
        if ($this->option('delete')) {
            $this->info("Menghapus Webhook dari Telegram API...");
            $res = $telegramService->deleteWebhook();

            if ($res['ok'] ?? false) {
                $this->info("✓ SUKSES: Webhook berhasil dihapus dari Telegram API.");
            } else {
                $this->error("✗ GAGAL: " . ($res['description'] ?? 'Terjadi kesalahan'));
            }
            return Command::SUCCESS;
        }

        // 3. Default atau --info: Cek Status Webhook Saat Ini
        $this->info("Mengambil informasi Webhook dari Telegram API...");
        $info = $telegramService->getWebhookInfo();

        if (empty($info)) {
            $this->warn("Tidak ada data webhook yang dikembalikan.");
            return Command::SUCCESS;
        }

        $tableData = [
            ['URL Terdaftar', $info['url'] ?: '<TIDAK ADA / BELUM DIPASANG>'],
            ['Custom Certificate', ($info['has_custom_certificate'] ?? false) ? 'Ya' : 'Tidak'],
            ['Pending Updates Count', $info['pending_update_count'] ?? 0],
            ['Last Error Date', !empty($info['last_error_date']) ? date('Y-m-d H:i:s', $info['last_error_date']) : '-'],
            ['Last Error Message', $info['last_error_message'] ?? '-'],
            ['Max Connections', $info['max_connections'] ?? '-'],
        ];

        $this->table(['Properti', 'Nilai'], $tableData);

        if (empty($info['url'])) {
            $this->line("\n💡 Tips Penggunaan:");
            $this->line("• Untuk server live/cPanel (dengan domain HTTPS):");
            $this->line("  <fg=green>php artisan ppdb:telegram-webhook --set=https://domain-anda.com/api/telegram/webhook</>");
            $this->line("• Untuk pengujian lokal di Laragon/komputer:");
            $this->line("  <fg=yellow>php artisan ppdb:telegram-poll</>");
        }

        return Command::SUCCESS;
    }
}
