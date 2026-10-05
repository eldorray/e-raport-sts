<?php

declare(strict_types=1);

use App\Exports\GuruTemplateExport;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Menulis baris-baris ke file .xlsx sementara dan membungkusnya sebagai upload.
 *
 * @param  list<array<string, mixed>>  $rows  Baris data dengan heading sebagai key
 */
function imporGuruFileXlsx(array $rows): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray(
        array_merge([array_keys($rows[0])], array_map('array_values', $rows)),
        null,
        'A1',
        true,
    );

    $path = tempnam(sys_get_temp_dir(), 'imp-guru').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile($path, 'guru.xlsx', null, null, true);
}

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('mengimpor template guru bawaan aplikasi dengan NIP panjang tanpa kehilangan digit', function () {
    $path = tempnam(sys_get_temp_dir(), 'tpl-guru').'.xlsx';
    file_put_contents($path, Excel::raw(new GuruTemplateExport, ExcelFormat::XLSX));

    $this->actingAs($this->admin)
        ->post(route('guru.import'), ['file' => new UploadedFile($path, 'template-guru.xlsx', null, null, true)])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Guru::sole()->nip)->toBe('197512312022011001');
});

it('mengimpor NIP dan NIK yang terbaca sebagai angka bulat', function () {
    $file = imporGuruFileXlsx([
        ['nip' => 198001012005011002, 'nama' => 'Guru Angka', 'jenis_kelamin' => 'P', 'nik' => 317401010101, 'password' => 123456],
    ]);

    $this->actingAs($this->admin)
        ->post(route('guru.import'), ['file' => $file])
        ->assertSessionHasNoErrors();

    $guru = Guru::sole();

    expect($guru->nip)->toBe('198001012005011002')
        ->and($guru->nik)->toBe('317401010101');
});

it('menolak NIP yang terbaca sebagai angka eksponen dan meminta format Teks', function () {
    $file = imporGuruFileXlsx([
        ['nip' => 1.98001012005011E+17, 'nama' => 'Guru Eksponen', 'jenis_kelamin' => 'L'],
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('guru.import'), ['file' => $file])
        ->assertSessionHasErrors('file');

    expect(implode(' ', $response->getSession()->get('errors')->get('file')))->toContain('format kolom NIP sebagai Teks')
        ->and(Guru::count())->toBe(0);
});

it('memberi pesan ramah tanpa SQL mentah saat NIK bentrok dengan data yang sudah ada', function () {
    Log::spy();

    $userLama = User::factory()->create(['role' => 'guru', 'nik' => '3174000000000001']);
    Guru::create([
        'user_id' => $userLama->id,
        'nama' => 'Guru Lama',
        'nip' => '1001',
        'nik' => '3174000000000001',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $file = imporGuruFileXlsx([
        ['nip' => '1002', 'nama' => 'Guru Baru', 'jenis_kelamin' => 'L', 'nik' => '3174000000000001'],
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('guru.import'), ['file' => $file])
        ->assertSessionHasErrors('file');

    $pesan = implode(' ', $response->getSession()->get('errors')->get('file'));

    expect($pesan)->toContain('bentrok')->not->toContain('SQLSTATE')
        ->and(Guru::where('nip', '1002')->exists())->toBeFalse();
    Log::shouldHaveReceived('error')->once();
});
