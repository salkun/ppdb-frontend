<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveBackupService;
use App\Services\TelegramNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PpdbBackupGoogleDriveCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ppdb:backup-gdrive {--keep-local : Jangan hapus arsip ZIP lokal setelah diunggah}';

    /**
     * The console command description.
     */
    protected $description = 'Cadangkan database PPDB dan seluruh berkas fisik siswa langsung ke Google Drive 4 TB';

    /**
     * Execute the console command.
     */
    public function handle(GoogleDriveBackupService $driveService, TelegramNotificationService $telegram): int
    {
        $this->info('========================================================');
        $this->info('  Proses Pencadangan PPDB ke Google Drive 4 TB');
        $this->info('========================================================');

        if (!$driveService->isConnected()) {
            $this->error('Google Drive belum terhubung! Jalankan perintah "php artisan ppdb:gdrive-connect" terlebih dahulu.');
            return 1;
        }

        try {
            // 1. Buat Arsip ZIP Lengkap
            $this->info('1. Mengarsipkan Database MySQL & Seluruh Berkas Dokumen Siswa...');
            $archive = $driveService->createBackupArchive();
            $fileSizeMB = round($archive['size'] / (1024 * 1024), 2);
            $this->line("   -> Berhasil membuat: {$archive['filename']}");
            $this->line("   -> Ukuran arsip: {$fileSizeMB} MB ({$archive['files_count']} berkas dokumen)");

            // 2. Unggah ke Google Drive 4 TB
            $this->info('2. Mengunggah arsip ke Google Drive 4 TB...');
            $driveResult = $driveService->uploadToGoogleDrive($archive['path'], $archive['filename']);
            $fileId = $driveResult['id'] ?? '-';
            $viewLink = $driveResult['webViewLink'] ?? "https://drive.google.com/file/d/{$fileId}/view";

            $this->info("   -> ✅ Berhasil diunggah ke Google Drive!");
            $this->line("   -> File ID: {$fileId}");
            $this->line("   -> Link File: {$viewLink}");

            // 3. Bersihkan file lokal jika tidak diminta disimpan
            if (!$this->option('keep-local')) {
                File::delete($archive['path']);
                $this->info('3. File cadangan sementara di server lokal telah dibersihkan.');
            }

            // 4. Kirim Notifikasi Sukses ke Telegram Panitia jika ada
            try {
                $msg = "💾 *BACKUP OTOMATIS GOOGLE DRIVE BERHASIL*\n\n"
                    . "• *Nama File:* `{$archive['filename']}`\n"
                    . "• *Ukuran:* {$fileSizeMB} MB\n"
                    . "• *Total Berkas:* {$archive['files_count']} dokumen\n"
                    . "• *Waktu:* " . now()->translatedFormat('d F Y, H:i:s') . " WIB\n"
                    . "• *Target:* Google Drive 4 TB\n\n"
                    . "🔗 [Buka Berkas di Google Drive]({$viewLink})";
                $telegram->sendCustomMessage($msg);
            } catch (\Throwable $te) {
                // Ignore telegram error if not configured
            }

            $this->newLine();
            $this->info('🎉 PROSES PENCADANGAN SELESAI 100% SUKSES!');
            return 0;

        } catch (\Throwable $e) {
            $this->error('Gagal mencadangkan ke Google Drive: ' . $e->getMessage());

            try {
                $telegram->sendCustomMessage("⚠️ *GAGAL BACKUP GOOGLE DRIVE*\n\nPesan Error: " . $e->getMessage());
            } catch (\Throwable $te) {
            }

            return 1;
        }
    }
}
