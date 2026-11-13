<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use App\Models\SyncLog;
use App\Models\User;
use App\Services\ApiSyncService;
use App\Services\PpdbExportService;
use App\Services\PpdbImportService;
use App\Support\AnnouncementSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

        // 1. Coba autentikasi via FastAPI Backend jika online
        try {
            $response = $this->httpClient()
                ->asForm()
                ->post($this->backendUrl() . '/api/auth/login', [
                    'username' => $request->username,
                    'password' => $request->password,
                ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $token = $tokenData['access_token'] ?? null;

                if ($token) {
                    $profileRes = $this->httpClient()
                        ->withToken($token)
                        ->get($this->backendUrl() . '/api/profile/me');

                    if ($profileRes->successful()) {
                        $profile = $profileRes->json();
                        $role = strtolower($profile['role'] ?? $profile['base_role'] ?? '');

                        if ($role === 'admin' || !empty(array_intersect(['admin', 'administrator', 'staff'], (array)($profile['roles'] ?? [])))) {
                            session([
                                'admin_api_token' => $token,
                                'admin_user' => $profile,
                                'is_admin' => true,
                            ]);

                            return redirect()->route('admin.ppdb.index')->with('success', 'Selamat datang di Panel Admin PPDB, ' . ($profile['username'] ?? 'Admin') . '!');
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Backend API offline / network error, lanjutkan ke pengecekan admin lokal
            Log::info('Admin Login Remote API Note: ' . $e->getMessage());
        }

        // 2. Fallback: Autentikasi Admin Lokal MySQL (tabel users)
        $localAdmin = User::where('name', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if ($localAdmin && Hash::check($request->password, $localAdmin->password)) {
            session([
                'admin_api_token' => 'local_admin_' . Str::random(32),
                'admin_user' => [
                    'id' => $localAdmin->id,
                    'username' => $localAdmin->name,
                    'email' => $localAdmin->email,
                    'role' => 'admin',
                ],
                'is_admin' => true,
            ]);

            return redirect()->route('admin.ppdb.index')->with('success', 'Selamat datang di Panel Admin PPDB, ' . $localAdmin->name . '!');
        }

        return back()->withInput($request->except('password'))->with('error', 'Username atau kata sandi administrator salah.');
    }

    /**
     * Admin Logout.
     */
    public function logout(Request $request)
    {
        $request->session()->forget(['admin_api_token', 'admin_user', 'is_admin']);

        return redirect()->route('admin.login')->with('info', 'Sesi admin telah berhasil ditutup.');
    }

    public function announcement()
    {
        return view('admin.ppdb.announcement', [
            'announcements' => [
                'dashboard' => AnnouncementSetting::get('dashboard'),
                'landing' => AnnouncementSetting::get('landing'),
            ],
        ]);
    }

    public function saveAnnouncement(Request $request)
    {
        $data = $request->validate([
            'scope' => ['required', 'in:dashboard,landing'],
            'enabled' => ['required', 'boolean'],
            'title' => ['required_if:enabled,1', 'nullable', 'string', 'max:150'],
            'message' => ['required_if:enabled,1', 'nullable', 'string', 'max:5000'],
        ], [
            'title.required_if' => 'Judul wajib diisi saat pengumuman diaktifkan.',
            'message.required_if' => 'Isi pengumuman wajib diisi saat pengumuman diaktifkan.',
        ]);

        AnnouncementSetting::save([
            'enabled' => $request->boolean('enabled'),
            'title' => trim($data['title'] ?? ''),
            'message' => trim($data['message'] ?? ''),
        ], $data['scope']);

        return redirect()->route('admin.ppdb.announcement')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Helper: Ambil seluruh data pendaftaran dari API jika ada mock/online, atau dari MySQL lokal (Source of Truth).
     */
    protected function fetchRegistrations(array $queryParams = []): array
    {
        // 1. Cek apakah ada remote API / test mock yang merespons
        if (session()->has('admin_api_token')) {
            try {
                $response = $this->httpWithAdminToken()->get($this->backendUrl() . '/api/ppdb/registrations', $queryParams);
                if ($response->successful()) {
                    $remoteRegs = $response->json();
                    if (is_array($remoteRegs) && !empty($remoteRegs)) {
                        return $remoteRegs;
                    }
                }
            } catch (\Throwable $e) {
                // Silently fallback to local
            }
        }

        // 2. Sumber Kebenaran Lokal (MySQL)
        $regs = PpdbRegistration::with('account')
            ->latest('created_at')
            ->get();

        return $regs->map(fn($r) => $this->formatRegistration($r))->values()->toArray();
    }

    /**
     * Format Model PpdbRegistration ke array standar yang kompatibel 100% dengan Blade Views.
     */
    protected function formatRegistration(PpdbRegistration $reg): array
    {
        $account = $reg->account;
        $formData = is_array($reg->form_data) ? $reg->form_data : (json_decode($reg->form_data ?? '', true) ?: []);

        return [
            'id' => $reg->id,
            'account_id' => $reg->account_id,
            'payment_status' => $reg->payment_status,
            'payment_method' => $reg->payment_method ?? 'transfer',
            'sender_bank_name' => $reg->sender_bank_name,
            'sender_name' => $reg->sender_name,
            'payment_amount' => (float) $reg->payment_amount,
            'payment_proof_path' => $reg->payment_proof_path,
            'payment_verified_at' => $reg->payment_verified_at ? $reg->payment_verified_at->toIso8601String() : null,
            'payment_verified_by' => $reg->payment_verified_by,
            'registration_status' => $reg->registration_status,
            'form_data' => $formData,
            'student_id' => $reg->student_id,
            'remote_id' => $reg->remote_id,
            'sync_status' => $reg->sync_status ?? 'pending',
            'sync_message' => $reg->sync_message,
            'last_synced_at' => $reg->last_synced_at ? $reg->last_synced_at->toIso8601String() : null,
            'created_at' => $reg->created_at ? $reg->created_at->toIso8601String() : now()->toIso8601String(),
            'updated_at' => $reg->updated_at ? $reg->updated_at->toIso8601String() : now()->toIso8601String(),
            'account' => $account ? [
                'id' => $account->id,
                'full_name' => $account->full_name,
                'email' => $account->email,
                'nik' => $account->nik,
                'phone' => $account->phone,
                'is_active' => $account->is_active,
                'remote_id' => $account->remote_id,
                'sync_status' => $account->sync_status ?? 'pending',
                'sync_message' => $account->sync_message,
                'created_at' => $account->created_at ? $account->created_at->toIso8601String() : now()->toIso8601String(),
            ] : [
                'id' => null,
                'full_name' => $formData['full_name'] ?? 'Calon Siswa',
                'email' => '-',
                'nik' => '-',
                'phone' => null,
                'is_active' => true,
                'remote_id' => null,
                'sync_status' => 'pending',
                'sync_message' => null,
                'created_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Helper: Paginate an array using LengthAwarePaginator with allowed per_page options (10/20/30/50/100).
     */
    protected function paginateArray(array $items, int $defaultPerPage = 20, Request $request = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $request = $request ?: request();
        $allowedPerPage = [10, 20, 30, 50, 100];
        $reqPerPage = (int) $request->query('per_page', $defaultPerPage);
        $perPage = in_array($reqPerPage, $allowedPerPage) ? $reqPerPage : $defaultPerPage;

        $page = (int) ($request->query('page') ?: 1);
        if ($page < 1) $page = 1;

        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $slicedItems = array_slice($items, $offset, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $slicedItems,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * PPDB Admin Dashboard: Ringkasan KPI, Statistik Jurusan & Verifikasi Cepat.
     */
    public function index(Request $request)
    {
        try {
            $allRegistrations = $this->fetchRegistrations();

            // Hitung statistik komprehensif
            $stats = [
                'total' => count($allRegistrations),
                'pending_payment' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'pending_verification')),
                'paid' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'paid')),
                'accepted' => count(array_filter($allRegistrations, fn($r) => ($r['registration_status'] ?? '') === 'accepted')),
                'with_form' => count(array_filter($allRegistrations, fn($r) => ppdb_is_form_filled($r))),
                'total_amount' => array_reduce($allRegistrations, function ($acc, $r) {
                    if (($r['payment_status'] ?? '') !== 'paid') {
                        return $acc;
                    }
                    $amt = isset($r['payment_amount']) && $r['payment_amount'] !== '' && $r['payment_amount'] !== null
                        ? (float)$r['payment_amount']
                        : 400000.0;
                    return $acc + $amt;
                }, 0),
            ];

            // Statistik Sync Status
            $syncStats = [
                'pending' => PpdbAccount::where('sync_status', 'pending')->count() + PpdbRegistration::where('sync_status', 'pending')->count(),
                'failed' => PpdbAccount::where('sync_status', 'failed')->count() + PpdbRegistration::where('sync_status', 'failed')->count(),
                'synced' => PpdbRegistration::where('sync_status', 'synced')->count(),
                'last_synced_at' => SyncLog::latest('executed_at')->value('executed_at'),
            ];

            // Statistik per jurusan & jenis kelamin (Laki-laki vs Perempuan)
            $majorCounts = [
                'reguler' => 0,
                'bahasa' => 0,
                'tahfidz' => 0,
                'ict' => 0,
                'belum_pilih' => 0,
            ];
            $genderCounts = [
                'laki_laki' => 0,
                'perempuan' => 0,
                'belum_diisi' => 0,
            ];

            foreach ($allRegistrations as $r) {
                $formData = is_array($r['form_data'] ?? null)
                    ? $r['form_data']
                    : (is_string($r['form_data'] ?? null) ? json_decode($r['form_data'], true) : []);

                // Jurusan
                $major = strtolower(trim((string)($formData['major'] ?? ($r['student']['major'] ?? ''))));
                if (isset($majorCounts[$major])) {
                    $majorCounts[$major]++;
                } else {
                    $majorCounts['belum_pilih']++;
                }

                // Jenis Kelamin
                $genderRaw = $formData['identity']['gender'] ?? ($formData['gender'] ?? ($r['student']['gender'] ?? ($r['student']['identity']['gender'] ?? '')));
                $g = strtolower(trim((string)$genderRaw));
                if (in_array($g, ['laki-laki', 'l', 'pria', 'male'])) {
                    $genderCounts['laki_laki']++;
                } elseif (in_array($g, ['perempuan', 'p', 'wanita', 'female'])) {
                    $genderCounts['perempuan']++;
                } else {
                    $genderCounts['belum_diisi']++;
                }
            }

            // Statistik Asal Sekolah Calon Pendaftar
            $schoolCounts = [];
            $unfilledSchoolCount = 0;
            $filledSchoolCount = 0;

            foreach ($allRegistrations as $r) {
                $formData = is_array($r['form_data'] ?? null)
                    ? $r['form_data']
                    : (is_string($r['form_data'] ?? null) ? json_decode($r['form_data'], true) : []);

                $rawSchool = trim($formData['school_origin'] ?? ($r['student']['school_origin'] ?? ''));

                if ($rawSchool === '' || $rawSchool === '-') {
                    $unfilledSchoolCount++;
                    continue;
                }

                $filledSchoolCount++;

                // Normalisasi spasi berlebih dan kapitalisasi judul (mendukung kata berpenghubung seperti Al-Muhajirin)
                $cleanSchool = preg_replace('/\s+/', ' ', $rawSchool);
                $normalized = ucwords(mb_strtolower($cleanSchool), " \t\r\n\f\v-");
                // Pertahankan kapitalisasi standar untuk akronim jenjang sekolah di Indonesia
                $normalized = preg_replace_callback('/\b(sdn|sds|sd|min|mis|mi|smpn|smp|mts|sdit|miit|smpit)\b/i', function ($matches) {
                    $upper = strtoupper($matches[0]);
                    return $upper === 'MTS' ? 'MTs' : $upper;
                }, $normalized);

                $schoolCounts[$normalized] = ($schoolCounts[$normalized] ?? 0) + 1;
            }

            arsort($schoolCounts);

            $topSchools = array_slice($schoolCounts, 0, 10, true);
            $otherSchoolsCount = array_sum(array_slice($schoolCounts, 10));

            $schoolStats = [
                'total_unique' => count($schoolCounts),
                'total_filled' => $filledSchoolCount,
                'total_unfilled' => $unfilledSchoolCount,
                'top' => $topSchools,
                'all' => $schoolCounts,
                'other_count' => $otherSchoolsCount,
                'top_school_name' => !empty($schoolCounts) ? array_key_first($schoolCounts) : null,
                'top_school_count' => !empty($schoolCounts) ? reset($schoolCounts) : 0,
            ];

            // Antrean verifikasi pembayaran tercepat
            $pendingVerifications = array_values(array_filter(
                $allRegistrations,
                fn($r) => ($r['payment_status'] ?? '') === 'pending_verification'
            ));

            // Pendaftar terbaru
            $recentRegistrations = $allRegistrations;
            usort($recentRegistrations, function ($a, $b) {
                return strtotime($b['created_at'] ?? 'now') - strtotime($a['created_at'] ?? 'now');
            });
            $recentRegistrations = array_slice($recentRegistrations, 0, 8);

            return view('admin.ppdb.index', [
                'registrations' => $allRegistrations,
                'stats' => $stats,
                'syncStats' => $syncStats,
                'majorCounts' => $majorCounts,
                'genderCounts' => $genderCounts,
                'schoolStats' => $schoolStats,
                'pendingVerifications' => $pendingVerifications,
                'recentRegistrations' => $recentRegistrations,
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Index Error: ' . $e->getMessage(), ['exception' => $e]);
            return view('admin.ppdb.index', [
                'registrations' => [],
                'stats' => ['total' => 0, 'pending_payment' => 0, 'paid' => 0, 'accepted' => 0, 'with_form' => 0, 'total_amount' => 0],
                'syncStats' => ['pending' => 0, 'failed' => 0, 'synced' => 0, 'last_synced_at' => null],
                'majorCounts' => ['reguler' => 0, 'bahasa' => 0, 'tahfidz' => 0, 'ict' => 0, 'belum_pilih' => 0],
                'genderCounts' => ['laki_laki' => 0, 'perempuan' => 0, 'belum_diisi' => 0],
                'schoolStats' => [
                    'total_unique' => 0,
                    'total_filled' => 0,
                    'total_unfilled' => 0,
                    'top' => [],
                    'all' => [],
                    'other_count' => 0,
                    'top_school_name' => null,
                    'top_school_count' => 0,
                ],
                'pendingVerifications' => [],
                'recentRegistrations' => [],
                'error' => 'Gagal memuat data dashboard PPDB. Silakan muat ulang halaman atau hubungi administrator.',
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

            $paginatedStudents = $this->paginateArray($students, 20, $request);

            return view('admin.ppdb.students', [
                'students' => $paginatedStudents,
                'stats' => $stats,
                'filters' => [
                    'search' => $search,
                    'major' => $major,
                    'paymentStatus' => $paymentStatus,
                    'registrationStatus' => $registrationStatus,
                    'perPage' => $paginatedStudents->perPage(),
                ],
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Students Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memuat Data Siswa. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Modul Data User Akun (Daftar Akun Login Calon Siswa).
     */
    public function users(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        try {
            $allRegistrations = $this->fetchRegistrations();

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
                    'payment_amount' => isset($r['payment_amount']) && $r['payment_amount'] !== '' && $r['payment_amount'] !== null ? (float)$r['payment_amount'] : 400000.0,
                    'registration_status' => $r['registration_status'] ?? 'pending',
                    'has_form' => ppdb_is_form_filled($r),
                    'sync_status' => $r['sync_status'] ?? 'pending',
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
                'has_form' => count(array_filter($allRegistrations, fn($r) => ppdb_is_form_filled($r))),
            ];

            $paginatedUsers = $this->paginateArray($users, 20, $request);

            return view('admin.ppdb.users', [
                'users' => $paginatedUsers,
                'stats' => $stats,
                'totalAccounts' => count($allRegistrations),
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'perPage' => $paginatedUsers->perPage(),
                ],
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Users Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memuat Data User. Silakan coba beberapa saat lagi.');
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
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
        ], [
            'full_name.required' => 'Nama lengkap calon siswa wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'payment_status.required' => 'Status pembayaran wajib dipilih.',
        ]);

        try {
            $reg = PpdbRegistration::with('account')
                ->where('id', $id)
                ->orWhere('account_id', $id)
                ->orWhere('remote_id', $id)
                ->first();

            $cleanNik = !empty($request->nik) ? preg_replace('/[^0-9]/', '', $request->nik) : ($reg?->account?->nik);
            $currentAmount = $reg && $reg->payment_amount !== null ? (float) $reg->payment_amount : null;
            $paymentAmount = ($request->has('payment_amount') && $request->payment_amount !== null && $request->payment_amount !== '')
                ? (float) $request->payment_amount
                : ($request->payment_status === 'paid' ? ($currentAmount ?? 400000.0) : 400000.0);
            $paymentStatus = $request->payment_status;
            if ($paymentAmount == 0.0 && $request->has('payment_amount') && $request->payment_amount !== null && $request->payment_amount !== '') {
                $paymentStatus = 'paid';
            }
            $formData = is_array($reg?->form_data) ? $reg->form_data : [];

            if ($reg) {
                // 1. Update Akun Lokal
                if ($reg->account) {
                    $accountData = [
                        'full_name' => $request->full_name,
                        'email' => $request->email,
                        'nik' => $cleanNik,
                        'phone' => $request->phone,
                        'sync_status' => 'pending',
                        'sync_message' => 'Data diperbarui di lokal, siap disinkronkan',
                    ];
                    if ($request->filled('password')) {
                        $accountData['password'] = Hash::make($request->password);
                    }
                    $reg->account->update($accountData);
                }

                // 2. Update Registrasi & Form Data Lokal
                $formData['full_name'] = $request->full_name;
                if (!empty($cleanNik)) {
                    $formData['nik'] = $cleanNik;
                }
                if (!isset($formData['contact'])) {
                    $formData['contact'] = [];
                }
                $formData['contact']['email'] = $request->email;
                if ($request->filled('phone')) {
                    $formData['contact']['whatsapp_number'] = $request->phone;
                    $formData['contact']['mobile_number'] = $request->phone;
                }

                $reg->update([
                    'payment_status' => $paymentStatus,
                    'payment_amount' => $paymentAmount,
                    'payment_verified_at' => $paymentStatus === 'paid' ? ($reg->payment_verified_at ?: now()) : null,
                    'payment_verified_by' => $paymentStatus === 'paid' ? ($reg->payment_verified_by ?: (session('admin_user.username') ?? 'Admin')) : null,
                    'form_data' => $formData,
                    'sync_status' => 'pending',
                    'sync_message' => 'Data diperbarui di lokal, siap disinkronkan',
                ]);
            }

            // Sync ke remote API jika online
            try {
                $targetRemoteId = $reg?->remote_id ?: $id;
                $payload = [
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'nik' => $cleanNik ?: '',
                    'payment_status' => $paymentStatus,
                    'payment_amount' => $paymentAmount,
                    'registration_status' => $reg?->registration_status ?? 'pending',
                    'form_data' => $formData,
                ];
                if ($request->filled('password')) {
                    $payload['password'] = $request->password;
                }
                $this->httpWithAdminToken()->put($this->backendUrl() . '/api/ppdb/registrations/' . $targetRemoteId, $payload);
            } catch (\Throwable $e) {
                // Offline fallback
            }

            $successMsg = "Data akun calon siswa '{$request->full_name}' berhasil diperbarui!" .
                          ($request->filled('password') ? " Password baru berhasil disetel." : "");

            return redirect()->route('admin.ppdb.users')->with('success', $successMsg);

        } catch (\Exception $e) {
            Log::error('Admin Update User Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan saat memperbarui akun calon siswa. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Hapus Akun User / Pendaftar PPDB secara Permanen.
     */
    public function destroyUser(Request $request, string $id)
    {
        try {
            $reg = PpdbRegistration::with('account')
                ->where('id', $id)
                ->orWhere('account_id', $id)
                ->orWhere('remote_id', $id)
                ->first();

            $remoteId = $reg?->remote_id ?: $id;

            if ($reg) {
                if ($reg->account) {
                    $reg->account->delete();
                }
                $reg->delete();
            }

            // Coba hapus juga di remote API
            try {
                $this->httpWithAdminToken()->delete($this->backendUrl() . '/api/ppdb/registrations/' . $remoteId);
            } catch (\Throwable $e) {
                // Offline
            }

            return redirect()->route('admin.ppdb.users')->with('success', 'Akun pendaftar berhasil dihapus secara permanen dari sistem PPDB.');

        } catch (\Exception $e) {
            Log::error('Admin Delete User Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.users')->with('error', 'Terjadi kendala saat menghapus akun calon siswa. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Tambah Akun User / Calon Siswa Baru secara Manual oleh Admin (Simpan ke MySQL Lokal).
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'nik' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:20'],
            'mark_as_paid' => ['nullable'],
        ], [
            'full_name.required' => 'Nama lengkap calon siswa wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        try {
            $cleanNik = !empty($request->nik) ? preg_replace('/[^0-9]/', '', $request->nik) : null;

            // 1. Simpan ke tabel ppdb_accounts lokal (dukung restore jika sebelumnya soft-deleted)
            $account = PpdbAccount::withTrashed()->where('email', $request->email)->first();
            if ($account) {
                if ($account->trashed()) {
                    $account->restore();
                }
                $account->update([
                    'nik' => $cleanNik,
                    'full_name' => $request->full_name,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                    'sync_status' => 'pending',
                    'sync_message' => 'Menunggu sinkronisasi ke Master API',
                ]);
            } else {
                $account = PpdbAccount::create([
                    'email' => $request->email,
                    'nik' => $cleanNik,
                    'full_name' => $request->full_name,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                    'sync_status' => 'pending',
                    'sync_message' => 'Menunggu sinkronisasi ke Master API',
                ]);
            }

            // 2. Buat entri registrasi lokal (dukung restore jika sebelumnya soft-deleted)
            $isPaid = $request->boolean('mark_as_paid');
            $reg = PpdbRegistration::withTrashed()->where('account_id', $account->id)->first();
            $regData = [
                'account_id' => $account->id,
                'payment_status' => $isPaid ? 'paid' : 'unpaid',
                'payment_method' => $isPaid ? $request->input('payment_method', 'cash') : 'transfer',
                'payment_amount' => $isPaid ? 400000.0 : 0.0,
                'payment_verified_at' => $isPaid ? now() : null,
                'payment_verified_by' => $isPaid ? (session('admin_user.username') ?? 'Admin') : null,
                'registration_status' => 'pending',
                'form_data' => [
                    'full_name' => $request->full_name,
                    'nik' => $cleanNik,
                    'contact' => [
                        'email' => $request->email,
                        'mobile_number' => $request->phone,
                        'whatsapp_number' => $request->phone,
                    ],
                ],
                'sync_status' => 'pending',
                'sync_message' => 'Menunggu sinkronisasi ke Master API',
            ];
            if ($reg) {
                if ($reg->trashed()) {
                    $reg->restore();
                }
                $reg->update($regData);
            } else {
                $reg = PpdbRegistration::create($regData);
            }

            // 3. Coba kirim langsung ke backend jika online
            try {
                $payload = [
                    'full_name' => $request->full_name,
                    'email' => $request->email,
                    'password' => $request->password,
                ];
                if ($cleanNik) $payload['nik'] = $cleanNik;

                $apiRes = $this->httpClient()->post($this->backendUrl() . '/api/ppdb/register-account', $payload);
                if ($apiRes->successful()) {
                    $accData = $apiRes->json();
                    if (!empty($accData['id'])) {
                        $account->markSynced($accData['id'], 'Tersinkron langsung');
                    }
                }
            } catch (\Throwable $e) {
                // Offline
            }

            $successMsg = "Akun calon siswa '{$request->full_name}' ({$request->email}) berhasil dibuat!";
            return redirect()->route('admin.ppdb.users')->with('success', $successMsg);

        } catch (\Exception $e) {
            Log::error('Admin Store User Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat membuat akun calon siswa. Silakan periksa kembali isian formulir.');
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
     * Import Akun User secara Massal via Excel (.xlsx) atau CSV (.csv) ke MySQL lokal.
     */
    public function importUsers(Request $request, PpdbImportService $importService)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'mark_all_paid' => ['nullable'],
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
            Log::error('Admin Import File Parse Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal membaca berkas impor. Pastikan format berkas sesuai dengan template yang disediakan.');
        }

        if (empty($rows)) {
            return back()->with('error', 'Berkas tidak memuat data calon siswa atau baris data kosong.');
        }

        $defaultPassword = $request->input('default_password', 'Ppdb2026!') ?: 'Ppdb2026!';
        $markAllPaid = $request->boolean('mark_all_paid');

        $successCount = 0;
        $failedList = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $item) {
                $rowNum = $item['row_number'] ?? '-';
                $name = trim($item['full_name'] ?? '');
                $email = trim($item['email'] ?? '');
                $nik = !empty($item['nik']) ? preg_replace('/[^0-9]/', '', (string)$item['nik']) : null;
                $phone = trim($item['phone'] ?? '');
                $password = !empty($item['password']) ? $item['password'] : $defaultPassword;

                if (empty($name) || empty($email)) {
                    $failedList[] = "Baris #{$rowNum}: Nama dan email wajib diisi.";
                    continue;
                }

                // Buat atau perbarui akun
                $account = PpdbAccount::updateOrCreate(
                    ['email' => $email],
                    [
                        'nik' => $nik,
                        'full_name' => $name,
                        'password' => Hash::make($password),
                        'phone' => $phone,
                        'sync_status' => 'pending',
                        'sync_message' => 'Diimpor via berkas, siap disinkronkan',
                    ]
                );

                // Buat atau perbarui registrasi
                PpdbRegistration::updateOrCreate(
                    ['account_id' => $account->id],
                    [
                        'payment_status' => $markAllPaid ? 'paid' : 'unpaid',
                        'payment_amount' => $markAllPaid ? 400000.0 : 0.0,
                        'payment_verified_at' => $markAllPaid ? now() : null,
                        'payment_verified_by' => $markAllPaid ? (session('admin_user.username') ?? 'Admin Import') : null,
                        'registration_status' => 'pending',
                        'form_data' => [
                            'full_name' => $name,
                            'nik' => $nik,
                            'major' => $item['major'] ?? 'reguler',
                            'school_origin' => $item['school_origin'] ?? null,
                            'contact' => [
                                'email' => $email,
                                'mobile_number' => $phone,
                                'whatsapp_number' => $phone,
                            ],
                        ],
                        'sync_status' => 'pending',
                        'sync_message' => 'Diimpor via berkas, siap disinkronkan',
                    ]
                );

                $successCount++;
            }

            DB::commit();

            $msg = "Import Selesai! Sebanyak <strong>{$successCount} akun</strong> calon siswa berhasil disimpan ke database lokal.";
            if ($markAllPaid) {
                $msg .= " Seluruh akun ditandai <strong>LUNAS (PAID)</strong>.";
            }

            return redirect()->route('admin.ppdb.users')
                ->with('success', $msg)
                ->with('import_summary', [
                    'success_count' => $successCount,
                    'failed_count' => count($failedList),
                    'failed_list' => $failedList,
                ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin Import Users Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan saat memproses impor data calon siswa. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Modul Data Berkas (Matriks Kelengkapan Dokumen).
     */
    public function documents(Request $request)
    {
        $search = $request->query('search');
        $docStatus = $request->query('doc_status');

        try {
            $allRegistrations = $this->fetchRegistrations();

            $docs = [];
            foreach ($allRegistrations as $r) {
                $form = $r['form_data'] ?? [];
                $acc = $r['account'] ?? [];

                $docsData = $form['documents'] ?? [];
                $kkPath = $docsData['kk'] ?? ($form['kk_path'] ?? null);
                $aktaPath = $docsData['akta'] ?? ($form['birth_cert_path'] ?? null);
                $nisnPath = $docsData['nisn'] ?? ($form['nisn_path'] ?? null);
                $fotoPath = $docsData['foto'] ?? ($form['photo_path'] ?? null);

                $hasPaymentProof = !empty($r['payment_proof_path']);
                $hasKk = !empty($kkPath) || !empty($form['identity']['family_card_number']) || !empty($form['family_card_number']);
                $hasAkta = !empty($aktaPath) || !empty($form['birth_certificate_number']);
                $hasNisn = !empty($nisnPath) || !empty($form['nisn']);
                $hasFoto = !empty($fotoPath);

                $uploadedCount = ($hasPaymentProof ? 1 : 0) + ($hasKk ? 1 : 0) + ($hasAkta ? 1 : 0) + ($hasNisn ? 1 : 0) + ($hasFoto ? 1 : 0);
                $isComplete = $uploadedCount >= 5;

                $docs[] = [
                    'id' => $r['id'] ?? null,
                    'registration_id' => $r['id'] ?? null,
                    'full_name' => $acc['full_name'] ?? ($form['full_name'] ?? 'Calon Siswa'),
                    'email' => $acc['email'] ?? '-',
                    'nik' => $acc['nik'] ?? ($form['nik'] ?? '-'),
                    'payment_status' => $r['payment_status'] ?? 'unpaid',
                    'payment_proof_path' => $r['payment_proof_path'] ?? null,
                    'kk_path' => $kkPath,
                    'akta_path' => $aktaPath,
                    'nisn_path' => $nisnPath,
                    'foto_path' => $fotoPath,
                    'has_payment_proof' => $hasPaymentProof,
                    'has_kk' => $hasKk,
                    'has_akta' => $hasAkta,
                    'has_nisn' => $hasNisn,
                    'has_foto' => $hasFoto,
                    'uploaded_count' => $uploadedCount,
                    'is_complete' => $isComplete,
                    'student_docs' => ppdb_get_student_documents($r, $this->backendUrl()),
                    'raw_reg' => $r,
                ];
            }

            // Filter Pencarian
            if (!empty($search)) {
                $q = strtolower(trim($search));
                $docs = array_filter($docs, function ($d) use ($q) {
                    return str_contains(strtolower($d['full_name']), $q) || str_contains(strtolower($d['nik']), $q);
                });
            }

            // Filter Status Dokumen
            if (!empty($docStatus)) {
                if ($docStatus === 'complete') {
                    $docs = array_filter($docs, fn($d) => $d['is_complete']);
                } elseif ($docStatus === 'incomplete') {
                    $docs = array_filter($docs, fn($d) => !$d['is_complete']);
                } elseif ($docStatus === 'has_payment') {
                    $docs = array_filter($docs, fn($d) => $d['has_payment_proof']);
                }
            }

            $docs = array_values($docs);

            $stats = [
                'total' => count($allRegistrations),
                'complete' => count(array_filter($docs, fn($d) => $d['is_complete'])),
                'incomplete' => count(array_filter($docs, fn($d) => !$d['is_complete'])),
                'has_proof' => count(array_filter($docs, fn($d) => $d['has_payment_proof'])),
            ];

            $paginatedDocs = $this->paginateArray($docs, 20, $request);

            return view('admin.ppdb.documents', [
                'documents' => $paginatedDocs,
                'stats' => $stats,
                'filters' => [
                    'search' => $search,
                    'docStatus' => $docStatus,
                    'perPage' => $paginatedDocs->perPage(),
                ],
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Documents Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memuat Data Berkas. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Hapus berkas persyaratan siswa tertentu (KK, Akta, NISN, Foto) secara individual atau sekaligus ('all').
     * Mengosongkan path di database agar pendaftar dapat mengunggah ulang dokumen tersebut,
     * dan menghapus file fisik di server jika masih ada (tanpa menyentuh bukti pembayaran / biodata).
     */
    public function deleteDocument(Request $request, string $id, string $documentType)
    {
        $allowedDocTypes = [
            'kk' => 'Kartu Keluarga (KK)',
            'akta' => 'Akta Kelahiran',
            'nisn' => 'Kartu / Bukti NISN',
            'foto' => 'Pas Foto 3x4',
            'all' => 'Semua Berkas Persyaratan (KK, Akta, NISN, Pas Foto)',
        ];

        if (!array_key_exists($documentType, $allowedDocTypes)) {
            return back()->with('error', 'Jenis dokumen yang dipilih tidak valid.');
        }

        try {
            $registration = PpdbRegistration::with('account')->find($id);

            if (!$registration) {
                $registration = PpdbRegistration::with('account')
                    ->where('remote_id', $id)
                    ->orWhere('account_id', $id)
                    ->first();
            }

            if (!$registration) {
                return back()->with('error', 'Data pendaftaran siswa tidak ditemukan.');
            }

            $formData = is_array($registration->form_data) 
                ? $registration->form_data 
                : (json_decode($registration->form_data ?? '', true) ?: []);

            $existingDocs = $formData['documents'] ?? [];

            // Berkas yang akan dihapus: ke-4 berkas jika 'all', atau berkas spesifik
            $typesToDelete = ($documentType === 'all')
                ? ['kk', 'akta', 'nisn', 'foto']
                : [$documentType];

            $realUploadDir = realpath(public_path('uploads/ppdb_documents'));

            foreach ($typesToDelete as $type) {
                $filePath = $existingDocs[$type] ?? match($type) {
                    'kk' => ($formData['kk_path'] ?? null),
                    'akta' => ($formData['birth_cert_path'] ?? null),
                    'nisn' => ($formData['nisn_path'] ?? null),
                    'foto' => ($formData['photo_path'] ?? null),
                    default => null,
                };

                // Hapus file fisik jika masih ada di server
                if (!empty($filePath)) {
                    $cleanPath = ltrim($filePath, '/');
                    $fullPath = public_path($cleanPath);
                    $realFilePath = realpath($fullPath);

                    if ($realFilePath && $realUploadDir && str_starts_with($realFilePath, $realUploadDir) && file_exists($realFilePath)) {
                        @unlink($realFilePath);
                    }

                    // Cek document root cPanel
                    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                        $docRootFile = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/' . $cleanPath;
                        if (file_exists($docRootFile)) {
                            @unlink($docRootFile);
                        }
                    }
                }

                // Kosongkan dari form_data documents dan legacy paths
                unset($existingDocs[$type]);
                if ($type === 'kk') unset($formData['kk_path']);
                if ($type === 'akta') unset($formData['birth_cert_path']);
                if ($type === 'nisn') unset($formData['nisn_path']);
                if ($type === 'foto') unset($formData['photo_path']);
            }

            $formData['documents'] = $existingDocs;

            $registration->form_data = $formData;
            $registration->sync_status = 'pending';
            $registration->save();

            $studentName = $registration->account->full_name ?? ($formData['full_name'] ?? 'Calon Siswa');

            if ($documentType === 'all') {
                return back()->with('success', "Semua berkas persyaratan (4 berkas: KK, Akta Kelahiran, NISN, Pas Foto) untuk {$studentName} berhasil dihapus/direset. Siswa kini dapat mengunggah ulang seluruh berkas tersebut.");
            }

            $docLabel = $allowedDocTypes[$documentType];
            return back()->with('success', "Berkas {$docLabel} untuk {$studentName} berhasil dihapus/direset. Siswa kini dapat mengunggah ulang berkas tersebut.");

        } catch (\Throwable $e) {
            Log::error('Admin Delete Document Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan saat menghapus berkas: ' . $e->getMessage());
        }
    }

    /**
     * Modul Verifikasi Pembayaran.
     */
    public function payments(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        try {
            $allRegistrations = $this->fetchRegistrations();

            $payments = [];
            foreach ($allRegistrations as $r) {
                $acc = $r['account'] ?? [];
                $form = $r['form_data'] ?? [];

                $payments[] = [
                    'id' => $r['id'] ?? null,
                    'account' => $acc,
                    'full_name' => $acc['full_name'] ?? ($form['full_name'] ?? 'Calon Siswa'),
                    'email' => $acc['email'] ?? '-',
                    'nik' => $acc['nik'] ?? '-',
                    'phone' => $form['contact']['mobile_number'] ?? ($acc['phone'] ?? '-'),
                    'payment_method' => $r['payment_method'] ?? 'transfer',
                    'sender_bank_name' => $r['sender_bank_name'] ?? null,
                    'sender_name' => $r['sender_name'] ?? null,
                    'payment_status' => $r['payment_status'] ?? 'unpaid',
                    'payment_amount' => (float)(isset($r['payment_amount']) && $r['payment_amount'] !== '' && $r['payment_amount'] !== null ? $r['payment_amount'] : 400000),
                    'payment_proof_path' => $r['payment_proof_path'] ?? null,
                    'payment_verified_at' => $r['payment_verified_at'] ?? null,
                    'payment_verified_by' => $r['payment_verified_by'] ?? null,
                    'created_at' => $r['created_at'] ?? now()->toIso8601String(),
                    'updated_at' => $r['updated_at'] ?? null,
                ];
            }

            // Hitung statistik komprehensif global
            $stats = [
                'total' => count($allRegistrations),
                'pending' => count(array_filter($allRegistrations, fn($r) => in_array($r['payment_status'] ?? '', ['pending', 'pending_verification']))),
                'paid' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'paid')),
                'rejected' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'rejected')),
                'unpaid' => count(array_filter($allRegistrations, fn($r) => !in_array($r['payment_status'] ?? '', ['paid', 'pending', 'pending_verification', 'rejected']))),
                'total_revenue' => array_reduce($allRegistrations, function ($acc, $r) {
                    return ($r['payment_status'] ?? '') === 'paid'
                        ? $acc + (float)(isset($r['payment_amount']) && $r['payment_amount'] !== '' && $r['payment_amount'] !== null ? $r['payment_amount'] : 400000)
                        : $acc;
                }, 0),
            ];

            // Filter Status
            $filterStatus = $status ?: 'all';
            $filteredPayments = $payments;

            if ($filterStatus === 'pending') {
                $filteredPayments = array_filter($filteredPayments, fn($p) => in_array($p['payment_status'], ['pending', 'pending_verification']));
            } elseif ($filterStatus === 'paid') {
                $filteredPayments = array_filter($filteredPayments, fn($p) => $p['payment_status'] === 'paid');
            } elseif ($filterStatus === 'rejected') {
                $filteredPayments = array_filter($filteredPayments, fn($p) => $p['payment_status'] === 'rejected');
            } elseif ($filterStatus === 'unpaid') {
                $filteredPayments = array_filter($filteredPayments, fn($p) => !in_array($p['payment_status'], ['paid', 'pending', 'pending_verification', 'rejected']));
            }

            // Filter Pencarian
            if (!empty($search)) {
                $q = strtolower(trim($search));
                $filteredPayments = array_filter($filteredPayments, function ($p) use ($q) {
                    return str_contains(strtolower($p['full_name']), $q)
                        || str_contains(strtolower($p['email']), $q)
                        || str_contains(strtolower($p['nik']), $q)
                        || str_contains(strtolower($p['phone']), $q)
                        || str_contains(strtolower($p['sender_bank_name'] ?? ''), $q)
                        || str_contains(strtolower($p['sender_name'] ?? ''), $q);
                });
            }

            $filteredPayments = array_values($filteredPayments);
            $paginatedPayments = $this->paginateArray($filteredPayments, 20, $request);

            return view('admin.ppdb.payments', [
                'payments' => $paginatedPayments,
                'stats' => $stats,
                'filterStatus' => $filterStatus,
                'filters' => [
                    'status' => $filterStatus,
                    'search' => $search ?: '',
                    'perPage' => $paginatedPayments->perPage(),
                ],
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Payments Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memuat Verifikasi Pembayaran. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Ekspor Data Calon Siswa (Excel .xlsx / CSV).
     */
    public function export(Request $request, PpdbExportService $exportService)
    {
        $format = strtolower($request->query('format', 'xlsx'));
        $paymentStatus = $request->query('payment_status');
        $registrationStatus = $request->query('registration_status');
        $major = $request->query('major');
        $search = $request->query('search');

        try {
            $registrations = $this->fetchRegistrations();

            // Terapkan Filter Ekspor
            if (!empty($search)) {
                $q = strtolower(trim($search));
                $registrations = array_filter($registrations, function ($r) use ($q) {
                    $name = strtolower($r['account']['full_name'] ?? ($r['form_data']['full_name'] ?? ''));
                    $nik = strtolower($r['account']['nik'] ?? '');
                    $school = strtolower($r['form_data']['school_origin'] ?? '');
                    return str_contains($name, $q) || str_contains($nik, $q) || str_contains($school, $q);
                });
            }

            if (!empty($major)) {
                $registrations = array_filter($registrations, function ($r) use ($major) {
                    $m = strtolower($r['form_data']['major'] ?? ($r['student']['major'] ?? ''));
                    return $m === strtolower($major);
                });
            }

            if (!empty($paymentStatus)) {
                $registrations = array_filter($registrations, fn($r) => ($r['payment_status'] ?? '') === $paymentStatus);
            }

            if (!empty($registrationStatus)) {
                $registrations = array_filter($registrations, fn($r) => ($r['registration_status'] ?? '') === $registrationStatus);
            }

            $registrations = array_values($registrations);

            $filterLabels = [];
            if (!empty($major)) $filterLabels[] = "Jurusan: " . strtoupper($major);
            if (!empty($paymentStatus)) $filterLabels[] = "Bayar: " . strtoupper($paymentStatus);
            if (!empty($registrationStatus)) $filterLabels[] = "Status: " . strtoupper($registrationStatus);
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
            Log::error('Admin PPDB Export Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memproses ekspor data calon siswa. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Show applicant full details and dossier.
     */
    public function show(Request $request, string $id)
    {
        try {
            // 1. Prioritas Lokal (MySQL Source of Truth)
            $reg = PpdbRegistration::with('account')
                ->where('id', $id)
                ->orWhere('remote_id', $id)
                ->orWhere('account_id', $id)
                ->first();

            if ($reg) {
                $registration = $this->formatRegistration($reg);
                $documents = ppdb_get_student_documents($registration, $this->backendUrl());
                return view('admin.ppdb.show', [
                    'registration' => $registration,
                    'formData' => $registration['form_data'] ?? [],
                    'account' => $registration['account'] ?? [],
                    'documents' => $documents,
                    'backendUrl' => $this->backendUrl(),
                ]);
            }

            // 2. Fallback: Cek API remote jika online atau mocked dalam unit test
            if (session()->has('admin_api_token')) {
                try {
                    $response = $this->httpWithAdminToken()->get($this->backendUrl() . '/api/ppdb/registrations/' . $id);
                    if ($response->successful()) {
                        $registration = $response->json();
                        $documents = ppdb_get_student_documents($registration, $this->backendUrl());
                        return view('admin.ppdb.show', [
                            'registration' => $registration,
                            'formData' => $registration['form_data'] ?? [],
                            'account' => $registration['account'] ?? [],
                            'documents' => $documents,
                            'backendUrl' => $this->backendUrl(),
                        ]);
                    }
                } catch (\Throwable $e) {
                }
            }

            return redirect()->route('admin.ppdb.index')->with('error', 'Data pendaftar tidak ditemukan.');

        } catch (\Exception $e) {
            Log::error('Admin PPDB Show Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.index')->with('error', 'Terjadi kesalahan saat memuat detail pendaftar. Silakan coba beberapa saat lagi.');
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
            'payment_method' => ['nullable', 'in:transfer,cash'],
        ]);

        try {
            $reg = PpdbRegistration::with('account')
                ->where('id', $id)
                ->orWhere('remote_id', $id)
                ->orWhere('account_id', $id)
                ->first();

            $verifiedAmount = ($request->has('payment_amount') && $request->payment_amount !== null && $request->payment_amount !== '')
                ? (float) $request->payment_amount
                : 400000.00;
            $paymentStatus = $request->payment_status;
            if ($verifiedAmount == 0.0) {
                $paymentStatus = 'paid';
            }

            if ($reg) {
                $updateData = [
                    'payment_status' => $paymentStatus,
                    'payment_amount' => $verifiedAmount,
                    'payment_verified_at' => now(),
                    'payment_verified_by' => session('admin_user.username') ?? 'Admin',
                    'sync_status' => 'pending',
                    'sync_message' => 'Status pembayaran diverifikasi di lokal, siap disinkronkan',
                ];
                if ($request->filled('payment_method')) {
                    $updateData['payment_method'] = $request->payment_method;
                }
                $wasPaid = $reg->payment_status === 'paid';
                $reg->update($updateData);
                if ($paymentStatus === 'paid' && !$wasPaid) {
                    \App\Services\PaymentEmailService::sendVerified($reg->fresh());
                }
            }

            // Coba kirim langsung ke backend jika online
            try {
                $targetRemoteId = $reg?->remote_id ?: $id;
                $this->httpWithAdminToken()->put($this->backendUrl() . '/api/ppdb/verify-payment/' . $targetRemoteId, [
                    'payment_status' => $paymentStatus,
                    'payment_amount' => $verifiedAmount,
                ]);
            } catch (\Throwable $e) {
                // Offline fallback
            }

            $label = $request->payment_status === 'paid' ? 'LUNAS (PAID)' : 'DITOLAK (REJECTED)';
            return back()->with('success', "Status pembayaran pendaftar berhasil diperbarui menjadi: {$label}.");

        } catch (\Exception $e) {
            Log::error('Admin Verify Payment Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan saat memproses verifikasi pembayaran. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Show applicant edit form for admin.
     */
    public function edit(Request $request, string $id)
    {
        try {
            // Cek API jika online / mocked
            if (session()->has('admin_api_token')) {
                try {
                    $response = $this->httpWithAdminToken()->get($this->backendUrl() . '/api/ppdb/registrations/' . $id);
                    if ($response->successful()) {
                        $registration = $response->json();
                        return view('admin.ppdb.edit', [
                            'registration' => $registration,
                            'formData' => $registration['form_data'] ?? [],
                            'account' => $registration['account'] ?? [],
                            'backendUrl' => $this->backendUrl(),
                        ]);
                    }
                } catch (\Throwable $e) {
                }
            }

            $reg = PpdbRegistration::with('account')
                ->where('id', $id)
                ->orWhere('remote_id', $id)
                ->first();

            if ($reg) {
                $registration = $this->formatRegistration($reg);
                return view('admin.ppdb.edit', [
                    'registration' => $registration,
                    'formData' => $registration['form_data'] ?? [],
                    'account' => $registration['account'] ?? [],
                    'backendUrl' => $this->backendUrl(),
                ]);
            }

            return redirect()->route('admin.ppdb.index')->with('error', 'Data pendaftar tidak ditemukan.');

        } catch (\Exception $e) {
            Log::error('Admin PPDB Edit Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.index')->with('error', 'Terjadi kesalahan saat memuat formulir pendaftar. Silakan coba beberapa saat lagi.');
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
            'father_status' => ['nullable', 'string', 'in:ada,meninggal,cerai,tidak_diketahui'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'father_nik' => ['nullable', 'string', 'max:20'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'mother_status' => ['nullable', 'string', 'in:ada,meninggal,cerai,tidak_diketahui'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'mother_nik' => ['nullable', 'string', 'max:20'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_nik' => ['nullable', 'string', 'max:20'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
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
            $reg = PpdbRegistration::with('account')
                ->where('id', $id)
                ->orWhere('remote_id', $id)
                ->first();

            // Update field form_data
            $currentForm = ($reg && is_array($reg->form_data)) ? $reg->form_data : [];
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

            // Parents / Wali
            if ($request->has('father_status') || $request->has('mother_status') || $request->filled('father_name') || $request->filled('mother_name')) {
                $rawParents = $currentForm['student_parents'] ?? ($currentForm['parents'] ?? []);
                $existingFather = [];
                $existingMother = [];
                $existingGuardian = [];
                if (is_array($rawParents)) {
                    foreach ($rawParents as $sp) {
                        $relType = (int) ($sp['relationship_type'] ?? 0);
                        $pObj = $sp['parent'] ?? $sp;
                        if ($relType === 1) $existingFather = is_array($pObj) ? $pObj : [];
                        elseif ($relType === 2) $existingMother = is_array($pObj) ? $pObj : [];
                        elseif ($relType === 3) $existingGuardian = is_array($pObj) ? $pObj : [];
                    }
                }

                $fStatus = $request->input('father_status', $existingFather['status'] ?? 'ada');
                $fLabel = match ($fStatus) {
                    'meninggal' => 'Meninggal Dunia',
                    'cerai' => 'Cerai / Pisah KK',
                    'tidak_diketahui' => 'Tidak Diketahui',
                    default => 'Ada / Tercantum di KK',
                };
                $fName = $request->filled('father_name') 
                    ? $request->father_name 
                    : ($fStatus !== 'ada' ? $fLabel : ($existingFather['full_name'] ?? ''));

                $existingFather['status'] = $fStatus;
                $existingFather['status_label'] = $fLabel;
                $existingFather['full_name'] = $fName;
                if ($request->filled('father_nik')) $existingFather['nik'] = $request->father_nik;
                if ($request->filled('father_phone')) {
                    $existingFather['phone_number'] = $request->father_phone;
                    $existingFather['whatsapp_number'] = $request->father_phone;
                }

                $mStatus = $request->input('mother_status', $existingMother['status'] ?? 'ada');
                $mLabel = match ($mStatus) {
                    'meninggal' => 'Meninggal Dunia',
                    'cerai' => 'Cerai / Pisah KK',
                    'tidak_diketahui' => 'Tidak Diketahui',
                    default => 'Ada / Tercantum di KK',
                };
                $mName = $request->filled('mother_name') 
                    ? $request->mother_name 
                    : ($mStatus !== 'ada' ? $mLabel : ($existingMother['full_name'] ?? ''));

                $existingMother['status'] = $mStatus;
                $existingMother['status_label'] = $mLabel;
                $existingMother['full_name'] = $mName;
                if ($request->filled('mother_nik')) $existingMother['nik'] = $request->mother_nik;
                if ($request->filled('mother_phone')) {
                    $existingMother['phone_number'] = $request->mother_phone;
                    $existingMother['whatsapp_number'] = $request->mother_phone;
                }

                $newParentsList = [
                    ['relationship_type' => 1, 'parent' => $existingFather],
                    ['relationship_type' => 2, 'parent' => $existingMother],
                ];

                if ($request->filled('guardian_name') || !empty($existingGuardian['full_name'])) {
                    if ($request->filled('guardian_name')) $existingGuardian['full_name'] = $request->guardian_name;
                    if ($request->filled('guardian_nik')) $existingGuardian['nik'] = $request->guardian_nik;
                    if ($request->filled('guardian_phone')) {
                        $existingGuardian['phone_number'] = $request->guardian_phone;
                        $existingGuardian['whatsapp_number'] = $request->guardian_phone;
                    }
                    $newParentsList[] = ['relationship_type' => 3, 'parent' => $existingGuardian];
                }

                $currentForm['student_parents'] = $newParentsList;
            }

            if ($reg) {
                // 1. Update Account
                if ($reg->account) {
                    $reg->account->update([
                        'full_name' => $request->full_name,
                        'nik' => $request->nik,
                        'email' => $request->email,
                        'sync_status' => 'pending',
                        'sync_message' => 'Data diperbarui di lokal, siap disinkronkan',
                    ]);
                }

                // 2. Update Registration
                $paymentAmount = ($request->has('payment_amount') && $request->payment_amount !== null && $request->payment_amount !== '')
                    ? (float) $request->payment_amount
                    : 400000.0;
                $paymentStatus = $request->payment_status;
                if ($paymentAmount == 0.0 && $request->has('payment_amount') && $request->payment_amount !== null && $request->payment_amount !== '') {
                    $paymentStatus = 'paid';
                }

                $reg->update([
                    'payment_status' => $paymentStatus,
                    'payment_amount' => $paymentAmount,
                    'payment_verified_at' => $paymentStatus === 'paid' ? ($reg->payment_verified_at ?: now()) : null,
                    'payment_verified_by' => $paymentStatus === 'paid' ? ($reg->payment_verified_by ?: (session('admin_user.username') ?? 'Admin')) : null,
                    'registration_status' => $request->registration_status,
                    'form_data' => $currentForm,
                    'sync_status' => 'pending',
                    'sync_message' => 'Data diperbarui di lokal, siap disinkronkan',
                ]);
            }

            // Sync ke remote API jika online
            try {
                $targetRemoteId = $reg?->remote_id ?: $id;
                $payload = [
                    'full_name' => $request->full_name,
                    'nik' => $request->nik,
                    'email' => $request->email,
                    'payment_status' => $paymentStatus ?? $request->payment_status,
                    'payment_amount' => $paymentAmount ?? 400000.0,
                    'registration_status' => $request->registration_status,
                    'form_data' => $currentForm,
                ];
                $this->httpWithAdminToken()->put($this->backendUrl() . '/api/ppdb/registrations/' . $targetRemoteId, $payload);
            } catch (\Throwable $e) {
                // Offline fallback
            }

            return redirect()->route('admin.ppdb.show', $reg?->id ?: $id)->with('success', "Data pendaftar {$request->full_name} berhasil diperbarui!");

        } catch (\Exception $e) {
            Log::error('Admin PPDB Update Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data calon siswa. Silakan periksa kembali isian formulir.');
        }
    }

    /**
     * Delete applicant registration permanently.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $reg = PpdbRegistration::with('account')
                ->where('id', $id)
                ->orWhere('remote_id', $id)
                ->first();

            $remoteId = $reg?->remote_id ?: $id;

            if ($reg) {
                if ($reg->account) {
                    $reg->account->delete();
                }
                $reg->delete();
            }

            // Coba hapus juga di remote API jika online
            try {
                $this->httpWithAdminToken()->delete($this->backendUrl() . '/api/ppdb/registrations/' . $remoteId);
            } catch (\Throwable $e) {
                // Offline
            }

            return redirect()->route('admin.ppdb.index')->with('success', 'Data pendaftar berhasil dihapus secara permanen dari sistem PPDB.');

        } catch (\Exception $e) {
            Log::error('Admin PPDB Delete Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.index')->with('error', 'Terjadi kendala saat menghapus data pendaftar. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Accept applicant and migrate data to school system.
     */
    public function acceptStudent(Request $request, string $id)
    {
        try {
            $reg = PpdbRegistration::with('account')
                ->where('id', $id)
                ->orWhere('remote_id', $id)
                ->first();

            if ($reg) {
                if ($reg->payment_status !== 'paid') {
                    return back()->with('error', 'Calon siswa harus berstatus LUNAS (PAID) terlebih dahulu sebelum dapat diterima.');
                }

                if (!ppdb_is_form_filled($reg)) {
                    return back()->with('error', 'Formulir pendaftaran calon siswa belum diisi.');
                }

                $reg->update([
                    'registration_status' => 'accepted',
                    'sync_status' => 'pending',
                    'sync_message' => 'Siswa diterima di lokal, siap disinkronkan ke SIAKAD',
                ]);
            }

            // Coba kirim langsung ke backend jika online
            $remoteMessage = null;
            try {
                $targetRemoteId = $reg?->remote_id ?: $id;
                $acceptRes = $this->httpWithAdminToken()->post($this->backendUrl() . '/api/ppdb/accept/' . $targetRemoteId);
                if ($acceptRes->successful()) {
                    $acceptData = $acceptRes->json();
                    $remoteMessage = $acceptData['message'] ?? null;
                    if ($reg && !empty($acceptData['student_id'])) {
                        $reg->student_id = $acceptData['student_id'];
                        $reg->sync_status = 'synced';
                        $reg->save();
                    }
                }
            } catch (\Throwable $e) {
                // Offline fallback
            }

            $successMsg = $remoteMessage ?: 'Calon siswa berhasil diterima di sistem PPDB!';
            return back()->with('success', $successMsg);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Accept Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Terjadi kesalahan saat memproses penerimaan calon siswa. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Satu-Klik Kirim ke Master Data API (One-Click Bulk Sync).
     */
    public function syncAllToApi(Request $request, ApiSyncService $syncService)
    {
        try {
            $token = session('admin_api_token');
            $summary = $syncService->syncAllPending($token);

            $msgParts = [];
            if ($summary['accounts_synced'] > 0) {
                $msgParts[] = "{$summary['accounts_synced']} akun calon siswa";
            }
            if ($summary['registrations_synced'] > 0) {
                $msgParts[] = "{$summary['registrations_synced']} data formulir & pendaftaran";
            }
            if ($summary['payments_synced'] > 0) {
                $msgParts[] = "{$summary['payments_synced']} verifikasi pembayaran";
            }
            if ($summary['acceptances_synced'] > 0) {
                $msgParts[] = "{$summary['acceptances_synced']} penerimaan siswa SIAKAD";
            }

            if (!empty($msgParts)) {
                $successText = "Sinkronisasi ke Master Data API Berhasil! Berhasil mengirim: " . implode(', ', $msgParts) . ".";
                if ($summary['failed_count'] > 0) {
                    return back()->with('warning', $successText . " Namun terdapat {$summary['failed_count']} data yang mengalami kendala koneksi.")
                        ->with('sync_results', $summary);
                }
                return back()->with('success', $successText)->with('sync_results', $summary);
            }

            if ($summary['failed_count'] > 0) {
                return back()->with('error', "Gagal menyinkronkan {$summary['failed_count']} data ke Master API. Pastikan server backend aktif di " . $this->backendUrl())
                    ->with('sync_results', $summary);
            }

            return back()->with('info', 'Seluruh data lokal sudah dalam keadaan tersinkronisasi (tidak ada data pending).')
                ->with('sync_results', $summary);

        } catch (\Exception $e) {
            Log::error('SyncAllToApi Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal memproses sinkronisasi ke Master API. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Halaman Manajemen Pencadangan Google Drive 4 TB.
     */
    public function backup(Request $request, \App\Services\GoogleDriveBackupService $driveService)
    {
        $isConnected = $driveService->isConnected();
        $authUrl = $driveService->getAuthUrl('http://127.0.0.1:8989');
        $folderId = config('services.google_drive.folder_id');
        $backups = $isConnected ? $driveService->listBackups() : [];

        return view('admin.ppdb.backup', [
            'isConnected' => $isConnected,
            'authUrl' => $authUrl,
            'folderId' => $folderId,
            'backups' => $backups,
            'backendUrl' => $this->backendUrl(),
        ]);
    }

    /**
     * Jalankan proses pencadangan manual ke Google Drive 4 TB.
     */
    public function runBackup(Request $request, \App\Services\GoogleDriveBackupService $driveService)
    {
        @ini_set('max_execution_time', '600');
        @set_time_limit(600);

        try {
            if (!$driveService->isConnected()) {
                return back()->with('error', 'Google Drive belum terhubung. Harap lakukan otorisasi akun terlebih dahulu.');
            }

            $archive = $driveService->createBackupArchive();
            $driveResult = $driveService->uploadToGoogleDrive($archive['path'], $archive['filename']);
            
            // Hapus file sementara lokal
            @\Illuminate\Support\Facades\File::delete($archive['path']);

            $fileSizeMB = round($archive['size'] / (1024 * 1024), 2);
            $fileName = $archive['filename'];

            return back()->with('success', "Pencadangan berhasil! File arsip {$fileName} ({$fileSizeMB} MB) telah berhasil disimpan ke Google Drive 4 TB Anda.");

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Run Backup Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal melakukan pencadangan ke Google Drive: ' . $e->getMessage());
        }
    }

    /**
     * Simpan kode otorisasi dari Google OAuth callback ke .env.
     */
    public function saveBackupAuthCode(Request $request, \App\Services\GoogleDriveBackupService $driveService)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => 'Kode otorisasi Google wajib diisi.',
        ]);

        try {
            $driveService->exchangeCode($request->code, 'http://127.0.0.1:8989');
            return back()->with('success', 'Google Drive 4 TB berhasil dihubungkan! Anda sekarang dapat melakukan pencadangan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghubungkan Google Drive: ' . $e->getMessage());
        }
    }
}
