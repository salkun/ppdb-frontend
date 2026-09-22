<?php

namespace App\Console\Commands;

use App\Services\ApiSyncService;
use Illuminate\Console\Command;

class PpdbSyncToApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ppdb:sync-to-api {--token= : Admin API Bearer token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi seluruh data lokal PPDB (akun, registrasi, formulir, verifikasi bayar) ke FastAPI Master Server';

    /**
     * Execute the console command.
     */
    public function handle(ApiSyncService $syncService): int
    {
        $this->info('Memulai sinkronisasi data lokal PPDB ke FastAPI Master Server...');

        $token = $this->option('token');
        $result = $syncService->syncAllPending($token);

        $this->table(
            ['Komponen', 'Jumlah Berhasil Disinkron'],
            [
                ['Akun Calon Siswa', $result['accounts_synced']],
                ['Data Registrasi & Formulir', $result['registrations_synced']],
                ['Verifikasi Pembayaran', $result['payments_synced']],
                ['Penerimaan Siswa (SIAKAD)', $result['acceptances_synced']],
                ['Gagal / Error', $result['failed_count']],
            ]
        );

        if (!empty($result['errors'])) {
            $this->warn('Daftar kendala sinkronisasi:');
            foreach ($result['errors'] as $err) {
                $this->error(" - {$err}");
            }
        }

        $this->info('Proses sinkronisasi selesai.');
        return Command::SUCCESS;
    }
}
