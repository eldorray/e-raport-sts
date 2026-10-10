<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;

/**
 * Setelah masuk lewat aplikasi HP, semua halaman web tampil dalam bingkai aplikasi
 * (header + tombol kembali + menu bawah), bukan tampilan web dengan sidebar.
 */
const PENANDA_BINGKAI_APLIKASI = 'rel="manifest"';
const PENANDA_SIDEBAR_WEB = 'closeSidebarOnMobile';

beforeEach(function () {
    $this->tahun = TahunAjaran::create(['nama' => '2026/2027', 'tahun_mulai' => 2026, 'tahun_selesai' => 2027, 'semester' => 'Ganjil', 'is_active' => true]);
    $this->sesi = ['selected_tahun_ajaran_id' => $this->tahun->id, 'selected_semester' => 'Ganjil'];
    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
});

it('menampilkan halaman web dalam tampilan web bila tidak masuk lewat aplikasi', function () {
    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('tahun-ajaran-baru.create'))
        ->assertOk()
        ->assertSee(PENANDA_SIDEBAR_WEB, false)
        ->assertDontSee(PENANDA_BINGKAI_APLIKASI, false);
});

it('menampilkan semua menu admin dalam bingkai aplikasi setelah masuk lewat aplikasi', function (string $rute, string $judul) {
    $this->actingAs($this->admin)->withSession($this->sesi)->get(route('admin.pwa.menu'))->assertOk();

    $this->get(route($rute))
        ->assertOk()
        ->assertSee(PENANDA_BINGKAI_APLIKASI, false)
        ->assertDontSee(PENANDA_SIDEBAR_WEB, false)
        ->assertSee($judul)
        ->assertSee(route('admin.pwa.menu'), false);
})->with([
    ['tahun-ajaran-baru.create', 'Tahun Ajaran Baru'],
    ['tahun-ajaran.index', 'Tahun Ajaran'],
    ['siswa.index', 'Siswa'],
    ['guru.index', 'Guru'],
    ['kelas.index', 'Kelas'],
    ['mata-pelajaran.index', 'Mata Pelajaran'],
    ['ekskul.index', 'Ekskul'],
    ['mengajar.index', 'Mengajar'],
    ['mengajar-tahfidz.index', 'Mengajar Tahfidz'],
    ['rombel.index', 'Rombel Kelas'],
    ['users.index', 'Manajemen User'],
    ['school-profile.index', 'Profil Sekolah'],
    ['rapor.print-settings.edit', 'Pengaturan Cetak'],
    ['rapor.index', 'Cetak Rapor'],
    ['koreksi-nilai.index', 'Koreksi Nilai'],
    ['tahfidz.index', 'Raport Tahfidz'],
    ['backup.index', 'Backup'],
    ['settings.appearance.edit', 'Tampilan'],
    ['dashboard', 'Dashboard'],
]);

it('kembali ke tampilan web setelah memilih Versi Web', function () {
    $this->actingAs($this->admin)->withSession($this->sesi)->get(route('admin.pwa.beranda'));

    $this->get(route('app.versi-web'))->assertRedirect(route('dashboard'));

    $this->get(route('siswa.index'))
        ->assertOk()
        ->assertSee(PENANDA_SIDEBAR_WEB, false)
        ->assertDontSee(PENANDA_BINGKAI_APLIKASI, false);
});

it('menampilkan halaman web guru dalam bingkai aplikasi beserta menu Wali bila wali kelas', function () {
    $guru = Guru::factory()->create(['is_active' => true]);
    Kelas::create(['nama' => '5A', 'tingkat' => '5', 'tahun_ajaran_id' => $this->tahun->id, 'guru_id' => $guru->id]);

    $this->actingAs(User::findOrFail($guru->user_id))->withSession($this->sesi)->get(route('guru.pwa.beranda'))->assertOk();

    $this->get(route('rapor.absen'))
        ->assertOk()
        ->assertSee(PENANDA_BINGKAI_APLIKASI, false)
        ->assertDontSee(PENANDA_SIDEBAR_WEB, false)
        ->assertSee('Data Absen')
        ->assertSee(route('guru.pwa.wali'), false);
});

it('menampilkan pesan warning dan success di bingkai aplikasi', function () {
    $this->actingAs($this->admin)->withSession($this->sesi + ['mode_aplikasi' => true, 'warning' => 'Ada kelas yang dilewati.', 'success' => 'Data tersimpan.'])
        ->get(route('kelas.index'))
        ->assertOk()
        ->assertSee('Ada kelas yang dilewati.')
        ->assertSee('Data tersimpan.');
});

it('menerapkan pengaturan font halaman Tampilan di bingkai aplikasi', function () {
    $this->actingAs($this->admin)->withSession($this->sesi + ['mode_aplikasi' => true])
        ->get(route('siswa.index'))
        ->assertOk()
        ->assertSee(PENANDA_BINGKAI_APLIKASI, false)
        ->assertSee("localStorage.getItem('fontSize')", false)
        ->assertSee("localStorage.getItem('fontFamily')", false)
        ->assertSee("localStorage.getItem('fontColor')", false);
});
