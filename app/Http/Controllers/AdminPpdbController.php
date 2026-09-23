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
                'with_form' => count(array_filter($allRegistrations, fn($r) => !empty($r['form_data']['nik']) && !empty($r['form_data']['major']))),
                'total_amount' => array_reduce($allRegistrations, function ($acc, $r) {
                    return ($r['payment_status'] ?? '') === 'paid' ? $acc + (float)($r['payment_amount'] ?? 400000) : $acc;
                }, 0),
            ];

            // Statistik Sync Status
            $syncStats = [
                'pending' => PpdbAccount::where('sync_status', 'pending')->count() + PpdbRegistration::where('sync_status', 'pending')->count(),
                'failed' => PpdbAccount::where('sync_status', 'failed')->count() + PpdbRegistration::where('sync_status', 'failed')->count(),
                'synced' => PpdbRegistration::where('sync_status', 'synced')->count(),
                'last_synced_at' => SyncLog::latest('executed_at')->value('executed_at'),
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

            return view('admin.ppdb.students', [
                'students' => $students,
                'stats' => $stats,
                'filters' => compact('search', 'major', 'paymentStatus', 'registrationStatus'),
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
                    'registration_status' => $r['registration_status'] ?? 'pending',
                    'has_form' => !empty($form['nik']) && !empty($form['major']),
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
                'has_form' => count(array_filter($allRegistrations, fn($r) => !empty($r['form_data']['nik']) && !empty($r['form_data']['major']))),
            ];

            return view('admin.ppdb.users', [
                'users' => $users,
                'stats' => $stats,
                'totalAccounts' => count($allRegistrations),
                'filters' => compact('search', 'status'),
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
                $formData = is_array($reg->form_data) ? $reg->form_data : [];
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

                $isPaid = $request->payment_status === 'paid';
                $reg->update([
                    'payment_status' => $request->payment_status,
                    'payment_amount' => $isPaid ? 400000.0 : (float)($reg->payment_amount ?? 0),
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
                    'payment_status' => $request->payment_status,
                    'payment_amount' => $request->payment_status === 'paid' ? 400000.0 : 0.0,
                    'registration_status' => $reg?->registration_status ?? 'pending',
                    'form_data' => $formData ?? [],
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

            // 1. Simpan ke tabel ppdb_accounts lokal
            $account = PpdbAccount::updateOrCreate(
                ['email' => $request->email],
                [
                    'nik' => $cleanNik,
                    'full_name' => $request->full_name,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                    'sync_status' => 'pending',
                    'sync_message' => 'Menunggu sinkronisasi ke Master API',
                ]
            );

            // 2. Buat entri registrasi lokal
            $isPaid = $request->boolean('mark_as_paid');
            PpdbRegistration::updateOrCreate(
                ['account_id' => $account->id],
                [
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
                ]
            );

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

            return view('admin.ppdb.documents', [
                'documents' => $docs,
                'stats' => $stats,
                'filters' => [
                    'search' => $search,
                    'docStatus' => $docStatus,
                ],
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('Admin PPDB Documents Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('admin.ppdb.index')->with('error', 'Gagal memuat Data Berkas. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Modul Verifikasi Pembayaran.
     */
    public function payments(Request $request)
    {
        $status = $request->query('status');

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
                    'payment_status' => $r['payment_status'] ?? 'unpaid',
                    'payment_amount' => (float)($r['payment_amount'] ?? 0),
                    'payment_proof_path' => $r['payment_proof_path'] ?? null,
                    'payment_verified_at' => $r['payment_verified_at'] ?? null,
                    'payment_verified_by' => $r['payment_verified_by'] ?? null,
                    'created_at' => $r['created_at'] ?? now()->toIso8601String(),
                ];
            }

            if (!empty($status)) {
                if ($status === 'pending' || $status === 'pending_verification') {
                    $payments = array_values(array_filter($payments, fn($p) => in_array($p['payment_status'], ['pending', 'pending_verification'])));
                } else {
                    $payments = array_values(array_filter($payments, fn($p) => $p['payment_status'] === $status));
                }
            }

            $stats = [
                'total' => count($allRegistrations),
                'pending' => count(array_filter($allRegistrations, fn($r) => in_array($r['payment_status'] ?? '', ['pending', 'pending_verification']))),
                'paid' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'paid')),
                'rejected' => count(array_filter($allRegistrations, fn($r) => ($r['payment_status'] ?? '') === 'rejected')),
            ];

            return view('admin.ppdb.payments', [
                'payments' => $payments,
                'stats' => $stats,
                'filterStatus' => $status,
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

            if ($reg) {
                $updateData = [
                    'payment_status' => $request->payment_status,
                    'payment_amount' => (float) ($request->payment_amount ?? 400000.00),
                    'payment_verified_at' => now(),
                    'payment_verified_by' => session('admin_user.username') ?? 'Admin',
                    'sync_status' => 'pending',
                    'sync_message' => 'Status pembayaran diverifikasi di lokal, siap disinkronkan',
                ];
                if ($request->filled('payment_method')) {
                    $updateData['payment_method'] = $request->payment_method;
                }
                $reg->update($updateData);
            }

            // Coba kirim langsung ke backend jika online
            try {
                $targetRemoteId = $reg?->remote_id ?: $id;
                $this->httpWithAdminToken()->put($this->backendUrl() . '/api/ppdb/verify-payment/' . $targetRemoteId, [
                    'payment_status' => $request->payment_status,
                    'payment_amount' => (float) ($request->payment_amount ?? 400000.00),
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
                $reg->update([
                    'payment_status' => $request->payment_status,
                    'payment_amount' => (float) ($request->payment_amount ?? 0),
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
                    'payment_status' => $request->payment_status,
                    'payment_amount' => (float) ($request->payment_amount ?? 0),
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

                if (empty($reg->form_data)) {
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
}
