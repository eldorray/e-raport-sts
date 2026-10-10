<?php

declare(strict_types=1);

use App\Models\Ekskul;
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
 * Membuat satu baris tahun ajaran (satu baris = satu semester).
 */
function buatTahunPeningkatan(string $nama, string $semester, bool $aktif): TahunAjaran
{
    return TahunAjaran::create([
        'nama' => $nama,
        'tahun_mulai' => (int) substr($nama, 0, 4),
        'tahun_selesai' => (int) substr($nama, 5, 4),
        'semester' => $semester,
        'is_active' => $aktif,
    ]);
}

/**
 * Membuat kelas dan penugasan mengajar guru pada tahun ajaran tertentu.
 */
function buatPenugasanPeningkatan(TahunAjaran $tahun, Guru $guru, string $namaKelas): Mengajar
{
    $kelas = Kelas::create([
        'nama' => $namaKelas,
        'tingkat' => 'I',
        'tahun_ajaran_id' => $tahun->id,
    ]);

    $mapel = MataPelajaran::create([
        'nama_mapel' => 'Matematika '.$namaKelas,
        'kode' => 'MTK'.$namaKelas,
    ]);

    return Mengajar::create([
        'tahun_ajaran_id' => $tahun->id,
        'semester' => $tahun->semester,
        'kelas_id' => $kelas->id,
        'mata_pelajaran_id' => $mapel->id,
        'guru_id' => $guru->id,
        'jtm' => 4,
    ]);
}

beforeEach(function () {
    $this->tahun = buatTahunPeningkatan('2026/2027', 'Ganjil', true);
    $this->tahunGenap = buatTahunPeningkatan('2026/2027', 'Genap', false);
    $this->tahunLama = buatTahunPeningkatan('2025/2026', 'Genap', false);

    $this->guru = Guru::factory()->create(['nama' => 'Ustadz Ahmad']);
    $this->userGuru = User::findOrFail($this->guru->user_id);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];
});

describe('pemilih tahun ajaran', function () {
    it('mendaftar semua tahun ajaran beserta semesternya dan mengirim ke switch-session', function (string $rute) {
        $respons = $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route($rute))
            ->assertOk()
            ->assertViewHas('daftarTahun', fn (Collection $daftar): bool => $daftar->pluck('id')->sort()->values()->all() === [
                $this->tahun->id,
                $this->tahunGenap->id,
                $this->tahunLama->id,
            ]);

        $respons->assertSee('action="'.route('tahun-ajaran.switch-session').'"', false)
            ->assertSee('name="_method" value="PATCH"', false)
            ->assertSee('name="tahun_ajaran_id" value="'.$this->tahun->id.'"', false)
            ->assertSee('name="tahun_ajaran_id" value="'.$this->tahunGenap->id.'"', false)
            ->assertSee('name="tahun_ajaran_id" value="'.$this->tahunLama->id.'"', false)
            ->assertSee('2025/2026')
            ->assertSee('Genap')
            ->assertSee('data-tahun-terpilih="'.$this->tahun->id.'"', false)
            ->assertSee('data-tahun-aktif="'.$this->tahun->id.'"', false)
            ->assertDontSee('data-tahun-aktif="'.$this->tahunLama->id.'"', false);
    })->with([
        'beranda' => 'guru.pwa.beranda',
        'nilai' => 'guru.pwa.nilai',
        'ekskul' => 'guru.pwa.ekskul',
        'wali' => 'guru.pwa.wali',
        'akun' => 'guru.pwa.akun',
    ]);

    it('menampilkan bagian tahun ajaran pada halaman akun', function () {
        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('guru.pwa.akun'))
            ->assertOk()
            ->assertSee('Tahun ajaran & semester')
            ->assertSee('Ganti tahun ajaran');
    });

    it('mengarahkan kembali ke halaman aplikasi setelah tahun ajaran diganti', function () {
        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->from(route('guru.pwa.nilai'))
            ->patch(route('tahun-ajaran.switch-session'), ['tahun_ajaran_id' => $this->tahunGenap->id])
            ->assertRedirect(route('guru.pwa.nilai'))
            ->assertSessionHas('selected_tahun_ajaran_id', $this->tahunGenap->id)
            ->assertSessionHas('selected_semester', 'Genap');
    });

    it('mengganti tautan Buka Dashboard dengan pemilih tahun ajaran saat belum ada pilihan', function (string $rute) {
        $this->actingAs($this->userGuru)
            ->get(route($rute))
            ->assertOk()
            ->assertSee('Tahun ajaran belum dipilih')
            ->assertSee('Pilih Tahun Ajaran')
            ->assertDontSee('Buka Dashboard')
            ->assertDontSee('href="'.route('dashboard').'"', false);
    })->with([
        'beranda' => 'guru.pwa.beranda',
        'nilai' => 'guru.pwa.nilai',
    ]);

    it('tidak memasang pemilih tahun ajaran di form nilai agar tidak berpindah ke penilaian tahun lain', function () {
        $mengajar = buatPenugasanPeningkatan($this->tahun, $this->guru, '1A');

        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('guru.pwa.nilai.form', $mengajar))
            ->assertOk()
            ->assertDontSee('action="'.route('tahun-ajaran.switch-session').'"', false);
    });
});

