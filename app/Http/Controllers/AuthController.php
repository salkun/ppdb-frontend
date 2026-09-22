<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use InteractsWithFastApi;

    /**
     * Show registration page.
     */
    public function showRegister()
    {
        if (session()->has('api_token') || session()->has('account_id')) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Process student registration: Save directly to MySQL local (Source of Truth).
     */
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
        ]);

        try {
            // 1. Simpan ke database MySQL lokal (firstOrCreate or update)
            $account = PpdbAccount::where('email', $request->email)->first();
            if ($account) {
                $account->update([
                    'full_name' => $request->full_name,
                    'password' => Hash::make($request->password),
                    'sync_status' => 'pending',
                ]);
            } else {
                $account = PpdbAccount::create([
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'sync_status' => 'pending',
                    'sync_message' => 'Akun baru dibuat secara mandiri oleh calon siswa',
                ]);
            }

            // 2. Buat entri registrasi lokal jika belum ada
            if (!$account->registration) {
                PpdbRegistration::create([
                    'account_id' => $account->id,
                    'payment_status' => 'unpaid',
                    'payment_amount' => 0.0,
                    'registration_status' => 'pending',
                    'form_data' => [
                        'full_name' => $request->full_name,
                        'contact' => [
                            'email' => $request->email,
                        ],
                    ],
                    'sync_status' => 'pending',
                    'sync_message' => 'Registrasi baru belum diverifikasi',
                ]);
            }

            // 3. Coba kirimkan ke backend FastAPI jika sedang terhubung
            try {
                $response = $this->httpClient()->post($this->backendUrl() . '/api/ppdb/register-account', [
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

                if ($response->successful()) {
                    $body = $response->json();
                    if (!empty($body['id'])) {
                        $account->markSynced($body['id'], 'Tersinkron saat registrasi mandiri');
                    }
                }
            } catch (\Throwable $apiEx) {
                Log::info('PPDB Register: Remote API currently unreachable, saved locally. Note: ' . $apiEx->getMessage());
            }

            return redirect()->route('login')->with('success', 'Akun pendaftaran berhasil dibuat! Silakan masuk menggunakan email dan password Anda.');

        } catch (\Exception $e) {
            Log::error('PPDB Register Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput($request->except('password', 'password_confirmation'))->with('error', 'Terjadi kendala saat memproses pendaftaran. Silakan periksa data Anda atau coba beberapa saat lagi.');
        }
    }

    /**
     * Show login page.
     */
    public function showLogin()
    {
        if (session()->has('api_token') || session()->has('account_id')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Process login: Hybrid Authentication (Online API JWT + Local MySQL Fallback).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        try {
            // 1. Coba login ke API backend terlebih dahulu (untuk dapat JWT token dan mock test)
            try {
                $response = $this->httpClient()->post($this->backendUrl() . '/api/ppdb/login', [
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $token = $data['access_token'] ?? null;
                    $remoteAccId = $data['account_id'] ?? null;

                    // Buat atau perbarui akun lokal
                    $account = PpdbAccount::where('email', $data['email'] ?? $request->email)->first();
                    if (!$account) {
                        $account = PpdbAccount::create([
                            'nik' => $data['nik'] ?? null,
                            'full_name' => $data['full_name'] ?? 'Calon Siswa',
                            'email' => $data['email'] ?? $request->email,
                            'password' => Hash::make($request->password),
                            'remote_id' => $remoteAccId,
                            'sync_status' => 'synced',
                            'last_synced_at' => now(),
                        ]);

                        PpdbRegistration::create([
                            'account_id' => $account->id,
                            'payment_status' => 'unpaid',
                            'payment_amount' => 0.0,
                            'registration_status' => 'pending',
                            'remote_id' => null,
                            'sync_status' => 'pending',
                        ]);
                    } else {
                        $account->update([
                            'password' => Hash::make($request->password),
                            'remote_id' => $remoteAccId ?: $account->remote_id,
                            'sync_status' => 'synced',
                            'last_synced_at' => now(),
                        ]);
                    }

                    session([
                        'api_token' => $token ?: ('local_auth_' . $account->id),
                        'account_id' => $account->id,
                        'full_name' => $data['full_name'] ?? $account->full_name,
                        'email' => $data['email'] ?? $account->email,
                        'nik' => $data['nik'] ?? $account->nik,
                    ]);

                    return redirect()->route('dashboard')->with('success', 'Selamat datang kembali, ' . ($data['full_name'] ?? $account->full_name) . '!');
                }
            } catch (\Throwable $apiEx) {
                Log::info('PPDB Login API note: ' . $apiEx->getMessage());
            }

            // 2. Offline Mode: Validasi kredensial langsung terhadap database MySQL lokal
            $localAccount = PpdbAccount::with('registration')
                ->where('email', $request->email)
                ->first();

            if ($localAccount && Hash::check($request->password, $localAccount->password)) {
                if (!$localAccount->registration) {
                    PpdbRegistration::create([
                        'account_id' => $localAccount->id,
                        'payment_status' => 'unpaid',
                        'payment_amount' => 0.0,
                        'registration_status' => 'pending',
                        'sync_status' => 'pending',
                    ]);
                    $localAccount->load('registration');
                }

                session([
                    'api_token' => 'local_auth_' . $localAccount->id,
                    'account_id' => $localAccount->id,
                    'full_name' => $localAccount->full_name,
                    'email' => $localAccount->email,
                    'nik' => $localAccount->nik,
                ]);

                return redirect()->route('dashboard')->with('success', 'Selamat datang kembali, ' . $localAccount->full_name . '!');
            }

            return back()->withInput($request->except('password'))->with('error', 'Kredensial salah atau akun tidak ditemukan di sistem.');

        } catch (\Exception $e) {
            Log::error('PPDB Login Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput($request->except('password'))->with('error', 'Terjadi kendala saat memproses masuk. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Logout and flush session.
     */
    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sesi pendaftaran.');
    }
}
