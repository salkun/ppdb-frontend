<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveBackupService;
use Illuminate\Console\Command;

class GoogleDriveConnectCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ppdb:gdrive-connect {--code= : Kode otorisasi dari Google jika diinput manual}';

    /**
     * The console command description.
     */
    protected $description = 'Hubungkan aplikasi PPDB ke Google Drive 4 TB melalui OAuth 2.0';

    /**
     * Execute the console command.
     */
    public function handle(GoogleDriveBackupService $driveService): int
    {
        $this->info('========================================================');
        $this->info('  Koneksi Google Drive 4 TB - SMPS2 Al-Muhajirin PPDB');
        $this->info('========================================================');

        $clientId = config('services.google_drive.client_id');
        $clientSecret = config('services.google_drive.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            $this->error('GOOGLE_DRIVE_CLIENT_ID atau GOOGLE_DRIVE_CLIENT_SECRET belum ada di .env!');
            return 1;
        }

        // Cek jika sudah ada kode via opsi --code
        $inputCode = $this->option('code');
        $redirectUri = 'http://127.0.0.1:8989';

        if (!empty($inputCode)) {
            try {
                $this->info('Menukarkan kode otorisasi dengan Refresh Token permanen...');
                $refreshToken = $driveService->exchangeCode($inputCode, $redirectUri);
                $this->info('✅ Berhasil terhubung! Refresh Token telah tersimpan di .env');
                return 0;
            } catch (\Throwable $e) {
                $this->error('Gagal: ' . $e->getMessage());
                return 1;
            }
        }

        $authUrl = $driveService->getAuthUrl($redirectUri);

        $this->newLine();
        $this->info('Langkah 1: Silakan buka link berikut di browser Anda:');
        $this->line('<fg=cyan>' . $authUrl . '</>');
        $this->newLine();
        $this->info('Langkah 2: Login dengan akun Google 4 TB Anda dan klik "Izinkan" / "Allow".');
        $this->info('Sistem sedang mendengarkan (*listening*) di http://127.0.0.1:8989 ...');

        // Buka browser secara otomatis di Windows
        if (PHP_OS_FAMILY === 'Windows') {
            @pclose(@popen('start "" "' . $authUrl . '"', 'r'));
        }

        // Buka local server socket port 8989 untuk menangkap callback otomatis
        $socket = @stream_socket_server('tcp://127.0.0.1:8989', $errno, $errstr);
        if ($socket) {
            $this->comment('Menunggu respon dari Google (maksimal 120 detik)...');
            stream_set_timeout($socket, 120);
            
            $client = @stream_socket_accept($socket, 120);
            if ($client) {
                $request = fread($client, 2048);
                
                // Cari parameter ?code= di HTTP request
                if (preg_match('/code=([^& ]+)/', $request, $matches)) {
                    $code = urldecode($matches[1]);
                    
                    try {
                        $driveService->exchangeCode($code, $redirectUri);
                        
                        $htmlResponse = "HTTP/1.1 200 OK\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n"
                            . "<!DOCTYPE html><html><body style='font-family:sans-serif;text-align:center;padding:50px;background:#f0fdf4;'>"
                            . "<h1 style='color:#166534;'>✅ Berhasil Terhubung!</h1>"
                            . "<p style='font-size:16px;color:#15803d;'>Aplikasi PPDB berhasil terhubung ke Google Drive 4 TB Anda.</p>"
                            . "<p style='color:#6b7280;'>Anda sekarang dapat menutup halaman ini dan kembali ke terminal.</p>"
                            . "</body></html>";
                        fwrite($client, $htmlResponse);
                        fclose($client);
                        fclose($socket);

                        $this->newLine();
                        $this->info('🎉 SELAMAT! Google Drive 4 TB telah berhasil terhubung dan tersimpan di .env.');
                        return 0;
                    } catch (\Throwable $e) {
                        $htmlErr = "HTTP/1.1 400 Bad Request\r\nContent-Type: text/html\r\n\r\n<h1>Gagal: " . htmlspecialchars($e->getMessage()) . "</h1>";
                        fwrite($client, $htmlErr);
                        fclose($client);
                        fclose($socket);
                        $this->error('Gagal menukarkan kode: ' . $e->getMessage());
                        return 1;
                    }
                }
                fclose($client);
            }
            fclose($socket);
        }

        // Fallback: Jika socket gagal atau terlewat, minta user masukkan manual
        $this->warn('Listener timeout atau port terhalang. Anda dapat menyalin parameter "code" dari URL browser:');
        $codeManual = $this->ask('Masukkan parameter code dari address bar browser');
        if (!empty($codeManual)) {
            try {
                $driveService->exchangeCode($codeManual, $redirectUri);
                $this->info('✅ Berhasil terhubung! Refresh Token telah tersimpan di .env.');
                return 0;
            } catch (\Throwable $e) {
                $this->error('Gagal: ' . $e->getMessage());
                return 1;
            }
        }

        return 1;
    }
}
