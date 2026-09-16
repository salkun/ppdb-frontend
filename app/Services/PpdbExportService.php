<?php

namespace App\Services;

use ZipArchive;

class PpdbExportService
{
    // Kamus Kode Pendidikan (Standar Kemendikbud / Dapodik)
    protected array $educationMap = [
        '01' => 'Tidak Sekolah',
        '02' => 'SD / Sederajat',
        '03' => 'SMP / Sederajat',
        '04' => 'SMA / SMK / Sederajat',
        '05' => 'D1 / D2 / D3',
        '06' => 'D4 / S1',
        '07' => 'S2',
        '08' => 'S3',
    ];

    // Kamus Kode Pekerjaan
    protected array $occupationMap = [
        '01' => 'Tidak Bekerja',
        '02' => 'PNS / TNI / Polri',
        '03' => 'Karyawan Swasta',
        '04' => 'Wiraswasta / Pedagang',
        '05' => 'Petani / Peternak / Nelayan',
        '06' => 'Buruh / Pekerja Lepas',
        '07' => 'Pensiunan',
        '08' => 'Lainnya',
    ];

    // Kamus Kode Penghasilan
    protected array $incomeMap = [
        '01' => 'Kurang dari Rp 1.000.000',
        '02' => 'Rp 1.000.000 - Rp 2.000.000',
        '03' => 'Rp 2.000.000 - Rp 5.000.000',
        '04' => 'Rp 5.000.000 - Rp 10.000.000',
        '05' => 'Lebih dari Rp 10.000.000',
        '06' => 'Tidak Berpenghasilan',
    ];

    /**
     * Definisi Header Kolom Dokumen Ekspor.
     */
    public function getHeaders(): array
    {
        return [
            'No',
            'ID Registrasi',
            'Waktu Pendaftaran',
            'Nama Lengkap Siswa',
            'NIK Siswa',
            'NISN',
            'No. Kartu Keluarga',
            'Pilihan Jurusan',
            'Nama Asal Sekolah',
            'Alamat Sekolah Asal',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Anak Ke',
            'Jml Saudara',
            'Alamat Jalan',
            'RT',
            'RW',
            'Kelurahan / Desa',
            'Kecamatan',
            'Kode Pos',
            'Tempat Tinggal',
            'Moda Transportasi',
            'No. Telepon / WA',
            'Email Siswa',
            'Nama Ayah',
            'NIK Ayah',
            'Pendidikan Ayah',
            'Pekerjaan Ayah',
            'Penghasilan Ayah',
            'No. HP Ayah',
            'Nama Ibu',
            'NIK Ibu',
            'Pendidikan Ibu',
            'Pekerjaan Ibu',
            'Penghasilan Ibu',
            'No. HP Ibu',
            'Nama Wali',
            'No. HP Wali',
            'Status Pembayaran',
            'Nominal Bayar (Rp)',
            'Status Seleksi',
            'Status Formulir',
        ];
    }

