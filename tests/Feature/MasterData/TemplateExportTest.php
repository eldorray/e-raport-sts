<?php

declare(strict_types=1);

use App\Exports\GuruTemplateExport;
use App\Exports\SiswaTemplateExport;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

it('menulis kolom identitas template sebagai teks supaya nol di depan tidak hilang', function (object $export, array $kolom) {
    $path = tempnam(sys_get_temp_dir(), 'tpl').'.xlsx';
    file_put_contents($path, Excel::raw($export, ExcelFormat::XLSX));
    $sheet = IOFactory::load($path)->getActiveSheet();

    foreach ($kolom as $huruf) {
        expect($sheet->getCell($huruf.'2')->getDataType())->toBe(DataType::TYPE_STRING)
            ->and($sheet->getStyle($huruf.'50')->getNumberFormat()->getFormatCode())->toBe(NumberFormat::FORMAT_TEXT);
    }
})->with([
    'siswa (nis, nisn, telpon)' => [new SiswaTemplateExport, ['A', 'B', 'L']],
    'guru (nip, nik, password)' => [new GuruTemplateExport, ['A', 'D', 'J']],
]);
