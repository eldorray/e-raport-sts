<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\MengajarTahfidz;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

/**
 * Wizard "Tahun Ajaran Baru": memindahkan seluruh sekolah ke semester/tahun ajaran
 * berikutnya sekaligus (kelas, rombel siswa, jadwal mengajar).
 */
function wizardTahun(string $nama, string $semester, bool $aktif = false): TahunAjaran
{
    return TahunAjaran::create([
        'nama' => $nama,
        'tahun_mulai' => (int) substr($nama, 0, 4),
        'tahun_selesai' => (int) substr($nama, 5, 4),
        'semester' => $semester,
        'is_active' => $aktif,
    ]);
}

function wizardKelas(TahunAjaran $tahun, string $nama, string $tingkat, ?Guru $wali = null): Kelas
{
    return Kelas::create([
        'tahun_ajaran_id' => $tahun->id,
        'nama' => $nama,
        'tingkat' => $tingkat,
        'jenis' => 'MI',
        'guru_id' => $wali?->id,
    ]);
}

function wizardSiswa(TahunAjaran $tahun, ?Kelas $kelas, string $nis, bool $aktif = true): Siswa
{
    return Siswa::create([
        'tahun_ajaran_id' => $tahun->id,
        'kelas_id' => $kelas?->id,
        'nis' => $nis,
        'nisn' => '00'.$nis,
        'nama' => 'Siswa '.$nis,
        'jenis_kelamin' => 'L',
        'tanggal_lahir' => '2018-05-01',
        'photo_path' => 'siswa/'.$nis.'.jpg',
        'is_active' => $aktif,
    ]);
}

function wizardGuru(string $nama): Guru
{
    return Guru::create([
        'user_id' => User::factory()->create(['role' => 'guru'])->id,
        'nama' => $nama,
        'nip' => fake()->unique()->numerify('##########'),
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
}

function wizardMapel(string $nama, string $kode): MataPelajaran
{
    return MataPelajaran::create(['nama_mapel' => $nama, 'kode' => $kode]);
}

function wizardMengajar(TahunAjaran $tahun, Kelas $kelas, MataPelajaran $mapel, Guru $guru, int $jtm = 2): Mengajar
{
    return Mengajar::create([
        'tahun_ajaran_id' => $tahun->id,
        'semester' => $tahun->semester,
        'kelas_id' => $kelas->id,
        'mata_pelajaran_id' => $mapel->id,
        'guru_id' => $guru->id,
        'jtm' => $jtm,
        'bobot_sumatif' => 60,
        'bobot_sts' => 40,
    ]);
}

/**
 * Potret seluruh data tahun ajaran sumber, untuk memastikan wizard tidak mengubahnya.
 *
 * @return array<string, mixed>
 */
function wizardPotretTahun(TahunAjaran $tahun): array
{
    return [
        'tahun' => $tahun->fresh()?->only(['nama', 'semester', 'tahun_mulai', 'tahun_selesai']),
        'kelas' => Kelas::where('tahun_ajaran_id', $tahun->id)->orderBy('id')->get()->toArray(),
        'siswa' => Siswa::where('tahun_ajaran_id', $tahun->id)->orderBy('id')->get()->toArray(),
        'mengajar' => Mengajar::where('tahun_ajaran_id', $tahun->id)->orderBy('id')->get()->toArray(),
        'tahfidz' => MengajarTahfidz::where('tahun_ajaran_id', $tahun->id)->orderBy('id')->get()->toArray(),
    ];
}

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
});