    /**
     * Mentransformasi data registrasi PPDB mentah menjadi deretan baris terstruktur.
     */
    public function formatRegistrations(array $registrations): array
    {
        $rows = [];
        $no = 1;

        foreach ($registrations as $reg) {
            $formData = $reg['form_data'] ?? [];
            $account = $reg['account'] ?? [];
            $identity = $formData['identity'] ?? [];
            $address = $formData['address'] ?? [];
            $contact = $formData['contact'] ?? [];

            // Ekstraksi data Orang Tua / Wali
            $father = null;
            $mother = null;
            $guardian = null;
            $rawParents = $formData['student_parents'] ?? ($formData['parents'] ?? []);
            if (!empty($rawParents) && is_array($rawParents)) {
                foreach ($rawParents as $sp) {
                    $relType = (int) ($sp['relationship_type'] ?? 0);
                    $pObj = $sp['parent'] ?? $sp;
                    if ($relType === 1) $father = $pObj;
                    elseif ($relType === 2) $mother = $pObj;
                    elseif ($relType === 3) $guardian = $pObj;
                }
            }

            // Status format human-readable
            $pStatus = $reg['payment_status'] ?? 'unpaid';
            $pStatusLabel = match ($pStatus) {
                'paid' => 'Lunas',
                'pending_verification' => 'Menunggu Verifikasi',
                'rejected' => 'Ditolak',
                default => 'Belum Bayar',
            };

            $rStatus = $reg['registration_status'] ?? 'pending';
            $rStatusLabel = match ($rStatus) {
                'accepted' => 'Resmi Diterima',
                'rejected' => 'Tidak Lolos',
                default => 'Proses Seleksi',
            };

            $formStatusLabel = !empty($formData) ? 'Lengkap' : 'Belum Diisi';

            // Nama & NIK fallback
            $fullName = $formData['full_name'] ?? ($account['full_name'] ?? 'Calon Siswa');
            $nik = $formData['nik'] ?? ($account['nik'] ?? '-');
            $email = $contact['email'] ?? ($account['email'] ?? '-');
            $mobile = $contact['mobile_number'] ?? ($contact['whatsapp_number'] ?? ($contact['phone_number'] ?? '-'));

            $rawMajor = $formData['major'] ?? ($reg['student']['major'] ?? '-');
            $majorLabel = match(strtolower((string)$rawMajor)) {
                'reguler' => 'REGULER',
                'bahasa' => 'BAHASA',
                'tahfidz' => 'TAHFIDZ',
                'ict' => 'ICT',
                default => !empty($rawMajor) && $rawMajor !== '-' ? strtoupper($rawMajor) : '-'
            };
            $schoolOrigin = $formData['school_origin'] ?? ($reg['student']['school_origin'] ?? '-');
            $schoolOriginAddress = $formData['school_origin_address'] ?? ($reg['student']['school_origin_address'] ?? '-');

            $rows[] = [
                $no++,
                $reg['id'] ?? '-',
                !empty($reg['created_at']) ? date('d/m/Y H:i', strtotime($reg['created_at'])) : '-',
                $fullName,
                $nik,
                $formData['nisn'] ?? '-',
                $identity['family_card_number'] ?? '-',
                $majorLabel,
                $schoolOrigin,
                $schoolOriginAddress,
                $identity['gender'] ?? '-',
                $identity['place_of_birth'] ?? '-',
                $identity['date_of_birth'] ?? '-',
                $identity['religion'] ?? '-',
                isset($identity['birth_order']) ? (string) $identity['birth_order'] : '-',
                isset($identity['siblings_count']) ? (string) $identity['siblings_count'] : '-',
                $address['street_address'] ?? '-',
                $address['rt'] ?? '-',
                $address['rw'] ?? '-',
                $address['village'] ?? '-',
                $address['district'] ?? '-',
                $address['postal_code'] ?? '-',
                $address['residence_type'] ?? '-',
                $address['transportation_mode'] ?? '-',
                $mobile,
                $email,
                // Data Ayah
                $father['full_name'] ?? '-',
                $father['nik'] ?? '-',
                $this->educationMap[$father['education_code'] ?? ''] ?? ($father['education_code'] ?? '-'),
                $this->occupationMap[$father['occupation_code'] ?? ''] ?? ($father['occupation_code'] ?? '-'),
                $this->incomeMap[$father['income_code'] ?? ''] ?? ($father['income_code'] ?? '-'),
                $father['phone_number'] ?? ($father['whatsapp_number'] ?? '-'),
                // Data Ibu
                $mother['full_name'] ?? '-',
                $mother['nik'] ?? '-',
                $this->educationMap[$mother['education_code'] ?? ''] ?? ($mother['education_code'] ?? '-'),
                $this->occupationMap[$mother['occupation_code'] ?? ''] ?? ($mother['occupation_code'] ?? '-'),
                $this->incomeMap[$mother['income_code'] ?? ''] ?? ($mother['income_code'] ?? '-'),
                $mother['phone_number'] ?? ($mother['whatsapp_number'] ?? '-'),
                // Data Wali
                $guardian['full_name'] ?? '-',
                $guardian['phone_number'] ?? ($guardian['whatsapp_number'] ?? '-'),
                // Status PPDB
                $pStatusLabel,
                number_format((float) ($reg['payment_amount'] ?? 0), 0, ',', '.'),
                $rStatusLabel,
                $formStatusLabel,
            ];
        }

        return $rows;
    }

    /**
     * Menghasilkan file Microsoft Excel (.xlsx) OpenXML murni tanpa dependensi Composer eksternal.
     * Mengembalikan path ke file sementara (temporary file).
     */
    public function generateXlsx(array $registrations, ?string $reportTitle = null): string
    {
        $headers = $this->getHeaders();
        $dataRows = $this->formatRegistrations($registrations);
        $title = $reportTitle ?: 'DATA PENDAFTAR PESERTA DIDIK BARU (PPDB ONLINE)';
        $subTitle = 'Diekspor pada: ' . now()->translatedFormat('d F Y, H:i') . ' WIB | Total: ' . count($dataRows) . ' Calon Siswa';

        $tempFile = tempnam(sys_get_temp_dir(), 'ppdb_xlsx_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat berkas sementara Excel (.xlsx)');
        }

        // 1. [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

        // 2. _rels/.rels
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        // 3. xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');

        // 4. xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Data Calon Siswa PPDB" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

