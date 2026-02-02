<?php

namespace App\Exports;

use App\Models\Guru;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export data guru ke Excel.
 *
 * Mengexport data NIP/NUPTK, nama guru, dan password.
 */
class GuruExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * Mengambil koleksi data guru yang akan diexport.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Guru::orderBy('nama')->get();
    }

    /**
     * Heading untuk kolom Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'NIP/NUPTK',
            'Nama Guru',
            'Password',
        ];
    }

    /**
     * Mapping data guru ke baris Excel.
     *
     * @param Guru $guru
     * @return array
     */
    public function map($guru): array
    {
        return [
            $guru->nip,
            $guru->nama,
            $guru->initial_password ?? '-',
        ];
    }

    /**
     * Styling untuk worksheet.
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style baris header
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ];
    }
}