describe('lanjut semester (Ganjil ke Genap)', function () {
    beforeEach(function () {
        $this->ganjil = wizardTahun('2026/2027', 'Ganjil', true);
        $this->genap = wizardTahun('2026/2027', 'Genap');

        $this->waliSatu = wizardGuru('Wali Satu');
        $this->waliDua = wizardGuru('Wali Dua');
        $this->guruMapel = wizardGuru('Guru Mapel');

        $this->kelas1A = wizardKelas($this->ganjil, '1A', 'I', $this->waliSatu);
        $this->kelas2A = wizardKelas($this->ganjil, '2A', 'II', $this->waliDua);

        wizardSiswa($this->ganjil, $this->kelas1A, '1001');
        wizardSiswa($this->ganjil, $this->kelas1A, '1002');
        wizardSiswa($this->ganjil, $this->kelas1A, '1003', aktif: false);
        wizardSiswa($this->ganjil, $this->kelas2A, '2001');
        wizardSiswa($this->ganjil, null, '9001');

        $this->mtk = wizardMapel('Matematika', 'MTK');
        $this->bin = wizardMapel('Bahasa Indonesia', 'BIN');
        wizardMengajar($this->ganjil, $this->kelas1A, $this->mtk, $this->guruMapel, 4);
        wizardMengajar($this->ganjil, $this->kelas1A, $this->bin, $this->waliSatu, 5);
        wizardMengajar($this->ganjil, $this->kelas2A, $this->mtk, $this->guruMapel, 3);

        MengajarTahfidz::create([
            'tahun_ajaran_id' => $this->ganjil->id,
            'semester' => 'Ganjil',
            'kelas_id' => $this->kelas1A->id,
            'guru_id' => $this->waliSatu->id,
        ]);

        $this->sesi = [
            'selected_tahun_ajaran_id' => $this->ganjil->id,
            'selected_semester' => 'Ganjil',
            'selected_tahun_ajaran_is_active' => true,
        ];
    });

    it('menampilkan langkah pertama dengan tahun ajaran sesi sebagai sumber', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route('tahun-ajaran-baru.create'))
            ->assertOk()
            ->assertViewHas('sumberId', $this->ganjil->id)
            ->assertSee('2026/2027 Genap');
    });

    it('menampilkan pratinjau lanjut semester', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route('tahun-ajaran-baru.preview', [
                'sumber_id' => $this->ganjil->id,
                'tujuan_pilihan' => 'ada',
                'tujuan_id' => $this->genap->id,
            ]))
            ->assertOk()
            ->assertViewHas('mode', 'lanjut_semester')
            ->assertSee('Wali Satu')
            ->assertSee(__('Lanjut semester'));
    });

    it('menyalin kelas beserta wali, siswa aktif, dan jadwal mengajar ke semester Genap', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), [
                'sumber_id' => $this->ganjil->id,
                'tujuan_pilihan' => 'ada',
                'tujuan_id' => $this->genap->id,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('tahun-ajaran-baru.hasil'))
            ->assertSessionHas('hasilTahunAjaranBaru', fn (array $hasil): bool => $hasil['mode'] === 'lanjut_semester'
                && $hasil['kelas_dibuat'] === 2
                && $hasil['siswa_disalin'] === 3
                && $hasil['siswa_tanpa_kelas'] === 1
                && $hasil['mengajar_disalin'] === 3
                && $hasil['mengajar_tahfidz_disalin'] === 1);

        $kelasBaru = Kelas::where('tahun_ajaran_id', $this->genap->id)->get()->keyBy('nama');

        expect($kelasBaru->keys()->sort()->values()->all())->toBe(['1A', '2A'])
            ->and($kelasBaru['1A']->guru_id)->toBe($this->waliSatu->id)
            ->and($kelasBaru['1A']->tingkat)->toBe('I')
            ->and($kelasBaru['1A']->jenis)->toBe('MI')
            ->and($kelasBaru['2A']->guru_id)->toBe($this->waliDua->id);

        $siswaBaru = Siswa::where('tahun_ajaran_id', $this->genap->id)->get()->keyBy('nis');

        expect($siswaBaru->pluck('nis')->sort()->values()->all())->toBe(['1001', '1002', '2001'])
            ->and($siswaBaru['1001']->kelas_id)->toBe($kelasBaru['1A']->id)
            ->and($siswaBaru['1002']->kelas_id)->toBe($kelasBaru['1A']->id)
            ->and($siswaBaru['2001']->kelas_id)->toBe($kelasBaru['2A']->id)
            ->and($siswaBaru['1001']->nisn)->toBe('001001')
            ->and($siswaBaru['1001']->photo_path)->toBe('siswa/1001.jpg')
            ->and($siswaBaru['1001']->tanggal_lahir?->format('Y-m-d'))->toBe('2018-05-01')
            ->and($siswaBaru['1001']->is_active)->toBeTrue();

        $mengajarBaru = Mengajar::where('tahun_ajaran_id', $this->genap->id)->get();
        $mtk1A = $mengajarBaru->first(fn (Mengajar $m): bool => $m->kelas_id === $kelasBaru['1A']->id && $m->mata_pelajaran_id === $this->mtk->id);

        expect($mengajarBaru)->toHaveCount(3)
            ->and($mengajarBaru->pluck('semester')->unique()->all())->toBe(['Genap'])
            ->and($mtk1A)->not->toBeNull()
            ->and($mtk1A->guru_id)->toBe($this->guruMapel->id)
            ->and($mtk1A->jtm)->toBe(4)
            ->and((float) $mtk1A->bobot_sumatif)->toBe(60.0)
            ->and($mtk1A->uuid)->not->toBeEmpty()
            ->and(Mengajar::where('uuid', $mtk1A->uuid)->count())->toBe(1);

        $tahfidz = MengajarTahfidz::where('tahun_ajaran_id', $this->genap->id)->get();

        expect($tahfidz)->toHaveCount(1)
            ->and($tahfidz->first()->kelas_id)->toBe($kelasBaru['1A']->id)
            ->and($tahfidz->first()->semester)->toBe('Genap')
            ->and($tahfidz->first()->guru_id)->toBe($this->waliSatu->id);
    });

    it('tidak mengubah data tahun ajaran sumber', function () {
        $sebelum = wizardPotretTahun($this->ganjil);

        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), [
                'sumber_id' => $this->ganjil->id,
                'tujuan_pilihan' => 'ada',
                'tujuan_id' => $this->genap->id,
                'aktifkan' => '1',
            ])
            ->assertSessionHasNoErrors();

        expect(wizardPotretTahun($this->ganjil))->toBe($sebelum);
    });

    it('mengaktifkan tahun ajaran tujuan dan memindahkan pilihan sesi bila dicentang', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), [
                'sumber_id' => $this->ganjil->id,
                'tujuan_pilihan' => 'ada',
                'tujuan_id' => $this->genap->id,
                'aktifkan' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('selected_tahun_ajaran_id', $this->genap->id)
            ->assertSessionHas('selected_semester', 'Genap')
            ->assertSessionHas('selected_tahun_ajaran_is_active', true);

        expect($this->genap->fresh()->is_active)->toBeTrue()
            ->and($this->ganjil->fresh()->is_active)->toBeFalse();
    });

    it('tidak mengaktifkan tahun ajaran tujuan bila tidak dicentang', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), [
                'sumber_id' => $this->ganjil->id,
                'tujuan_pilihan' => 'ada',
                'tujuan_id' => $this->genap->id,
                'aktifkan' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('selected_tahun_ajaran_id', $this->ganjil->id);

        expect($this->genap->fresh()->is_active)->toBeFalse()
            ->and($this->ganjil->fresh()->is_active)->toBeTrue();
    });

    it('menampilkan ringkasan hasil setelah dijalankan', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), [
                'sumber_id' => $this->ganjil->id,
                'tujuan_pilihan' => 'ada',
                'tujuan_id' => $this->genap->id,
            ]);

        $this->get(route('tahun-ajaran-baru.hasil'))
            ->assertOk()
            ->assertSee('2026/2027 Genap');
    });

    it('menolak tahun ajaran tujuan yang sudah berisi data', function (string $isi) {
        match ($isi) {
            'kelas' => wizardKelas($this->genap, '1A', 'I'),
            'siswa' => wizardSiswa($this->genap, null, '5001'),
            'mengajar' => wizardMengajar($this->genap, $this->kelas1A, $this->mtk, $this->guruMapel),
        };

        $kelasSebelum = Kelas::count();
        $siswaSebelum = Siswa::count();
        $mengajarSebelum = Mengajar::count();

        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->from(route('tahun-ajaran-baru.create'))
            ->post(route('tahun-ajaran-baru.store'), [
                'sumber_id' => $this->ganjil->id,
                'tujuan_pilihan' => 'ada',
                'tujuan_id' => $this->genap->id,
                'aktifkan' => '1',
            ])
            ->assertRedirect(route('tahun-ajaran-baru.create'))
            ->assertSessionHasErrors('tujuan');

        expect(Kelas::count())->toBe($kelasSebelum)
            ->and(Siswa::count())->toBe($siswaSebelum)
            ->and(Mengajar::count())->toBe($mengajarSebelum)
            ->and($this->genap->fresh()->is_active)->toBeFalse();
    })->with(['kelas', 'siswa', 'mengajar']);

    it('menolak tahun ajaran tujuan berisi data sejak pratinjau', function () {
        wizardKelas($this->genap, '1A', 'I');

        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->from(route('tahun-ajaran-baru.create'))
            ->get(route('tahun-ajaran-baru.preview', [
                'sumber_id' => $this->ganjil->id,
                'tujuan_pilihan' => 'ada',
                'tujuan_id' => $this->genap->id,
            ]))
            ->assertRedirect(route('tahun-ajaran-baru.create'))
            ->assertSessionHasErrors('tujuan');
    });
});

