<?php

declare(strict_types=1);

use App\Models\Ekskul;
use App\Models\EkskulPenilaian;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Membuat tahun ajaran untuk pengujian perbaikan aplikasi guru.
 */
function buatTahunPerbaikan(string $nama, bool $aktif): TahunAjaran
{
    return TahunAjaran::create([
        'nama' => $nama,
        'tahun_mulai' => (int) substr($nama, 0, 4),
        'tahun_selesai' => (int) substr($nama, 5, 4),
        'semester' => 'Ganjil',
        'is_active' => $aktif,
    ]);
}

/**
 * Membuat kelas beserta penugasan mengajar guru pada kelas tersebut.
 */
function buatPenugasanPerbaikan(TahunAjaran $tahun, Guru $guru, string $namaKelas, string $kodeMapel): Mengajar
{
    $kelas = Kelas::create([
        'nama' => $namaKelas,
        'tingkat' => 'I',
        'tahun_ajaran_id' => $tahun->id,
    ]);

    $mapel = MataPelajaran::create([
        'nama_mapel' => 'Mapel '.$kodeMapel,
        'kode' => $kodeMapel,
    ]);

    return Mengajar::create([
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $kelas->id,
        'mata_pelajaran_id' => $mapel->id,
        'guru_id' => $guru->id,
        'jtm' => 4,
    ]);
}

/**
 * Mengisi siswa sebuah kelas; sebagian di antaranya diberi nilai lengkap.
 */
function isiKelasPerbaikan(Mengajar $mengajar, int $jumlahSiswa, int $jumlahLengkap): void
{
    $siswas = Siswa::factory()->count($jumlahSiswa)->create([
        'kelas_id' => $mengajar->kelas_id,
        'tahun_ajaran_id' => $mengajar->tahun_ajaran_id,
    ]);

    $siswas->take($jumlahLengkap)->each(function (Siswa $siswa) use ($mengajar): void {
        Penilaian::create([
            'tahun_ajaran_id' => $mengajar->tahun_ajaran_id,
            'semester' => 'Ganjil',
            'kelas_id' => $mengajar->kelas_id,
            'siswa_id' => $siswa->id,
            'mata_pelajaran_id' => $mengajar->mata_pelajaran_id,
            'guru_id' => $mengajar->guru_id,
            'mengajar_id' => $mengajar->id,
            'nilai_sumatif' => 80,
            'nilai_sts' => 80,
        ]);
    });
}

beforeEach(function () {
    $this->tahun = buatTahunPerbaikan('2026/2027', true);

    $this->guru = Guru::factory()->create(['nama' => 'Ustadz Ahmad']);
    $this->userGuru = User::findOrFail($this->guru->user_id);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];
});

it('tidak menampilkan penugasan yang sudah lengkap di lanjutkan pengisian dan mengurutkan menurut persentase', function () {
    // Urutan pembuatan = urutan kelas_id, sehingga urutan awal: lima, dua, lengkap.
    $limaSiswa = buatPenugasanPerbaikan($this->tahun, $this->guru, '1A', 'MTK');
    isiKelasPerbaikan($limaSiswa, 5, 2); // 40%, lengkap = 2

    $duaSiswa = buatPenugasanPerbaikan($this->tahun, $this->guru, '1B', 'BIN');
    isiKelasPerbaikan($duaSiswa, 2, 1); // 50%, lengkap = 1

    $sudahLengkap = buatPenugasanPerbaikan($this->tahun, $this->guru, '1C', 'IPA');
    isiKelasPerbaikan($sudahLengkap, 1, 1); // 100%

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.beranda'))
        ->assertOk()
        ->assertViewHas('lanjutkan', function (Collection $lanjutkan) use ($limaSiswa, $duaSiswa): bool {
            return $lanjutkan->pluck('mengajar.id')->all() === [$limaSiswa->id, $duaSiswa->id];
        });
});

it('hanya menawarkan siswa aktif dari tahun ajaran terpilih saat menambah peserta ekskul', function () {
    $ekskul = Ekskul::create(['nama' => 'Pramuka', 'guru_id' => $this->guru->id]);
    $kelas = Kelas::create(['nama' => '2A', 'tingkat' => 'II', 'tahun_ajaran_id' => $this->tahun->id]);

    $tahunLama = buatTahunPerbaikan('2025/2026', false);
    $kelasLama = Kelas::create(['nama' => '1A', 'tingkat' => 'I', 'tahun_ajaran_id' => $tahunLama->id]);

    $aktif = Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Aisyah Tahun Ini',
    ]);

    $nonaktif = Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Bilal Nonaktif',
        'is_active' => false,
    ]);

    $salinanLama = Siswa::factory()->create([
        'kelas_id' => $kelasLama->id,
        'tahun_ajaran_id' => $tahunLama->id,
        'nama' => 'Aisyah Tahun Lalu',
    ]);

    $sudahPeserta = Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Hafiz Peserta',
    ]);

    EkskulPenilaian::create([
        'ekskul_id' => $ekskul->id,
        'guru_id' => $this->guru->id,
        'siswa_id' => $sudahPeserta->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.ekskul.form', $ekskul))
        ->assertOk()
        ->assertViewHas('tersedia', function (Collection $tersedia) use ($aktif): bool {
            return $tersedia->pluck('id')->all() === [$aktif->id];
        })
        ->assertDontSee($nonaktif->nama)
        ->assertDontSee($salinanLama->nama);
});

