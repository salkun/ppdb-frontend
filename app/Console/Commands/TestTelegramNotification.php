<?php

namespace App\Console\Commands;

use App\Services\TelegramNotificationService;
use Illuminate\Console\Command;

class TestTelegramNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ppdb:test-telegram';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uji konektivitas Bot Telegram dan pengiriman pesan ke Grup Panitia PPDB';

    /**
     * Execute the console command.
     */
    public function handle(TelegramNotificationService $telegramService)
    {
        $this->info('====================================================');
        $this->info('       PENGUJIAN INTEGRASI TELEGRAM PANITIA PPDB    ');
        $this->info('====================================================');

        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.panitia_chat_id');

        $maskedToken = $botToken ? substr($botToken, 0, 8) . '...' . substr($botToken, -5) : '<KOSONG>';
        $this->table(
            ['Konfigurasi', 'Nilai / Status'],
            [
                ['TELEGRAM_BOT_TOKEN', $maskedToken],
                ['TELEGRAM_PANITIA_CHAT_ID', $chatId ?: '<KOSONG>'],
            ]
        );

        if (!$telegramService->isConfigured()) {
            $this->warn('Perhatian: Kredensial Telegram belum lengkap disetel pada file .env.');
            $this->line('Silakan tambahkan:');
            $this->line('  TELEGRAM_BOT_TOKEN=xxxxxxx');
            $this->line('  TELEGRAM_PANITIA_CHAT_ID=-100xxxxxxx');
            return Command::FAILURE;
        }

        $this->info('Sedang menghubungi Telegram API...');
        $result = $telegramService->testConnection();

        if ($result['ok']) {
            $this->info('SUKSES: ' . $result['message']);
            if (isset($result['bot'])) {
                $this->line("• Nama Bot: {$result['bot']['first_name']} (@{$result['bot']['username']})");
                $this->line("• ID Bot: {$result['bot']['id']}");
            }
            return Command::SUCCESS;
        } else {
            $this->error('GAGAL: ' . $result['message']);
            if (isset($result['bot'])) {
                $this->line("• Bot dikenali: @{$result['bot']['username']}");
                $this->warn('Tip: Pastikan bot telah dimasukkan ke dalam grup dan memiliki hak mengirim pesan.');
            }
            return Command::FAILURE;
        }
    }
}
