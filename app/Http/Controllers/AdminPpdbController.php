<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPpdbController extends Controller
{
    use InteractsWithFastApi;

    /**
     * Show Admin Login Page.
     */
    public function showLogin()
    {
        if (session()->has('admin_api_token') && session('is_admin')) {
            return redirect()->route('admin.ppdb.index');
        }

        return view('admin.login');
    }

    /**
     * Process Admin Login via FastAPI master auth.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username administrator wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        try {
            // FastAPI OAuth2 password flow requires form-urlencoded
            $response = $this->httpClient()
                ->asForm()
                ->post($this->backendUrl() . '/api/auth/login', [
                    'username' => $request->username,
                    'password' => $request->password,
                ]);

            if (!$response->successful()) {
                $errorMsg = $this->extractErrorMessage($response, 'Username atau password admin salah.');
                return back()->withInput($request->except('password'))->with('error', $errorMsg);
            }

            $tokenData = $response->json();
            $token = $tokenData['access_token'] ?? null;

            if (!$token) {
                return back()->withInput($request->except('password'))->with('error', 'Token autentikasi tidak valid dari server.');
            }

            // Verify role via /api/profile/me
            $profileRes = $this->httpClient()
                ->withToken($token)
                ->get($this->backendUrl() . '/api/profile/me');

            if (!$profileRes->successful()) {
                return back()->withInput($request->except('password'))->with('error', 'Gagal memverifikasi profil akun admin.');
            }

            $profile = $profileRes->json();
            $role = strtolower($profile['role'] ?? $profile['base_role'] ?? '');

            // Ensure user has admin privileges
            if ($role !== 'admin' && empty(array_intersect(['admin', 'administrator', 'staff'], (array)($profile['roles'] ?? [])))) {
                return back()->withInput($request->except('password'))->with('error', 'Akses ditolak. Akun Anda tidak memiliki hak akses Administrator.');
            }

            session([
                'admin_api_token' => $token,
                'admin_user' => $profile,
                'is_admin' => true,
            ]);

            return redirect()->route('admin.ppdb.index')->with('success', 'Selamat datang di Panel Admin PPDB, ' . ($profile['username'] ?? 'Admin') . '!');

        } catch (\Exception $e) {
            Log::error('Admin PPDB Login Error: ' . $e->getMessage());
            return back()->withInput($request->except('password'))->with('error', 'Koneksi ke backend gagal: ' . $e->getMessage());
        }
    }

    /**
     * Admin Logout.
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['admin_api_token', 'admin_user', 'is_admin']);

        return redirect()->route('admin.login')->with('info', 'Sesi admin telah berhasil ditutup.');
    }

    /**
     * PPDB Registrations Monitoring Dashboard.
     */
    public function index(Request $request)
    {
        $paymentStatus = $request->query('payment_status');
        $registrationStatus = $request->query('registration_status');
        $search = $request->query('search');

        try {
            $queryParams = [];
            if (!empty($paymentStatus)) $queryParams['payment_status'] = $paymentStatus;
            if (!empty($registrationStatus)) $queryParams['registration_status'] = $registrationStatus;
            if (!empty($search)) $queryParams['search'] = $search;

            $response = $this->httpWithAdminToken()
                ->get($this->backendUrl() . '/api/ppdb/registrations', $queryParams);

            if ($response->status() === 401) {
                $request->session()->forget(['admin_api_token', 'admin_user', 'is_admin']);
                return redirect()->route('admin.login')->with('warning', 'Sesi admin Anda telah kedaluwarsa.');
            }

            if (!$response->successful()) {
                $errorMsg = $this->extractErrorMessage($response, 'Gagal mengambil daftar pendaftar PPDB.');
                return view('admin.ppdb.index', [
                    'registrations' => [],
                    'stats' => ['total' => 0, 'pending_payment' => 0, 'paid' => 0, 'accepted' => 0],
                    'filters' => compact('paymentStatus', 'registrationStatus', 'search'),
                    'error' => $errorMsg,
                    'backendUrl' => $this->backendUrl(),
                ]);
            }

            $registrations = $response->json();

            // Fetch overall unfiltered count for accurate KPIs if filter applied
            $allResponse = empty($queryParams) ? $response : $this->httpWithAdminToken()->get($this->backendUrl() . '/api/ppdb/registrations');
            $allRegistrations = $allResponse->successful() ? $allResponse->json() : $registrations;

            $stats = [
                'total' => count($allRegistrations),
                'pending_payment' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'pending_verification')),
                'paid' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'paid')),
                'accepted' => count(array_filter($allRegistrations, fn($r) => ($r['registration_status'] ?? '') === 'accepted')),
            ];

            return view('admin.ppdb.index', [
                'registrations' => $registrations,
                'stats' => $stats,
                'filters' => compact('paymentStatus', 'registrationStatus', 'search'),
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Index Error: ' . $e->getMessage());
            return view('admin.ppdb.index', [
                'registrations' => [],
                'stats' => ['total' => 0, 'pending_payment' => 0, 'paid' => 0, 'accepted' => 0],
                'filters' => compact('paymentStatus', 'registrationStatus', 'search'),
                'error' => 'Koneksi ke backend bermasalah: ' . $e->getMessage(),
                'backendUrl' => $this->backendUrl(),
            ]);
        }
    }

    /**
     * Show applicant full details and dossier.
     */
    public function show(Request $request, string $id)
    {
        try {
            $response = $this->httpWithAdminToken()
                ->get($this->backendUrl() . '/api/ppdb/registrations/' . $id);

            if ($response->status() === 401) {
                $request->session()->forget(['admin_api_token', 'admin_user', 'is_admin']);
                return redirect()->route('admin.login')->with('warning', 'Sesi admin telah kedaluwarsa.');
            }

            if (!$response->successful()) {
                $errorMsg = $this->extractErrorMessage($response, 'Data pendaftar tidak ditemukan.');
                return redirect()->route('admin.ppdb.index')->with('error', $errorMsg);
            }

            $registration = $response->json();

            return view('admin.ppdb.show', [
                'registration' => $registration,
                'formData' => $registration['form_data'] ?? [],
                'account' => $registration['account'] ?? [],
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Show Error: ' . $e->getMessage());
            return redirect()->route('admin.ppdb.index')->with('error', 'Koneksi ke backend gagal: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment proof (paid / rejected).
     */
    public function verifyPayment(Request $request, string $id)
    {
        $request->validate([
            'payment_status' => ['required', 'in:paid,rejected'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $response = $this->httpWithAdminToken()
                ->put($this->backendUrl() . '/api/ppdb/verify-payment/' . $id, [
                    'payment_status' => $request->payment_status,
                    'payment_amount' => (float) ($request->payment_amount ?? 250000.00),
                ]);

            if ($response->successful()) {
                $label = $request->payment_status === 'paid' ? 'LUNAS (PAID)' : 'DITOLAK (REJECTED)';
                return back()->with('success', "Status pembayaran pendaftar berhasil diperbarui menjadi: {$label}.");
            }

            $errorMsg = $this->extractErrorMessage($response, 'Gagal memverifikasi pembayaran.');
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            Log::error('Admin Verify Payment Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memverifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Accept student and trigger atomic migration into master SIAKAD.
     */
    public function acceptStudent(Request $request, string $id)
    {
        try {
            $response = $this->httpWithAdminToken()
                ->post($this->backendUrl() . '/api/ppdb/accept/' . $id);

            if ($response->successful()) {
                $data = $response->json();
                $username = $data['username'] ?? 'NISN';
                $fullName = $data['full_name'] ?? 'Siswa';

                return back()->with('success', "Calon siswa {$fullName} resmi DITERIMA! Akun portal SIAKAD berhasil dibuat dengan Username: {$username}. Data telah dimigrasikan ke tabel master.");
            }

            $errorMsg = $this->extractErrorMessage($response, 'Gagal menerima calon siswa.');
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            Log::error('Admin Accept Student Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kendala saat migrasi data: ' . $e->getMessage());
        }
    }
}
