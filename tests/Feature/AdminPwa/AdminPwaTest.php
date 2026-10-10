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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Membuat satu baris tahun ajaran (satu baris = satu semester).
 */
function buatTahunAdminPwa(string $nama, string $semester, bool $aktif): TahunAjaran
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
 * Membuat penugasan mengajar pada kelas tertentu.
 */
function buatJadwalAdminPwa(TahunAjaran $tahun, Kelas $kelas, Guru $guru, string $kodeMapel): Mengajar
{
    $mapel = MataPelajaran::firstOrCreate(['kode' => $kodeMapel], ['nama_mapel' => 'Mapel '.$kodeMapel]);

    return Mengajar::create([
        'tahun_ajaran_id' => $tahun->id,
        'semester' => $tahun->semester,
        'kelas_id' => $kelas->id,
        'mata_pelajaran_id' => $mapel->id,
        'guru_id' => $guru->id,
        'jtm' => 2,
    ]);
}

/**
 * Menyimpan nilai satu siswa untuk satu penugasan.
 */
function nilaiAdminPwa(Mengajar $mengajar, Siswa $siswa, ?float $sumatif, ?float $sts): void
{
    Penilaian::create([
        'tahun_ajaran_id' => $mengajar->tahun_ajaran_id,
        'semester' => $mengajar->semester,
        'kelas_id' => $mengajar->kelas_id,
        'siswa_id' => $siswa->id,
        'mata_pelajaran_id' => $mengajar->mata_pelajaran_id,
        'guru_id' => $mengajar->guru_id,
        'mengajar_id' => $mengajar->id,
        'nilai_sumatif' => $sumatif,
        'nilai_sts' => $sts,
    ]);
}

beforeEach(function () {
    $this->tahun = buatTahunAdminPwa('2026/2027', 'Ganjil', true);
    $this->tahunLain = buatTahunAdminPwa('2025/2026', 'Genap', false);

    $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Madrasah', 'email' => 'admin@madrasah.test']);
    $this->guru = Guru::factory()->create(['nama' => 'Ustadz Ahmad']);
    $this->userGuru = User::findOrFail($this->guru->user_id);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];
});

describe('akses', function () {
    it('membuka halaman aplikasi admin untuk admin', function (string $rute) {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route($rute))
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee("navigator.serviceWorker.register('/sw.js')", false);
    })->with([
        'beranda' => 'admin.pwa.beranda',
        'menu' => 'admin.pwa.menu',
        'akun' => 'admin.pwa.akun',
    ]);

    it('menolak guru membuka aplikasi admin', function (string $rute) {
        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route($rute))
            ->assertForbidden();
    })->with([
        'beranda' => 'admin.pwa.beranda',
        'menu' => 'admin.pwa.menu',
        'akun' => 'admin.pwa.akun',
    ]);

    it('mengarahkan tamu ke halaman masuk', function () {
        $this->get(route('admin.pwa.beranda'))->assertRedirect(route('login'));
    });
});

describe('pintu masuk /app', function () {
    it('mengarahkan admin ke aplikasi admin', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get('/app')
            ->assertRedirect(route('admin.pwa.beranda'));
    });

    it('mengarahkan guru ke aplikasi guru', function () {
        $this->actingAs($this->userGuru)
            ->withSession($this->sesi)
            ->get(route('app.start'))
            ->assertRedirect(route('guru.pwa.beranda'));
    });

    it('meminta tamu masuk terlebih dahulu', function () {
        $this->get('/app')->assertRedirect(route('login'));
    });

    it('memakai /app sebagai start_url manifest dan menjaga identitas aplikasi lama', function () {
        $manifest = json_decode((string) file_get_contents(public_path('manifest.webmanifest')), true);

        expect($manifest['start_url'])->toBe('/app')
            ->and($manifest['id'])->toBe('/guru-app')
            ->and($manifest['scope'])->toBe('/');
    });
});

describe('navigasi bawah admin', function () {
    it('memuat Beranda, Nilai, Rapor, Menu, dan Akun', function () {
        $respons = $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route('admin.pwa.beranda'))
            ->assertOk()
            ->assertSee('aria-label="Menu utama"', false)
            ->assertSee('grid-cols-5', false)
            ->assertDontSee('href="'.route('guru.pwa.beranda').'"', false);

        foreach (['admin.pwa.beranda', 'koreksi-nilai.index', 'rapor.index', 'admin.pwa.menu', 'admin.pwa.akun'] as $rute) {
            $respons->assertSee('href="'.route($rute).'"', false);
        }
    });

    it('memasang prefetch untuk halaman aplikasi admin', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route('admin.pwa.beranda'))
            ->assertSee('/admin-app*', false)
            ->assertSee('/guru-app*', false);
    });
});

