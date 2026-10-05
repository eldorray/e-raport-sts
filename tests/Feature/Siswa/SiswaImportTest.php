<?php

declare(strict_types=1);

use App\Exports\SiswaTemplateExport;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Menulis baris-baris ke file .xlsx sementara dan membungkusnya sebagai upload.
 *
 * Nilai numerik ditulis sebagai sel angka, sama seperti hasil ketikan di Excel.
 *
 * @param  list<array<string, mixed>>  $rows  Baris data dengan heading sebagai key
 */
function imporSiswaFileXlsx(array $rows): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray(
        array_merge([array_keys($rows[0])], array_map('array_values', $rows)),
        null,
        'A1',
        true,
    );

    $path = tempnam(sys_get_temp_dir(), 'imp-siswa').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile($path, 'siswa.xlsx', null, null, true);
}

/**
 * Membuat tahun ajaran untuk pengujian import siswa.
 */
function imporSiswaTahun(string $nama, bool $aktif = false): TahunAjaran
{
    return TahunAjaran::create([
        'nama' => $nama,
        'tahun_mulai' => (int) substr($nama, 0, 4),
        'tahun_selesai' => (int) substr($nama, 5, 4),
        'semester' => 'Ganjil',
        'is_active' => $aktif,
    ]);
}

beforeEach(function () {
    $this->tahun = imporSiswaTahun('2026/2027', true);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('mengimpor template bawaan aplikasi yang NIS/NISN-nya terbaca sebagai angka', function () {
    $path = tempnam(sys_get_temp_dir(), 'tpl-siswa').'.xlsx';
    file_put_contents($path, Excel::raw(new SiswaTemplateExport, ExcelFormat::XLSX));

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.import'), ['file' => new UploadedFile($path, 'template-siswa.xlsx', null, null, true)])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $siswa = Siswa::where('tahun_ajaran_id', $this->tahun->id)->sole();

    expect($siswa->nis)->toBe('20240001')
        ->and($siswa->nisn)->toBe('1234567890');
});

it('mengimpor baris dengan NIS berupa angka bulat', function () {
    $file = imporSiswaFileXlsx([
        ['nis' => 20240001, 'nisn' => 9876543210, 'nama' => 'Budi', 'jenis_kelamin' => 'L'],
    ]);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.import'), ['file' => $file])
        ->assertSessionHasNoErrors();

    expect(Siswa::where('nis', '20240001')->where('nisn', '9876543210')->exists())->toBeTrue();
});

it('tetap menyisipkan siswa saat diimpor ulang ke tahun ajaran lain', function () {
    $tahunLama = imporSiswaTahun('2025/2026');
    Siswa::create([
        'tahun_ajaran_id' => $tahunLama->id,
        'nis' => '20240001',
        'nisn' => '9876543210',
        'nama' => 'Budi',
        'jenis_kelamin' => 'L',
    ]);

    $file = imporSiswaFileXlsx([
        ['nis' => '20240001', 'nisn' => '9876543210', 'nama' => 'Budi', 'jenis_kelamin' => 'L'],
    ]);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.import'), ['file' => $file])
        ->assertSessionHasNoErrors();

    expect(Siswa::where('nis', '20240001')->count())->toBe(2)
        ->and(Siswa::where('tahun_ajaran_id', $this->tahun->id)->where('nis', '20240001')->exists())->toBeTrue();
});

it('memberi pesan ramah saat NISN sudah dipakai di tahun ajaran yang sama', function () {
    Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'nis' => '1001',
        'nisn' => '9876543210',
        'nama' => 'Siswa Lama',
        'jenis_kelamin' => 'P',
    ]);

    $file = imporSiswaFileXlsx([
        ['nis' => '1002', 'nisn' => '9876543210', 'nama' => 'Siswa Baru', 'jenis_kelamin' => 'L'],
    ]);

    $response = $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.import'), ['file' => $file])
        ->assertSessionHasNoErrors();

    expect($response->getSession()->get('warning'))->toContain('NISN 9876543210 sudah terdaftar di tahun ajaran ini')
        ->and(Siswa::where('nis', '1002')->exists())->toBeFalse();
});

it('menolak NIS yang terbaca sebagai angka desimal/eksponen dan meminta format Teks', function () {
    $file = imporSiswaFileXlsx([
        ['nis' => 1.98765432101234E+17, 'nisn' => null, 'nama' => 'Budi', 'jenis_kelamin' => 'L'],
    ]);

    $response = $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.import'), ['file' => $file])
        ->assertSessionHasErrors('file');

    expect(implode(' ', $response->getSession()->get('errors')->get('file')))->toContain('format kolom NIS sebagai Teks')
        ->and(Siswa::count())->toBe(0);
});

it('menyembunyikan pesan SQL mentah saat import bentrok di database', function () {
    Log::spy();
    Excel::shouldReceive('import')->once()->andThrow(new QueryException(
        'sqlite',
        'insert into "siswas" ("nis") values (?)',
        ['1001'],
        new PDOException('SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: siswas.nisn'),
    ));

    $response = $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.import'), ['file' => UploadedFile::fake()->create('siswa.xlsx', 1)])
        ->assertSessionHasErrors('file');

    $pesan = implode(' ', $response->getSession()->get('errors')->get('file'));

    expect($pesan)->toContain('bentrok')->not->toContain('SQLSTATE');
    Log::shouldHaveReceived('error')->once();
});