describe('kenaikan kelas (Genap ke Ganjil tahun berikutnya)', function () {
    beforeEach(function () {
        $this->genap = wizardTahun('2025/2026', 'Genap', true);

        $this->wali1A = wizardGuru('Wali 1A');
        $this->wali6A = wizardGuru('Wali 6A');
        $this->guruMtk = wizardGuru('Guru Matematika Kelas 2');
        $this->guruBin = wizardGuru('Guru Bahasa Kelas 1');

        $this->kelas1A = wizardKelas($this->genap, '1A', 'I', $this->wali1A);
        $this->kelas2A = wizardKelas($this->genap, '2A', 'II');
        $this->kelas6A = wizardKelas($this->genap, '6A', 'VI', $this->wali6A);

        $this->naik = wizardSiswa($this->genap, $this->kelas1A, '1001');
        $this->tinggal = wizardSiswa($this->genap, $this->kelas1A, '1002');
        wizardSiswa($this->genap, $this->kelas1A, '1003', aktif: false);
        $this->siswa2A = wizardSiswa($this->genap, $this->kelas2A, '2001');
        $this->lulus = wizardSiswa($this->genap, $this->kelas6A, '6001');

        $this->mtk = wizardMapel('Matematika', 'MTK');
        $this->bin = wizardMapel('Bahasa Indonesia', 'BIN');
        wizardMengajar($this->genap, $this->kelas2A, $this->mtk, $this->guruMtk, 5);
        wizardMengajar($this->genap, $this->kelas1A, $this->bin, $this->guruBin, 6);
        wizardMengajar($this->genap, $this->kelas6A, $this->mtk, $this->wali6A, 4);

        $this->sesi = [
            'selected_tahun_ajaran_id' => $this->genap->id,
            'selected_semester' => 'Genap',
            'selected_tahun_ajaran_is_active' => true,
        ];

        $this->tujuanBaru = [
            'sumber_id' => $this->genap->id,
            'tujuan_pilihan' => 'baru',
            'tujuan_nama' => '2026/2027',
            'tujuan_tahun_mulai' => 2026,
            'tujuan_tahun_selesai' => 2027,
            'tujuan_semester' => 'Ganjil',
        ];
    });

    it('mengusulkan kelas tujuan naik satu tingkat dan lulus untuk tingkat tertinggi', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route('tahun-ajaran-baru.preview', $this->tujuanBaru))
            ->assertOk()
            ->assertViewHas('mode', 'kenaikan_kelas')
            ->assertViewHas('rencana', function (array $rencana): bool {
                $perKelas = collect($rencana['kelas'])->keyBy('nama');

                return $perKelas['1A']['usulan_tujuan'] === '2A'
                    && $perKelas['2A']['usulan_tujuan'] === '3A'
                    && $perKelas['6A']['tertinggi'] === true
                    && $perKelas['1A']['siswa'][0]['pilihan'] === 'naik'
                    && $perKelas['6A']['siswa'][0]['pilihan'] === 'lulus'
                    && count($perKelas['1A']['siswa']) === 2;
            })
            ->assertSee(__('Wali kelas tidak ikut disalin'));

        expect(TahunAjaran::where('nama', '2026/2027')->exists())->toBeFalse();
    });

    it('menaikkan siswa, menyisakan siswa tinggal kelas, dan tidak menyalin siswa lulus', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), $this->tujuanBaru + [
                'pilihan_siswa' => [$this->tinggal->id => 'tinggal'],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('tahun-ajaran-baru.hasil'))
            ->assertSessionHas('hasilTahunAjaranBaru', fn (array $hasil): bool => $hasil['mode'] === 'kenaikan_kelas'
                && $hasil['kelas_dibuat'] === 3
                && $hasil['siswa_disalin'] === 3
                && $hasil['siswa_tinggal'] === 1
                && $hasil['siswa_lulus'] === 1
                && $hasil['mengajar_disalin'] === 2);

        $tujuan = TahunAjaran::where('nama', '2026/2027')->where('semester', 'Ganjil')->firstOrFail();

        expect($tujuan->tahun_mulai)->toBe(2026)
            ->and($tujuan->tahun_selesai)->toBe(2027);

        $kelasBaru = Kelas::where('tahun_ajaran_id', $tujuan->id)->get()->keyBy('nama');

        // 6A semua lulus, jadi kelas 7A tidak dibuat.
        expect($kelasBaru->keys()->sort()->values()->all())->toBe(['1A', '2A', '3A'])
            ->and($kelasBaru['2A']->tingkat)->toBe('II')
            ->and($kelasBaru['3A']->tingkat)->toBe('III')
            ->and($kelasBaru['1A']->tingkat)->toBe('I')
            ->and($kelasBaru->pluck('guru_id')->filter()->all())->toBe([]);

        $siswaBaru = Siswa::where('tahun_ajaran_id', $tujuan->id)->get()->keyBy('nis');

        expect($siswaBaru->pluck('nis')->sort()->values()->all())->toBe(['1001', '1002', '2001'])
            ->and($siswaBaru['1001']->kelas_id)->toBe($kelasBaru['2A']->id)
            ->and($siswaBaru['1002']->kelas_id)->toBe($kelasBaru['1A']->id)
            ->and($siswaBaru['2001']->kelas_id)->toBe($kelasBaru['3A']->id);

        // Jadwal disalin dari kelas sumber bernama sama: guru mapel tetap di tingkatnya.
        $mengajarBaru = Mengajar::where('tahun_ajaran_id', $tujuan->id)->get();

        expect($mengajarBaru)->toHaveCount(2)
            ->and($mengajarBaru->firstWhere('kelas_id', $kelasBaru['2A']->id)?->guru_id)->toBe($this->guruMtk->id)
            ->and($mengajarBaru->firstWhere('kelas_id', $kelasBaru['2A']->id)?->jtm)->toBe(5)
            ->and($mengajarBaru->firstWhere('kelas_id', $kelasBaru['1A']->id)?->guru_id)->toBe($this->guruBin->id)
            ->and($mengajarBaru->firstWhere('kelas_id', $kelasBaru['3A']->id))->toBeNull()
            ->and($mengajarBaru->pluck('semester')->unique()->all())->toBe(['Ganjil']);
    });

    it('memakai nama kelas tujuan yang diubah admin', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), $this->tujuanBaru + [
                'kelas_tujuan' => [$this->kelas1A->id => ' 2B '],
            ])
            ->assertSessionHasNoErrors();

        $tujuan = TahunAjaran::where('nama', '2026/2027')->firstOrFail();
        $kelas2B = Kelas::where('tahun_ajaran_id', $tujuan->id)->where('nama', '2B')->firstOrFail();

        expect($kelas2B->tingkat)->toBe('II')
            ->and(Siswa::where('tahun_ajaran_id', $tujuan->id)->where('nis', '1001')->value('kelas_id'))->toBe($kelas2B->id)
            ->and(Siswa::where('tahun_ajaran_id', $tujuan->id)->where('nis', '1002')->value('kelas_id'))->toBe($kelas2B->id);
    });

    it('menyalin siswa tingkat tertinggi bila admin memilih naik', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), $this->tujuanBaru + [
                'pilihan_siswa' => [$this->lulus->id => 'naik'],
            ])
            ->assertSessionHasNoErrors();

        $tujuan = TahunAjaran::where('nama', '2026/2027')->firstOrFail();

        expect(Kelas::where('tahun_ajaran_id', $tujuan->id)->where('nama', '7A')->value('tingkat'))->toBe('VII')
            ->and(Siswa::where('tahun_ajaran_id', $tujuan->id)->where('nis', '6001')->exists())->toBeTrue();
    });

    it('menolak siswa naik ke kelas tujuan yang dikosongkan dan membatalkan semuanya', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), $this->tujuanBaru + [
                'kelas_tujuan' => [$this->kelas2A->id => ''],
            ])
            ->assertSessionHasErrors('kelas_tujuan.'.$this->kelas2A->id);

        expect(TahunAjaran::where('nama', '2026/2027')->exists())->toBeFalse()
            ->and(Kelas::count())->toBe(3)
            ->and(Siswa::count())->toBe(5);
    });

    it('tidak mengubah data tahun ajaran sumber', function () {
        $sebelum = wizardPotretTahun($this->genap);

        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), $this->tujuanBaru + [
                'pilihan_siswa' => [$this->tinggal->id => 'tinggal'],
                'aktifkan' => '1',
            ])
            ->assertSessionHasNoErrors();

        expect(wizardPotretTahun($this->genap))->toBe($sebelum);
    });

    it('mengaktifkan tahun ajaran baru dan memindahkan pilihan sesi', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), $this->tujuanBaru + ['aktifkan' => '1'])
            ->assertSessionHasNoErrors();

        $tujuan = TahunAjaran::where('nama', '2026/2027')->firstOrFail();

        expect($tujuan->is_active)->toBeTrue()
            ->and($this->genap->fresh()->is_active)->toBeFalse()
            ->and(session('selected_tahun_ajaran_id'))->toBe($tujuan->id)
            ->and(session('selected_semester'))->toBe('Ganjil')
            ->and(session('selected_tahun_ajaran_is_active'))->toBeTrue();
    });

    it('menolak membuat tahun ajaran baru yang namanya sudah ada', function () {
        wizardTahun('2026/2027', 'Ganjil');

        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->post(route('tahun-ajaran-baru.store'), $this->tujuanBaru)
            ->assertSessionHasErrors('tujuan_nama');

        expect(TahunAjaran::where('nama', '2026/2027')->count())->toBe(1);
    });
});