describe('beranda', function () {
    it('menghitung kelengkapan nilai per penugasan, per kelas, dan per guru', function () {
        $guruKedua = Guru::factory()->create(['nama' => 'Ustadzah Fatimah']);

        $kelasA = Kelas::create(['nama' => '1A', 'tingkat' => 'I', 'tahun_ajaran_id' => $this->tahun->id]);
        $kelasB = Kelas::create(['nama' => '2A', 'tingkat' => 'II', 'tahun_ajaran_id' => $this->tahun->id]);
        $kelasKosong = Kelas::create(['nama' => '3A', 'tingkat' => 'III', 'tahun_ajaran_id' => $this->tahun->id]);

        $siswaA = Siswa::factory()->count(3)->create(['kelas_id' => $kelasA->id, 'tahun_ajaran_id' => $this->tahun->id]);
        $siswaB = Siswa::factory()->count(2)->create(['kelas_id' => $kelasB->id, 'tahun_ajaran_id' => $this->tahun->id]);

        // 1A Matematika (Ustadz Ahmad): 2 lengkap, 1 hanya sumatif -> belum lengkap.
        $mtkA = buatJadwalAdminPwa($this->tahun, $kelasA, $this->guru, 'MTK');
        nilaiAdminPwa($mtkA, $siswaA[0], 80, 85);
        nilaiAdminPwa($mtkA, $siswaA[1], 70, 75);
        nilaiAdminPwa($mtkA, $siswaA[2], 90, null);

        // 1A IPA (Ustadzah Fatimah): belum ada nilai.
        buatJadwalAdminPwa($this->tahun, $kelasA, $guruKedua, 'IPA');

        // 2A Matematika (Ustadz Ahmad): lengkap semua.
        $mtkB = buatJadwalAdminPwa($this->tahun, $kelasB, $this->guru, 'MTK');
        nilaiAdminPwa($mtkB, $siswaB[0], 80, 80);
        nilaiAdminPwa($mtkB, $siswaB[1], 88, 90);

        // Nilai milik siswa yang sudah pindah kelas tidak ikut dihitung.
        $siswaPindah = Siswa::factory()->create(['kelas_id' => $kelasKosong->id, 'tahun_ajaran_id' => $this->tahun->id]);
        nilaiAdminPwa($mtkB, $siswaPindah, 90, 90);

        // Penugasan tahun ajaran lain tidak ikut dihitung.
        $kelasLama = Kelas::create(['nama' => '1A', 'tingkat' => 'I', 'tahun_ajaran_id' => $this->tahunLain->id]);
        $siswaLama = Siswa::factory()->create(['kelas_id' => $kelasLama->id, 'tahun_ajaran_id' => $this->tahunLain->id]);
        $mtkLama = buatJadwalAdminPwa($this->tahunLain, $kelasLama, $this->guru, 'MTK');
        nilaiAdminPwa($mtkLama, $siswaLama, 80, 80);

        $respons = $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route('admin.pwa.beranda'))
            ->assertOk()
            ->assertViewHas('ringkasan', fn (array $ringkasan): bool => $ringkasan === [
                'target' => 8,
                'lengkap' => 4,
                'persen' => 50,
                'penugasan' => 3,
                'kelas' => 3,
                'siswa' => 6,
            ])
            ->assertViewHas('perKelas', function (Collection $perKelas) use ($kelasA, $kelasB, $kelasKosong): bool {
                $ringkas = $perKelas->map(fn (array $baris): array => [
                    $baris['kelas']->id,
                    $baris['penugasan'],
                    $baris['target'],
                    $baris['lengkap'],
                ])->all();

                return $ringkas === [
                    [$kelasA->id, 2, 6, 2],
                    [$kelasB->id, 1, 2, 2],
                    [$kelasKosong->id, 0, 0, 0],
                ];
            })
            ->assertViewHas('guruBelumLengkap', function (Collection $daftar) use ($guruKedua): bool {
                $ringkas = $daftar->map(fn (array $baris): array => [$baris['nama'], $baris['belum'], $baris['penugasan']])->all();

                return $ringkas === [
                    ['Ustadz Ahmad', 1, 2],
                    [$guruKedua->nama, 1, 1],
                ];
            })
            ->assertViewHas('jumlahGuruBelumLengkap', 2);

        $respons->assertSee('Aplikasi Admin')
            ->assertSee('href="'.route('koreksi-nilai.index', ['kelas' => $kelasA->id]).'"', false)
            ->assertSee('href="'.route('koreksi-nilai.index', ['kelas' => $kelasB->id]).'"', false)
            ->assertSee('Guru dengan nilai belum lengkap')
            ->assertSee('Ustadzah Fatimah')
            ->assertSee('50%');
    });

    it('memakai pemilih tahun ajaran yang sama dengan aplikasi guru', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route('admin.pwa.beranda'))
            ->assertOk()
            ->assertSee('action="'.route('tahun-ajaran.switch-session').'"', false)
            ->assertSee('name="tahun_ajaran_id" value="'.$this->tahunLain->id.'"', false)
            ->assertSee('data-tahun-terpilih="'.$this->tahun->id.'"', false);
    });

    it('meminta admin memilih tahun ajaran bila sesi belum berisi pilihan', function () {
        $this->actingAs($this->admin)
            ->get(route('admin.pwa.beranda'))
            ->assertOk()
            ->assertSee('Tahun ajaran belum dipilih')
            ->assertSee('Pilih Tahun Ajaran')
            ->assertViewHas('ringkasan', fn (array $ringkasan): bool => $ringkasan['target'] === 0);
    });

    it('menghitung kelengkapan dengan jumlah kueri yang tetap', function () {
        $kelas = Kelas::create(['nama' => '1A', 'tingkat' => 'I', 'tahun_ajaran_id' => $this->tahun->id]);
        $siswa = Siswa::factory()->count(2)->create(['kelas_id' => $kelas->id, 'tahun_ajaran_id' => $this->tahun->id]);
        nilaiAdminPwa(buatJadwalAdminPwa($this->tahun, $kelas, $this->guru, 'MTK'), $siswa[0], 80, 80);

        $hitungKueri = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $this->actingAs($this->admin)
                ->withSession($this->sesi)
                ->get(route('admin.pwa.beranda'))
                ->assertOk();

            $jumlah = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $jumlah;
        };

        $sebelum = $hitungKueri();

        foreach (range(1, 4) as $urut) {
            $kelasTambahan = Kelas::create(['nama' => ($urut + 1).'B', 'tingkat' => 'II', 'tahun_ajaran_id' => $this->tahun->id]);
            Siswa::factory()->create(['kelas_id' => $kelasTambahan->id, 'tahun_ajaran_id' => $this->tahun->id]);
            buatJadwalAdminPwa($this->tahun, $kelasTambahan, Guru::factory()->create(), 'MTK');
        }

        expect($hitungKueri())->toBe($sebelum);
    });
});