it('tidak menawarkan siswa apa pun untuk ekskul bila tahun ajaran belum dipilih', function () {
    $ekskul = Ekskul::create(['nama' => 'Pramuka', 'guru_id' => $this->guru->id]);
    $kelas = Kelas::create(['nama' => '2A', 'tingkat' => 'II', 'tahun_ajaran_id' => $this->tahun->id]);

    Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $this->actingAs($this->userGuru)
        ->get(route('guru.pwa.ekskul.form', $ekskul))
        ->assertOk()
        ->assertViewHas('tersedia', fn (Collection $tersedia): bool => $tersedia->isEmpty());
});

it('menampilkan kembali isian nilai setelah penyimpanan gagal', function () {
    $mengajar = buatPenugasanPerbaikan($this->tahun, $this->guru, '1A', 'MTK');

    $salah = Siswa::factory()->create([
        'kelas_id' => $mengajar->kelas_id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Aisyah Nur',
    ]);

    $benar = Siswa::factory()->create([
        'kelas_id' => $mengajar->kelas_id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Bilal Abdurrahman',
    ]);

    Penilaian::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $mengajar->kelas_id,
        'siswa_id' => $benar->id,
        'mata_pelajaran_id' => $mengajar->mata_pelajaran_id,
        'guru_id' => $this->guru->id,
        'mengajar_id' => $mengajar->id,
        'nilai_sumatif' => 60,
        'nilai_sts' => 61,
        'materi_tp' => 'Materi lama',
    ]);

    $respons = $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->from(route('guru.pwa.nilai.form', $mengajar))
        ->followingRedirects()
        ->post(route('guru.penilaian.store', $mengajar), [
            'tahun_ajaran_id' => $this->tahun->id,
            'semester' => 'Ganjil',
            'nilai_sumatif' => [$salah->id => '150', $benar->id => '77'],
            'nilai_sts' => [$salah->id => '70', $benar->id => '78.5'],
            'materi_tp' => 'Bab 4 — Pecahan',
        ]);

    $respons->assertOk()
        ->assertSee('Bab 4 — Pecahan')
        ->assertDontSee('Materi lama')
        ->assertSeeInOrder(['name="nilai_sumatif['.$salah->id.']"', 'value="150"'], false)
        ->assertSeeInOrder(['name="nilai_sumatif['.$benar->id.']"', 'value="77"'], false)
        ->assertSeeInOrder(['name="nilai_sts['.$benar->id.']"', 'value="78.5"'], false)
        ->assertDontSee('value="60"', false);

    expect(Penilaian::where('siswa_id', $benar->id)->value('nilai_sumatif'))->toEqual(60);
});

it('mengirim konteks tahun ajaran dan semester bersama form nilai', function () {
    $mengajar = buatPenugasanPerbaikan($this->tahun, $this->guru, '1A', 'MTK');

    Siswa::factory()->create([
        'kelas_id' => $mengajar->kelas_id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai.form', $mengajar))
        ->assertOk()
        ->assertSee('name="tahun_ajaran_id" value="'.$this->tahun->id.'"', false)
        ->assertSee('name="semester" value="Ganjil"', false)
        ->assertSee('href="'.route('guru.pwa.akun').'#bobot"', false)
        ->assertDontSee(route('penilaian.bobot.edit'), false);
});

it('meminta draf nilai di perangkat dihapus hanya setelah penyimpanan berhasil', function () {
    $mengajar = buatPenugasanPerbaikan($this->tahun, $this->guru, '1A', 'MTK');

    $siswa = Siswa::factory()->create([
        'kelas_id' => $mengajar->kelas_id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    // Js::from() menulis tanda kutip sebagai \u0022 di dalam JSON.parse('...').
    $kutip = chr(92).'u0022';
    $kunci = $kutip.'kunciDraft'.$kutip.':'.$kutip.'nilai-draft:'.$mengajar->id.':'.$this->tahun->id.':Ganjil'.$kutip;

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai.form', $mengajar))
        ->assertOk()
        ->assertSee($kunci, false)
        ->assertSee($kutip.'hapusDraft'.$kutip.':false', false);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->from(route('guru.pwa.nilai.form', $mengajar))
        ->followingRedirects()
        ->post(route('guru.penilaian.store', $mengajar), [
            'tahun_ajaran_id' => $this->tahun->id,
            'semester' => 'Ganjil',
            'nilai_sumatif' => [$siswa->id => '90'],
            'nilai_sts' => [$siswa->id => '80'],
        ])
        ->assertOk()
        ->assertSee($kutip.'hapusDraft'.$kutip.':true', false);
});

it('menampilkan kembali nilai dan catatan ekskul setelah penyimpanan gagal', function () {
    $ekskul = Ekskul::create(['nama' => 'Pramuka', 'guru_id' => $this->guru->id]);
    $kelas = Kelas::create(['nama' => '2A', 'tingkat' => 'II', 'tahun_ajaran_id' => $this->tahun->id]);

    $siswa = Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    EkskulPenilaian::create([
        'ekskul_id' => $ekskul->id,
        'guru_id' => $this->guru->id,
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'nilai' => 70,
        'catatan' => 'Catatan lama',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->from(route('guru.pwa.ekskul.form', $ekskul))
        ->followingRedirects()
        ->post(route('guru.ekskul.store', $ekskul), [
            'nilai' => [$siswa->id => '150'],
            'catatan' => [$siswa->id => 'Catatan baru belum tersimpan'],
        ])
        ->assertOk()
        ->assertSeeInOrder(['name="nilai['.$siswa->id.']"', 'value="150"'], false)
        ->assertSee('Catatan baru belum tersimpan')
        ->assertDontSee('Catatan lama');
});

it('memakai versi cache service worker yang baru', function () {
    expect(file_get_contents(public_path('sw.js')))
        ->toContain('eraport-guru-v2')
        ->not->toContain('eraport-guru-v1');
});