it('menolak kombinasi sumber dan tujuan yang bukan lanjut semester atau kenaikan kelas', function (array $sumber, array $tujuan) {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $tahunSumber = wizardTahun($sumber[0], $sumber[1], true);
    $tahunTujuan = wizardTahun($tujuan[0], $tujuan[1]);
    wizardKelas($tahunSumber, '1A', 'I');

    $this->actingAs($admin)
        ->from(route('tahun-ajaran-baru.create'))
        ->post(route('tahun-ajaran-baru.store'), [
            'sumber_id' => $tahunSumber->id,
            'tujuan_pilihan' => 'ada',
            'tujuan_id' => $tahunTujuan->id,
        ])
        ->assertRedirect(route('tahun-ajaran-baru.create'))
        ->assertSessionHasErrors('tujuan');

    expect(Kelas::where('tahun_ajaran_id', $tahunTujuan->id)->exists())->toBeFalse();
})->with([
    'genap ke ganjil tahun yang sama' => [['2026/2027', 'Genap'], ['2026/2027', 'Ganjil']],
    'ganjil ke ganjil tahun berikutnya' => [['2025/2026', 'Ganjil'], ['2026/2027', 'Ganjil']],
    'ganjil ke genap tahun berikutnya' => [['2025/2026', 'Ganjil'], ['2026/2027', 'Genap']],
    'genap ke genap tahun berikutnya' => [['2025/2026', 'Genap'], ['2026/2027', 'Genap']],
    'melompati satu tahun' => [['2024/2025', 'Genap'], ['2026/2027', 'Ganjil']],
    'mundur ke tahun sebelumnya' => [['2026/2027', 'Genap'], ['2025/2026', 'Ganjil']],
]);

