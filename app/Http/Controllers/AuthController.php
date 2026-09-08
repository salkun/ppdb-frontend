<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use InteractsWithFastApi;

    /**
     * Show registration page.
     */
    public function showRegister()
    {
        if (session()->has('api_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Process student registration via FastAPI.
     */
    public function register(Request $request)
    {
        $request->validate([
            'nik' => ['required', 'string', 'digits:16'],
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'nik.digits' => 'NIK harus berjumlah 16 digit angka.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
        ]);

        try {
            $response = $this->httpClient()->post($this->backendUrl() . '/api/ppdb/register-account', [
                'nik' => $request->nik,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($response->successful()) {
                return redirect()->route('login')->with('success', 'Akun pendaftaran berhasil dibuat! Silakan masuk menggunakan NIK dan password Anda.');
            }

            $errorMessage = $this->extractErrorMessage($response, 'Pendaftaran akun gagal. Silakan coba beberapa saat lagi.');
            return back()->withInput($request->except('password', 'password_confirmation'))->with('error', $errorMessage);

        } catch (\Exception $e) {
            Log::error('PPDB Register Error: ' . $e->getMessage());
            return back()->withInput($request->except('password', 'password_confirmation'))->with('error', 'Gagal terhubung ke server backend: ' . $e->getMessage());
        }
    }

    /**
     * Show login page.
     */
    public function showLogin()
    {
        if (session()->has('api_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Process login via FastAPI.
     */
    public function login(Request $request)
    {
        $request->validate([
            'nik' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nik.required' => 'NIK atau Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        try {
            $response = $this->httpClient()->post($this->backendUrl() . '/api/ppdb/login', [
                'nik' => $request->nik,
                'password' => $request->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                session([
                    'api_token' => $data['access_token'],
                    'account_id' => $data['account_id'] ?? null,
                    'nik' => $data['nik'] ?? $request->nik,
                    'full_name' => $data['full_name'] ?? 'Calon Siswa',
                    'email' => $data['email'] ?? null,
                ]);

                return redirect()->route('dashboard')->with('success', 'Selamat datang kembali, ' . ($data['full_name'] ?? 'Calon Siswa') . '!');
            }

            $errorMessage = $this->extractErrorMessage($response, 'Kredensial salah atau akun tidak ditemukan.');
            return back()->withInput($request->except('password'))->with('error', $errorMessage);

        } catch (\Exception $e) {
            Log::error('PPDB Login Error: ' . $e->getMessage());
            return back()->withInput($request->except('password'))->with('error', 'Gagal terhubung ke server backend: ' . $e->getMessage());
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
