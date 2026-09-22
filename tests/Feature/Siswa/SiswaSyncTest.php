<?php

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/**
 * Menyusun response API data induk untuk satu halaman data.
 *
 * @param  array<int, array<string, mixed>>  $siswas  Data siswa
 * @return array<string, mixed> Payload response API
 */
function payloadSyncSiswa(array $siswas): array
{
    return [
        'success' => true,
        'message' => 'OK',
        'data' => $siswas,
        'total' => count($siswas),
    ];
}

/**
 * Menyusun satu record siswa sesuai format API data induk.
 *
 * @param  array<string, mixed>  $overrides  Nilai yang ingin ditimpa
 * @return array<string, mixed> Record siswa
 */
function recordSiswaApi(array $overrides = []): array
{
    return array_merge([
        'id' => 1,
        'nama_lengkap' => 'Ahmad Siswa',
        'nisn' => '0012345678',
        'nik' => '3172052406190001',
        'tempat_lahir' => 'JAKARTA',
        'tanggal_lahir' => '2019-06-24T00:00:00.000000Z',
        'tingkat_rombel' => 'Kelas 1 - KELAS 1A',
        'status' => 'Aktif',
        'jenis_kelamin' => 'L',
        'alamat' => 'Jl. Contoh No. 1',
        'no_telepon' => null,
        'nama_ayah_kandung' => 'Ayah Siswa',
        'nama_ibu_kandung' => 'Ibu Siswa',
        'nama_wali' => null,
    ], $overrides);
}

