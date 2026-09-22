<?php

namespace App\Services;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Log;

class ApiSyncService
{
    use InteractsWithFastApi;

    /**
     * One-Click Bulk Sync: Kirim seluruh data lokal yang berstatus pending/failed ke Master Data API.
     * Local wins: Nilai lokal menjadi sumber kebenaran yang ditimpa ke server master.
     *
     * @param string|null $adminToken
     * @return array
     */
    public function syncAllPending(?string $adminToken = null): array
    {
        $token = $adminToken ?: $this->adminApiToken();
        $summary = [
            'accounts_synced' => 0,
            'registrations_synced' => 0,
            'payments_synced' => 0,
            'acceptances_synced' => 0,
            'failed_count' => 0,
            'errors' => [],
        ];

        // 1. Sinkronisasi Akun Pendaftar yang belum tersinkron
        $pendingAccounts = PpdbAccount::whereIn('sync_status', ['pending', 'failed'])
            ->latest()
            ->get();

        foreach ($pendingAccounts as $account) {
            try {
                $success = $this->syncAccount($account, $token);
                if ($success) {
                    $summary['accounts_synced']++;
                } else {
                    $summary['failed_count']++;
                    $summary['errors'][] = "Akun {$account->full_name} ({$account->email}): {$account->sync_message}";
                }
            } catch (\Throwable $e) {
                $summary['failed_count']++;
                $summary['errors'][] = "Akun {$account->full_name}: " . $e->getMessage();
            }
        }

        // 2. Sinkronisasi Registrasi & Formulir & Pembayaran
        $pendingRegistrations = PpdbRegistration::with('account')
            ->whereIn('sync_status', ['pending', 'failed'])
            ->latest()
            ->get();

        foreach ($pendingRegistrations as $reg) {
            try {
                $regResult = $this->syncRegistration($reg, $token);
                if ($regResult['success']) {
                    $summary['registrations_synced']++;
                    if ($regResult['payment_synced'] ?? false) {
                        $summary['payments_synced']++;
                    }
                    if ($regResult['accepted_synced'] ?? false) {
                        $summary['acceptances_synced']++;
                    }
                } else {
                    $summary['failed_count']++;
                    $summary['errors'][] = "Pendaftaran {$reg->account->full_name}: {$reg->sync_message}";
                }
            } catch (\Throwable $e) {
                $summary['failed_count']++;
                $summary['errors'][] = "Pendaftaran {$reg->id}: " . $e->getMessage();
            }
        }

        return $summary;
    }

