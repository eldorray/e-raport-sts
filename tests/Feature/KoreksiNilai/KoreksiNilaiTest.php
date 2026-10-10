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
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->tahun = TahunAjaran::create([
        'nama' => '2026/2027',
        'tahun_mulai' => 2026,
        'tahun_selesai' => 2027,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Kurikulum', 'is_active' => true]);

    $this->guru = Guru::factory()->create(['nama' => 'Ustadzah Pengampu', 'is_active' => true]);
    $this->userGuru = User::findOrFail($this->guru->user_id);
    $this->userGuru->update(['bobot_sumatif' => 60, 'bobot_sts' => 40]);

    $this->kelas = Kelas::create([
        'nama' => '6A',
        'tingkat' => '6',
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $this->mapel = MataPelajaran::create(['nama_mapel' => 'Fikih', 'kode' => 'FKH']);

    $this->mengajar = Mengajar::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $this->kelas->id,
        'mata_pelajaran_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'jtm' => 2,
    ]);

    $this->siswas = collect(['Ahmad', 'Budi', 'Citra'])
        ->map(fn (string $nama, int $i) => Siswa::create([
            'tahun_ajaran_id' => $this->tahun->id,
            'kelas_id' => $this->kelas->id,
            'nis' => 'K'.$i,
            'nama' => $nama,
            'jenis_kelamin' => 'L',
            'is_active' => true,
        ]));

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];

    $this->nilaiGuru = function (Siswa $siswa, ?float $sumatif, ?float $sts, array $tambahan = []): Penilaian {
        return Penilaian::create($tambahan + [
            'tahun_ajaran_id' => $this->mengajar->tahun_ajaran_id,
            'semester' => $this->mengajar->semester,
            'kelas_id' => $this->mengajar->kelas_id,
            'siswa_id' => $siswa->id,
            'mata_pelajaran_id' => $this->mengajar->mata_pelajaran_id,
            'guru_id' => $this->guru->id,
            'mengajar_id' => $this->mengajar->id,
            'materi_tp' => 'Thaharah',
            'nilai_sumatif' => $sumatif,
            'nilai_sts' => $sts,
        ]);
    };
});

it('menampilkan daftar kelas beserta mapel, guru, dan progres nilai lengkap', function () {
    ($this->nilaiGuru)($this->siswas[0], 80, 70);
    ($this->nilaiGuru)($this->siswas[1], 75, null);

    $kelasLain = Kelas::create(['nama' => '5B', 'tingkat' => '5', 'tahun_ajaran_id' => $this->tahun->id]);
    $mapelLain = MataPelajaran::create(['nama_mapel' => 'Bahasa Arab', 'kode' => 'BAR']);
    Mengajar::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $kelasLain->id,
        'mata_pelajaran_id' => $mapelLain->id,
        'guru_id' => null,
        'jtm' => 2,
    ]);

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('koreksi-nilai.index'))
        ->assertOk()
        ->assertSee('6A')
        ->assertSee('5B')
        ->assertSee('Fikih')
        ->assertSee('Ustadzah Pengampu')
        ->assertSee('Bahasa Arab')
        ->assertSee('Belum ada guru')
        ->assertSee('1/3')
        ->assertSee(route('koreksi-nilai.show', $this->mengajar), false);

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('koreksi-nilai.index', ['kelas' => $this->kelas->id]))
        ->assertOk()
        ->assertSee('Fikih')
        ->assertDontSee('Bahasa Arab');
});

it('menampilkan form koreksi dengan nama guru dan bobot milik guru', function () {
    ($this->nilaiGuru)($this->siswas[0], 80, 70);

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('koreksi-nilai.show', $this->mengajar))
        ->assertOk()
        ->assertSee('Ustadzah Pengampu')
        ->assertSee('Fikih')
        ->assertSee('6A')
        ->assertSee('action="'.route('koreksi-nilai.store', $this->mengajar).'"', false)
        ->assertSee('name="nilai_sumatif['.$this->siswas[0]->id.']"', false)
        ->assertSee('value="80"', false)
        ->assertSee('value="70"', false)
        ->assertSee('value="Thaharah"', false)
        // Bobot guru 60/40, bukan bobot admin: 80*0.6 + 70*0.4 = 76
        ->assertSee('60%')
        ->assertSee('40%')
        ->assertSee('data-nilai-akhir>76<', false);
});

it('menyimpan koreksi dengan guru_id pengampu tanpa membuat baris ganda', function () {
    ($this->nilaiGuru)($this->siswas[0], 80, 70);
    ($this->nilaiGuru)($this->siswas[1], 60, 65);

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->from(route('koreksi-nilai.show', $this->mengajar))
        ->post(route('koreksi-nilai.store', $this->mengajar), [
            'materi_tp' => 'Thaharah',
            'nilai_sumatif' => [$this->siswas[0]->id => 85, $this->siswas[1]->id => 60, $this->siswas[2]->id => 90],
            'nilai_sts' => [$this->siswas[0]->id => 70, $this->siswas[1]->id => 65, $this->siswas[2]->id => 88],
        ])
        ->assertRedirect(route('koreksi-nilai.show', $this->mengajar))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    expect(Penilaian::count())->toBe(3)
        ->and(Penilaian::where('guru_id', '!=', $this->guru->id)->count())->toBe(0)
        ->and(Penilaian::where('siswa_id', $this->siswas[0]->id)->value('nilai_sumatif'))->toEqual(85.0)
        ->and(Penilaian::where('siswa_id', $this->siswas[2]->id)->value('nilai_sts'))->toEqual(88.0);

    $this->assertDatabaseHas('penilaians', [
        'siswa_id' => $this->siswas[2]->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $this->kelas->id,
        'mata_pelajaran_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'mengajar_id' => $this->mengajar->id,
    ]);
});

