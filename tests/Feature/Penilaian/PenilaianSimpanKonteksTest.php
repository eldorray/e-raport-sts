<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

const PESAN_KONTEKS_PENILAIAN = 'Tahun ajaran atau semester sudah diganti di tab lain. Muat ulang halaman ini, lalu simpan lagi.';

beforeEach(function () {
    $this->tahun = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->guru = Guru::factory()->create(['nama' => 'Ustadz Penilai', 'is_active' => true]);
    $this->userGuru = User::findOrFail($this->guru->user_id);

    $this->kelas = Kelas::create([
        'nama' => '4A',
        'tingkat' => '4',
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $mapel = MataPelajaran::create(['nama_mapel' => 'Matematika', 'kode' => 'MTK']);

    $this->mengajar = Mengajar::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $this->kelas->id,
        'mata_pelajaran_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'jtm' => 4,
    ]);

    $this->siswas = collect(['Siswa Satu', 'Siswa Dua', 'Siswa Tiga', 'Siswa Empat'])
        ->map(fn (string $nama, int $i) => Siswa::create([
            'tahun_ajaran_id' => $this->tahun->id,
            'kelas_id' => $this->kelas->id,
            'nis' => '200'.$i,
            'nama' => $nama,
            'jenis_kelamin' => 'L',
            'is_active' => true,
        ]));

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];
});

it('tetap menerima form lama yang tidak mengirim field konteks', function () {
    $siswa = $this->siswas[0];

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.penilaian.store', $this->mengajar), [
            'nilai_sumatif' => [$siswa->id => 80],
            'nilai_sts' => [$siswa->id => 90],
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Nilai disimpan.');

    $this->assertDatabaseHas('penilaians', [
        'mengajar_id' => $this->mengajar->id,
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'nilai_sumatif' => 80,
        'nilai_sts' => 90,
    ]);
});

it('menerima form yang field konteksnya sama dengan sesi', function () {
    $siswa = $this->siswas[0];

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.penilaian.store', $this->mengajar), [
            'tahun_ajaran_id' => (string) $this->tahun->id,
            'semester' => 'Ganjil',
            'nilai_sumatif' => [$siswa->id => 70],
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    expect(Penilaian::where('siswa_id', $siswa->id)->value('nilai_sumatif'))->toEqual(70.0);
});

it('menolak simpan bila semester di sesi sudah diganti di tab lain', function () {
    $siswa = $this->siswas[0];

    $this->actingAs($this->userGuru)
        ->withSession(['selected_semester' => 'Genap'] + $this->sesi)
        ->post(route('guru.penilaian.store', $this->mengajar), [
            'nilai_sumatif' => [$siswa->id => 80],
        ])
        ->assertSessionHasErrors(['tahun_ajaran' => PESAN_KONTEKS_PENILAIAN])
        ->assertSessionHasInput('nilai_sumatif')
        ->assertSessionMissing('status');

    expect(Penilaian::count())->toBe(0);
});

it('menolak simpan bila tahun ajaran di sesi sudah diganti di tab lain', function () {
    $tahunBaru = TahunAjaran::create([
        'nama' => '2026/2027',
        'tahun_mulai' => 2026,
        'tahun_selesai' => 2027,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);
    $siswa = $this->siswas[0];

    $this->actingAs($this->userGuru)
        ->withSession(['selected_tahun_ajaran_id' => $tahunBaru->id, 'selected_semester' => 'Ganjil'])
        ->post(route('guru.penilaian.store', $this->mengajar), [
            'nilai_sumatif' => [$siswa->id => 80],
        ])
        ->assertSessionHasErrors(['tahun_ajaran' => PESAN_KONTEKS_PENILAIAN])
        ->assertSessionHasInput('nilai_sumatif');

    expect(Penilaian::count())->toBe(0);
});

it('menolak simpan bila field konteks dari form berbeda dengan sesi', function (array $konteks) {
    $siswa = $this->siswas[0];

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.penilaian.store', $this->mengajar), $konteks + [
            'nilai_sumatif' => [$siswa->id => 80],
        ])
        ->assertSessionHasErrors(['tahun_ajaran' => PESAN_KONTEKS_PENILAIAN])
        ->assertSessionHasInput('nilai_sumatif');

    expect(Penilaian::count())->toBe(0);
})->with([
    'semester berbeda' => [['semester' => 'Genap']],
    'tahun ajaran berbeda' => [['tahun_ajaran_id' => '999999']],
]);

it('mempertahankan input ketika tahun ajaran tidak aktif', function () {
    $this->tahun->update(['is_active' => false]);
    $siswa = $this->siswas[0];

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.penilaian.store', $this->mengajar), [
            'nilai_sumatif' => [$siswa->id => 80],
        ])
        ->assertSessionHasErrors('tahun_ajaran')
        ->assertSessionHasInput('nilai_sumatif');

    expect(Penilaian::count())->toBe(0);
});

it('menyimpan semua siswa walau kunci sumatif dan sts berbeda', function () {
    [$s1, $s2, $s3, $s4] = $this->siswas->all();

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.penilaian.store', $this->mengajar), [
            'nilai_sumatif' => [$s1->id => 70, $s2->id => 75],
            'nilai_sts' => [$s2->id => 80, $s3->id => 85, $s4->id => 90],
        ])
        ->assertSessionHasNoErrors();

    expect(Penilaian::where('mengajar_id', $this->mengajar->id)->pluck('nilai_sts', 'siswa_id')->all())
        ->toEqual([
            $s1->id => null,
            $s2->id => 80.0,
            $s3->id => 85.0,
            $s4->id => 90.0,
        ]);
});

it('menolak reset bila konteks sesi atau form sudah berubah', function (array $sesi, array $konteks) {
    $siswa = $this->siswas[0];
    Penilaian::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $this->kelas->id,
        'siswa_id' => $siswa->id,
        'mata_pelajaran_id' => $this->mengajar->mata_pelajaran_id,
        'guru_id' => $this->guru->id,
        'mengajar_id' => $this->mengajar->id,
        'nilai_sumatif' => 80,
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($sesi + $this->sesi)
        ->delete(route('guru.penilaian.reset', $this->mengajar), $konteks)
        ->assertSessionHasErrors(['tahun_ajaran' => PESAN_KONTEKS_PENILAIAN]);

    expect(Penilaian::count())->toBe(1);
})->with([
    'semester sesi berubah' => [['selected_semester' => 'Genap'], []],
    'semester form berbeda' => [[], ['semester' => 'Genap']],
]);

it('tetap bisa reset nilai pada konteks yang sama', function () {
    $siswa = $this->siswas[0];
    Penilaian::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $this->kelas->id,
        'siswa_id' => $siswa->id,
        'mata_pelajaran_id' => $this->mengajar->mata_pelajaran_id,
        'guru_id' => $this->guru->id,
        'mengajar_id' => $this->mengajar->id,
        'nilai_sumatif' => 80,
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->delete(route('guru.penilaian.reset', $this->mengajar), [
            'tahun_ajaran_id' => (string) $this->tahun->id,
            'semester' => 'Ganjil',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    expect(Penilaian::count())->toBe(0);
});
