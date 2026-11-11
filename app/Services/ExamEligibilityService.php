<?php

namespace App\Services;

use App\Models\PpdbRegistration;

class ExamEligibilityService
{
    public function missingRequirements(?PpdbRegistration $registration): array
    {
        if (!$registration) {
            return ['Data pendaftaran belum ditemukan.'];
        }

        $missing = [];
        $formData = is_array($registration->form_data) ? $registration->form_data : [];
        $identity = is_array($formData['identity'] ?? null) ? $formData['identity'] : [];
        $address = is_array($formData['address'] ?? null) ? $formData['address'] : [];
        $contact = is_array($formData['contact'] ?? null) ? $formData['contact'] : [];
        $required = [
            'NIK' => $formData['nik'] ?? null,
            'NISN' => $formData['nisn'] ?? null,
            'Nama siswa' => $formData['full_name'] ?? null,
            'Nama depan' => $formData['first_name'] ?? null,
            'Jurusan' => in_array($formData['major'] ?? null, ['reguler', 'bahasa', 'tahfidz', 'ict'], true),
            'Asal sekolah' => $formData['school_origin'] ?? null,
            'Alamat sekolah asal' => $formData['school_origin_address'] ?? null,
            'Nomor Kartu Keluarga' => $identity['family_card_number'] ?? null,
            'Jenis kelamin' => $identity['gender'] ?? null,
            'Agama' => $identity['religion'] ?? null,
            'Tempat lahir' => $identity['place_of_birth'] ?? null,
            'Tanggal lahir' => $identity['date_of_birth'] ?? null,
            'Urutan anak' => $identity['birth_order'] ?? null,
            'Jumlah saudara' => $identity['siblings_count'] ?? null,
            'Alamat jalan' => $address['street_address'] ?? null,
            'RT' => $address['rt'] ?? null,
            'RW' => $address['rw'] ?? null,
            'Kelurahan/desa' => $address['village'] ?? null,
            'Kecamatan' => $address['district'] ?? null,
            'Kode pos' => $address['postal_code'] ?? null,
            'Jenis tempat tinggal' => $address['residence_type'] ?? null,
            'Transportasi' => $address['transportation_mode'] ?? null,
            'Nomor HP siswa' => $contact['mobile_number'] ?? null,
            'Nomor WhatsApp siswa' => $contact['whatsapp_number'] ?? null,
            'Email siswa' => $contact['email'] ?? null,
        ];

        foreach ($required as $label => $value) {
            if (empty($value)) {
                $missing[] = $label;
            }
        }

        $parents = [];
        $studentParents = $formData['student_parents'] ?? [];
        foreach (is_array($studentParents) ? $studentParents : [] as $studentParent) {
            if (is_array($studentParent)) {
                $relationship = $studentParent['relationship_type'] ?? null;
                $parent = $studentParent['parent'] ?? [];
                if (in_array($relationship, [1, 2, 3, '1', '2', '3'], true) && is_array($parent)) {
                    $parents[(int) $relationship] = $parent;
                }
            }
        }

        $parentStatuses = [];
        foreach ([1 => 'Ayah', 2 => 'Ibu'] as $relationship => $label) {
            $parent = $parents[$relationship] ?? [];
            $status = $parent['status'] ?? null;
            if (!$parent || !in_array($status, ['ada', 'meninggal', 'cerai', 'tidak_diketahui'], true)) {
                $missing[] = 'Data ' . strtolower($label);
                continue;
            }
            $parentStatuses[] = $status;
            if (empty($parent['full_name'])) {
                $missing[] = 'Nama ' . strtolower($label);
            }
            if ($status === 'ada') {
                foreach ([
                    'nik' => 'NIK',
                    'birth_year' => 'tahun lahir',
                    'education_code' => 'pendidikan',
                    'occupation_code' => 'pekerjaan',
                    'income_code' => 'penghasilan',
                ] as $key => $fieldLabel) {
                    if (empty($parent[$key])) {
                        $missing[] = $fieldLabel . ' ' . strtolower($label);
                    }
                }
            }
        }

        if (count($parentStatuses) === 2 && !in_array('ada', $parentStatuses, true)) {
            $guardian = $parents[3] ?? [];
            foreach ([
                'nik' => 'NIK wali',
                'full_name' => 'Nama wali',
                'birth_year' => 'Tahun lahir wali',
                'education_code' => 'Pendidikan wali',
                'occupation_code' => 'Pekerjaan wali',
                'income_code' => 'Penghasilan wali',
            ] as $key => $label) {
                if (empty($guardian[$key])) {
                    $missing[] = $label;
                }
            }
        }

        $documents = ppdb_get_student_documents($registration);
        foreach (['kk' => 'Kartu Keluarga', 'akta' => 'Akta Kelahiran', 'nisn' => 'Bukti NISN', 'foto' => 'Pas foto'] as $key => $label) {
            if (empty($documents['items'][$key]['has_file'])) {
                $missing[] = $label;
            }
        }

        if ($registration->payment_status !== 'paid') {
            $missing[] = 'Pembayaran terverifikasi';
        }

        return $missing;
    }
}
