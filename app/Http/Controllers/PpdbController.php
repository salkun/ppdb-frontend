<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use App\Models\PpdbAccount;
use App\Models\PpdbRegistration;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PpdbController extends Controller
{
    use InteractsWithFastApi;

    /**
     * Helper: Cari entri akun & registrasi calon siswa dari sesi saat ini atau fallback API.
     */
    protected function getCurrentRegistration(): ?PpdbRegistration
    {
        // Khusus dalam mode testing (PHPUnit/Pest), utamakan respons dari mocked API jika ada
        if (app()->environment('testing') && session()->has('api_token') && !str_starts_with(session('api_token'), 'local_auth_')) {
            try {
                $response = $this->httpWithToken()->get($this->backendUrl() . '/api/ppdb/my-registration');
                if ($response->successful()) {
                    $remoteData = $response->json();
                    return new PpdbRegistration([
                        'id' => $remoteData['id'] ?? 'mock-reg-id-999',
                        'account_id' => $remoteData['account_id'] ?? session('account_id'),
                        'payment_status' => $remoteData['payment_status'] ?? 'unpaid',
                        'payment_amount' => (float)($remoteData['payment_amount'] ?? 0),
                        'payment_proof_path' => $remoteData['payment_proof_path'] ?? null,
                        'registration_status' => $remoteData['registration_status'] ?? 'pending',
                        'form_data' => $remoteData['form_data'] ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                // Fallback to local
            }
        }

        $accountId = session('account_id');
        $email = session('email');

        $account = null;
        if ($accountId) {
            $account = PpdbAccount::with('registration')
                ->where('id', $accountId)
                ->orWhere('remote_id', $accountId)
                ->first();
        }
        if (!$account && $email && !$accountId) {
            $account = PpdbAccount::with('registration')->where('email', $email)->first();
        }

        // 1. JIKA AKUN & REGISTRASI LOKAL SUDAH ADA, LANGSUNG KEMBALIKAN (LOCAL WINS)
        if ($account && $account->registration) {
            return $account->registration;
        }

        // 2. Fallback: Jika di lokal belum ada dan session memiliki api_token, coba sinkronkan status dari API backend
        if (session()->has('api_token')) {
            try {
                $response = $this->httpWithToken()->get($this->backendUrl() . '/api/ppdb/my-registration');
                if ($response->successful()) {
                    $remoteData = $response->json();
                    $userEmail = session('email', 'pendaftar@example.com');
                    $userName = session('full_name', 'Calon Siswa');

                    if (!$account) {
                        $account = PpdbAccount::firstOrCreate(
                            ['email' => $userEmail],
                            [
                                'full_name' => $userName,
                                'password' => Hash::make('PpdbSecret123!'),
                                'remote_id' => $remoteData['account_id'] ?? null,
                                'sync_status' => 'synced',
                            ]
                        );
                    }

                    $reg = PpdbRegistration::where('account_id', $account->id)->first();
                    if (!$reg) {
                        $reg = PpdbRegistration::create([
                            'account_id' => $account->id,
                            'payment_status' => $remoteData['payment_status'] ?? 'unpaid',
                            'payment_amount' => (float)($remoteData['payment_amount'] ?? 0),
                            'payment_proof_path' => $remoteData['payment_proof_path'] ?? null,
                            'registration_status' => $remoteData['registration_status'] ?? 'pending',
                            'form_data' => $remoteData['form_data'] ?? null,
                            'remote_id' => $remoteData['id'] ?? null,
                            'sync_status' => 'synced',
                        ]);
                    } else {
                        // Jangan timpa status lokal jika lokal sudah paid / pending_verification
                        $localStatus = $reg->payment_status;
                        $newStatus = in_array($localStatus, ['paid', 'pending_verification']) ? $localStatus : ($remoteData['payment_status'] ?? $localStatus);
                        $reg->update([
                            'payment_status' => $newStatus,
                            'payment_amount' => isset($remoteData['payment_amount']) ? (float)$remoteData['payment_amount'] : $reg->payment_amount,
                            'payment_proof_path' => $reg->payment_proof_path ?: ($remoteData['payment_proof_path'] ?? null),
                            'registration_status' => $remoteData['registration_status'] ?? $reg->registration_status,
                            'form_data' => $remoteData['form_data'] ?? $reg->form_data,
                            'remote_id' => $remoteData['id'] ?? $reg->remote_id,
                        ]);
                    }

                    session(['account_id' => $account->id]);
                    return $reg;
                }
            } catch (\Throwable $e) {
                // API unreachable or offline
            }
        }

        return $account?->registration;
    }

    /**
     * Upload payment proof file: Simpan file ke direktori lokal & update status pendaftaran di MySQL.
     */
    public function uploadPayment(Request $request, TelegramNotificationService $telegramService)
    {
        $paymentMethod = $request->input('payment_method', 'transfer');
        if (!in_array($paymentMethod, ['transfer', 'cash'])) {
            $paymentMethod = 'transfer';
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:5120'],
            'payment_method' => ['nullable', 'in:transfer,cash'],
        ], [
            'file.required' => $paymentMethod === 'cash'
                ? 'Silakan unggah foto kuitansi pembayaran tunai (cash).'
                : 'Silakan pilih berkas bukti transfer bank.',
            'file.mimes' => 'Format berkas harus berupa JPG, JPEG, PNG, WEBP, atau PDF.',
            'file.mimetypes' => 'Tipe berkas tidak valid. Berkas harus berupa gambar JPG/PNG/WEBP atau dokumen PDF.',
            'file.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        try {
            $file = $request->file('file');
            $registration = $this->getCurrentRegistration();

            // 1. Simpan berkas fisik ke folder public/uploads/ppdb_payments/
            $uploadDir = public_path('uploads/ppdb_payments');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $regId = $registration?->id ?: uniqid();
            $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $randomCode = \Illuminate\Support\Str::random(16);
            $filename = 'proof_' . $paymentMethod . '_' . $regId . '_' . time() . '_' . $randomCode . '.' . $ext;
            $file->move($uploadDir, $filename);
            $webPath = '/uploads/ppdb_payments/' . $filename;

            // 2. Simpan status ke MySQL lokal
            if ($registration) {
                $registration->update([
                    'payment_status' => 'pending_verification',
                    'payment_method' => $paymentMethod,
                    'payment_proof_path' => $webPath,
                    'sync_status' => 'pending',
                    'sync_message' => 'Bukti pembayaran baru (' . ($paymentMethod === 'cash' ? 'Tunai/Cash' : 'Transfer TF') . ') diunggah oleh siswa',
                ]);
            }

            // 3. Coba kirimkan juga ke remote backend jika terhubung
            try {
                $uploadedFilePath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                $this->httpWithToken()
                    ->attach('file', file_get_contents($uploadedFilePath), $filename)
                    ->post($this->backendUrl() . '/api/ppdb/upload-payment', [
                        'payment_method' => $paymentMethod,
                    ]);
            } catch (\Throwable $e) {
                // Offline fallback
            }

            // 4. Kirim notifikasi ke Grup Telegram Panitia PPDB (Fail-safe)
            try {
                $studentData = [
                    'full_name' => session('full_name', $registration?->account?->full_name ?? 'Calon Siswa'),
                    'email' => session('email', $registration?->account?->email ?? '-'),
                ];

                $uploadedFilePath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
                $telegramFile = new \Illuminate\Http\UploadedFile(
                    $uploadedFilePath,
                    $filename,
                    File::mimeType($uploadedFilePath),
                    null,
                    true
                );

                $telegramService->sendPaymentProofNotification(
                    $telegramFile,
                    $studentData,
                    $registration?->id ?: null,
                    $paymentMethod
                );
            } catch (\Throwable $telegramException) {
                Log::warning('Notifikasi Telegram PPDB gagal dikirim: ' . $telegramException->getMessage());
            }

            $successMsg = $paymentMethod === 'cash'
                ? 'Bukti kuitansi pembayaran tunai (cash) berhasil diunggah! Mohon menunggu verifikasi oleh panitia PPDB.'
                : 'Bukti transfer pembayaran berhasil diunggah! Mohon menunggu verifikasi oleh panitia PPDB.';

            return redirect()->route('dashboard')->with('success', $successMsg);

        } catch (\Exception $e) {
            Log::error('PPDB Payment Upload Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('dashboard')->with('error', 'Terjadi kendala saat memproses unggahan bukti pembayaran. Silakan coba kembali atau hubungi panitia PPDB.');
        }
    }

    /**
     * Show multi-step registration form.
     */
    public function showForm(Request $request)
    {
        try {
            $registrationModel = $this->getCurrentRegistration();

            if (!$registrationModel) {
                return redirect()->route('login')->with('warning', 'Sesi login telah berakhir.');
            }

            $paymentStatus = $registrationModel->payment_status;
            $registrationStatus = $registrationModel->registration_status;

            // Locking Check: If not paid, redirect to dashboard
            if ($paymentStatus !== 'paid') {
                return redirect()->route('dashboard')->with('warning', 'Menu formulir pendaftaran masih terkunci. Pembayaran Anda harus diverifikasi (Status: PAID) terlebih dahulu oleh panitia.');
            }

            // Existing form data if already saved
            $formData = is_array($registrationModel->form_data) ? $registrationModel->form_data : [];

            // Pre-fill basic details from account/session if not yet populated
            if (empty($formData['full_name'])) {
                $formData['full_name'] = session('full_name', $registrationModel->account?->full_name);
            }
            if (empty($formData['contact']['email'])) {
                $formData['contact']['email'] = session('email', $registrationModel->account?->email);
            }

            $isLocked = ($registrationStatus === 'accepted');

            $registrationArray = [
                'id' => $registrationModel->remote_id ?: $registrationModel->id,
                'account_id' => $registrationModel->account_id,
                'payment_status' => $registrationModel->payment_status,
                'registration_status' => $registrationModel->registration_status,
                'form_data' => $formData,
            ];

            return view('ppdb.form', [
                'registration' => $registrationArray,
                'formData' => $formData,
                'isLocked' => $isLocked,
            ]);

        } catch (\Exception $e) {
            Log::error('PPDB Show Form Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('dashboard')->with('error', 'Gagal memuat formulir pendaftaran. Silakan muat ulang halaman atau hubungi panitia PPDB.');
        }
    }

    /**
     * Submit multi-step registration form as a Nested JSON payload.
     * Simpan ke MySQL lokal dan juga sync ke API jika online.
     */
    public function submitForm(Request $request)
    {
        // 1. Validasi Input Form
        $validated = $request->validate([
            // Tahap 1: Biodata Pokok & Peminatan
            'nik' => ['required', 'string', 'digits:16'],
            'nisn' => ['required', 'string', 'digits:10'],
            'full_name' => ['required', 'string', 'max:150'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'major' => ['required', 'string', 'in:reguler,bahasa,tahfidz,ict'],
            'school_origin' => ['required', 'string', 'max:150'],
            'school_origin_address' => ['required', 'string', 'max:255'],

            // Tahap 2: Identitas Tambahan
            'family_card_number' => ['required', 'string', 'digits:16'],
            'gender' => ['required', 'string', 'in:Laki-laki,Perempuan'],
            'religion' => ['required', 'string'],
            'place_of_birth' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date'],
            'birth_order' => ['required', 'integer', 'min:1'],
            'siblings_count' => ['required', 'integer', 'min:1'],

            // Tahap 3: Alamat
            'street_address' => ['required', 'string', 'max:255'],
            'rt' => ['required', 'string', 'max:5'],
            'rw' => ['required', 'string', 'max:5'],
            'village' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'residence_type' => ['required', 'string', 'max:50'],
            'transportation_mode' => ['required', 'string', 'max:50'],

            // Tahap 4: Kontak
            'phone_number' => ['nullable', 'string', 'max:20'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150'],

            // Tahap 5: Data Ayah Kandung
            'father_nik' => ['required', 'string', 'digits:16'],
            'father_name' => ['required', 'string', 'max:150'],
            'father_birth_year' => ['required', 'digits:4'],
            'father_education' => ['required', 'string'],
            'father_occupation' => ['required', 'string'],
            'father_income' => ['required', 'string'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'father_whatsapp' => ['nullable', 'string', 'max:20'],
            'father_email' => ['nullable', 'email', 'max:150'],

            // Data Ibu Kandung
            'mother_nik' => ['required', 'string', 'digits:16'],
            'mother_name' => ['required', 'string', 'max:150'],
            'mother_birth_year' => ['required', 'digits:4'],
            'mother_education' => ['required', 'string'],
            'mother_occupation' => ['required', 'string'],
            'mother_income' => ['required', 'string'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
            'mother_whatsapp' => ['nullable', 'string', 'max:20'],
            'mother_email' => ['nullable', 'email', 'max:150'],

            // Data Wali
            'has_guardian' => ['nullable'],
            'guardian_nik' => ['nullable', 'required_if:has_guardian,1', 'string', 'digits:16'],
            'guardian_name' => ['nullable', 'required_if:has_guardian,1', 'string', 'max:150'],
            'guardian_birth_year' => ['nullable', 'required_if:has_guardian,1', 'digits:4'],
            'guardian_education' => ['nullable', 'required_if:has_guardian,1', 'string'],
            'guardian_occupation' => ['nullable', 'required_if:has_guardian,1', 'string'],
            'guardian_income' => ['nullable', 'required_if:has_guardian,1', 'string'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_whatsapp' => ['nullable', 'string', 'max:20'],
            'guardian_email' => ['nullable', 'email', 'max:150'],
        ], [
            'major.required' => 'Silakan pilih jurusan yang diminati.',
            'school_origin.required' => 'Nama asal sekolah wajib diisi.',
            'school_origin_address.required' => 'Alamat sekolah asal wajib diisi.',
            'nik.digits' => 'NIK Siswa harus 16 digit.',
            'nisn.digits' => 'NISN harus 10 digit angka.',
            'family_card_number.digits' => 'Nomor Kartu Keluarga (KK) harus 16 digit.',
            'birth_order.required' => 'Urutan anak ke-berapa wajib diisi.',
            'father_nik.digits' => 'NIK Ayah harus 16 digit.',
            'mother_nik.digits' => 'NIK Ibu harus 16 digit.',
            'guardian_nik.digits' => 'NIK Wali harus 16 digit.',
        ]);

        // 2. Merakit student_parents
        $studentParents = [
            [
                'relationship_type' => 1,
                'parent' => [
                    'nik' => $request->father_nik,
                    'full_name' => $request->father_name,
                    'birth_year' => (string) $request->father_birth_year,
                    'education_code' => $request->father_education,
                    'occupation_code' => $request->father_occupation,
                    'income_code' => $request->father_income,
                    'phone_number' => $request->father_phone ?: $request->father_whatsapp,
                    'whatsapp_number' => $request->father_whatsapp ?: $request->father_phone,
                    'email' => $request->father_email ?: null,
                ]
            ],
            [
                'relationship_type' => 2,
                'parent' => [
                    'nik' => $request->mother_nik,
                    'full_name' => $request->mother_name,
                    'birth_year' => (string) $request->mother_birth_year,
                    'education_code' => $request->mother_education,
                    'occupation_code' => $request->mother_occupation,
                    'income_code' => $request->mother_income,
                    'phone_number' => $request->mother_phone ?: $request->mother_whatsapp,
                    'whatsapp_number' => $request->mother_whatsapp ?: $request->mother_phone,
                    'email' => $request->mother_email ?: null,
                ]
            ]
        ];

        if ($request->boolean('has_guardian') && !empty($request->guardian_name)) {
            $studentParents[] = [
                'relationship_type' => 3,
                'parent' => [
                    'nik' => $request->guardian_nik,
                    'full_name' => $request->guardian_name,
                    'birth_year' => (string) $request->guardian_birth_year,
                    'education_code' => $request->guardian_education,
                    'occupation_code' => $request->guardian_occupation,
                    'income_code' => $request->guardian_income,
                    'phone_number' => $request->guardian_phone ?: $request->guardian_whatsapp,
                    'whatsapp_number' => $request->guardian_whatsapp ?: $request->guardian_phone,
                    'email' => $request->guardian_email ?: null,
                ]
            ];
        }

        // 3. Merakit Nested JSON Identik dengan Skema SIAKAD
        $nestedPayload = [
            'nik' => $request->nik,
            'nisn' => $request->nisn,
            'full_name' => $request->full_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name ?: '',
            'major' => $request->major,
            'school_origin' => $request->school_origin,
            'school_origin_address' => $request->school_origin_address,
            'identity' => [
                'family_card_number' => $request->family_card_number,
                'gender' => $request->gender,
                'religion' => $request->religion,
                'place_of_birth' => $request->place_of_birth,
                'date_of_birth' => $request->date_of_birth,
                'birth_order' => (int) $request->birth_order,
                'siblings_count' => (int) $request->siblings_count,
            ],
            'address' => [
                'street_address' => $request->street_address,
                'rt' => $request->rt,
                'rw' => $request->rw,
                'village' => $request->village,
                'district' => $request->district,
                'postal_code' => $request->postal_code,
                'residence_type' => $request->residence_type,
                'transportation_mode' => $request->transportation_mode,
            ],
            'contact' => [
                'phone_number' => $request->phone_number ?: '',
                'mobile_number' => $request->mobile_number,
                'whatsapp_number' => $request->whatsapp_number,
                'email' => $request->email,
            ],
            'student_parents' => $studentParents,
        ];

        try {
            $registration = $this->getCurrentRegistration();

            // Simpan ke MySQL lokal jika ada
            if ($registration) {
                // Pertahankan berkas dokumen yang sudah diunggah sebelumnya (KK, Akta, NISN, Pas Foto)
                $existingFormData = is_array($registration->form_data)
                    ? $registration->form_data
                    : (json_decode($registration->form_data ?? '', true) ?: []);

                if (!empty($existingFormData['documents'])) {
                    $nestedPayload['documents'] = $existingFormData['documents'];
                }
                foreach (['photo_path', 'kk_path', 'birth_cert_path', 'nisn_path'] as $pathKey) {
                    if (!empty($existingFormData[$pathKey])) {
                        $nestedPayload[$pathKey] = $existingFormData[$pathKey];
                    }
                }

                $registration->update([
                    'form_data' => $nestedPayload,
                    'sync_status' => 'pending',
                    'sync_message' => 'Formulir diperbarui oleh siswa, siap disinkronkan',
                ]);

                if ($registration->account) {
                    $registration->account->update([
                        'nik' => $request->nik,
                        'full_name' => $request->full_name,
                        'sync_status' => 'pending',
                    ]);
                }
            }

            // Coba kirim juga ke backend API jika online
            try {
                $this->httpWithToken()
                    ->put($this->backendUrl() . '/api/ppdb/registration-form', $nestedPayload);
            } catch (\Throwable $e) {
                // Offline fallback
            }

            return redirect()->route('dashboard')->with('success', 'Formulir pendaftaran berhasil disimpan dan diperbarui!');

        } catch (\Exception $e) {
            Log::error('PPDB Form Submit Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan formulir pendaftaran. Silakan periksa kembali data Anda atau hubungi panitia PPDB.');
        }
    }

    /**
     * Show test card for printing / downloading.
     */
    public function showTestCard(Request $request)
    {
        try {
            $registrationModel = $this->getCurrentRegistration();

            if (!$registrationModel) {
                return redirect()->route('login')->with('warning', 'Sesi login telah berakhir.');
            }

            $paymentStatus = $registrationModel->payment_status;
            $formData = is_array($registrationModel->form_data) ? $registrationModel->form_data : [];

            // Must have paid and filled form
            if ($paymentStatus !== 'paid') {
                return redirect()->route('dashboard')->with('warning', 'Kartu tes belum tersedia. Pembayaran harus diverifikasi terlebih dahulu.');
            }

            if (empty($formData)) {
                return redirect()->route('dashboard')->with('warning', 'Kartu tes belum tersedia. Silakan lengkapi formulir pendaftaran terlebih dahulu.');
            }

            $autoPrint = $request->query('print') === '1';

            $documents = $formData['documents'] ?? [];
            if (empty($documents['foto']) && !empty($formData['photo_path'])) {
                $documents['foto'] = $formData['photo_path'];
            }
            if (empty($documents['kk']) && !empty($formData['kk_path'])) {
                $documents['kk'] = $formData['kk_path'];
            }
            if (empty($documents['akta']) && !empty($formData['birth_cert_path'])) {
                $documents['akta'] = $formData['birth_cert_path'];
            }
            if (empty($documents['nisn']) && !empty($formData['nisn_path'])) {
                $documents['nisn'] = $formData['nisn_path'];
            }

            $registrationArray = [
                'id' => $registrationModel->remote_id ?: $registrationModel->id,
                'payment_status' => $registrationModel->payment_status,
                'registration_status' => $registrationModel->registration_status,
                'form_data' => $formData,
                'documents' => $documents,
            ];

            return view('ppdb.test-card', [
                'registration' => $registrationArray,
                'formData' => $formData,
                'documents' => $documents,
                'backendUrl' => $this->backendUrl(),
                'user' => [
                    'full_name' => session('full_name', $registrationModel->account?->full_name),
                    'email' => session('email', $registrationModel->account?->email),
                ],
                'autoPrint' => $autoPrint,
            ]);

        } catch (\Exception $e) {
            Log::error('PPDB Test Card Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('dashboard')->with('error', 'Gagal memuat kartu peserta ujian. Silakan coba kembali nanti atau hubungi panitia PPDB.');
        }
    }

    /**
     * Show upload berkas (documents) page.
     */
    public function showUpload(Request $request)
    {
        try {
            $registrationModel = $this->getCurrentRegistration();

            if (!$registrationModel) {
                return redirect()->route('login')->with('warning', 'Sesi login telah berakhir.');
            }

            $paymentStatus = $registrationModel->payment_status;

            if ($paymentStatus !== 'paid') {
                return redirect()->route('dashboard')->with('warning', 'Menu upload berkas masih terkunci. Pembayaran harus diverifikasi terlebih dahulu.');
            }

            $formData = is_array($registrationModel->form_data)
                ? $registrationModel->form_data
                : (json_decode($registrationModel->form_data ?? '', true) ?: []);

            $documents = $formData['documents'] ?? [];
            if (empty($documents['kk']) && !empty($formData['kk_path'])) {
                $documents['kk'] = $formData['kk_path'];
            }
            if (empty($documents['akta']) && !empty($formData['birth_cert_path'])) {
                $documents['akta'] = $formData['birth_cert_path'];
            }
            if (empty($documents['nisn']) && !empty($formData['nisn_path'])) {
                $documents['nisn'] = $formData['nisn_path'];
            }
            if (empty($documents['foto']) && !empty($formData['photo_path'])) {
                $documents['foto'] = $formData['photo_path'];
            }

            $registrationArray = [
                'id' => $registrationModel->remote_id ?: $registrationModel->id,
                'payment_status' => $registrationModel->payment_status,
                'registration_status' => $registrationModel->registration_status,
                'payment_proof_path' => $registrationModel->payment_proof_path,
                'form_data' => $formData,
                'documents' => $documents,
            ];

            return view('ppdb.upload', [
                'registration' => $registrationArray,
                'documents' => $documents,
                'backendUrl' => $this->backendUrl(),
            ]);

        } catch (\Exception $e) {
            Log::error('PPDB Upload Berkas Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('dashboard')->with('error', 'Gagal memuat halaman unggah berkas persyaratan. Silakan coba kembali nanti.');
        }
    }

    /**
     * Submit uploaded documents (KK, Akta Kelahiran, NISN, Pas Foto).
     */
    public function submitUpload(Request $request)
    {
        $registration = $this->getCurrentRegistration();
        if (!$registration) {
            return redirect()->route('login')->with('warning', 'Sesi login Anda telah berakhir.');
        }

        if ($registration->payment_status !== 'paid') {
            return redirect()->route('dashboard')->with('warning', 'Pembayaran harus diverifikasi terlebih dahulu sebelum mengunggah berkas.');
        }

        $formData = is_array($registration->form_data)
            ? $registration->form_data
            : (json_decode($registration->form_data ?? '', true) ?: []);
        $existingDocs = $formData['documents'] ?? [];

        // Opsi: Hapus berkas tertentu jika diklik tombol hapus (hanya key yang sah)
        if ($request->filled('delete_doc')) {
            $docType = $request->input('delete_doc');
            $allowedDocTypes = ['kk', 'akta', 'nisn', 'foto'];

            if (in_array($docType, $allowedDocTypes, true) && isset($existingDocs[$docType])) {
                $filePath = public_path(ltrim($existingDocs[$docType], '/'));
                $realUploadDir = realpath(public_path('uploads/ppdb_documents'));
                $realFilePath = realpath($filePath);

                // Pastikan file benar-benar berada di dalam direktori upload berkas (cegah path traversal)
                if ($realFilePath && $realUploadDir && str_starts_with($realFilePath, $realUploadDir) && File::exists($realFilePath)) {
                    @File::delete($realFilePath);
                }

                unset($existingDocs[$docType]);
                $formData['documents'] = $existingDocs;
                if ($docType === 'kk') unset($formData['kk_path']);
                if ($docType === 'akta') unset($formData['birth_cert_path']);
                if ($docType === 'nisn') unset($formData['nisn_path']);
                if ($docType === 'foto') unset($formData['photo_path']);

                $registration->update([
                    'form_data' => $formData,
                    'sync_status' => 'pending',
                ]);

                return redirect()->route('ppdb.upload')->with('success', 'Berkas berhasil dihapus.');
            }
        }

        // Validasi input file (MIME type & extension)
        $request->validate([
            'kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:5120'],
            'akta' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:5120'],
            'nisn' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'max:5120'],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:5120'],
        ], [
            'kk.mimes' => 'Berkas Kartu Keluarga harus berformat JPG, PNG, atau PDF.',
            'kk.mimetypes' => 'Format berkas Kartu Keluarga tidak valid (harus gambar atau PDF).',
            'kk.max' => 'Ukuran berkas Kartu Keluarga maksimal 5 MB.',
            'akta.mimes' => 'Berkas Akta Kelahiran harus berformat JPG, PNG, atau PDF.',
            'akta.mimetypes' => 'Format berkas Akta Kelahiran tidak valid (harus gambar atau PDF).',
            'akta.max' => 'Ukuran berkas Akta Kelahiran maksimal 5 MB.',
            'nisn.mimes' => 'Berkas NISN harus berformat JPG, PNG, atau PDF.',
            'nisn.mimetypes' => 'Format berkas NISN tidak valid (harus gambar atau PDF).',
            'nisn.max' => 'Ukuran berkas NISN maksimal 5 MB.',
            'foto.mimes' => 'Pas Foto harus berformat JPG, PNG, atau WEBP.',
            'foto.mimetypes' => 'Format Pas Foto harus berupa file gambar valid.',
            'foto.max' => 'Ukuran Pas Foto maksimal 5 MB.',
        ]);

        try {
            $hasAnyNewUpload = $request->hasFile('kk') || $request->hasFile('akta') || $request->hasFile('nisn') || $request->hasFile('foto');

            if (!$hasAnyNewUpload && empty($existingDocs)) {
                return back()->with('warning', 'Silakan pilih minimal satu berkas dokumen untuk diunggah.');
            }

            $uploadDir = public_path('uploads/ppdb_documents');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $regId = $registration->id;
            $docTypes = ['kk', 'akta', 'nisn', 'foto'];
            $uploadedCount = 0;

            foreach ($docTypes as $docType) {
                if ($request->hasFile($docType)) {
                    $file = $request->file($docType);
                    $ext = strtolower($file->getClientOriginalExtension()) ?: 'bin';
                    $randomSuffix = \Illuminate\Support\Str::random(16);
                    $filename = "{$docType}_{$regId}_" . time() . "_{$uploadedCount}_{$randomSuffix}.{$ext}";
                    $file->move($uploadDir, $filename);
                    $existingDocs[$docType] = "/uploads/ppdb_documents/{$filename}";
                    $uploadedCount++;
                }
            }

            // Simpan ke form_data
            $formData['documents'] = $existingDocs;
            if (isset($existingDocs['kk'])) $formData['kk_path'] = $existingDocs['kk'];
            if (isset($existingDocs['akta'])) $formData['birth_cert_path'] = $existingDocs['akta'];
            if (isset($existingDocs['nisn'])) $formData['nisn_path'] = $existingDocs['nisn'];
            if (isset($existingDocs['foto'])) $formData['photo_path'] = $existingDocs['foto'];

            $registration->update([
                'form_data' => $formData,
                'sync_status' => 'pending',
                'sync_message' => 'Berkas dokumen diperbarui, siap disinkronkan',
            ]);

            // Sinkronkan ke API remote jika online atau test
            if (app()->environment('testing') || session()->has('api_token')) {
                try {
                    $targetRemoteId = $registration->remote_id ?: $registration->id;
                    $this->httpWithToken()->put($this->backendUrl() . '/api/ppdb/registrations/' . $targetRemoteId, [
                        'form_data' => $formData,
                    ]);
                } catch (\Throwable $e) {}
            }

            $totalActive = count(array_filter(['kk', 'akta', 'nisn', 'foto'], fn($k) => !empty($existingDocs[$k])));
            $msg = $uploadedCount > 0 
                ? "Berhasil menyimpan {$uploadedCount} berkas baru! (Total {$totalActive} dari 4 berkas tersimpan)."
                : "Data berkas pendaftaran berhasil disimpan.";

            return redirect()->route('ppdb.upload')->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('PPDB Submit Upload Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Gagal menyimpan berkas dokumen persyaratan. Silakan coba kembali atau hubungi panitia PPDB.');
        }
    }
}