describe('menu wali pada navigasi bawah', function () {
    it('menyembunyikan menu Wali bagi guru yang bukan wali kelas', function () {
        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('guru.pwa.beranda'))
            ->assertOk()
            ->assertSee('aria-label="Menu utama"', false)
            ->assertSee('grid-cols-4', false)
            ->assertDontSee('href="'.route('guru.pwa.wali').'"', false);
    });

    it('menampilkan menu Wali bagi wali kelas pada tahun ajaran terpilih', function () {
        Kelas::create([
            'nama' => '4C',
            'tingkat' => 'IV',
            'tahun_ajaran_id' => $this->tahun->id,
            'guru_id' => $this->guru->id,
        ]);

        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('guru.pwa.ekskul'))
            ->assertOk()
            ->assertSee('grid-cols-5', false)
            ->assertSee('href="'.route('guru.pwa.wali').'"', false);
    });

    it('tidak menghitung kelas wali dari tahun ajaran lain', function () {
        Kelas::create([
            'nama' => '4C',
            'tingkat' => 'IV',
            'tahun_ajaran_id' => $this->tahunLama->id,
            'guru_id' => $this->guru->id,
        ]);

        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('guru.pwa.akun'))
            ->assertOk()
            ->assertViewHas('adaKelasWali', false)
            ->assertDontSee('href="'.route('guru.pwa.wali').'"', false);
    });
});

describe('form nilai ringkas', function () {
    beforeEach(function () {
        $this->mengajar = buatPenugasanPeningkatan($this->tahun, $this->guru, '1A');

        $this->siswas = collect(['Aisyah Nur', 'Bilal Abdurrahman', 'Hafiz Alfatih'])
            ->map(fn (string $nama, int $urut): Siswa => Siswa::factory()->create([
                'kelas_id' => $this->mengajar->kelas_id,
                'tahun_ajaran_id' => $this->tahun->id,
                'nama' => $nama,
                'nis' => '700'.$urut,
            ]));
    });

    it('tetap memuat semua kolom, data tersembunyi, dan kunci draf', function () {
        $respons = $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('guru.pwa.nilai.form', $this->mengajar))
            ->assertOk();

        foreach ($this->siswas as $siswa) {
            $respons->assertSee('data-siswa="'.$siswa->id.'"', false)
                ->assertSee('name="nilai_sumatif['.$siswa->id.']"', false)
                ->assertSee('name="nilai_sts['.$siswa->id.']"', false)
                ->assertSee('NIS '.$siswa->nis);
        }

        $kutip = chr(92).'u0022';

        $respons->assertSee('name="tahun_ajaran_id" value="'.$this->tahun->id.'"', false)
            ->assertSee('name="semester" value="Ganjil"', false)
            ->assertSee('name="materi_tp"', false)
            ->assertSee($kutip.'kunciDraft'.$kutip.':'.$kutip.'nilai-draft:'.$this->mengajar->id.':'.$this->tahun->id.':Ganjil'.$kutip, false)
            ->assertSee('data-nilai-akhir', false)
            ->assertSee('kolomBerikut($event)', false)
            ->assertSee('normalisasi($event)', false)
            ->assertSee('enterkeyhint="next"', false)
            ->assertSee('inputmode="decimal"', false)
            ->assertSee('Isi Cepat')
            ->assertSee('Pulihkan');
    });

    it('menampilkan satu baris per siswa dengan kolom nilai setinggi target sentuh', function () {
        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('guru.pwa.nilai.form', $this->mengajar))
            ->assertOk()
            ->assertSee('data-baris-ringkas', false)
            ->assertSee('aria-label="Sumatif Aisyah Nur"', false)
            ->assertSee('aria-label="STS Aisyah Nur"', false)
            ->assertDontSee('mt-3 grid grid-cols-2 gap-2', false);
    });

    it('menyembunyikan navigasi bawah di form nilai', function () {
        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('guru.pwa.nilai.form', $this->mengajar))
            ->assertOk()
            ->assertDontSee('aria-label="Menu utama"', false)
            ->assertSee('href="'.route('guru.pwa.nilai').'"', false);
    });

    it('menyembunyikan navigasi bawah di form ekskul', function () {
        $ekskul = Ekskul::create(['nama' => 'Pramuka', 'guru_id' => $this->guru->id]);

        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('guru.pwa.ekskul.form', $ekskul))
            ->assertOk()
            ->assertDontSee('aria-label="Menu utama"', false)
            ->assertSee('href="'.route('guru.pwa.ekskul').'"', false);
    });
});

it('menampilkan jumlah siswa lengkap yang benar pada daftar nilai', function () {
    $mengajar = buatPenugasanPeningkatan($this->tahun, $this->guru, '2B');

    $siswas = Siswa::factory()->count(3)->create([
        'kelas_id' => $mengajar->kelas_id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $siswas->take(2)->each(fn (Siswa $siswa) => Penilaian::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $mengajar->kelas_id,
        'siswa_id' => $siswa->id,
        'mata_pelajaran_id' => $mengajar->mata_pelajaran_id,
        'guru_id' => $this->guru->id,
        'mengajar_id' => $mengajar->id,
        'nilai_sumatif' => 80,
        'nilai_sts' => 85,
    ]));

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai'))
        ->assertOk()
        ->assertSee('2 dari 3 siswa lengkap')
        ->assertSee('Sebagian');
});