        // 5. xl/styles.xml
        // Style 0: Normal Font Calibri 10pt, border tipis
        // Style 1: Header Table (Bold 10pt putih, fill Navy #1E293B, centered)
        // Style 2: Title Judul (Bold 14pt, Navy)
        // Style 3: Subtitle (Italic 9pt, Gray)
        // Style 4: Center text row
        // Style 5: Right aligned (nominal/angka)
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="4">
    <font><name val="Calibri"/><sz val="10"/></font>
    <font><b/><name val="Calibri"/><sz val="10"/><color rgb="FFFFFFFF"/></font>
    <font><b/><name val="Calibri"/><sz val="14"/><color rgb="FF0F172A"/></font>
    <font><i/><name val="Calibri"/><sz val="9"/><color rgb="FF64748B"/></font>
  </fonts>
  <fills count="4">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFE2E8F0"/></left>
      <right style="thin"><color rgb="FFE2E8F0"/></right>
      <top style="thin"><color rgb="FFE2E8F0"/></top>
      <bottom style="thin"><color rgb="FFE2E8F0"/></bottom>
      <diagonal/>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="6">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>
    <xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>
  </cellXfs>
</styleSheet>');

        // 6. xl/worksheets/sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";

        // Kolom width
        $sheetXml .= '  <cols>' . "\n";
        $sheetXml .= '    <col min="1" max="1" width="6" customWidth="1"/>' . "\n";   // No
        $sheetXml .= '    <col min="2" max="2" width="22" customWidth="1"/>' . "\n";  // ID Registrasi
        $sheetXml .= '    <col min="3" max="3" width="18" customWidth="1"/>' . "\n";  // Waktu Daftar
        $sheetXml .= '    <col min="4" max="4" width="28" customWidth="1"/>' . "\n";  // Nama Lengkap
        $sheetXml .= '    <col min="5" max="5" width="20" customWidth="1"/>' . "\n";  // NIK
        $sheetXml .= '    <col min="6" max="6" width="16" customWidth="1"/>' . "\n";  // NISN
        $sheetXml .= '    <col min="7" max="7" width="20" customWidth="1"/>' . "\n";  // No KK
        $sheetXml .= '    <col min="8" max="8" width="16" customWidth="1"/>' . "\n";  // Jurusan
        $sheetXml .= '    <col min="9" max="9" width="28" customWidth="1"/>' . "\n";  // Asal Sekolah
        $sheetXml .= '    <col min="10" max="10" width="30" customWidth="1"/>' . "\n"; // Alamat Sekolah Asal
        $sheetXml .= '    <col min="11" max="11" width="14" customWidth="1"/>' . "\n"; // Jenis Kelamin
        $sheetXml .= '    <col min="12" max="12" width="18" customWidth="1"/>' . "\n"; // Tempat Lahir
        $sheetXml .= '    <col min="13" max="13" width="14" customWidth="1"/>' . "\n"; // Tanggal Lahir
        $sheetXml .= '    <col min="14" max="14" width="12" customWidth="1"/>' . "\n"; // Agama
        $sheetXml .= '    <col min="15" max="16" width="12" customWidth="1"/>' . "\n"; // Anak ke / Jml saudara
        $sheetXml .= '    <col min="17" max="17" width="30" customWidth="1"/>' . "\n"; // Alamat Jalan
        $sheetXml .= '    <col min="18" max="19" width="8" customWidth="1"/>' . "\n";  // RT / RW
        $sheetXml .= '    <col min="20" max="21" width="20" customWidth="1"/>' . "\n"; // Desa / Kec
        $sheetXml .= '    <col min="22" max="22" width="10" customWidth="1"/>' . "\n"; // Kode Pos
        $sheetXml .= '    <col min="23" max="24" width="20" customWidth="1"/>' . "\n"; // Tempat Tinggal / Moda
        $sheetXml .= '    <col min="25" max="25" width="18" customWidth="1"/>' . "\n"; // No Telp
        $sheetXml .= '    <col min="26" max="26" width="26" customWidth="1"/>' . "\n"; // Email
        $sheetXml .= '    <col min="27" max="27" width="24" customWidth="1"/>' . "\n"; // Nama Ayah
        $sheetXml .= '    <col min="28" max="28" width="20" customWidth="1"/>' . "\n"; // NIK Ayah
        $sheetXml .= '    <col min="29" max="31" width="22" customWidth="1"/>' . "\n"; // Pddk/Pkj/Pgh Ayah
        $sheetXml .= '    <col min="32" max="32" width="18" customWidth="1"/>' . "\n"; // HP Ayah
        $sheetXml .= '    <col min="33" max="33" width="24" customWidth="1"/>' . "\n"; // Nama Ibu
        $sheetXml .= '    <col min="34" max="34" width="20" customWidth="1"/>' . "\n"; // NIK Ibu
        $sheetXml .= '    <col min="35" max="37" width="22" customWidth="1"/>' . "\n"; // Pddk/Pkj/Pgh Ibu
        $sheetXml .= '    <col min="38" max="38" width="18" customWidth="1"/>' . "\n"; // HP Ibu
        $sheetXml .= '    <col min="39" max="40" width="22" customWidth="1"/>' . "\n"; // Wali
        $sheetXml .= '    <col min="41" max="44" width="20" customWidth="1"/>' . "\n"; // Status
        $sheetXml .= '  </cols>' . "\n";

