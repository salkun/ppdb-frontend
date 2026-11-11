<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Support\Str;
use ZipArchive;

class ExamResultExportService
{
    public function generateXlsx(Exam $exam): string
    {
        $headers = ['No', 'Nama Siswa', 'Email', 'Nomor HP', 'Jurusan', 'Tes', 'Status', 'Nilai', 'Pelanggaran', 'Mulai', 'Selesai'];
        $rows = $exam->attempts()
            ->with('account.registration')
            ->orderBy('started_at')
            ->get()
            ->map(function ($attempt, $index) use ($exam) {
                $account = $attempt->account;
                $major = $account?->registration?->form_data['major'] ?? '';

                return [
                    $index + 1,
                    $account?->full_name ?? $attempt->account_id,
                    $account?->email ?? '',
                    $account?->phone ?? '',
                    match ($major) {
                        'reguler' => 'Reguler',
                        'tahfidz' => 'Tahfidz',
                        'bahasa' => 'Bahasa',
                        'ict' => 'ICT',
                        default => $major,
                    },
                    $exam->title,
                    match ($attempt->status) {
                        'in_progress' => 'Sedang mengerjakan',
                        'suspended' => 'Ditangguhkan',
                        'submitted' => 'Selesai',
                        'terminated' => 'Dihentikan',
                        default => $attempt->status,
                    },
                    $attempt->score === null ? '' : number_format((float) $attempt->score, 2, '.', ''),
                    $attempt->violations,
                    $attempt->started_at?->format('d/m/Y H:i:s') ?? '',
                    $attempt->completed_at?->format('d/m/Y H:i:s') ?? '',
                ];
            })
            ->all();

        $tempFile = tempnam(sys_get_temp_dir(), 'ppdb-exam-results-');
        if ($tempFile === false) {
            throw new \RuntimeException('Gagal menyiapkan file sementara laporan ujian.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tempFile);
            throw new \RuntimeException('Gagal membuat file Excel laporan ujian.');
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Hasil Ujian" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><name val="Calibri"/><sz val="11"/></font><font><b/><name val="Calibri"/><sz val="11"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE2E8F0"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="1" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
            . '</styleSheet>');

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        $sheet .= $this->rowXml(1, $headers, true);
        foreach ($rows as $index => $row) {
            $sheet .= $this->rowXml($index + 2, $row);
        }
        $sheet .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);

        if (!$zip->close()) {
            @unlink($tempFile);
            throw new \RuntimeException('Gagal menyelesaikan file Excel laporan ujian.');
        }

        return $tempFile;
    }

    public function filename(Exam $exam): string
    {
        return 'hasil-' . Str::slug($exam->type) . '-' . now()->format('Y-m-d_His') . '.xlsx';
    }

    private function rowXml(int $rowNumber, array $values, bool $isHeader = false): string
    {
        $xml = '<row r="' . $rowNumber . '">';
        foreach ($values as $index => $value) {
            $cell = $this->columnLetter($index + 1) . $rowNumber;
            $text = htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);
            $style = $isHeader ? ' s="1"' : '';
            $xml .= '<c r="' . $cell . '"' . $style . ' t="inlineStr"><is><t xml:space="preserve">' . $text . '</t></is></c>';
        }

        return $xml . '</row>';
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $letter = chr(65 + $remainder) . $letter;
            $index = intdiv($index - 1, 26);
        }

        return $letter;
    }
}
