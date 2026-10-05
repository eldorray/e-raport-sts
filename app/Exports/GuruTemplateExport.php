<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Kolom identitas ditulis dan diformat sebagai teks supaya nol di depan (mis. NISN "0081…") tidak hilang di Excel.
 */
class GuruTemplateExport extends StringValueBinder implements FromArray, WithColumnFormatting, WithCustomValueBinder, WithHeadings
{
    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'nip',
            'nama',
            'jenis_kelamin',
            'nik',
            'tempat_lahir',
            'tanggal_lahir',
            'pendidikan',
            'wali_kelas',
            'jtm',
            'password',
            'is_active',
        ];
    }

    /**
     * @return list<list<string|int>>
     */
    public function array(): array
    {
        return [
            [
                '197512312022011001',
                'Nama Guru Contoh',
                'L',
                '3174XXXXXXXXXXXX',
                'Jakarta',
                '1980-01-15',
                'S1 Pendidikan',
                'Kelas 5A',
                24,
                'rahasia123',
                1,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'A1:A1000' => NumberFormat::FORMAT_TEXT,
            'D1:D1000' => NumberFormat::FORMAT_TEXT,
            'J1:J1000' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
