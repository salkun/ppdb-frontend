<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use App\Services\PpdbExportService;
use App\Services\PpdbImportService;
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
     * Fetch registrations from backend API with error handling.
     */
    protected function fetchRegistrations(array $queryParams = []): ?array
    {
        $response = $this->httpWithAdminToken()
            ->get($this->backendUrl() . '/api/ppdb/registrations', $queryParams);

        if ($response->status() === 401) {
            session()->forget(['admin_api_token', 'admin_user', 'is_admin']);
            return null;
        }

        if (!$response->successful()) {
            return [];
        }

        return $response->json();
    }

    /**
     * PPDB Admin Dashboard: Ringkasan KPI, Statistik Jurusan & Verifikasi Cepat.
     */
    public function index(Request $request)
    {
        try {
            $allRegistrations = $this->fetchRegistrations();
            if ($allRegistrations === null) {
                return redirect()->route('admin.login')->with('warning', 'Sesi admin Anda telah kedaluwarsa.');
            }

            // Hitung statistik komprehensif
            $stats = [
                'total' => count($allRegistrations),
                'pending_payment' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'pending_verification')),
                'paid' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'paid')),
                'accepted' => count(array_filter($allRegistrations, fn($r) => ($r['registration_status'] ?? '') === 'accepted')),
                'with_form' => count(array_filter($allRegistrations, fn($r) => !empty($r['form_data']))),
                'total_amount' => array_reduce($allRegistrations, function ($acc, $r) {
                    return ($r['payment_status'] ?? '') === 'paid' ? $acc + (float)($r['payment_amount'] ?? 400000) : $acc;
                }, 0),
            ];

            // Statistik per jurusan
            $majorCounts = [
                'reguler' => 0,
                'bahasa' => 0,
                'tahfidz' => 0,
                'ict' => 0,
                'belum_pilih' => 0,
            ];
            foreach ($allRegistrations as $r) {
                $major = strtolower($r['form_data']['major'] ?? ($r['student']['major'] ?? ''));
                if (isset($majorCounts[$major])) {
                    $majorCounts[$major]++;
                } else {
                    $majorCounts['belum_pilih']++;
                }
            }

            // Antrean verifikasi pembayaran tercepat (yang butuh tindakan)
            $pendingVerifications = array_values(array_filter(
                $allRegistrations,
                fn($r) => ($r['payment_status'] ?? '') === 'pending_verification'
            ));

            // Pendaftar terbaru (urut berdasarkan tanggal terbaru)
            $recentRegistrations = $allRegistrations;
            usort($recentRegistrations, function ($a, $b) {
                return strtotime($b['created_at'] ?? 'now') - strtotime($a['created_at'] ?? 'now');
            });
            $recentRegistrations = array_slice($recentRegistrations, 0, 8);

            return view('admin.ppdb.index', [
                'registrations' => $allRegistrations,
                'stats' => $stats,
                'majorCounts' => $majorCounts,
                'pendingVerifications' => $pendingVerifications,
                'recentRegistrations' => $recentRegistrations,
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Index Error: ' . $e->getMessage());
            return view('admin.ppdb.index', [
                'registrations' => [],
                'stats' => ['total' => 0, 'pending_payment' => 0, 'paid' => 0, 'accepted' => 0, 'with_form' => 0, 'total_amount' => 0],
                'majorCounts' => ['reguler' => 0, 'bahasa' => 0, 'tahfidz' => 0, 'ict' => 0, 'belum_pilih' => 0],
                'pendingVerifications' => [],
                'recentRegistrations' => [],
                'error' => 'Koneksi ke backend bermasalah: ' . $e->getMessage(),
                'backendUrl' => $this->backendUrl(),
            ]);
        }
    }

    /**
     * Modul Data Siswa (Daftar Calon Siswa & Formulir).
     */
    public function students(Request $request)
    {
        $search = $request->query('search');
        $major = $request->query('major');
        $paymentStatus = $request->query('payment_status');
        $registrationStatus = $request->query('registration_status');

        try {
            $allRegistrations = $this->fetchRegistrations();
            if ($allRegistrations === null) {
                return redirect()->route('admin.login')->with('warning', 'Sesi admin Anda telah kedaluwarsa.');
            }

            $students = $allRegistrations;

            // Filter Pencarian
            if (!empty($search)) {
                $q = strtolower(trim($search));
                $students = array_filter($students, function ($r) use ($q) {
                    $name = strtolower($r['account']['full_name'] ?? ($r['form_data']['full_name'] ?? ''));
                    $nik = strtolower($r['account']['nik'] ?? '');
                    $school = strtolower($r['form_data']['school_origin'] ?? '');
                    $email = strtolower($r['account']['email'] ?? '');
                    return str_contains($name, $q) || str_contains($nik, $q) || str_contains($school, $q) || str_contains($email, $q);
                });
            }

            // Filter Jurusan
            if (!empty($major)) {
                $students = array_filter($students, function ($r) use ($major) {
                    $m = strtolower($r['form_data']['major'] ?? ($r['student']['major'] ?? ''));
                    return $m === strtolower($major);
                });
            }

            // Filter Status Pembayaran
            if (!empty($paymentStatus)) {
                $students = array_filter($students, function ($r) use ($paymentStatus) {
                    return ($r['payment_status'] ?? '') === $paymentStatus;
                });
            }

            // Filter Status Seleksi
            if (!empty($registrationStatus)) {
                $students = array_filter($students, function ($r) use ($registrationStatus) {
                    return ($r['registration_status'] ?? '') === $registrationStatus;
                });
            }

            $students = array_values($students);

            $stats = [
                'total' => count($allRegistrations),
                'filtered' => count($students),
                'accepted' => count(array_filter($allRegistrations, fn($r) => ($r['registration_status'] ?? '') === 'accepted')),
                'pending' => count(array_filter($allRegistrations, fn($r) => ($r['registration_status'] ?? 'pending') === 'pending')),
            ];

            return view('admin.ppdb.students', [
                'students' => $students,
                'stats' => $stats,
                'filters' => compact('search', 'major', 'paymentStatus', 'registrationStatus'),
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Students Error: ' . $e->getMessage());
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memuat Data Siswa: ' . $e->getMessage());
        }
    }

    /**
     * Modul Data User (Daftar Akun Login Pendaftar & Kontak).
     */
    public function users(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        try {
            $allRegistrations = $this->fetchRegistrations();
            if ($allRegistrations === null) {
                return redirect()->route('admin.login')->with('warning', 'Sesi admin Anda telah kedaluwarsa.');
            }

            $users = [];
            foreach ($allRegistrations as $r) {
                $acc = $r['account'] ?? [];
                $form = $r['form_data'] ?? [];
                $phone = $form['contact']['mobile_number'] ?? ($form['contact']['whatsapp_number'] ?? ($acc['phone'] ?? null));

                $users[] = [
                    'registration_id' => $r['id'] ?? null,
                    'account_id' => $r['account_id'] ?? null,
                    'full_name' => $acc['full_name'] ?? ($form['full_name'] ?? 'Calon Siswa'),
                    'email' => $acc['email'] ?? '-',
                    'nik' => $acc['nik'] ?? '-',
                    'phone' => $phone,
                    'payment_status' => $r['payment_status'] ?? 'unpaid',
                    'registration_status' => $r['registration_status'] ?? 'pending',
                    'has_form' => !empty($form),
                    'created_at' => $r['created_at'] ?? now()->toIso8601String(),
                ];
            }

            // Filter Pencarian
            if (!empty($search)) {
                $q = strtolower(trim($search));
                $users = array_filter($users, function ($u) use ($q) {
                    return str_contains(strtolower($u['full_name']), $q)
                        || str_contains(strtolower($u['email']), $q)
                        || str_contains(strtolower($u['nik']), $q)
                        || str_contains(strtolower($u['phone'] ?? ''), $q);
                });
            }

            // Filter Status
            if (!empty($status)) {
                if ($status === 'paid') {
                    $users = array_filter($users, fn($u) => $u['payment_status'] === 'paid');
                } elseif ($status === 'unpaid') {
                    $users = array_filter($users, fn($u) => $u['payment_status'] !== 'paid');
                } elseif ($status === 'has_form') {
                    $users = array_filter($users, fn($u) => $u['has_form']);
                }
            }

            $users = array_values($users);

            $stats = [
                'total' => count($allRegistrations),
                'paid' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'paid')),
                'unpaid' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') !== 'paid')),
                'has_form' => count(array_filter($allRegistrations, fn($r) => !empty($r['form_data']))),
            ];

            return view('admin.ppdb.users', [
                'users' => $users,
                'stats' => $stats,
                'totalAccounts' => count($allRegistrations),
                'filters' => compact('search', 'status'),
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Users Error: ' . $e->getMessage());
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memuat Data User: ' . $e->getMessage());
        }
    }

    /**
     * Update Akun User / Pendaftar PPDB (Quick Edit).
     */
    public function updateUser(Request $request, string $id)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'nik' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'payment_status' => ['required', 'in:unpaid,pending_verification,paid,rejected'],
        ], [
            'full_name.required' => 'Nama lengkap calon siswa wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'payment_status.required' => 'Status pembayaran wajib dipilih.',
        ]);

        try {
            $res = $this->httpWithAdminToken()->get($this->backendUrl() . '/api/ppdb/registrations/' . $id);
            if (!$res->successful()) {
                return back()->with('error', 'Data akun pendaftar tidak ditemukan di sistem.');
            }

            $reg = $res->json();
            $formData = $reg['form_data'] ?? [];

            $formData['full_name'] = $request->full_name;
            if (!empty($request->nik)) {
                $formData['nik'] = preg_replace('/[^0-9]/', '', $request->nik);
            }

            if (!isset($formData['contact'])) {
                $formData['contact'] = [];
            }
            $formData['contact']['email'] = $request->email;
            if ($request->filled('phone')) {
                $formData['contact']['whatsapp_number'] = $request->phone;
                $formData['contact']['mobile_number'] = $request->phone;
            }

            $cleanNik = !empty($request->nik) ? preg_replace('/[^0-9]/', '', $request->nik) : ($reg['account']['nik'] ?? '');

            $payload = [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'nik' => $cleanNik,
                'payment_status' => $request->payment_status,
                'payment_amount' => $request->payment_status === 'paid' ? 400000.0 : (float)($reg['payment_amount'] ?? 0),
                'registration_status' => $reg['registration_status'] ?? 'pending',
                'form_data' => $formData,
            ];

            if ($request->filled('password')) {
                $payload['password'] = $request->password;
            }

            $updateRes = $this->httpWithAdminToken()->put($this->backendUrl() . '/api/ppdb/registrations/' . $id, $payload);

            // Jika ada perubahan password dan ada account_id, update juga ke master users / accounts jika didukung
            if ($request->filled('password')) {
                $accId = $reg['account_id'] ?? null;
                if ($accId) {
                    try {
                        $this->httpWithAdminToken()->put($this->backendUrl() . '/api/users/' . $accId, [
                            'password' => $request->password,
                        ]);
                    } catch (\Exception $e) {
                        Log::info('Update user master password note: ' . $e->getMessage());
                    }
                }
            }

            if ($updateRes->successful()) {
                $successMsg = "Data akun calon siswa '{$request->full_name}' berhasil diperbarui!" . 
                              ($request->filled('password') ? " Password baru berhasil disetel ke '{$request->password}'." : "");
                return redirect()->route('admin.ppdb.users')->with('success', $successMsg);
            }

            $errorMsg = $this->extractErrorMessage($updateRes, 'Gagal memperbarui data akun siswa.');
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            Log::error('Admin Update User Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui akun: ' . $e->getMessage());
        }
    }

    /**
     * Hapus Akun User / Pendaftar PPDB secara Permanen.
     */
    public function destroyUser(Request $request, string $id)
    {
        try {
            $response = $this->httpWithAdminToken()
                ->delete($this->backendUrl() . '/api/ppdb/registrations/' . $id);

            if ($response->status() === 401) {
                $request->session()->forget(['admin_api_token', 'admin_user', 'is_admin']);
                return redirect()->route('admin.login')->with('warning', 'Sesi admin telah kedaluwarsa.');
            }

            if ($response->successful()) {
                return redirect()->route('admin.ppdb.users')->with('success', 'Akun pendaftar berhasil dihapus secara permanen dari sistem PPDB.');
            }

            $errorMsg = $this->extractErrorMessage($response, 'Gagal menghapus data akun.');
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            Log::error('Admin Delete User Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kendala saat menghapus akun: ' . $e->getMessage());
        }
    }

    /**
     * Tambah Akun User / Calon Siswa Baru secara Manual oleh Admin.
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'nik' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'mark_as_paid' => ['nullable', 'boolean'],
        ], [
            'full_name.required' => 'Nama lengkap calon siswa wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        try {
            $payload = [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => $request->password,
            ];
            if (!empty($request->nik)) {
                $payload['nik'] = preg_replace('/[^0-9]/', '', $request->nik);
            }

            $response = $this->httpClient()->post($this->backendUrl() . '/api/ppdb/register-account', $payload);

            if (!$response->successful()) {
                $errorMsg = $this->extractErrorMessage($response, 'Gagal membuat akun calon siswa.');
                return back()->withInput()->with('error', $errorMsg);
            }

            $accountData = $response->json();
            $accountId = $accountData['id'] ?? null;

            // Jika admin memilih untuk langsung menandai lunas
            if ($request->boolean('mark_as_paid')) {
                $allRegs = $this->fetchRegistrations();
                $newReg = null;
                if (!empty($allRegs)) {
                    foreach ($allRegs as $reg) {
                        if (($reg['account_id'] ?? null) === $accountId || 
                            (($reg['account']['email'] ?? '') === $request->email)) {
                            $newReg = $reg;
                            break;
                        }
                    }
                }

                if ($newReg && !empty($newReg['id'])) {
                    $this->httpWithAdminToken()->put($this->backendUrl() . '/api/ppdb/verify-payment/' . $newReg['id'], [
                        'payment_status' => 'paid',
                        'payment_amount' => 400000.0,
                    ]);
                }
            }

            $successMsg = "Akun calon siswa '{$request->full_name}' berhasil dibuat! Email: {$request->email}, Password: {$request->password}";
            return redirect()->route('admin.ppdb.users')->with('success', $successMsg);

        } catch (\Exception $e) {
            Log::error('Admin Store User Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Unduh Template Excel / CSV untuk Import User.
     */
    public function downloadUserTemplate(Request $request, PpdbImportService $importService)
    {
        $format = strtolower($request->query('format', 'xlsx'));

        if ($format === 'csv') {
            $csv = $importService->generateTemplateCsv();
            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="template-import-user-ppdb.csv"',
                'Cache-Control' => 'no-store, no-cache',
            ]);
        }

        $xlsxFile = $importService->generateTemplateXlsx();
        return response()->download($xlsxFile, 'template-import-user-ppdb.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Import Akun User secara Massal via Excel (.xlsx) atau CSV (.csv).
     */
    public function importUsers(Request $request, PpdbImportService $importService)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'mark_all_paid' => ['nullable', 'boolean'],
            'default_password' => ['nullable', 'string'],
        ], [
            'file.required' => 'Berkas Excel / CSV wajib dipilih.',
            'file.max' => 'Ukuran berkas maksimal 5MB.',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'])) {
            return back()->with('error', 'Format berkas tidak valid. Harap pilih file .xlsx atau .csv');
        }

        try {
            $rows = $importService->parseFile($file);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca berkas: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'Berkas tidak memuat data calon siswa atau baris data kosong.');
        }

        $defaultPassword = $request->input('default_password', 'Ppdb2026!') ?: 'Ppdb2026!';
        $markAllPaid = $request->boolean('mark_all_paid');

        $successCount = 0;
        $failedList = [];
        $createdAccounts = [];

        foreach ($rows as $item) {
            $rowNum = $item['row_number'] ?? '-';
            $fullName = trim($item['full_name'] ?? '');
            $email = trim($item['email'] ?? '');
            $nik = trim($item['nik'] ?? '');
            $password = trim($item['password'] ?? '') ?: $defaultPassword;

            if (empty($fullName) || empty($email)) {
                $failedList[] = [
                    'row' => $rowNum,
                    'name' => $fullName ?: '(Nama Kosong)',
                    'email' => $email ?: '-',
                    'reason' => 'Nama lengkap dan Email wajib terisi.',
                ];
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $failedList[] = [
                    'row' => $rowNum,
                    'name' => $fullName,
                    'email' => $email,
                    'reason' => 'Format email tidak valid.',
                ];
                continue;
            }

            try {
                $payload = [
                    'full_name' => $fullName,
                    'email' => $email,
                    'password' => $password,
                ];
                if (!empty($nik)) {
                    $payload['nik'] = $nik;
                }

                $response = $this->httpClient()->post($this->backendUrl() . '/api/ppdb/register-account', $payload);

                if ($response->successful()) {
                    $successCount++;
                    $createdData = $response->json();
                    $createdAccounts[] = [
                        'account_id' => $createdData['id'] ?? null,
                        'email' => $email,
                    ];
                } else {
                    $reason = $this->extractErrorMessage($response, 'Gagal mendaftarkan akun ke backend.');
                    $failedList[] = [
                        'row' => $rowNum,
                        'name' => $fullName,
                        'email' => $email,
                        'reason' => $reason,
                    ];
                }
            } catch (\Exception $e) {
                $failedList[] = [
                    'row' => $rowNum,
                    'name' => $fullName,
                    'email' => $email,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        // Tandai lunas sekaligus jika diminta
        if ($markAllPaid && !empty($createdAccounts)) {
            try {
                $allRegs = $this->fetchRegistrations();
                if (!empty($allRegs)) {
                    $createdEmails = array_column($createdAccounts, 'email');
                    foreach ($allRegs as $reg) {
                        $regEmail = $reg['account']['email'] ?? '';
                        if (in_array($regEmail, $createdEmails) && ($reg['payment_status'] ?? '') !== 'paid') {
                            $this->httpWithAdminToken()->put($this->backendUrl() . '/api/ppdb/verify-payment/' . $reg['id'], [
                                'payment_status' => 'paid',
                                'payment_amount' => 400000.0,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Admin Import Mark Paid Warning: ' . $e->getMessage());
            }
        }

        $importSummary = [
            'total_rows' => count($rows),
            'success_count' => $successCount,
            'failed_count' => count($failedList),
            'failed_list' => $failedList,
        ];
        session()->flash('import_summary', $importSummary);

        if ($successCount > 0 && empty($failedList)) {
            return redirect()->route('admin.ppdb.users')->with('success', "Berhasil mengimpor {$successCount} akun calon siswa baru!");
        } elseif ($successCount > 0 && !empty($failedList)) {
            return redirect()->route('admin.ppdb.users')->with('warning', "Berhasil mengimpor {$successCount} akun. Terdapat " . count($failedList) . " baris yang dilewati/gagal.");
        } else {
            return redirect()->route('admin.ppdb.users')->with('error', "Gagal mengimpor akun. Seluruh " . count($failedList) . " baris gagal diproses. Silakan periksa laporan.");
        }
    }

    /**
     * Modul Data Berkas (Monitoring Kelengkapan Berkas Pendaftar).
     */
    public function documents(Request $request)
    {
        $search = $request->query('search');
        $docStatus = $request->query('doc_status');

        try {
            $allRegistrations = $this->fetchRegistrations();
            if ($allRegistrations === null) {
                return redirect()->route('admin.login')->with('warning', 'Sesi admin Anda telah kedaluwarsa.');
            }

            $documents = [];
            foreach ($allRegistrations as $r) {
                $acc = $r['account'] ?? [];
                $form = $r['form_data'] ?? [];
                $hasPaymentProof = !empty($r['payment_proof_path']);
                $hasForm = !empty($form);

                // Check list berkas
                $hasKk = $hasForm; // Mockup status berkas
                $hasAkta = $hasForm;
                $hasNisn = !empty($form['nisn']) || $hasForm;
                $hasFoto = $hasForm;

                $uploadedCount = ($hasPaymentProof ? 1 : 0) + ($hasForm ? 4 : 0);
                $isComplete = $hasPaymentProof && $hasForm;

                $documents[] = [
                    'id' => $r['id'],
                    'full_name' => $acc['full_name'] ?? ($form['full_name'] ?? 'Calon Siswa'),
                    'nik' => $acc['nik'] ?? '-',
                    'payment_status' => $r['payment_status'] ?? 'unpaid',
                    'payment_proof_path' => $r['payment_proof_path'] ?? null,
                    'has_payment_proof' => $hasPaymentProof,
                    'has_kk' => $hasKk,
                    'has_akta' => $hasAkta,
                    'has_nisn' => $hasNisn,
                    'has_foto' => $hasFoto,
                    'uploaded_count' => $uploadedCount,
                    'is_complete' => $isComplete,
                    'created_at' => $r['created_at'] ?? now()->toIso8601String(),
                ];
            }

            // Filter Pencarian
            if (!empty($search)) {
                $q = strtolower(trim($search));
                $documents = array_filter($documents, function ($d) use ($q) {
                    return str_contains(strtolower($d['full_name']), $q)
                        || str_contains(strtolower($d['nik']), $q);
                });
            }

            // Filter Status Berkas
            if (!empty($docStatus)) {
                if ($docStatus === 'complete') {
                    $documents = array_filter($documents, fn($d) => $d['is_complete']);
                } elseif ($docStatus === 'incomplete') {
                    $documents = array_filter($documents, fn($d) => !$d['is_complete']);
                } elseif ($docStatus === 'has_payment') {
                    $documents = array_filter($documents, fn($d) => $d['has_payment_proof']);
                }
            }

            $documents = array_values($documents);

            $stats = [
                'total' => count($allRegistrations),
                'complete' => count(array_filter($documents, fn($d) => $d['is_complete'])),
                'incomplete' => count(array_filter($documents, fn($d) => !$d['is_complete'])),
                'has_proof' => count(array_filter($documents, fn($d) => $d['has_payment_proof'])),
            ];

            return view('admin.ppdb.documents', [
                'documents' => $documents,
                'stats' => $stats,
                'filters' => compact('search', 'docStatus'),
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Documents Error: ' . $e->getMessage());
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memuat Data Berkas: ' . $e->getMessage());
        }
    }

    /**
     * Modul Verifikasi Pembayaran (Billing / Kasir PPDB).
     */
    public function payments(Request $request)
    {
        $status = $request->query('status', 'pending'); // default tampilkan yang butuh verifikasi
        $search = $request->query('search');

        try {
            $allRegistrations = $this->fetchRegistrations();
            if ($allRegistrations === null) {
                return redirect()->route('admin.login')->with('warning', 'Sesi admin Anda telah kedaluwarsa.');
            }

            $payments = $allRegistrations;

            // Filter Status
            if ($status === 'pending') {
                $payments = array_filter($payments, fn($r) => ($r['payment_status'] ?? '') === 'pending_verification');
            } elseif ($status === 'paid') {
                $payments = array_filter($payments, fn($r) => ($r['payment_status'] ?? '') === 'paid');
            } elseif ($status === 'rejected') {
                $payments = array_filter($payments, fn($r) => ($r['payment_status'] ?? '') === 'rejected');
            } elseif ($status === 'unpaid') {
                $payments = array_filter($payments, fn($r) => ($r['payment_status'] ?? 'unpaid') === 'unpaid');
            }

            // Filter Pencarian
            if (!empty($search)) {
                $q = strtolower(trim($search));
                $payments = array_filter($payments, function ($r) use ($q) {
                    $name = strtolower($r['account']['full_name'] ?? '');
                    $nik = strtolower($r['account']['nik'] ?? '');
                    $email = strtolower($r['account']['email'] ?? '');
                    return str_contains($name, $q) || str_contains($nik, $q) || str_contains($email, $q);
                });
            }

            $payments = array_values($payments);

            $stats = [
                'pending' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'pending_verification')),
                'paid' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'paid')),
                'rejected' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'rejected')),
                'unpaid' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? 'unpaid') === 'unpaid')),
                'total_revenue' => array_reduce($allRegistrations, function ($acc, $r) {
                    return ($r['payment_status'] ?? '') === 'paid' ? $acc + (float)($r['payment_amount'] ?? 400000) : $acc;
                }, 0),
            ];

            return view('admin.ppdb.payments', [
                'payments' => $payments,
                'stats' => $stats,
                'filters' => compact('status', 'search'),
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Payments Error: ' . $e->getMessage());
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memuat Verifikasi Pembayaran: ' . $e->getMessage());
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
                    'payment_amount' => (float) ($request->payment_amount ?? 400000.00),
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

    /**
     * Accept applicant and migrate data to school system.
     */
    public function acceptStudent(Request $request, string $id)
    {
        try {
            $response = $this->httpWithAdminToken()
                ->post($this->backendUrl() . '/api/ppdb/accept/' . $id);

            if ($response->status() === 401) {
                $request->session()->forget(['admin_api_token', 'admin_user', 'is_admin']);
                return redirect()->route('admin.login')->with('warning', 'Sesi admin telah kedaluwarsa.');
            }

            if ($response->successful()) {
                $data = $response->json();
                $message = $data['message'] ?? 'Calon siswa berhasil diterima dan data resmi diterbitkan ke sistem sekolah.';
                return back()->with('success', $message);
            }

            $errorMsg = $this->extractErrorMessage($response, 'Gagal menerima calon siswa.');
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Accept Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memproses penerimaan: ' . $e->getMessage());
        }
    }
}
