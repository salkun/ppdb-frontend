<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use App\Services\PpdbExportService;
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
        $major = $request->query('major');
        $search = $request->query('search');

        try {
            $queryParams = [];
            if (!empty($paymentStatus)) $queryParams['payment_status'] = $paymentStatus;
            if (!empty($registrationStatus)) $queryParams['registration_status'] = $registrationStatus;
            if (!empty($major)) $queryParams['major'] = $major;
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
                    'filters' => compact('paymentStatus', 'registrationStatus', 'major', 'search'),
                    'error' => $errorMsg,
                    'backendUrl' => $this->backendUrl(),
                ]);
            }

            $registrations = $response->json();

            // Client-side fallback filter for major if needed
            if (!empty($major)) {
                $registrations = array_values(array_filter($registrations, function ($r) use ($major) {
                    $rMajor = $r['form_data']['major'] ?? '';
                    return strtolower($rMajor) === strtolower($major);
                }));
            }

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
                'filters' => compact('paymentStatus', 'registrationStatus', 'major', 'search'),
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Index Error: ' . $e->getMessage());
            return view('admin.ppdb.index', [
                'registrations' => [],
                'stats' => ['total' => 0, 'pending_payment' => 0, 'paid' => 0, 'accepted' => 0],
                'filters' => compact('paymentStatus', 'registrationStatus', 'major', 'search'),
                'error' => 'Koneksi ke backend bermasalah: ' . $e->getMessage(),
                'backendUrl' => $this->backendUrl(),
            ]);
        }
    }

    /**
     * Export PPDB applicants to Excel (.xlsx) or CSV (.csv).
     */
    public function export(Request $request, PpdbExportService $exportService)
    {
        $paymentStatus = $request->query('payment_status');
        $registrationStatus = $request->query('registration_status');
        $major = $request->query('major');
        $search = $request->query('search');
        $format = strtolower($request->query('format', 'xlsx'));

        try {
            $queryParams = [];
            if (!empty($paymentStatus)) $queryParams['payment_status'] = $paymentStatus;
            if (!empty($registrationStatus)) $queryParams['registration_status'] = $registrationStatus;
            if (!empty($major)) $queryParams['major'] = $major;
            if (!empty($search)) $queryParams['search'] = $search;

            $response = $this->httpWithAdminToken()
                ->get($this->backendUrl() . '/api/ppdb/registrations', $queryParams);

            if ($response->status() === 401) {
                $request->session()->forget(['admin_api_token', 'admin_user', 'is_admin']);
                return redirect()->route('admin.login')->with('warning', 'Sesi admin Anda telah kedaluwarsa.');
            }

            if (!$response->successful()) {
                $errorMsg = $this->extractErrorMessage($response, 'Gagal mengambil data pendaftar untuk diekspor.');
                return redirect()->route('admin.ppdb.index')->with('error', $errorMsg);
            }

            $registrations = $response->json();

            // Client-side fallback filter for major if needed
            if (!empty($major)) {
                $registrations = array_values(array_filter($registrations, function ($r) use ($major) {
                    $rMajor = $r['form_data']['major'] ?? '';
                    return strtolower($rMajor) === strtolower($major);
                }));
            }

            // Label filter untuk judul dokumen
            $filterLabels = [];
            if (!empty($major)) $filterLabels[] = "Jurusan: " . strtoupper($major);
            if (!empty($paymentStatus)) $filterLabels[] = "Bayar: " . ucfirst($paymentStatus);
            if (!empty($registrationStatus)) $filterLabels[] = "Status: " . ucfirst($registrationStatus);
            if (!empty($search)) $filterLabels[] = "Cari: " . $search;
            $filterSuffix = !empty($filterLabels) ? " (" . implode(', ', $filterLabels) . ")" : "";

            $dateSuffix = date('Y-m-d_His');

            if ($format === 'csv') {
                $csvContent = $exportService->generateCsv($registrations);
                $filename = "data-pendaftar-ppdb_{$dateSuffix}.csv";

                return response($csvContent, 200, [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    'Cache-Control' => 'no-store, no-cache',
                ]);
            }

            // Default: Excel OpenXML (.xlsx)
            $reportTitle = 'DATA PENDAFTAR PESERTA DIDIK BARU (PPDB ONLINE)' . $filterSuffix;
            $xlsxTempFile = $exportService->generateXlsx($registrations, $reportTitle);
            $filename = "data-pendaftar-ppdb_{$dateSuffix}.xlsx";

            return response()->download($xlsxTempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-store, no-cache',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Export Error: ' . $e->getMessage());
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memproses ekspor data: ' . $e->getMessage());
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
     * Show applicant edit form for admin.
     */
    public function edit(Request $request, string $id)
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

            return view('admin.ppdb.edit', [
                'registration' => $registration,
                'formData' => $registration['form_data'] ?? [],
                'account' => $registration['account'] ?? [],
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Edit Error: ' . $e->getMessage());
            return redirect()->route('admin.ppdb.index')->with('error', 'Koneksi ke backend gagal: ' . $e->getMessage());
        }
    }

    /**
     * Update applicant data by admin.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'nik' => ['required', 'digits:16'],
            'email' => ['required', 'email', 'max:150'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'major' => ['nullable', 'string', 'in:reguler,bahasa,tahfidz,ict'],
            'school_origin' => ['nullable', 'string', 'max:150'],
            'school_origin_address' => ['nullable', 'string', 'max:500'],
            'gender' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'place_of_birth' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'payment_status' => ['required', 'in:unpaid,pending_verification,paid,rejected'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'registration_status' => ['required', 'in:pending,accepted,rejected'],
        ], [
            'full_name.required' => 'Nama lengkap calon siswa wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'payment_status.required' => 'Status pembayaran wajib dipilih.',
            'registration_status.required' => 'Status seleksi pendaftaran wajib dipilih.',
        ]);

        try {
            // Ambil data formulir saat ini untuk menjaga integritas data nested lainnya
            $currentRes = $this->httpWithAdminToken()->get($this->backendUrl() . '/api/ppdb/registrations/' . $id);
            $currentData = $currentRes->successful() ? $currentRes->json() : [];
            $currentForm = $currentData['form_data'] ?? [];

            // Update field form_data
            $currentForm['full_name'] = $request->full_name;
            $currentForm['nik'] = $request->nik;
            if ($request->filled('nisn')) $currentForm['nisn'] = $request->nisn;
            if ($request->filled('major')) $currentForm['major'] = $request->major;
            if ($request->filled('school_origin')) $currentForm['school_origin'] = $request->school_origin;
            if ($request->filled('school_origin_address')) $currentForm['school_origin_address'] = $request->school_origin_address;

            // Identity
            $identity = $currentForm['identity'] ?? [];
            if ($request->filled('gender')) $identity['gender'] = $request->gender;
            if ($request->filled('place_of_birth')) $identity['place_of_birth'] = $request->place_of_birth;
            if ($request->filled('date_of_birth')) $identity['date_of_birth'] = $request->date_of_birth;
            $currentForm['identity'] = $identity;

            // Contact
            $contact = $currentForm['contact'] ?? [];
            $contact['email'] = $request->email;
            if ($request->filled('mobile_number')) $contact['mobile_number'] = $request->mobile_number;
            if ($request->filled('whatsapp_number')) $contact['whatsapp_number'] = $request->whatsapp_number;
            $currentForm['contact'] = $contact;

            // Address
            $address = $currentForm['address'] ?? [];
            if ($request->filled('street_address')) $address['street_address'] = $request->street_address;
            $currentForm['address'] = $address;

            $payload = [
                'full_name' => $request->full_name,
                'nik' => $request->nik,
                'email' => $request->email,
                'payment_status' => $request->payment_status,
                'payment_amount' => (float) ($request->payment_amount ?? 0),
                'registration_status' => $request->registration_status,
                'form_data' => $currentForm,
            ];

            $response = $this->httpWithAdminToken()
                ->put($this->backendUrl() . '/api/ppdb/registrations/' . $id, $payload);

            if ($response->successful()) {
                return redirect()->route('admin.ppdb.show', $id)->with('success', "Data pendaftar {$request->full_name} berhasil diperbarui!");
            }

            $errorMsg = $this->extractErrorMessage($response, 'Gagal memperbarui data pendaftar.');
            return back()->withInput()->with('error', $errorMsg);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Delete applicant registration permanently.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $response = $this->httpWithAdminToken()
                ->delete($this->backendUrl() . '/api/ppdb/registrations/' . $id);

            if ($response->status() === 401) {
                $request->session()->forget(['admin_api_token', 'admin_user', 'is_admin']);
                return redirect()->route('admin.login')->with('warning', 'Sesi admin telah kedaluwarsa.');
            }

            if ($response->successful()) {
                return redirect()->route('admin.ppdb.index')->with('success', 'Data pendaftar berhasil dihapus secara permanen dari sistem PPDB.');
            }

            $errorMsg = $this->extractErrorMessage($response, 'Gagal menghapus data pendaftar.');
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kendala saat menghapus data: ' . $e->getMessage());
        }
    }
}