    /**
     * Sinkronisasi akun calon siswa ke FastAPI backend (/api/ppdb/register-account).
     */
    public function syncAccount(PpdbAccount $account, ?string $adminToken = null): bool
    {
        $endpoint = $this->backendUrl() . '/api/ppdb/register-account';

        // Jika akun sudah punya remote_id, cari atau verifikasi di API
        if ($account->remote_id) {
            $account->markSynced($account->remote_id, 'Sudah terdaftar di master server.');
            return true;
        }

        $payload = [
            'full_name' => $account->full_name,
            'email' => $account->email,
            'password' => 'PpdbPassword' . rand(1000, 9999) . '!', // Fallback password jika akun dibuat lokal tanpa plaintext
        ];
        if (!empty($account->nik)) {
            $payload['nik'] = preg_replace('/[^0-9]/', '', $account->nik);
        }

        try {
            $response = $this->httpClient()->post($endpoint, $payload);
            $statusCode = $response->status();
            $body = $response->json();

            // Success (201 Created)
            if ($response->successful() && isset($body['id'])) {
                $remoteId = $body['id'];
                $account->markSynced($remoteId, 'Berhasil disinkronkan ke Master API');

                $this->logSync(
                    'account',
                    $account->id,
                    'create',
                    $endpoint,
                    'POST',
                    $payload,
                    $statusCode,
                    $body,
                    'success'
                );

                // Cari apakah ada registrasi terkait untuk update remote_id
                $this->linkRegistrationRemoteId($account, $adminToken);

                return true;
            }

            // Jika akun sudah ada di remote server (misal duplikasi email/nik), temukan remote_id lewat endpoint admin
            if (in_array($statusCode, [400, 409, 422, 500])) {
                $linked = $this->findAndLinkRemoteAccount($account, $adminToken);
                if ($linked) {
                    return true;
                }
            }

            $errorMsg = $this->extractErrorMessage($response, 'Gagal mendaftarkan akun ke backend.');
            $account->markFailed($errorMsg);

            $this->logSync(
                'account',
                $account->id,
                'create',
                $endpoint,
                'POST',
                $payload,
                $statusCode,
                $body,
                'failed',
                $errorMsg
            );

            return false;

        } catch (\Throwable $e) {
            Log::error("ApiSyncService::syncAccount error: {$e->getMessage()}");
            $account->markFailed('Koneksi timeout/gagal: ' . $e->getMessage());

            $this->logSync(
                'account',
                $account->id,
                'create',
                $endpoint,
                'POST',
                $payload,
                null,
                null,
                'failed',
                $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Sinkronisasi data pendaftaran, formulir, verifikasi pembayaran, dan kelulusan ke Master API.
     */
    public function syncRegistration(PpdbRegistration $registration, ?string $adminToken = null): array
    {
        $result = [
            'success' => false,
            'payment_synced' => false,
            'accepted_synced' => false,
        ];

        // Pastikan akun sudah tersinkron dan punya remote_id
        $account = $registration->account;
        if (!$account) {
            $registration->markFailed('Relasi akun pendaftar tidak ditemukan.');
            return $result;
        }

        if (!$account->remote_id) {
            $accountSynced = $this->syncAccount($account, $adminToken);
            if (!$accountSynced || !$account->remote_id) {
                $registration->markFailed('Menunggu akun berhasil tersinkron ke Master API.');
                return $result;
            }
        }

        // Cari remote_id pendaftaran jika belum tertaut
        if (!$registration->remote_id) {
            $this->linkRegistrationRemoteId($account, $adminToken);
            $registration->refresh();
        }

        $remoteRegId = $registration->remote_id;
        if (!$remoteRegId) {
            $registration->markFailed('ID registrasi master tidak ditemukan.');
            return $result;
        }

        $token = $adminToken ?: $this->adminApiToken();
        $client = $token ? $this->httpClient()->withToken($token) : $this->httpClient();

        // 1. Kirim update data pendaftaran & formulir via PUT /api/ppdb/registrations/{id}
        $endpoint = $this->backendUrl() . '/api/ppdb/registrations/' . $remoteRegId;
        $cleanNik = !empty($account->nik) ? preg_replace('/[^0-9]/', '', $account->nik) : '';

        $payload = [
            'full_name' => $account->full_name,
            'nik' => $cleanNik,
            'email' => $account->email,
            'payment_status' => $registration->payment_status,
            'payment_amount' => (float) $registration->payment_amount,
            'registration_status' => $registration->registration_status,
            'form_data' => $registration->form_data ?: [],
        ];

        try {
            $response = $client->put($endpoint, $payload);
            $statusCode = $response->status();
            $body = $response->json();

            if (!$response->successful()) {
                $errorMsg = $this->extractErrorMessage($response, 'Gagal update data registrasi di server master.');
                $registration->markFailed($errorMsg);

                $this->logSync(
                    'registration',
                    $registration->id,
                    'update',
                    $endpoint,
                    'PUT',
                    $payload,
                    $statusCode,
                    $body,
                    'failed',
                    $errorMsg
                );

                return $result;
            }

            // 2. Jika status lokal adalah 'paid', pastikan endpoint verifikasi pembayaran juga terpanggil
            if ($registration->payment_status === 'paid') {
                $payEndpoint = $this->backendUrl() . '/api/ppdb/verify-payment/' . $remoteRegId;
                $payRes = $client->put($payEndpoint, [
                    'payment_status' => 'paid',
                    'payment_amount' => (float) ($registration->payment_amount ?: 400000.0),
                ]);

                if ($payRes->successful()) {
                    $result['payment_synced'] = true;
                }
            }

            // 3. Jika status registrasi lokal adalah 'accepted', kirim accept ke master SIAKAD
            if ($registration->registration_status === 'accepted' && empty($registration->student_id)) {
                $acceptEndpoint = $this->backendUrl() . '/api/ppdb/accept/' . $remoteRegId;
                $acceptRes = $client->post($acceptEndpoint);
                if ($acceptRes->successful()) {
                    $acceptData = $acceptRes->json();
                    if (!empty($acceptData['student_id'])) {
                        $registration->student_id = $acceptData['student_id'];
                    }
                    $result['accepted_synced'] = true;
                }
            }

            $registration->markSynced($remoteRegId, 'Berhasil disinkronkan ke Master API');
            $this->logSync(
                'registration',
                $registration->id,
                'update',
                $endpoint,
                'PUT',
                $payload,
                $statusCode,
                $body,
                'success'
            );

            $result['success'] = true;
            return $result;

        } catch (\Throwable $e) {
            Log::error("ApiSyncService::syncRegistration error: {$e->getMessage()}");
            $registration->markFailed('Koneksi timeout/gagal: ' . $e->getMessage());

            $this->logSync(
                'registration',
                $registration->id,
                'update',
                $endpoint,
                'PUT',
                $payload,
                null,
                null,
                'failed',
                $e->getMessage()
            );

            return $result;
        }
    }

    /**
     * Mencari registrasi di master server berdasarkan email / account_id untuk menautkan remote_id.
     */
    protected function linkRegistrationRemoteId(PpdbAccount $account, ?string $adminToken = null): void
    {
        $token = $adminToken ?: $this->adminApiToken();
        if (!$token) return;

        try {
            $client = $this->httpClient()->withToken($token);
            $res = $client->get($this->backendUrl() . '/api/ppdb/registrations', [
                'search' => $account->email,
            ]);

            if ($res->successful()) {
                $registrations = $res->json();
                foreach ($registrations as $remoteReg) {
                    $remoteAccEmail = $remoteReg['account']['email'] ?? '';
                    $remoteAccId = $remoteReg['account_id'] ?? '';

                    if ($remoteAccEmail === $account->email || $remoteAccId === $account->remote_id) {
                        $localReg = $account->registration;
                        if ($localReg && !empty($remoteReg['id'])) {
                            $localReg->remote_id = $remoteReg['id'];
                            $localReg->save();
                        }
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("ApiSyncService::linkRegistrationRemoteId note: {$e->getMessage()}");
        }
    }

    /**
     * Temukan remote ID akun dari daftar registrasi jika backend menolak registrasi baru (karena duplikat).
     */
    protected function findAndLinkRemoteAccount(PpdbAccount $account, ?string $adminToken = null): bool
    {
        $token = $adminToken ?: $this->adminApiToken();
        if (!$token) return false;

        try {
            $client = $this->httpClient()->withToken($token);
            $res = $client->get($this->backendUrl() . '/api/ppdb/registrations', [
                'search' => $account->email,
            ]);

            if ($res->successful()) {
                $registrations = $res->json();
                foreach ($registrations as $remoteReg) {
                    $remoteAccEmail = $remoteReg['account']['email'] ?? '';
                    $remoteAccNik = $remoteReg['account']['nik'] ?? '';

                    if ($remoteAccEmail === $account->email || (!empty($account->nik) && $remoteAccNik === $account->nik)) {
                        $remoteAccId = $remoteReg['account_id'] ?? ($remoteReg['account']['id'] ?? null);
                        if ($remoteAccId) {
                            $account->markSynced($remoteAccId, 'Ditautkan dengan akun yang sudah ada di Master API');
                            $localReg = $account->registration;
                            if ($localReg && !empty($remoteReg['id'])) {
                                $localReg->remote_id = $remoteReg['id'];
                                $localReg->save();
                            }
                            return true;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("ApiSyncService::findAndLinkRemoteAccount note: {$e->getMessage()}");
        }

        return false;
    }

    /**
     * Catat histori sinkronisasi ke tabel sync_logs.
     */
    protected function logSync(
        string $syncableType,
        string $syncableId,
        string $action,
        string $endpoint,
        string $httpMethod,
        ?array $payload,
        ?int $responseStatus,
        mixed $responseBody,
        string $status,
        ?string $errorMessage = null
    ): void {
        try {
            SyncLog::create([
                'syncable_type' => $syncableType,
                'syncable_id' => $syncableId,
                'action' => $action,
                'api_endpoint' => $endpoint,
                'http_method' => $httpMethod,
                'request_payload' => $payload ? json_encode($payload) : null,
                'response_status' => $responseStatus,
                'response_body' => is_array($responseBody) ? json_encode($responseBody) : (is_string($responseBody) ? $responseBody : null),
                'status' => $status,
                'error_message' => $errorMessage,
                'attempt' => 1,
                'executed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to write to sync_logs: {$e->getMessage()}");
        }
    }
}
