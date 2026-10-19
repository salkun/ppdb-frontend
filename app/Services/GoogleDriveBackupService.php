<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class GoogleDriveBackupService
{
    /**
     * Dapatkan URL autentikasi Google OAuth 2.0 untuk akun pengguna.
     */
    public function getAuthUrl(string $redirectUri = 'http://127.0.0.1:8989'): string
    {
        $clientId = config('services.google_drive.client_id');

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Tukarkan authorization code dengan refresh token permanen dan simpan ke .env.
     */
    public function exchangeCode(string $code, string $redirectUri = 'http://127.0.0.1:8989'): string
    {
        $clientId = config('services.google_drive.client_id');
        $clientSecret = config('services.google_drive.client_secret');

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => trim($code),
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            $err = $response->json('error_description') ?? $response->body();
            throw new \RuntimeException('Gagal menukarkan kode OAuth Google: ' . $err);
        }

        $data = $response->json();
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            throw new \RuntimeException('Google tidak mengembalikan refresh token. Harap coba ulangi dengan prompt=consent.');
        }

        $this->saveRefreshTokenToEnv($refreshToken);

        return $refreshToken;
    }

    /**
     * Simpan refresh token ke file .env
     */
    public function saveRefreshTokenToEnv(string $refreshToken): void
    {
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $content = File::get($envPath);
            if (str_contains($content, 'GOOGLE_DRIVE_REFRESH_TOKEN=')) {
                $content = preg_replace(
                    '/^GOOGLE_DRIVE_REFRESH_TOKEN=.*$/m',
                    'GOOGLE_DRIVE_REFRESH_TOKEN=' . $refreshToken,
                    $content
                );
            } else {
                $content .= "\nGOOGLE_DRIVE_REFRESH_TOKEN=" . $refreshToken . "\n";
            }
            File::put($envPath, $content);
        }

        config(['services.google_drive.refresh_token' => $refreshToken]);
    }

    /**
     * Ambil access token sementara menggunakan refresh token.
     */
    public function getAccessToken(): string
    {
        $clientId = config('services.google_drive.client_id');
        $clientSecret = config('services.google_drive.client_secret');
        $refreshToken = config('services.google_drive.refresh_token');

        if (empty($refreshToken)) {
            throw new \RuntimeException('Google Drive belum terhubung. Refresh token belum tersedia.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            $err = $response->json('error_description') ?? $response->body();
            throw new \RuntimeException('Gagal memperbarui Access Token Google: ' . $err);
        }

        return $response->json('access_token');
    }

    /**
     * Cek apakah status integrasi Google Drive saat ini aktif dan valid.
     */
    public function isConnected(): bool
    {
        try {
            $token = $this->getAccessToken();
            return !empty($token);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Buat file arsip ZIP lengkap berisi dump database MySQL + seluruh file berkas siswa.
     */
    public function createBackupArchive(): array
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $zipFilename = 'PPDB_BACKUP_' . $timestamp . '.zip';
        $zipPath = $backupDir . DIRECTORY_SEPARATOR . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file ZIP di: ' . $zipPath);
        }

        // 1. Ekspor Database MySQL ke format SQL (Pure PHP Dump)
        $sqlDump = $this->generateDatabaseDump();
        $zip->addFromString('database/ppdb_database_dump.sql', $sqlDump);

        // 2. Tambahkan Seluruh Berkas Dokumen Persyaratan & Bukti Bayar
        $filesCount = 0;
        $directoriesToBackup = [
            'uploads/ppdb_documents' => public_path('uploads/ppdb_documents'),
            'uploads/ppdb_payments' => public_path('uploads/ppdb_payments'),
            'storage/documents' => storage_path('app/public/documents'),
            'storage/ppdb_documents' => storage_path('app/public/ppdb_documents'),
            'storage/ppdb_payments' => storage_path('app/public/ppdb_payments'),
        ];

        foreach ($directoriesToBackup as $zipSubDir => $dirPath) {
            if (File::exists($dirPath) && File::isDirectory($dirPath)) {
                $allFiles = File::allFiles($dirPath);
                foreach ($allFiles as $file) {
                    $relativeZipPath = $zipSubDir . '/' . $file->getFilename();
                    $zip->addFile($file->getRealPath(), $relativeZipPath);
                    $filesCount++;
                }
            }
        }

        // 3. Tambahkan File Metadata Manifest Informasi Backup
        $manifest = [
            'backup_name' => $zipFilename,
            'created_at' => now()->toIso8601String(),
            'total_files' => $filesCount,
            'database_tables_included' => ['ppdb_accounts', 'ppdb_registrations', 'users', 'sync_logs'],
            'system' => 'SMPS2 Al-Muhajirin PPDB Portal',
        ];
        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        $zip->close();

        return [
            'path' => $zipPath,
            'filename' => $zipFilename,
            'size' => File::size($zipPath),
            'files_count' => $filesCount,
        ];
    }

    /**
     * Unggah file ZIP backup ke folder Google Drive 4 TB milik pengguna.
     */
    public function uploadToGoogleDrive(string $filePath, string $filename): array
    {
        @ini_set('max_execution_time', '600');
        @set_time_limit(600);

        $accessToken = $this->getAccessToken();
        $folderId = config('services.google_drive.folder_id');

        if (empty($folderId)) {
            throw new \RuntimeException('Folder ID Google Drive belum dikonfigurasi di file .env.');
        }

        $fileSize = filesize($filePath);
        $metadata = [
            'name' => $filename,
            'parents' => [$folderId],
            'description' => 'Backup Otomatis PPDB SMPS2 Al-Muhajirin (' . now()->translatedFormat('d F Y H:i') . ')',
        ];

        // 1. Inisiasi Resumable Upload Session (ringan, cepat)
        $initResponse = Http::timeout(60)
            ->withToken($accessToken)
            ->withHeaders([
                'X-Upload-Content-Type' => 'application/zip',
                'X-Upload-Content-Length' => $fileSize,
                'Content-Type' => 'application/json; charset=UTF-8',
            ])
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable', $metadata);

        if (!$initResponse->successful()) {
            $err = $initResponse->json('error.message') ?? $initResponse->body();
            throw new \RuntimeException('Gagal memulai sesi upload Google Drive: ' . $err);
        }

        $uploadUrl = $initResponse->header('Location');
        if (empty($uploadUrl)) {
            throw new \RuntimeException('Google Drive tidak mengembalikan URL upload resumable.');
        }

        // 2. Unggah file fisik secara streaming ke upload URL dengan timeout 600 detik (10 menit)
        $handle = fopen($filePath, 'rb');
        $uploadResponse = Http::timeout(600)
            ->connectTimeout(60)
            ->withHeaders([
                'Content-Type' => 'application/zip',
                'Content-Length' => $fileSize,
            ])
            ->withBody($handle, 'application/zip')
            ->put($uploadUrl);

        if (is_resource($handle)) {
            fclose($handle);
        }

        if (!$uploadResponse->successful()) {
            $err = $uploadResponse->json('error.message') ?? $uploadResponse->body();
            throw new \RuntimeException('Gagal mengunggah file ke Google Drive: ' . $err);
        }

        return $uploadResponse->json() ?? [];
    }

    /**
     * Ambil daftar file backup yang tersimpan di folder Google Drive.
     */
    public function listBackups(): array
    {
        try {
            $accessToken = $this->getAccessToken();
            $folderId = config('services.google_drive.folder_id');

            $response = Http::withToken($accessToken)->get('https://www.googleapis.com/drive/v3/files', [
                'q' => "'{$folderId}' in parents and trashed = false and name contains 'PPDB_BACKUP_'",
                'fields' => 'files(id, name, size, createdTime, webViewLink)',
                'orderBy' => 'createdTime desc',
                'pageSize' => 20,
            ]);

            if ($response->successful()) {
                return $response->json('files') ?? [];
            }
            return [];
        } catch (\Throwable $e) {
            Log::error('Google Drive List Backups Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate SQL dump untuk tabel-tabel utama PPDB secara mandiri (Pure PHP MySQL Dump).
     */
    protected function generateDatabaseDump(): string
    {
        $tables = ['ppdb_accounts', 'ppdb_registrations', 'users', 'sync_logs'];
        $output = "-- =====================================================\n";
        $output .= "-- PPDB SMPS2 Al-Muhajirin Database Backup\n";
        $output .= "-- Generated At: " . now()->toIso8601String() . "\n";
        $output .= "-- =====================================================\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            try {
                // Periksa apakah tabel ada
                $rows = DB::table($table)->get();

                $output .= "--\n-- Table structure for `{$table}`\n--\n";
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                if (!empty($createTable)) {
                    $firstKey = array_keys((array)$createTable[0])[1] ?? 'Create Table';
                    $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    $output .= ((array)$createTable[0])[$firstKey] . ";\n\n";
                }

                // Baris data
                if ($rows->count() > 0) {
                    $output .= "--\n-- Dumping data for table `{$table}`\n--\n";
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $keys = array_map(fn($k) => "`{$k}`", array_keys($rowArray));
                        $values = array_map(function ($val) {
                            if (is_null($val)) return 'NULL';
                            return "'" . addslashes((string)$val) . "'";
                        }, array_values($rowArray));

                        $output .= "INSERT INTO `{$table}` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            } catch (\Throwable $e) {
                $output .= "-- Warning: Could not dump table `{$table}`: " . $e->getMessage() . "\n\n";
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $output;
    }
}
