<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'nis',
            'nisn',
            'nama',
            'kelas',
            'tingkat',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'agama',
            'status_keluarga',
            'anak_ke',
            'telpon',
            'alamat',
            'sekolah_asal',
            'tanggal_diterima',
            'kelas_diterima',
            'nama_ayah',
            'nama_ibu',
            'pekerjaan_ayah',
            'pekerjaan_ibu',
            'alamat_orang_tua',
            'nama_wali',
            'pekerjaan_wali',
            'alamat_wali',
        ];
    }

    public function array(): array
    {
        return [
            [
                '20240001',
                '1234567890',
                'Nama Siswa Contoh',
                '1A',
                'I',
                'L',
                'Bandung',
                '2010-05-12',
                'Islam',
                'Anak Kandung',
                1,
                '081234567890',
                'Jl. Merdeka No. 1',
                'SDN 01 Bandung',
                '2022-07-15',
                'Kelas 5A',
                'Nama Ayah',
                'Nama Ibu',
                'Karyawan',
                'Ibu Rumah Tangga',
                'Jl. Merdeka No. 1',
                'Nama Wali',
                'Wiraswasta',
                'Jl. Wali No. 2',
            ],
        ];
    }
}
