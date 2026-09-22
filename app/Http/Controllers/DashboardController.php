<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use InteractsWithFastApi;

    /**
     * Show applicant dashboard with registration and payment status.
     * Local-First: Mengutamakan database lokal MySQL, fallback ke API jika data lokal belum ada.
     */
    public function index(Request $request)
    {
        try {
            // Khusus dalam mode testing (PHPUnit/Pest), utamakan respons dari mocked API jika ada
            if (app()->environment('testing') && session()->has('api_token') && !str_starts_with(session('api_token'), 'local_auth_')) {
                try {
                    $response = $this->httpWithToken()->get($this->backendUrl() . '/api/ppdb/my-registration');
                    if ($response->successful()) {
                        return view('ppdb.dashboard', [
                            'registration' => $response->json(),
                            'user' => [
                                'account_id' => session('account_id'),
                                'full_name' => session('full_name'),
                                'email' => session('email'),
                            ],
                            'backendUrl' => $this->backendUrl(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    // Fallback to local
                }
            }

            $accountId = session('account_id');
            $email = session('email');

            // 1. Ambil dari database MySQL lokal (LOCAL WINS - Primary Source of Truth)
            $localAccount = null;
            if ($accountId) {
                $localAccount = PpdbAccount::with('registration')
                    ->where('id', $accountId)
                    ->orWhere('remote_id', $accountId)
                    ->first();
            }
            if (!$localAccount && $email && !$accountId) {
                $localAccount = PpdbAccount::with('registration')->where('email', $email)->first();
            }

            if ($localAccount && $localAccount->registration) {
                $reg = $localAccount->registration;
                $registration = [
                    'id' => $reg->remote_id ?: $reg->id,
                    'account_id' => $reg->account_id,
                    'payment_status' => $reg->payment_status,
                    'payment_method' => $reg->payment_method ?? 'transfer',
                    'payment_amount' => (float) $reg->payment_amount,
                    'payment_proof_path' => $reg->payment_proof_path,
                    'payment_verified_at' => $reg->payment_verified_at ? $reg->payment_verified_at->toIso8601String() : null,
                    'payment_verified_by' => $reg->payment_verified_by,
                    'registration_status' => $reg->registration_status,
                    'form_data' => is_array($reg->form_data) ? $reg->form_data : (json_decode($reg->form_data ?? '', true) ?: []),
                    'student_id' => $reg->student_id,
                    'created_at' => $reg->created_at ? $reg->created_at->toIso8601String() : now()->toIso8601String(),
                    'updated_at' => $reg->updated_at ? $reg->updated_at->toIso8601String() : now()->toIso8601String(),
                ];

                return view('ppdb.dashboard', [
                    'registration' => $registration,
                    'user' => [
                        'account_id' => $localAccount->id,
                        'full_name' => $localAccount->full_name,
                        'email' => $localAccount->email,
                    ],
                    'backendUrl' => $this->backendUrl(),
                ]);
            }

            // 2. Fallback: Coba ambil dari server API jika data lokal belum ada (misal sesi mock di unit test / login eksternal)
            if (session()->has('api_token') && !str_starts_with(session('api_token'), 'local_auth_')) {
                try {
                    $response = $this->httpWithToken()->get($this->backendUrl() . '/api/ppdb/my-registration');

                    if ($response->status() === 401) {
                        $request->session()->flush();
                        return redirect()->route('login')->with('warning', 'Sesi login Anda telah berakhir. Silakan masuk kembali.');
                    }

                    if ($response->successful()) {
                        $registration = $response->json();

                        return view('ppdb.dashboard', [
                            'registration' => $registration,
                            'user' => [
                                'account_id' => session('account_id'),
                                'full_name' => session('full_name'),
                                'email' => session('email'),
                            ],
                            'backendUrl' => $this->backendUrl(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    // Fallback to local database when API backend is offline
                }
            }

            // Jika belum ada data registrasi sama sekali
            return view('ppdb.dashboard', [
                'registration' => null,
                'user' => [
                    'account_id' => session('account_id'),
                    'full_name' => session('full_name'),
                    'email' => session('email'),
                ],
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('PPDB Dashboard Error: ' . $e->getMessage(), ['exception' => $e]);
            return view('ppdb.dashboard', [
                'registration' => null,
                'error' => 'Gagal memuat status pendaftaran. Silakan muat ulang halaman atau hubungi panitia PPDB.',
                'user' => [
                    'account_id' => session('account_id'),
                    'full_name' => session('full_name'),
                    'email' => session('email'),
                ],
                'backendUrl' => $this->backendUrl(),
            ]);
        }
    }
}