beforeEach(function () {
    config(['services.data_induk.base_url' => 'https://datainduk.test']);

    $this->tahun = TahunAjaran::create([
        'nama' => '2026/2027',
        'tahun_mulai' => 2026,
        'tahun_selesai' => 2027,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('membuat kelas baru dan langsung menempatkan siswa saat sync dari API', function () {
    Http::fake([
        'datainduk.test/api/siswa-mi/all*' => Http::response(payloadSyncSiswa([
            recordSiswaApi(),
            recordSiswaApi([
                'id' => 2,
                'nama_lengkap' => 'Bilal Siswa',
                'nisn' => '0012345679',
                'nik' => '3172052406190002',
                'tingkat_rombel' => 'Kelas 1 - KELAS 1B',
            ]),
        ])),
    ]);

    $response = $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.sync'), ['source' => 'siswa-mi']);

    $response->assertRedirect();
    $response->assertSessionHas('status', fn (string $status) => str_contains($status, '2 siswa baru')
        && str_contains($status, '2 kelas baru dibuat otomatis'));

    $kelasA = Kelas::where('nama', '1A')->first();
    $kelasB = Kelas::where('nama', '1B')->first();

    expect($kelasA)->not->toBeNull()
        ->and($kelasB)->not->toBeNull()
        ->and($kelasA->tingkat)->toBe('I')
        ->and($kelasA->jenis)->toBe('MI')
        ->and($kelasA->tahun_ajaran_id)->toBe($this->tahun->id);

    $ahmad = Siswa::where('nisn', '0012345678')->first();

    expect($ahmad)->not->toBeNull()
        ->and($ahmad->kelas_id)->toBe($kelasA->id)
        ->and($ahmad->kelas_diterima)->toBe('1A')
        ->and($ahmad->tahun_ajaran_id)->toBe($this->tahun->id)
        ->and(Siswa::where('nisn', '0012345679')->value('kelas_id'))->toBe($kelasB->id);
});

it('memakai kelas yang sudah ada tanpa membuat kelas duplikat', function () {
    $kelas = Kelas::create([
        'nama' => '1a',
        'tingkat' => 'I',
        'jenis' => 'Umum',
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    Http::fake([
        'datainduk.test/api/siswa-mi/all*' => Http::response(payloadSyncSiswa([recordSiswaApi()])),
    ]);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.sync'), ['source' => 'siswa-mi'])
        ->assertRedirect()
        ->assertSessionHas('status', fn (string $status) => ! str_contains($status, 'kelas baru dibuat otomatis'));

    expect(Kelas::count())->toBe(1)
        ->and(Siswa::where('nisn', '0012345678')->value('kelas_id'))->toBe($kelas->id);
});

it('memetakan rombel SMP ke tingkat romawi dan jenis SMP', function () {
    Http::fake([
        'datainduk.test/api/siswa-smp/all*' => Http::response(payloadSyncSiswa([
            recordSiswaApi([
                'nama_lengkap' => 'Citra Siswa',
                'nisn' => '0098765432',
                'tingkat_rombel' => 'Kelas VII - Kelas VII A',
            ]),
        ])),
    ]);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.sync'), ['source' => 'siswa-smp'])
        ->assertRedirect();

    $kelas = Kelas::where('nama', 'VII A')->first();

    expect($kelas)->not->toBeNull()
        ->and($kelas->tingkat)->toBe('VII')
        ->and($kelas->jenis)->toBe('SMP')
        ->and(Siswa::where('nisn', '0098765432')->value('kelas_id'))->toBe($kelas->id);
});

it('memindahkan siswa ke rombel terbaru saat sync ulang', function () {
    $kelasLama = Kelas::create([
        'nama' => '1B',
        'tingkat' => 'I',
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $siswa = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $kelasLama->id,
        'nis' => '0012345678',
        'nisn' => '0012345678',
        'nama' => 'Ahmad Siswa',
        'jenis_kelamin' => 'L',
        'kelas_diterima' => '1B',
        'is_active' => true,
    ]);

    Http::fake([
        'datainduk.test/api/siswa-mi/all*' => Http::response(payloadSyncSiswa([recordSiswaApi()])),
    ]);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.sync'), ['source' => 'siswa-mi'])
        ->assertRedirect()
        ->assertSessionHas('status', fn (string $status) => str_contains($status, '1 diperbarui'));

    $siswa->refresh();

    expect($siswa->kelas_id)->toBe(Kelas::where('nama', '1A')->value('id'))
        ->and($siswa->kelas_diterima)->toBe('1B');
});

it('tidak menghapus kelas lama bila API tidak mengirim rombel', function () {
    $kelas = Kelas::create([
        'nama' => '1A',
        'tingkat' => 'I',
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $siswa = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $kelas->id,
        'nis' => '0012345678',
        'nisn' => '0012345678',
        'nama' => 'Ahmad Siswa',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    Http::fake([
        'datainduk.test/api/siswa-mi/all*' => Http::response(payloadSyncSiswa([
            recordSiswaApi(['tingkat_rombel' => null]),
        ])),
    ]);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.sync'), ['source' => 'siswa-mi'])
        ->assertRedirect();

    $siswa->refresh();

    expect($siswa->kelas_id)->toBe($kelas->id);
});

it('tetap menyimpan siswa tanpa kelas bila rombel tidak tersedia', function () {
    Http::fake([
        'datainduk.test/api/siswa-mi/all*' => Http::response(payloadSyncSiswa([
            recordSiswaApi(['tingkat_rombel' => null]),
        ])),
    ]);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.sync'), ['source' => 'siswa-mi'])
        ->assertRedirect()
        ->assertSessionHas('status', fn (string $status) => str_contains($status, '1 siswa baru'));

    expect(Siswa::where('nisn', '0012345678')->value('kelas_id'))->toBeNull()
        ->and(Kelas::count())->toBe(0);
});

it('tidak menetapkan kelas bila opsi kelas otomatis dimatikan', function () {
    Http::fake([
        'datainduk.test/api/siswa-mi/all*' => Http::response(payloadSyncSiswa([recordSiswaApi()])),
    ]);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->post(route('siswa.sync'), ['source' => 'siswa-mi', 'assign_kelas' => '0'])
        ->assertRedirect();

    expect(Siswa::where('nisn', '0012345678')->value('kelas_id'))->toBeNull()
        ->and(Kelas::count())->toBe(0);
});