describe('menu', function () {
    it('memuat semua menu admin versi web', function () {
        $respons = $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route('admin.pwa.menu'))
            ->assertOk();

        $menu = [
            'dashboard' => 'Dashboard',
            'school-profile.index' => 'Profil Sekolah',
            'tahun-ajaran.index' => 'Tahun Ajaran',
            'tahun-ajaran-baru.create' => 'Tahun Ajaran Baru',
            'mata-pelajaran.index' => 'Mata Pelajaran',
            'kelas.index' => 'Kelas',
            'ekskul.index' => 'Ekskul',
            'guru.index' => 'Guru',
            'mengajar.index' => 'Mengajar',
            'mengajar-tahfidz.index' => 'Mengajar Tahfidz',
            'siswa.index' => 'Siswa',
            'rombel.index' => 'Rombel Kelas',
            'users.index' => 'Manajemen User',
            'rapor.print-settings.edit' => 'Pengaturan Cetak',
            'rapor.index' => 'Cetak Rapor',
            'koreksi-nilai.index' => 'Koreksi Nilai',
            'tahfidz.index' => 'Raport Tahfidz',
            'backup.index' => 'Backup',
            'settings.appearance.edit' => 'Setting Tampilan',
        ];

        foreach ($menu as $rute => $label) {
            $respons->assertSee('href="'.route($rute).'"', false)->assertSee($label);
        }

        $respons->assertSeeInOrder(['Lembaga', 'Guru', 'Siswa', 'Rapor', 'Sistem']);
    });
});

describe('akun', function () {
    it('menampilkan identitas, tema, tahun ajaran, sandi, dan keluar tanpa bobot nilai', function () {
        $this->actingAs($this->admin)
            ->withSession($this->sesi)
            ->get(route('admin.pwa.akun'))
            ->assertOk()
            ->assertSee('Admin Madrasah')
            ->assertSee('admin@madrasah.test')
            ->assertSee('Terang')
            ->assertSee('Gelap')
            ->assertSee('Sistem')
            ->assertSee('Tahun ajaran & semester')
            ->assertSee('href="'.route('settings.password.edit').'"', false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertDontSee('bobot_sumatif', false)
            ->assertDontSee('Bobot nilai rapor');
    });
});
