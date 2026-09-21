<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use ZipArchive;
use SimpleXMLElement;
use RuntimeException;

class PpdbImportService
{
    /**
     * Memproses file upload (Excel .xlsx / .xls atau CSV) dan mengembalikan array data baris terstandarisasi.
     *
     * @param UploadedFile|string $file
     * @return array
     */
    public function parseFile($file): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $extension = strtolower($file instanceof UploadedFile ? $file->getClientOriginalExtension() : pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'csv' || $extension === 'txt') {
            return $this->parseCsv($path);
        }

        if ($extension === 'xlsx' || $extension === 'xls') {
            return $this->parseXlsx($path);
        }

        throw new RuntimeException("Format berkas .{$extension} tidak didukung. Harap unggah file Excel (.xlsx) atau CSV (.csv).");
    }

    /**
     * Parsing file CSV dengan auto-detect delimiter (, atau ;) dan strip BOM UTF-8.
     */
    public function parseCsv(string $path): array
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new RuntimeException("Berkas CSV tidak dapat dibaca di sistem.");
        }

        $content = file_get_contents($path);
        // Strip UTF-8 BOM jika ada
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (empty($lines)) {
            return [];
        }

        // Auto-detect delimiter
        $firstLine = $lines[0];
        $countSemicolon = substr_count($firstLine, ';');
        $countComma = substr_count($firstLine, ',');
        $countTab = substr_count($firstLine, "\t");

        $delimiter = ',';
        if ($countSemicolon > $countComma && $countSemicolon > $countTab) {
            $delimiter = ';';
        } elseif ($countTab > $countComma && $countTab > $countSemicolon) {
            $delimiter = "\t";
        }

        $rawRows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $rawRows[] = str_getcsv($line, $delimiter);
        }

        return $this->standardizeRows($rawRows);
    }

    /**
     * Parsing file Excel (.xlsx) OpenXML murni dengan ZipArchive & SimpleXML.
     */
    public function parseXlsx(string $path): array
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new RuntimeException("Berkas Excel (.xlsx) tidak ditemukan atau tidak dapat dibaca.");
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            // Fallback: coba parse sebagai CSV jika file sebenarnya adalah CSV yang dinamai xlsx
            try {
                return $this->parseCsv($path);
            } catch (\Exception $e) {
                throw new RuntimeException("Berkas Excel (.xlsx) rusak atau tidak valid.");
            }
        }

        // 1. Ekstrak Shared Strings Table (jika ada)
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $sstXml = @simplexml_load_string($sharedStringsXml);
            if ($sstXml && isset($sstXml->si)) {
                foreach ($sstXml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $str = '';
                        foreach ($si->r as $r) {
                            $str .= (string) $r->t;
                        }
                        $sharedStrings[] = $str;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Baca worksheet pertama (sheet1.xml)
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            // Coba cari nama sheet alternatif
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (str_starts_with($stat['name'], 'xl/worksheets/sheet') && str_ends_with($stat['name'], '.xml')) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException("Tidak ditemukan worksheet data di dalam berkas Excel.");
        }

        $xml = @simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData)) {
            return [];
        }

        $rawRows = [];
        foreach ($xml->sheetData->row as $row) {
            $rowValues = [];
            foreach ($row->c as $cell) {
                $coord = (string) $cell['r'];
                $colLetter = preg_replace('/[0-9]/', '', $coord);
                $colIndex = $this->columnLetterToIndex($colLetter);

                $cellType = (string) $cell['t'];
                $val = '';

                if ($cellType === 's') {
                    $sstIndex = (int) $cell->v;
                    $val = $sharedStrings[$sstIndex] ?? '';
                } elseif ($cellType === 'inlineStr') {
                    $val = (string) $cell->is->t;
                } elseif (isset($cell->v)) {
                    $val = (string) $cell->v;
                }

                $rowValues[$colIndex] = trim($val);
            }

            if (!empty($rowValues)) {
                // Susun array terurut berdasarkan indeks kolom
                $maxIndex = max(array_keys($rowValues));
                $normalizedRow = [];
                for ($i = 0; $i <= $maxIndex; $i++) {
                    $normalizedRow[$i] = $rowValues[$i] ?? '';
                }
                $rawRows[] = $normalizedRow;
            }
        }

        return $this->standardizeRows($rawRows);
    }

    /**
     * Mengubah nama huruf kolom Excel ('A', 'B', ... 'AA') menjadi indeks 0-based.
     */
    protected function columnLetterToIndex(string $col): int
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $index = 0;
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - 64);
        }
        return $index - 1;
    }

    /**
     * Memetakan raw 2D array baris menjadi associative array terstandarisasi.
     */
    public function standardizeRows(array $rawRows): array
    {
        if (empty($rawRows)) {
            return [];
        }

        // Temukan baris header
        $headerIndex = 0;
        $headerMap = [];

        foreach ($rawRows as $idx => $row) {
            $map = $this->detectHeaderMap($row);
            if (!empty($map['full_name']) || !empty($map['email']) || !empty($map['nik'])) {
                $headerIndex = $idx;
                $headerMap = $map;
                break;
            }
        }

        // Jika tidak terdeteksi header khusus, gunakan fallback posisi kolom default:
        // Kolom 0: NIK, Kolom 1: Full Name, Kolom 2: Email, Kolom 3: Password
        if (empty($headerMap)) {
            $headerMap = [
                'nik' => 0,
                'full_name' => 1,
                'email' => 2,
                'password' => 3,
                'phone' => 4,
            ];
            $headerIndex = -1; // Semua baris dianggap data
        }

        $standardized = [];
        $totalRows = count($rawRows);

        for ($i = $headerIndex + 1; $i < $totalRows; $i++) {
            $row = $rawRows[$i];
            
            // Lewati baris jika benar-benar kosong
            $allEmpty = true;
            foreach ($row as $val) {
                if (trim((string) $val) !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) continue;

            $nik = isset($headerMap['nik']) && isset($row[$headerMap['nik']]) ? trim((string) $row[$headerMap['nik']]) : '';
            $fullName = isset($headerMap['full_name']) && isset($row[$headerMap['full_name']]) ? trim((string) $row[$headerMap['full_name']]) : '';
            $email = isset($headerMap['email']) && isset($row[$headerMap['email']]) ? trim((string) $row[$headerMap['email']]) : '';
            $password = isset($headerMap['password']) && isset($row[$headerMap['password']]) ? trim((string) $row[$headerMap['password']]) : '';
            $phone = isset($headerMap['phone']) && isset($row[$headerMap['phone']]) ? trim((string) $row[$headerMap['phone']]) : '';

            // Bersihkan format NIK (jika ada petik atau float scientific notation)
            $nik = preg_replace('/[^0-9]/', '', $nik);

            $standardized[] = [
                'row_number' => $i + 1,
                'nik' => $nik,
                'full_name' => $fullName,
                'email' => $email,
                'password' => $password,
                'phone' => $phone,
            ];
        }

        return $standardized;
    }

    /**
     * Mendeteksi letak indeks kolom berdasarkan header teks.
     */
    protected function detectHeaderMap(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $colIdx => $title) {
            $clean = strtolower(trim((string) $title));
            $clean = str_replace(['_', '-', '.', ' '], '', $clean);

            if (in_array($clean, ['nik', 'nonik', 'nomornik', 'niksiswa', 'noktp', 'identitas'])) {
                $map['nik'] = $colIdx;
            } elseif (in_array($clean, ['nama', 'namalengkap', 'fullname', 'namasiswa', 'name', 'studentname'])) {
                $map['full_name'] = $colIdx;
            } elseif (in_array($clean, ['email', 'surel', 'alamatemail', 'mail', 'emailaddress'])) {
                $map['email'] = $colIdx;
            } elseif (in_array($clean, ['password', 'katasandi', 'pass', 'sandi', 'passwordpendaftar'])) {
                $map['password'] = $colIdx;
            } elseif (in_array($clean, ['nohp', 'telepon', 'whatsapp', 'wa', 'phone', 'kontak', 'nomorhp'])) {
                $map['phone'] = $colIdx;
            }
        }
        return $map;
    }

    /**
     * Menghasilkan teks CSV template import user.
     */
    public function generateTemplateCsv(): string
    {
        $rows = [
            ['NIK', 'Nama Lengkap', 'Email', 'Password', 'No. WhatsApp'],
            ['3201012345670001', 'Ahmad Fauzi Rahman', 'ahmad.fauzi@example.com', 'Siswa2026!', '081234567890'],
            ['3201012345670002', 'Nur Halimah Putri', 'nur.halimah@example.com', 'Siswa2026!', '081298765432'],
            ['3201012345670003', 'Rizky Dwi Pratama', 'rizky.pratama@example.com', 'Siswa2026!', '085712345678'],
        ];

        $output = fopen('php://temp', 'r+');
        // Masukkan UTF-8 BOM agar Excel membacanya dengan tepat
        fwrite($output, "\xEF\xBB\xBF");
        foreach ($rows as $row) {
            fputcsv($output, $row, ',');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Menghasilkan file Excel (.xlsx) OpenXML murni untuk template import user.
     * Mengembalikan path ke file sementara (temp file).
     */
    public function generateTemplateXlsx(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'ppdb_tpl_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat berkas sementara template Excel (.xlsx)');
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
    <sheet name="Template Import User" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

        // 5. xl/styles.xml
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><name val="Calibri"/><sz val="11"/></font>
    <font><b/><color rgb="FFFFFFFF"/><name val="Calibri"/><sz val="11"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF005AB4"/></patternFill></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
  </cellXfs>
</styleSheet>');

        // 6. xl/worksheets/sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <cols>
    <col min="1" max="1" width="24" customWidth="1"/>
    <col min="2" max="2" width="30" customWidth="1"/>
    <col min="3" max="3" width="32" customWidth="1"/>
    <col min="4" max="4" width="20" customWidth="1"/>
    <col min="5" max="5" width="22" customWidth="1"/>
  </cols>
  <sheetData>
    <row r="1">
      <c r="A1" t="inlineStr" s="1"><is><t>NIK</t></is></c>
      <c r="B1" t="inlineStr" s="1"><is><t>Nama Lengkap</t></is></c>
      <c r="C1" t="inlineStr" s="1"><is><t>Email</t></is></c>
      <c r="D1" t="inlineStr" s="1"><is><t>Password</t></is></c>
      <c r="E1" t="inlineStr" s="1"><is><t>No. WhatsApp</t></is></c>
    </row>
    <row r="2">
      <c r="A2" t="inlineStr"><is><t>3201012345670001</t></is></c>
      <c r="B2" t="inlineStr"><is><t>Ahmad Fauzi Rahman</t></is></c>
      <c r="C2" t="inlineStr"><is><t>ahmad.fauzi@example.com</t></is></c>
      <c r="D2" t="inlineStr"><is><t>Siswa2026!</t></is></c>
      <c r="E2" t="inlineStr"><is><t>081234567890</t></is></c>
    </row>
    <row r="3">
      <c r="A3" t="inlineStr"><is><t>3201012345670002</t></is></c>
      <c r="B3" t="inlineStr"><is><t>Nur Halimah Putri</t></is></c>
      <c r="C3" t="inlineStr"><is><t>nur.halimah@example.com</t></is></c>
      <c r="D3" t="inlineStr"><is><t>Siswa2026!</t></is></c>
      <c r="E3" t="inlineStr"><is><t>081298765432</t></is></c>
    </row>
    <row r="4">
      <c r="A4" t="inlineStr"><is><t>3201012345670003</t></is></c>
      <c r="B4" t="inlineStr"><is><t>Rizky Dwi Pratama</t></is></c>
      <c r="C4" t="inlineStr"><is><t>rizky.pratama@example.com</t></is></c>
      <c r="D4" t="inlineStr"><is><t>Siswa2026!</t></is></c>
      <c r="E4" t="inlineStr"><is><t>085712345678</t></is></c>
    </row>
  </sheetData>
</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return $tempFile;
    }
}
