<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RaporMetadata;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

const PESAN_KONTEKS_RAPOR_DATA = 'Tahun ajaran atau semester sudah diganti di tab lain. Muat ulang halaman ini, lalu simpan lagi.';

beforeEach(function () {
    $this->tahun = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->guru = Guru::factory()->create(['nama' => 'Wali Kelas', 'is_active' => true]);
    $this->userWali = User::findOrFail($this->guru->user_id);

    $this->kelas = Kelas::create([
        'nama' => '6A',
        'tingkat' => '6',
        'guru_id' => $this->guru->id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $this->siswaSatu = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '5001',
        'nama' => 'Siswa Satu',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
    $this->siswaDua = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '5002',
        'nama' => 'Siswa Dua',
        'jenis_kelamin' => 'P',
        'is_active' => true,
    ]);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];
});

/**
 * Payload simpan untuk tiap halaman data rapor wali kelas.
 *
 * @return array<string, array{0: string, 1: Closure(int): array<string, mixed>}>
 */
dataset('form data rapor', [
    'absen' => ['rapor.absen.store', fn (int $siswaId): array => ['absen' => [$siswaId => ['sakit' => 2, 'izin' => 1, 'alpa' => 0]]]],
    'prestasi' => ['rapor.prestasi.store', fn (int $siswaId): array => ['prestasi' => [$siswaId => 'Juara 1 Pidato']]],
    'catatan' => ['rapor.catatan.store', fn (int $siswaId): array => ['catatan' => [$siswaId => 'Pertahankan prestasimu']]],
]);

it('menolak simpan bila kelas bukan milik tahun ajaran di sesi', function (string $route, Closure $payload) {
    $tahunBaru = TahunAjaran::create([
        'nama' => '2026/2027',
        'tahun_mulai' => 2026,
        'tahun_selesai' => 2027,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->actingAs($this->userWali)
        ->withSession(['selected_tahun_ajaran_id' => $tahunBaru->id, 'selected_semester' => 'Ganjil'])
        ->post(route($route), ['kelas_id' => $this->kelas->id] + $payload($this->siswaSatu->id))
        ->assertSessionHasErrors(['tahun_ajaran' => PESAN_KONTEKS_RAPOR_DATA])
        ->assertSessionHasInput('kelas_id')
        ->assertSessionMissing('status');

    expect(RaporMetadata::count())->toBe(0);
})->with('form data rapor');

it('menolak simpan bila field konteks dari form berbeda dengan sesi', function (string $route, Closure $payload) {
    foreach ([['semester' => 'Genap'], ['tahun_ajaran_id' => '999999']] as $konteks) {
        $this->actingAs($this->userWali)
            ->withSession($this->sesi)
            ->post(route($route), $konteks + ['kelas_id' => $this->kelas->id] + $payload($this->siswaSatu->id))
            ->assertSessionHasErrors(['tahun_ajaran' => PESAN_KONTEKS_RAPOR_DATA])
            ->assertSessionHasInput('kelas_id');
    }

    expect(RaporMetadata::count())->toBe(0);
})->with('form data rapor');

it('menyimpan bila field konteks sama dengan sesi', function (string $route, Closure $payload) {
    $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->post(route($route), [
            'tahun_ajaran_id' => (string) $this->tahun->id,
            'semester' => 'Ganjil',
            'kelas_id' => $this->kelas->id,
        ] + $payload($this->siswaSatu->id))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('rapor_metadatas', [
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'siswa_id' => $this->siswaSatu->id,
        'kelas_id' => $this->kelas->id,
    ]);
})->with('form data rapor');

it('membatalkan seluruh simpanan bila satu siswa gagal disimpan', function (string $route, Closure $payload) {
    $siswaGagalId = $this->siswaDua->id;
    RaporMetadata::saving(function (RaporMetadata $meta) use ($siswaGagalId): void {
        if ((int) $meta->siswa_id === $siswaGagalId) {
            throw new RuntimeException('Gagal simpan');
        }
    });

    $body = array_replace_recursive($payload($this->siswaSatu->id), $payload($this->siswaDua->id));

    $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->post(route($route), ['kelas_id' => $this->kelas->id] + $body)
        ->assertServerError();

    expect(RaporMetadata::count())->toBe(0);
})->with('form data rapor');

it('menolak absen di atas 255 hari sebelum menyimpan apa pun', function () {
    $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->post(route('rapor.absen.store'), [
            'kelas_id' => $this->kelas->id,
            'absen' => [
                $this->siswaSatu->id => ['sakit' => 3, 'izin' => 0, 'alpa' => 0],
                $this->siswaDua->id => ['sakit' => 256, 'izin' => 0, 'alpa' => 0],
            ],
        ])
        ->assertSessionHasErrors('absen.'.$this->siswaDua->id.'.sakit');

    expect(RaporMetadata::count())->toBe(0);
});

it('memuat field konteks tersembunyi dan batas absen di form', function (string $halaman) {
    $response = $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->get(route($halaman, ['kelas_id' => $this->kelas->id]))
        ->assertOk()
        ->assertSee('name="tahun_ajaran_id" value="'.$this->tahun->id.'"', false)
        ->assertSee('name="semester" value="Ganjil"', false);

    if ($halaman === 'rapor.absen') {
        $response->assertSee('max="255"', false)->assertDontSee('max="365"', false);
    }
})->with(['rapor.absen', 'rapor.prestasi', 'rapor.catatan']);
