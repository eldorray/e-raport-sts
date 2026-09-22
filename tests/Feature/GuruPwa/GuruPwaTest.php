<?php

use App\Models\Ekskul;
use App\Models\EkskulPenilaian;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahfidzPenilaian;
use App\Models\TahunAjaran;
use App\Models\User;

/**
 * Membuat tahun ajaran untuk pengujian aplikasi guru.
 */
function buatTahunPwa(string $nama, bool $aktif): TahunAjaran
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
 * Membuat kelas pada tahun ajaran tertentu.
 */
function buatKelasPwa(TahunAjaran $tahun, string $nama, string $tingkat): Kelas
{
    return Kelas::create([
        'nama' => $nama,
        'tingkat' => $tingkat,
        'tahun_ajaran_id' => $tahun->id,
    ]);
}

/**
 * Membuat mata pelajaran.
 */
function buatMapelPwa(string $nama, string $kode): MataPelajaran
{
    return MataPelajaran::create([
        'nama_mapel' => $nama,
        'kode' => $kode,
    ]);
}

/**
 * Membuat penugasan mengajar.
 */
function buatJadwalPwa(int $tahunId, int $kelasId, int $mapelId, int $guruId, string $semester = 'Ganjil'): Mengajar
{
    return Mengajar::create([
        'tahun_ajaran_id' => $tahunId,
        'semester' => $semester,
        'kelas_id' => $kelasId,
        'mata_pelajaran_id' => $mapelId,
        'guru_id' => $guruId,
        'jtm' => 4,
    ]);
}

beforeEach(function () {
    $this->tahun = buatTahunPwa('2026/2027', true);

    $this->guru = Guru::factory()->create(['nama' => 'Ustadz Ahmad']);
    $this->userGuru = User::findOrFail($this->guru->user_id);

    $this->guruLain = Guru::factory()->create(['nama' => 'Ustadzah Fatimah']);

    $this->kelas = buatKelasPwa($this->tahun, '1A', 'I');
    $this->mapel = buatMapelPwa('Matematika', 'MTK');
    $this->mengajar = buatJadwalPwa($this->tahun->id, $this->kelas->id, $this->mapel->id, $this->guru->id);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];
});

it('menampilkan beranda aplikasi guru untuk akun guru', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.beranda'))
        ->assertOk()
        ->assertSee('Ustadz Ahmad')
        ->assertSee('Input Nilai Sekarang')
        ->assertSee('manifest.webmanifest', false);
});

it('menolak akun admin membuka aplikasi guru', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.beranda'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai'))
        ->assertForbidden();
});

