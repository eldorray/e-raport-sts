<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GuruTemplateExport implements FromArray, WithHeadings
{
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
}