it('hanya memberi jejak koreksi pada baris yang benar-benar berubah', function () {
    $diubah = ($this->nilaiGuru)($this->siswas[0], 80, 70);
    $tetap = ($this->nilaiGuru)($this->siswas[1], 60, 65);
    $updatedAtTetap = $tetap->fresh()->updated_at;

    Carbon::setTestNow('2026-10-10 10:15:00');

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->post(route('koreksi-nilai.store', $this->mengajar), [
            'materi_tp' => 'Thaharah',
            'nilai_sumatif' => [$this->siswas[0]->id => 82, $this->siswas[1]->id => '60.00'],
            'nilai_sts' => [$this->siswas[0]->id => 70, $this->siswas[1]->id => 65],
        ])
        ->assertSessionHasNoErrors();

    $diubah->refresh();
    $tetap->refresh();

    expect($diubah->dikoreksi_oleh)->toBe($this->admin->id)
        ->and($diubah->dikoreksi_pada?->format('Y-m-d H:i'))->toBe('2026-10-10 10:15')
        ->and($diubah->pengoreksi?->name)->toBe('Admin Kurikulum')
        ->and($tetap->dikoreksi_oleh)->toBeNull()
        ->and($tetap->dikoreksi_pada)->toBeNull()
        ->and($tetap->updated_at->equalTo($updatedAtTetap))->toBeTrue();

    Carbon::setTestNow();
});

it('menghapus jejak koreksi hanya pada baris yang diubah guru', function () {
    $jejak = ['dikoreksi_oleh' => $this->admin->id, 'dikoreksi_pada' => '2026-10-01 08:00:00'];
    $diubahGuru = ($this->nilaiGuru)($this->siswas[0], 82, 70, $jejak);
    $tetap = ($this->nilaiGuru)($this->siswas[1], 60, 65, $jejak);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.penilaian.store', $this->mengajar), [
            'materi_tp' => 'Thaharah',
            'nilai_sumatif' => [$this->siswas[0]->id => 88, $this->siswas[1]->id => 60],
            'nilai_sts' => [$this->siswas[0]->id => 70, $this->siswas[1]->id => 65],
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Nilai disimpan.');

    expect($diubahGuru->fresh()->nilai_sumatif)->toEqual(88.0)
        ->and($diubahGuru->fresh()->dikoreksi_oleh)->toBeNull()
        ->and($diubahGuru->fresh()->dikoreksi_pada)->toBeNull()
        ->and($tetap->fresh()->dikoreksi_oleh)->toBe($this->admin->id)
        ->and($tetap->fresh()->dikoreksi_pada)->not->toBeNull()
        ->and(Penilaian::count())->toBe(2);
});

it('menolak guru membuka rute koreksi nilai', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('koreksi-nilai.index'))
        ->assertForbidden();

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('koreksi-nilai.show', $this->mengajar))
        ->assertForbidden();

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('koreksi-nilai.store', $this->mengajar), [
            'nilai_sumatif' => [$this->siswas[0]->id => 10],
        ])
        ->assertForbidden();

    expect(Penilaian::count())->toBe(0);
});

it('tidak bisa menyimpan koreksi pada jadwal tanpa guru', function () {
    $this->mengajar->update(['guru_id' => null]);

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('koreksi-nilai.show', $this->mengajar))
        ->assertOk()
        ->assertSee('Jadwal ini belum punya guru pengampu')
        ->assertDontSee('Simpan Koreksi');

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->from(route('koreksi-nilai.show', $this->mengajar))
        ->post(route('koreksi-nilai.store', $this->mengajar), [
            'nilai_sumatif' => [$this->siswas[0]->id => 90],
            'nilai_sts' => [$this->siswas[0]->id => 90],
        ])
        ->assertRedirect(route('koreksi-nilai.show', $this->mengajar))
        ->assertSessionHasErrors('guru')
        ->assertSessionHasInput('nilai_sumatif');

    expect(Penilaian::count())->toBe(0);
});