it('memberi tahu bila akun guru belum tertaut ke data guru', function () {
    $userTanpaGuru = User::factory()->create(['role' => 'guru']);

    $this->actingAs($userTanpaGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.beranda'))
        ->assertOk()
        ->assertSee('belum tertaut ke data guru');
});

it('hanya menampilkan penugasan milik guru yang sedang masuk', function () {
    $kelasLain = buatKelasPwa($this->tahun, '2A', 'II');
    $mapelGuruLain = buatMapelPwa('Bahasa Arab', 'BAR');
    buatJadwalPwa($this->tahun->id, $kelasLain->id, $mapelGuruLain->id, $this->guruLain->id);

    $tahunLama = buatTahunPwa('2025/2026', false);
    $kelasLama = buatKelasPwa($tahunLama, '1A', 'I');
    $mapelLama = buatMapelPwa('Fiqih', 'FQH');
    buatJadwalPwa($tahunLama->id, $kelasLama->id, $mapelLama->id, $this->guru->id);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai'))
        ->assertOk()
        ->assertSee('Matematika')
        ->assertDontSee('Bahasa Arab')
        ->assertDontSee('Fiqih');
});

it('menampilkan nilai yang sudah pernah diisi pada form input', function () {
    $siswa = Siswa::factory()->create([
        'kelas_id' => $this->kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Aisyah Nur',
        'nis' => '12345',
    ]);

    Penilaian::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $this->kelas->id,
        'siswa_id' => $siswa->id,
        'mata_pelajaran_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'mengajar_id' => $this->mengajar->id,
        'nilai_sumatif' => 88,
        'nilai_sts' => 76,
        'materi_tp' => 'Bab 1 — Bilangan',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai.form', $this->mengajar))
        ->assertOk()
        ->assertSee('Aisyah Nur')
        ->assertSee('nilai_sumatif['.$siswa->id.']', false)
        ->assertSee('value="88"', false)
        ->assertSee('value="76"', false)
        ->assertSee('Bab 1 — Bilangan')
        ->assertSee('Isi Cepat');
});

it('menolak guru lain membuka form nilai bukan miliknya', function () {
    $userGuruLain = User::findOrFail($this->guruLain->user_id);

    $this->actingAs($userGuruLain)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai.form', $this->mengajar))
        ->assertForbidden();
});

it('menolak penugasan dari tahun ajaran lain', function () {
    $tahunLama = buatTahunPwa('2025/2026', false);
    $kelasLama = buatKelasPwa($tahunLama, '1A', 'I');
    $mengajarLama = buatJadwalPwa($tahunLama->id, $kelasLama->id, $this->mapel->id, $this->guru->id);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.nilai.form', $mengajarLama))
        ->assertNotFound();
});

it('menyimpan nilai sumatif dan sts dari form aplikasi guru', function () {
    $siswa = Siswa::factory()->create([
        'kelas_id' => $this->kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Aisyah Nur',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.penilaian.store', $this->mengajar), [
            'nilai_sumatif' => [$siswa->id => '90'],
            'nilai_sts' => [$siswa->id => '80'],
            'materi_tp' => 'Bab 2',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('penilaians', [
        'siswa_id' => $siswa->id,
        'mengajar_id' => $this->mengajar->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'materi_tp' => 'Bab 2',
    ]);

    $tersimpan = Penilaian::where('siswa_id', $siswa->id)->firstOrFail();

    expect((float) $tersimpan->nilai_sumatif)->toBe(90.0)
        ->and((float) $tersimpan->nilai_sts)->toBe(80.0);
});

it('menolak nilai di luar rentang 0 sampai 100', function () {
    $siswa = Siswa::factory()->create([
        'kelas_id' => $this->kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.penilaian.store', $this->mengajar), [
            'nilai_sumatif' => [$siswa->id => '120'],
        ])
        ->assertSessionHasErrors('nilai_sumatif.'.$siswa->id);

    expect(Penilaian::count())->toBe(0);
});

it('menyediakan berkas manifest, service worker, dan halaman offline', function () {
    foreach (['manifest.webmanifest', 'sw.js', 'offline.html'] as $berkas) {
        expect(is_file(public_path($berkas)))->toBeTrue();
    }

    $manifest = json_decode((string) file_get_contents(public_path('manifest.webmanifest')), true);

    expect($manifest['start_url'])->toBe('/guru-app')
        ->and($manifest['display'])->toBe('standalone')
        ->and($manifest['name'])->toContain('e-Raport')
        ->and(collect($manifest['icons'])->pluck('purpose')->all())->toContain('maskable');

    expect(file_get_contents(public_path('sw.js')))
        ->toContain('eraport-guru-v1')
        ->toContain('/offline.html');

    expect(file_get_contents(public_path('offline.html')))->toContain('Tidak ada koneksi');
});

it('menyediakan ikon PWA yang berukuran benar', function () {
    expect(is_file(public_path('images/pwa-192.png')))->toBeTrue()
        ->and(is_file(public_path('images/pwa-512.png')))->toBeTrue();

    $ukuran192 = getimagesize(public_path('images/pwa-192.png'));
    $ukuran512 = getimagesize(public_path('images/pwa-512.png'));

    expect($ukuran192[0])->toBe(192)
        ->and($ukuran192[1])->toBe(192)
        ->and($ukuran512[0])->toBe(512)
        ->and($ukuran512[1])->toBe(512);
});

it('memuat registrasi service worker dan tombol pasang pada halaman guru', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.beranda'))
        ->assertOk()
        ->assertSee("navigator.serviceWorker.register('/sw.js')", false)
        ->assertSee('apple-mobile-web-app-capable', false)
        ->assertSee('Pasang e-Raport di HP Anda');
});

it('menampilkan tautan aplikasi guru pada sidebar guru', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pelajaran'))
        ->assertOk()
        ->assertSee('Aplikasi Guru (HP)')
        ->assertSee(route('guru.pwa.beranda'), false);
});

it('hanya menampilkan ekskul yang diampu guru pada aplikasi guru', function () {
    $ekskulSaya = Ekskul::create(['nama' => 'Pramuka', 'guru_id' => $this->guru->id]);
    Ekskul::create(['nama' => 'Futsal', 'guru_id' => $this->guruLain->id]);

    $siswa = Siswa::factory()->create([
        'kelas_id' => $this->kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Aisyah Nur',
    ]);

    EkskulPenilaian::create([
        'ekskul_id' => $ekskulSaya->id,
        'guru_id' => $this->guru->id,
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'nilai' => 90,
        'catatan' => 'Sangat aktif',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.ekskul'))
        ->assertOk()
        ->assertSee('Pramuka')
        ->assertDontSee('Futsal')
        ->assertSee('1 dari 1 peserta sudah dinilai');
});

it('menampilkan peserta ekskul beserta nilai dan catatannya', function () {
    $ekskul = Ekskul::create(['nama' => 'Pramuka', 'guru_id' => $this->guru->id]);

    $siswa = Siswa::factory()->create([
        'kelas_id' => $this->kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Bilal Abdurrahman',
    ]);

    EkskulPenilaian::create([
        'ekskul_id' => $ekskul->id,
        'guru_id' => $this->guru->id,
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'nilai' => 85,
        'catatan' => 'Rajin latihan',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.ekskul.form', $ekskul))
        ->assertOk()
        ->assertSee('Bilal Abdurrahman')
        ->assertSee('nilai['.$siswa->id.']', false)
        ->assertSee('value="85"', false)
        ->assertSee('Rajin latihan')
        ->assertSee('Tambah peserta ekskul');
});

it('menolak guru lain membuka penilaian ekskul bukan miliknya', function () {
    $ekskul = Ekskul::create(['nama' => 'Pramuka', 'guru_id' => $this->guru->id]);
    $userGuruLain = User::findOrFail($this->guruLain->user_id);

    $this->actingAs($userGuruLain)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.ekskul.form', $ekskul))
        ->assertForbidden();
});

it('menambah dan menilai peserta ekskul dari aplikasi guru', function () {
    $ekskul = Ekskul::create(['nama' => 'Pramuka', 'guru_id' => $this->guru->id]);

    $siswa = Siswa::factory()->create([
        'kelas_id' => $this->kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.ekskul.store', $ekskul), [
            'action' => 'add',
            'siswa_ids' => [$siswa->id],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('ekskul_penilaians', [
        'ekskul_id' => $ekskul->id,
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.ekskul.store', $ekskul), [
            'nilai' => [$siswa->id => '88'],
            'catatan' => [$siswa->id => 'Aktif'],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $tersimpan = EkskulPenilaian::where('siswa_id', $siswa->id)->firstOrFail();

    expect((float) $tersimpan->nilai)->toBe(88.0)
        ->and($tersimpan->catatan)->toBe('Aktif');
});

it('menampilkan halaman akun aplikasi guru', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.akun'))
        ->assertOk()
        ->assertSee($this->guru->nama)
        ->assertSee('Bobot nilai rapor')
        ->assertSee('Ubah kata sandi')
        ->assertSee('bobot_sumatif', false)
        ->assertSee('Gelap')
        ->assertSee(route('logout'), false);
});

it('menolak admin membuka halaman akun aplikasi guru', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.akun'))
        ->assertForbidden();
});

it('menyimpan bobot nilai dari halaman akun aplikasi guru', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('penilaian.bobot.update'), [
            'bobot_sumatif' => 60,
            'bobot_sts' => 40,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $this->userGuru->id,
        'bobot_sumatif' => 60,
        'bobot_sts' => 40,
    ]);
});

it('memakai menu bawah aplikasi guru untuk ekskul dan akun versi PWA', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.beranda'))
        ->assertOk()
        ->assertSee(route('guru.pwa.ekskul'), false)
        ->assertSee(route('guru.pwa.akun'), false);
});

it('menampilkan halaman wali kelas dengan daftar siswa dan tautan cetak', function () {
    $kelas = Kelas::create([
        'nama' => '6A',
        'tingkat' => 'VI',
        'tahun_ajaran_id' => $this->tahun->id,
        'guru_id' => $this->guru->id,
    ]);

    $lengkap = Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Hafiz Alfatih',
        'nis' => '9001',
    ]);

    Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Nadia Salsabila',
        'nis' => '9002',
    ]);

    $mengajar = buatJadwalPwa($this->tahun->id, $kelas->id, $this->mapel->id, $this->guru->id);

    Penilaian::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $kelas->id,
        'siswa_id' => $lengkap->id,
        'mata_pelajaran_id' => $this->mapel->id,
        'guru_id' => $this->guru->id,
        'mengajar_id' => $mengajar->id,
        'nilai_sumatif' => 90,
        'nilai_sts' => 88,
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.wali'))
        ->assertOk()
        ->assertSee('6A')
        ->assertSee('Wali kelas')
        ->assertSee('Hafiz Alfatih')
        ->assertSee('Nadia Salsabila')
        ->assertSee('Siap cetak')
        ->assertSee('Kosong')
        ->assertSee('Cetak Leger Kelas')
        ->assertSee(route('rapor.ledger', ['kelas' => $kelas->id]), false)
        ->assertSee(route('rapor.print', ['siswa' => $lengkap->id]), false);
});

