<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFastApi;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PpdbController extends Controller
{
    use InteractsWithFastApi;

    /**
     * Upload payment proof file to FastAPI.
     */
    public function uploadPayment(Request $request, TelegramNotificationService $telegramService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'file.required' => 'Silakan pilih berkas bukti transfer.',
            'file.mimes' => 'Format berkas harus berupa JPG, JPEG, PNG, WEBP, atau PDF.',
            'file.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        try {
            $file = $request->file('file');

            $response = $this->httpWithToken()
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($this->backendUrl() . '/api/ppdb/upload-payment');

            if ($response->successful()) {
                // Kirim notifikasi ke Grup Telegram Panitia PPDB (Fail-safe)
                try {
                    $paymentData = $response->json();
                    $registrationId = $paymentData['id'] ?? null;

                    $studentData = [
                        'full_name' => session('full_name', 'Calon Siswa'),
                        'nik' => session('nik', '-'),
                        'email' => session('email', '-'),
                    ];

                    $telegramService->sendPaymentProofNotification(
                        $file,
                        $studentData,
                        $registrationId
                    );
                } catch (\Throwable $telegramException) {
                    Log::warning('Notifikasi Telegram PPDB gagal dikirim: ' . $telegramException->getMessage());
                }

                return redirect()->route('dashboard')->with('success', 'Bukti pembayaran berhasil diunggah! Mohon menunggu verifikasi oleh panitia PPDB.');
            }

            $errorMessage = $this->extractErrorMessage($response, 'Gagal mengunggah bukti pembayaran.');
            return redirect()->route('dashboard')->with('error', $errorMessage);

        } catch (\Exception $e) {
            Log::error('PPDB Payment Upload Error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Terjadi kendala saat mengunggah berkas: ' . $e->getMessage());
        }
    }

    /**
     * Show multi-step registration form.
     */
    public function showForm(Request $request)
    {
        try {
            $regResponse = $this->httpWithToken()->get($this->backendUrl() . '/api/ppdb/my-registration');

            if ($regResponse->status() === 401) {
                $request->session()->flush();
                return redirect()->route('login')->with('warning', 'Sesi login telah berakhir.');
            }

            if (!$regResponse->successful()) {
                return redirect()->route('dashboard')->with('error', 'Gagal memverifikasi status pendaftaran.');
            }

            $registration = $regResponse->json();
            $paymentStatus = $registration['payment_status'] ?? 'unpaid';
            $registrationStatus = $registration['registration_status'] ?? 'pending';

            // Locking Check: If not paid, redirect to dashboard
            if ($paymentStatus !== 'paid') {
                return redirect()->route('dashboard')->with('warning', 'Menu formulir pendaftaran masih terkunci. Pembayaran Anda harus diverifikasi (Status: PAID) terlebih dahulu oleh panitia.');
            }

            // Existing form data if already saved
            $formData = $registration['form_data'] ?? [];

            // Pre-fill basic details from account/session if not yet populated
            if (empty($formData['nik'])) {
                $formData['nik'] = session('nik');
            }
            if (empty($formData['full_name'])) {
                $formData['full_name'] = session('full_name');
            }
            if (empty($formData['contact']['email'])) {
                $formData['contact']['email'] = session('email');
            }

            $isLocked = ($registrationStatus === 'accepted');

            return view('ppdb.form', [
                'registration' => $registration,
                'formData' => $formData,
                'isLocked' => $isLocked,
            ]);

        } catch (\Exception $e) {
            Log::error('PPDB Show Form Error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Koneksi ke backend bermasalah: ' . $e->getMessage());
        }
    }

    /**
     * Submit multi-step registration form as a Nested JSON payload.
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

            // Tahap 5: Data Ayah Kandung (Relationship Type 1)
            'father_nik' => ['required', 'string', 'digits:16'],
            'father_name' => ['required', 'string', 'max:150'],
            'father_birth_year' => ['required', 'digits:4'],
            'father_education' => ['required', 'string'],
            'father_occupation' => ['required', 'string'],
            'father_income' => ['required', 'string'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'father_whatsapp' => ['nullable', 'string', 'max:20'],
            'father_email' => ['nullable', 'email', 'max:150'],

            // Data Ibu Kandung (Relationship Type 2)
            'mother_nik' => ['required', 'string', 'digits:16'],
            'mother_name' => ['required', 'string', 'max:150'],
            'mother_birth_year' => ['required', 'digits:4'],
            'mother_education' => ['required', 'string'],
            'mother_occupation' => ['required', 'string'],
            'mother_income' => ['required', 'string'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
            'mother_whatsapp' => ['nullable', 'string', 'max:20'],
            'mother_email' => ['nullable', 'email', 'max:150'],

            // Data Wali (Optional - Relationship Type 3)
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
            'major.required' => 'Silakan pilih jurusan yang diminati (Reguler, Bahasa, Tahfidz, atau ICT).',
            'major.in' => 'Pilihan jurusan tidak valid.',
            'school_origin.required' => 'Nama asal sekolah wajib diisi.',
            'school_origin_address.required' => 'Alamat sekolah asal wajib diisi.',
            'nik.digits' => 'NIK Siswa harus 16 digit.',
            'nisn.digits' => 'NISN harus 10 digit angka.',
            'family_card_number.digits' => 'Nomor Kartu Keluarga (KK) harus 16 digit.',
            'birth_order.required' => 'Urutan anak ke-berapa wajib diisi.',
            'birth_order.integer' => 'Urutan anak harus berupa angka.',
            'birth_order.min' => 'Urutan anak minimal bernilai 1.',
            'siblings_count.required' => 'Jumlah bersaudara wajib diisi.',
            'siblings_count.integer' => 'Jumlah bersaudara harus berupa angka.',
            'siblings_count.min' => 'Jumlah bersaudara minimal bernilai 1.',
            'father_nik.digits' => 'NIK Ayah harus 16 digit.',
            'mother_nik.digits' => 'NIK Ibu harus 16 digit.',
            'guardian_nik.digits' => 'NIK Wali harus 16 digit.',
        ]);

        // 2. Merakit student_parents
        $studentParents = [
            [
                'relationship_type' => 1, // Ayah Kandung
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
                'relationship_type' => 2, // Ibu Kandung
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

        // Jika wali dicentang
        if ($request->boolean('has_guardian') && !empty($request->guardian_name)) {
            $studentParents[] = [
                'relationship_type' => 3, // Wali
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

        // 3. Merakit Nested JSON 100% Identik dengan Skema SIAKAD
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
            $response = $this->httpWithToken()
                ->put($this->backendUrl() . '/api/ppdb/registration-form', $nestedPayload);

            if ($response->successful()) {
                return redirect()->route('dashboard')->with('success', 'Formulir pendaftaran berhasil disimpan dan diperbarui!');
            }

            $errorMessage = $this->extractErrorMessage($response, 'Gagal menyimpan formulir pendaftaran.');
            return back()->withInput()->with('error', $errorMessage);

        } catch (\Exception $e) {
            Log::error('PPDB Form Submit Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat mengirim formulir ke backend: ' . $e->getMessage());
        }
    }
}