it('tetap mengizinkan koreksi pada tahun ajaran tidak aktif dengan pemberitahuan', function () {
    $this->tahun->update(['is_active' => false]);

    $tahunAktif = TahunAjaran::create([
        'nama' => '2027/2028',
        'tahun_mulai' => 2027,
        'tahun_selesai' => 2028,
        'semester' => 'Genap',
        'is_active' => true,
    ]);
    $sesiLain = ['selected_tahun_ajaran_id' => $tahunAktif->id, 'selected_semester' => 'Genap'];

    $this->actingAs($this->admin)
        ->withSession($sesiLain)
        ->get(route('koreksi-nilai.show', $this->mengajar))
        ->assertOk()
        ->assertSee('Tahun ajaran ini sudah ditutup')
        ->assertSee('Simpan Koreksi');

    // Konteks simpan mengikuti jadwal mengajar, bukan tahun ajaran di sesi.
    $this->actingAs($this->admin)
        ->withSession($sesiLain)
        ->post(route('koreksi-nilai.store', $this->mengajar), [
            'nilai_sumatif' => [$this->siswas[0]->id => 77],
            'nilai_sts' => [$this->siswas[0]->id => 66],
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('penilaians', [
        'siswa_id' => $this->siswas[0]->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'guru_id' => $this->guru->id,
        'nilai_sumatif' => 77,
        'dikoreksi_oleh' => $this->admin->id,
    ]);
});

it('mengembalikan isian lama ketika validasi gagal', function () {
    ($this->nilaiGuru)($this->siswas[0], 80, 70);

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->from(route('koreksi-nilai.show', $this->mengajar))
        ->post(route('koreksi-nilai.store', $this->mengajar), [
            'materi_tp' => str_repeat('a', 256),
            'nilai_sumatif' => [$this->siswas[0]->id => 150],
            'nilai_sts' => [$this->siswas[0]->id => 'abc'],
        ])
        ->assertRedirect(route('koreksi-nilai.show', $this->mengajar))
        ->assertSessionHasErrors([
            'materi_tp',
            'nilai_sumatif.'.$this->siswas[0]->id,
            'nilai_sts.'.$this->siswas[0]->id,
        ])
        ->assertSessionHasInput('nilai_sumatif');

    expect(Penilaian::where('siswa_id', $this->siswas[0]->id)->value('nilai_sumatif'))->toEqual(80.0);

    $this->actingAs($this->admin)
        ->withSession($this->sesi + [
            '_old_input' => [
                'materi_tp' => 'Najis',
                'nilai_sumatif' => [$this->siswas[0]->id => '55'],
                'nilai_sts' => [$this->siswas[0]->id => '66'],
            ],
        ])
        ->get(route('koreksi-nilai.show', $this->mengajar))
        ->assertOk()
        ->assertSee('value="55"', false)
        ->assertSee('value="66"', false)
        ->assertSee('value="Najis"', false)
        ->assertDontSee('value="80"', false);
});

it('menampilkan catatan koreksi pada form nilai guru', function () {
    ($this->nilaiGuru)($this->siswas[0], 82, 70, [
        'dikoreksi_oleh' => $this->admin->id,
        'dikoreksi_pada' => '2026-03-05 09:30:00',
    ]);
    ($this->nilaiGuru)($this->siswas[1], 60, 65);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.penilaian.show', $this->mengajar))
        ->assertOk()
        ->assertSee('Dikoreksi Admin Kurikulum, 05 Mar 2026 09:30')
        ->assertSee('1 nilai siswa dikoreksi admin');
});

it('tidak menampilkan ringkasan koreksi bila tidak ada baris yang dikoreksi', function () {
    ($this->nilaiGuru)($this->siswas[0], 82, 70);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.penilaian.show', $this->mengajar))
        ->assertOk()
        ->assertDontSee('dikoreksi admin')
        ->assertDontSee('Dikoreksi ');
});

it('menampilkan catatan koreksi admin di form nilai aplikasi HP guru', function () {
    ($this->nilaiGuru)($this->siswas[0], 80, 85, [
        'dikoreksi_oleh' => $this->admin->id,
        'dikoreksi_pada' => Carbon::parse('2026-10-12 09:30'),
    ]);
    ($this->nilaiGuru)($this->siswas[1], 70, 75);
    $waktu = Carbon::parse('2026-10-12 09:30')->translatedFormat('d M Y H:i');

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai.form', $this->mengajar))
        ->assertOk()
        ->assertSee('data-ringkasan-koreksi', false)
        ->assertSee('1 nilai siswa dikoreksi admin, terakhir oleh Admin Kurikulum pada '.$waktu.'.')
        ->assertSee('Dikoreksi Admin Kurikulum, '.$waktu);
});

it('tidak menampilkan catatan koreksi di form HP bila tidak ada koreksi', function () {
    ($this->nilaiGuru)($this->siswas[0], 80, 85);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai.form', $this->mengajar))
        ->assertOk()
        ->assertDontSee('data-ringkasan-koreksi', false);
});

it('menautkan cetak rapor satu kelas dari halaman wali aplikasi HP', function () {
    $this->kelas->update(['guru_id' => $this->guru->id]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.wali'))
        ->assertOk()
        ->assertSee(route('rapor.print-kelas', $this->kelas), false);
});

it('mengarahkan pintasan Nilai di ikon aplikasi sesuai peran', function () {
    $this->actingAs($this->admin)->get('/app/nilai')->assertRedirect(route('koreksi-nilai.index'));
    $this->actingAs($this->userGuru)->get('/app/nilai')->assertRedirect(route('guru.pwa.nilai'));
});