it('hanya menautkan cetak raport tahfidz bila penilaian tahfidz sudah ada', function () {
    $kelas = Kelas::create([
        'nama' => '5B',
        'tingkat' => 'V',
        'tahun_ajaran_id' => $this->tahun->id,
        'guru_id' => $this->guru->id,
    ]);

    $adaTahfidz = Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Yusuf Hamdan',
        'nis' => '9101',
    ]);

    $tanpaTahfidz = Siswa::factory()->create([
        'kelas_id' => $kelas->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'nama' => 'Sekar Ayu',
        'nis' => '9102',
    ]);

    TahfidzPenilaian::create([
        'siswa_id' => $adaTahfidz->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'pembimbing_id' => $this->guru->id,
        'predikat_adab' => 'A',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.wali'))
        ->assertOk()
        ->assertSee('Cetak Raport Tahfidz')
        ->assertSee(route('tahfidz.print', ['siswa' => $adaTahfidz->id]), false)
        ->assertDontSee(route('tahfidz.print', ['siswa' => $tanpaTahfidz->id]), false);
});

it('menampilkan keterangan bila guru bukan wali kelas', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.wali'))
        ->assertOk()
        ->assertSee('Anda bukan wali kelas pada tahun ajaran ini')
        ->assertDontSee('Cetak Leger Kelas');
});

it('menampilkan kartu wali kelas di beranda dan menu bawah PWA', function () {
    Kelas::create([
        'nama' => '4C',
        'tingkat' => 'IV',
        'tahun_ajaran_id' => $this->tahun->id,
        'guru_id' => $this->guru->id,
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.beranda'))
        ->assertOk()
        ->assertSee('Wali kelas 4C')
        ->assertSee(route('guru.pwa.wali'), false);
});

it('menolak admin membuka halaman wali kelas aplikasi guru', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->withSession($this->sesi)
        ->get(route('guru.pwa.wali'))
        ->assertForbidden();
});