        $sheetXml .= '  <sheetData>' . "\n";

        // Baris 1: Judul Laporan
        $sheetXml .= '    <row r="1" ht="28" customHeight="1">' . "\n";
        $sheetXml .= '      <c r="A1" s="2" t="inlineStr"><is><t>' . $this->escapeXml($title) . '</t></is></c>' . "\n";
        $sheetXml .= '    </row>' . "\n";

        // Baris 2: Subtitle
        $sheetXml .= '    <row r="2" ht="20" customHeight="1">' . "\n";
        $sheetXml .= '      <c r="A2" s="3" t="inlineStr"><is><t>' . $this->escapeXml($subTitle) . '</t></is></c>' . "\n";
        $sheetXml .= '    </row>' . "\n";

        // Baris 3: Spacer
        $sheetXml .= '    <row r="3" ht="8" customHeight="1"/>' . "\n";

        // Baris 4: Header Tabel
        $headerRowIndex = 4;
        $sheetXml .= '    <row r="' . $headerRowIndex . '" ht="26" customHeight="1">' . "\n";
        foreach ($headers as $colIdx => $headerTitle) {
            $colLetter = $this->getColumnLetter($colIdx + 1);
            $sheetXml .= '      <c r="' . $colLetter . $headerRowIndex . '" s="1" t="inlineStr"><is><t>' . $this->escapeXml($headerTitle) . '</t></is></c>' . "\n";
        }
        $sheetXml .= '    </row>' . "\n";

        // Baris Data
        $currentRow = 5;
        foreach ($dataRows as $rowData) {
            $sheetXml .= '    <row r="' . $currentRow . '" ht="20" customHeight="1">' . "\n";
            foreach ($rowData as $colIdx => $cellValue) {
                $colLetter = $this->getColumnLetter($colIdx + 1);

                // No urut atau nilai center
                $styleId = 0;
                if ($colIdx === 0 || in_array($colIdx, [2, 7, 10, 12, 13, 14, 15, 17, 18, 21, 40, 42, 43])) {
                    $styleId = 4; // center
                } elseif ($colIdx === 41) {
                    $styleId = 5; // right aligned (nominal bayar)
                }

                // Simpan selalu sebagai inlineStr agar NIK, NISN, nomor telepon utuh 100%
                $sheetXml .= '      <c r="' . $colLetter . $currentRow . '" s="' . $styleId . '" t="inlineStr"><is><t>' . $this->escapeXml((string) $cellValue) . '</t></is></c>' . "\n";
            }
            $sheetXml .= '    </row>' . "\n";
            $currentRow++;
        }

        $sheetXml .= '  </sheetData>' . "\n";
        $sheetXml .= '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return $tempFile;
    }

    /**
     * Menghasilkan string CSV dengan encoding UTF-8 BOM untuk kompatibilitas penuh Microsoft Excel.
     */
    public function generateCsv(array $registrations): string
    {
        $headers = $this->getHeaders();
        $dataRows = $this->formatRegistrations($registrations);

        $fp = fopen('php://memory', 'r+');

        // Tambahkan UTF-8 BOM (Byte Order Mark) agar Excel otomatis mendeteksi encoding UTF-8
        fwrite($fp, "\xEF\xBB\xBF");

        // Baris Header
        fputcsv($fp, $headers);

        // Baris Data (Tambahkan tanda petik khusus untuk angka panjang agar Excel tidak mengonversi ke scientific notation)
        foreach ($dataRows as $row) {
            // NIK Siswa (indeks 4), NISN (indeks 5), KK (indeks 6), HP (indeks 21), NIK Ayah (indeks 24), dsb
            $formattedRow = array_map(function ($val) {
                $strVal = (string) $val;
                // Jika numeric 10 digit atau lebih, format dengan petik pembuka agar Excel memperlakukan sebagai teks
                if (is_numeric($strVal) && strlen($strVal) >= 10) {
                    return "'" . $strVal;
                }
                return $strVal;
            }, $row);

            fputcsv($fp, $formattedRow);
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        return $csvContent;
    }

    /**
     * Mengubah indeks kolom berbasis 1 menjadi huruf alfabet Excel (1 -> A, 27 -> AA, dst).
     */
    protected function getColumnLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = (int) (($colIndex - $mod) / 26);
        }
        return $letter;
    }

    /**
     * Sanitasi karakter untuk sintaks XML.
     */
    protected function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