it('menolak tahun ajaran sumber yang belum punya kelas', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $ganjil = wizardTahun('2026/2027', 'Ganjil', true);
    $genap = wizardTahun('2026/2027', 'Genap');

    $this->actingAs($admin)
        ->post(route('tahun-ajaran-baru.store'), [
            'sumber_id' => $ganjil->id,
            'tujuan_pilihan' => 'ada',
            'tujuan_id' => $genap->id,
        ])
        ->assertSessionHasErrors('sumber_id');
});

it('menolak guru membuka atau menjalankan wizard', function () {
    $guru = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $ganjil = wizardTahun('2026/2027', 'Ganjil', true);
    $genap = wizardTahun('2026/2027', 'Genap');
    wizardKelas($ganjil, '1A', 'I');

    $this->actingAs($guru)->get(route('tahun-ajaran-baru.create'))->assertForbidden();
    $this->actingAs($guru)->get(route('tahun-ajaran-baru.preview', [
        'sumber_id' => $ganjil->id,
        'tujuan_pilihan' => 'ada',
        'tujuan_id' => $genap->id,
    ]))->assertForbidden();
    $this->actingAs($guru)->post(route('tahun-ajaran-baru.store'), [
        'sumber_id' => $ganjil->id,
        'tujuan_pilihan' => 'ada',
        'tujuan_id' => $genap->id,
        'aktifkan' => '1',
    ])->assertForbidden();

    expect(Kelas::where('tahun_ajaran_id', $genap->id)->exists())->toBeFalse()
        ->and($genap->fresh()->is_active)->toBeFalse();
});

