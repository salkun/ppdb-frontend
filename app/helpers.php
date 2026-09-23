<?php

if (!function_exists('ppdb_proof_url')) {
    /**
     * Generate the correct public URL for payment proof images.
     * Cek keberadaan file di penyimpanan lokal Laravel sebelum fallback ke API remote.
     *
     * @param string|null $path
     * @param string|null $backendUrl
     * @return string
     */
    function ppdb_proof_url(?string $path, ?string $backendUrl = null): string
    {
        if (empty($path)) {
            return '';
        }

        // Jika sudah berupa URL absolut (http/https)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        // 1. Cek apakah berkas ada di direktori public/ Laravel lokal atau Document Root cPanel
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }
        if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists(rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/' . $cleanPath)) {
            return asset($cleanPath);
        }

        // 2. Cek apakah berkas ada di public/uploads/ppdb_payments/ atau public/uploads/ppdb_documents/ berdasarkan nama berkas
        $filename = basename($cleanPath);
        if (!empty($filename)) {
            if (file_exists(public_path('uploads/ppdb_payments/' . $filename)) || 
                (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists(rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/uploads/ppdb_payments/' . $filename))) {
                return asset('uploads/ppdb_payments/' . $filename);
            }
            if (file_exists(public_path('uploads/ppdb_documents/' . $filename)) ||
                (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists(rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/uploads/ppdb_documents/' . $filename))) {
                return asset('uploads/ppdb_documents/' . $filename);
            }
        }

        // 3. Cek apakah berkas ada di storage/app/public/
        if (file_exists(storage_path('app/public/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        // 4. Jika ini adalah path uploads lokal, SELALU gunakan asset() karena Laravel memiliki fallback route /uploads/{folder}/{filename}
        if (str_starts_with($cleanPath, 'uploads/')) {
            return asset($cleanPath);
        }

        // 5. Fallback ke remote backend URL jika ada (khusus backend resmi non-localhost)
        $baseBackend = $backendUrl ?: config('ppdb.api_url');
        if (!empty($baseBackend) && !str_contains($baseBackend, '127.0.0.1') && !str_contains($baseBackend, 'localhost')) {
            return rtrim($baseBackend, '/') . '/' . $cleanPath;
        }

        return asset($cleanPath);
    }
}

if (!function_exists('ppdb_doc_url')) {
    /**
     * Generate the correct public URL for student uploaded documents (KK, Akta, NISN, Foto).
     *
     * @param string|null $path
     * @param string|null $backendUrl
     * @return string
     */
    function ppdb_doc_url(?string $path, ?string $backendUrl = null): string
    {
        return ppdb_proof_url($path, $backendUrl);
    }
}

if (!function_exists('ppdb_get_student_documents')) {
    /**
     * Helper terpadu untuk mengambil data dan status berkas dokumen persyaratan calon siswa.
     *
     * @param array|\App\Models\PpdbRegistration $reg
     * @param string|null $backendUrl
     * @return array
     */
    function ppdb_get_student_documents($reg, ?string $backendUrl = null): array
    {
        $formData = is_array($reg) ? ($reg['form_data'] ?? []) : ($reg->form_data ?? []);
        if (!is_array($formData)) {
            $formData = json_decode((string)$formData, true) ?: [];
        }
        $docsData = $formData['documents'] ?? [];

        $kk = $docsData['kk'] ?? ($formData['kk_path'] ?? null);
        $akta = $docsData['akta'] ?? ($formData['birth_cert_path'] ?? null);
        $nisn = $docsData['nisn'] ?? ($formData['nisn_path'] ?? null);
        $foto = $docsData['foto'] ?? ($formData['photo_path'] ?? null);
        $paymentProof = is_array($reg) ? ($reg['payment_proof_path'] ?? null) : ($reg->payment_proof_path ?? null);
        $payMethod = is_array($reg) ? ($reg['payment_method'] ?? 'transfer') : ($reg->payment_method ?? 'transfer');

        $extKk = $kk ? strtolower(pathinfo($kk, PATHINFO_EXTENSION)) : null;
        $extAkta = $akta ? strtolower(pathinfo($akta, PATHINFO_EXTENSION)) : null;
        $extNisn = $nisn ? strtolower(pathinfo($nisn, PATHINFO_EXTENSION)) : null;
        $extFoto = $foto ? strtolower(pathinfo($foto, PATHINFO_EXTENSION)) : null;
        $extProof = $paymentProof ? strtolower(pathinfo($paymentProof, PATHINFO_EXTENSION)) : null;

        $items = [
            'kk' => [
                'code' => 'kk',
                'label' => 'Kartu Keluarga (KK)',
                'path' => $kk,
                'url' => ppdb_doc_url($kk, $backendUrl),
                'has_file' => !empty($kk),
                'ext' => $extKk,
                'is_image' => in_array($extKk, ['jpg', 'jpeg', 'png', 'webp']),
            ],
            'akta' => [
                'code' => 'akta',
                'label' => 'Akta Kelahiran',
                'path' => $akta,
                'url' => ppdb_doc_url($akta, $backendUrl),
                'has_file' => !empty($akta),
                'ext' => $extAkta,
                'is_image' => in_array($extAkta, ['jpg', 'jpeg', 'png', 'webp']),
            ],
            'nisn' => [
                'code' => 'nisn',
                'label' => 'Kartu / Bukti NISN',
                'path' => $nisn,
                'url' => ppdb_doc_url($nisn, $backendUrl),
                'has_file' => !empty($nisn),
                'ext' => $extNisn,
                'is_image' => in_array($extNisn, ['jpg', 'jpeg', 'png', 'webp']),
            ],
            'foto' => [
                'code' => 'foto',
                'label' => 'Pas Foto 3x4',
                'path' => $foto,
                'url' => ppdb_doc_url($foto, $backendUrl),
                'has_file' => !empty($foto),
                'ext' => $extFoto,
                'is_image' => in_array($extFoto, ['jpg', 'jpeg', 'png', 'webp']),
            ],
            'bukti_bayar' => [
                'code' => 'bukti_bayar',
                'label' => ($payMethod === 'cash') ? 'Kuitansi Pembayaran Tunai (Cash)' : 'Bukti Transfer Pembayaran (TF)',
                'path' => $paymentProof,
                'url' => ppdb_proof_url($paymentProof, $backendUrl),
                'has_file' => !empty($paymentProof),
                'ext' => $extProof,
                'is_image' => in_array($extProof, ['jpg', 'jpeg', 'png', 'webp']),
                'payment_method' => $payMethod,
            ],
        ];

        $uploadedCount = (!empty($kk) ? 1 : 0) + (!empty($akta) ? 1 : 0) + (!empty($nisn) ? 1 : 0) + (!empty($foto) ? 1 : 0);

        return [
            'items' => $items,
            'uploaded_count' => $uploadedCount,
            'total_required' => 4,
            'is_complete' => ($uploadedCount >= 4),
            'has_payment_proof' => !empty($paymentProof),
        ];
    }
}

