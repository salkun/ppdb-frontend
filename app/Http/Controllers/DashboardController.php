<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    use InteractsWithFastApi;

    /**
     * Show applicant dashboard with registration and payment status.
     */
    public function index(Request $request)
    {
        try {
            $response = $this->httpWithToken()->get($this->backendUrl() . '/api/ppdb/my-registration');

            if ($response->status() === 401) {
                // Token expired or invalid
                $request->session()->flush();
                return redirect()->route('login')->with('warning', 'Sesi login Anda telah berakhir. Silakan masuk kembali.');
            }

            if ($response->successful()) {
                $registration = $response->json();

                return view('ppdb.dashboard', [
                    'registration' => $registration,
                    'user' => [
                        'account_id' => session('account_id'),
                        'nik' => session('nik'),
                        'full_name' => session('full_name'),
                        'email' => session('email'),
                    ],
                    'backendUrl' => $this->backendUrl(),
                ]);
            }

            $errorMessage = $this->extractErrorMessage($response, 'Gagal mengambil data pendaftaran Anda.');
            return view('ppdb.dashboard', [
                'registration' => null,
                'error' => $errorMessage,
                'user' => [
                    'account_id' => session('account_id'),
                    'nik' => session('nik'),
                    'full_name' => session('full_name'),
                    'email' => session('email'),
                ],
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('PPDB Dashboard Error: ' . $e->getMessage());
            return view('ppdb.dashboard', [
                'registration' => null,
                'error' => 'Koneksi ke backend bermasalah: ' . $e->getMessage(),
                'user' => [
                    'account_id' => session('account_id'),
                    'nik' => session('nik'),
                    'full_name' => session('full_name'),
                    'email' => session('email'),
                ],
                'backendUrl' => $this->backendUrl(),
            ]);
        }
    }
}