it('mengusulkan nama kelas tujuan sesuai pola nama kelas sumber', function (string $nama, string $tingkat, string $usulan) {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $genap = wizardTahun('2025/2026', 'Genap', true);
    wizardKelas($genap, $nama, $tingkat);

    $this->actingAs($admin)
        ->get(route('tahun-ajaran-baru.preview', [
            'sumber_id' => $genap->id,
            'tujuan_pilihan' => 'baru',
            'tujuan_nama' => '2026/2027',
            'tujuan_tahun_mulai' => 2026,
            'tujuan_tahun_selesai' => 2027,
            'tujuan_semester' => 'Ganjil',
        ]))
        ->assertOk()
        ->assertViewHas('rencana', fn (array $rencana): bool => $rencana['kelas'][0]['usulan_tujuan'] === $usulan);
})->with([
    'angka' => ['1A', 'I', '2A'],
    'romawi berspasi' => ['VII A', 'VII', 'VIII A'],
    'romawi menempel' => ['VIIB', 'VII', 'VIIIB'],
    'dua digit' => ['9C', '9', '10C'],
    'awalan kelas' => ['Kelas 3B', '3', 'Kelas 4B'],
    'nama tokoh tidak terbaca' => ['Ibnu Sina', 'I', ''],
]);

it('menentukan tingkat tertinggi per jenis kelas (MI dan SMP terpisah)', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $genap = wizardTahun('2025/2026', 'Genap', true);

    foreach ([['5A', 'V', 'MI'], ['6A', 'VI', 'MI'], ['7A', 'VII', 'SMP'], ['9A', 'IX', 'SMP']] as [$nama, $tingkat, $jenis]) {
        Kelas::create(['tahun_ajaran_id' => $genap->id, 'nama' => $nama, 'tingkat' => $tingkat, 'jenis' => $jenis]);
    }

    $this->actingAs($admin)
        ->get(route('tahun-ajaran-baru.preview', [
            'sumber_id' => $genap->id,
            'tujuan_pilihan' => 'baru',
            'tujuan_nama' => '2026/2027',
            'tujuan_tahun_mulai' => 2026,
            'tujuan_tahun_selesai' => 2027,
            'tujuan_semester' => 'Ganjil',
        ]))
        ->assertOk()
        ->assertViewHas('rencana', function (array $rencana): bool {
            $tertinggi = collect($rencana['kelas'])->pluck('tertinggi', 'nama')->all();

            return $tertinggi === ['5A' => false, '6A' => true, '7A' => false, '9A' => true];
        });
});

it('menolak pilihan siswa yang tidak dikenal', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $genap = wizardTahun('2025/2026', 'Genap', true);
    $siswa = wizardSiswa($genap, wizardKelas($genap, '1A', 'I'), '1001');

    $this->actingAs($admin)
        ->post(route('tahun-ajaran-baru.store'), [
            'sumber_id' => $genap->id,
            'tujuan_pilihan' => 'baru',
            'tujuan_nama' => '2026/2027',
            'tujuan_tahun_mulai' => 2026,
            'tujuan_tahun_selesai' => 2027,
            'tujuan_semester' => 'Ganjil',
            'pilihan_siswa' => [$siswa->id => 'pindah'],
        ])
        ->assertSessionHasErrors('pilihan_siswa.'.$siswa->id);

    expect(TahunAjaran::count())->toBe(1);
});

it('mengarahkan halaman hasil ke langkah pertama bila wizard belum dijalankan', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->get(route('tahun-ajaran-baru.hasil'))
        ->assertRedirect(route('tahun-ajaran-baru.create'));
});
