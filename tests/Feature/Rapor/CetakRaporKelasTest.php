<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\RaporMetadata;
use App\Models\SchoolProfile;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

beforeEach(function () {
    // Satu baris TahunAjaran = satu semester; kelas ini milik semester Genap
    $this->tahun = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Genap',
        'is_active' => true,
    ]);
    $this->tahunLain = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Ganjil',
        'is_active' => false,
    ]);

    SchoolProfile::create([
        'name' => 'MI Nurul Jihad',
        'nsm' => '111236710001',
        'npsn' => '60700001',
        'email' => 'mi@example.test',
        'address' => 'Jl. Contoh No. 1',
        'city' => 'Tangerang',
        'headmaster' => 'IDA ROSIDA, S.Pd',
    ]);

    $this->userWali = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $this->wali = Guru::create([
        'user_id' => $this->userWali->id,
        'nama' => 'SATIYAH, S.Pd',
        'nip' => '198001012005011002',
        'jenis_kelamin' => 'P',
        'is_active' => true,
    ]);

    $userWaliLain = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $this->waliLain = Guru::create([
        'user_id' => $userWaliLain->id,
        'nama' => 'Wali Kelas Lain',
        'nip' => '198001012005011003',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $this->kelas = Kelas::create(['nama' => '6A', 'tingkat' => '6', 'tahun_ajaran_id' => $this->tahun->id, 'guru_id' => $this->wali->id]);
    $this->kelasLain = Kelas::create(['nama' => '6B', 'tingkat' => '6', 'tahun_ajaran_id' => $this->tahun->id, 'guru_id' => $this->waliLain->id]);

    $this->siswas = collect(['Citra Lestari', 'Ahmad Fauzi', 'Budi Santoso'])->map(fn (string $nama, int $i) => Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '100'.$i,
        'nama' => $nama,
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]));
    Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '1099',
        'nama' => 'Dodi Sudah Pindah',
        'jenis_kelamin' => 'L',
        'is_active' => false,
    ]);
    Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $this->kelasLain->id,
        'nis' => '2001',
        'nama' => 'Eka Kelas Sebelah',
        'jenis_kelamin' => 'P',
        'is_active' => true,
    ]);

    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    // Sesi sengaja menunjuk semester lain: halaman kelas harus memakai konteks kelasnya sendiri
    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahunLain->id,
        'selected_semester' => 'Ganjil',
    ];
});

it('menampilkan rapor setiap siswa aktif berurutan nama dengan satu pemisah halaman per siswa berikutnya', function () {
    $response = $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('rapor.print-kelas', $this->kelas))
        ->assertOk()
        ->assertSeeInOrder(['Ahmad Fauzi', 'Budi Santoso', 'Citra Lestari'])
        ->assertDontSee('Dodi Sudah Pindah')
        ->assertDontSee('Eka Kelas Sebelah');

    $html = $response->getContent();

    expect(substr_count($html, 'class="rapor-siswa"'))->toBe(1)
        ->and(substr_count($html, 'class="rapor-siswa ganti-halaman"'))->toBe(2)
        // Satu blok tanda tangan per siswa, persis seperti cetak per siswa
        ->and(substr_count($html, 'class="ttd"'))->toBe(3);
});

it('menampilkan toolbar layar tanpa mencetak otomatis saat dibuka', function () {
    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('rapor.print-kelas', $this->kelas))
        ->assertOk()
        ->assertDontSee('onload="window.print()"', false)
        ->assertSee('onclick="window.print()"', false)
        ->assertSee('Cetak / Simpan PDF')
        ->assertSee('Kelas 6A')
        ->assertSee('2025/2026')
        ->assertSee('3 siswa')
        ->assertSee(route('rapor.index', ['kelas_id' => $this->kelas->id]), false);
});

it('memakai tahun ajaran dan semester milik kelas, bukan dari sesi atau query string', function () {
    $mapelGenap = MataPelajaran::create(['kode' => 'MTK', 'nama_mapel' => 'Matematika Genap', 'kelompok' => 'A', 'urutan' => '1']);
    $mapelGanjil = MataPelajaran::create(['kode' => 'BSD', 'nama_mapel' => 'Bahasa Sunda Ganjil', 'kelompok' => 'A', 'urutan' => '2']);
    $ahmad = $this->siswas[1];

    foreach ([[$mapelGenap, $this->tahun, 'Genap'], [$mapelGanjil, $this->tahunLain, 'Ganjil']] as [$mapel, $tahun, $semester]) {
        $mengajar = Mengajar::create([
            'tahun_ajaran_id' => $tahun->id,
            'semester' => $semester,
            'kelas_id' => $this->kelas->id,
            'mata_pelajaran_id' => $mapel->id,
            'guru_id' => $this->wali->id,
        ]);
        Penilaian::create([
            'tahun_ajaran_id' => $tahun->id,
            'semester' => $semester,
            'kelas_id' => $this->kelas->id,
            'siswa_id' => $ahmad->id,
            'mata_pelajaran_id' => $mapel->id,
            'guru_id' => $this->wali->id,
            'mengajar_id' => $mengajar->id,
            'nilai_sumatif' => 80,
            'nilai_sts' => 90,
        ]);
    }

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('rapor.print-kelas', [
            'kelas' => $this->kelas,
            'tahun_ajaran_id' => $this->tahunLain->id,
            'semester' => 'Ganjil',
        ]))
        ->assertOk()
        ->assertSee('Matematika Genap')
        ->assertDontSee('Bahasa Sunda Ganjil')
        ->assertSee(': Genap', false);
});

it('tidak membuat RaporMetadata saat pratinjau dan memakai metadata yang sudah ada', function () {
    RaporMetadata::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Genap',
        'siswa_id' => $this->siswas[1]->id,
        'kelas_id' => $this->kelas->id,
        'wali_guru_id' => $this->wali->id,
        'sakit' => 4,
        'catatan_wali' => 'Ananda rajin membaca di perpustakaan',
        'prestasi' => [['jenis' => 'Juara 1 Lomba Tahfidz', 'keterangan' => 'Tingkat kecamatan']],
    ]);

    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('rapor.print-kelas', $this->kelas))
        ->assertOk()
        ->assertSee('Ananda rajin membaca di perpustakaan')
        ->assertSee('Juara 1 Lomba Tahfidz');

    $this->assertDatabaseCount('rapor_metadatas', 1);
});

it('mengizinkan wali kelas mencetak kelasnya sendiri', function () {
    $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->get(route('rapor.print-kelas', $this->kelas))
        ->assertOk()
        ->assertSee('Ahmad Fauzi');
});

it('menolak wali kelas mencetak kelas lain', function () {
    $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->get(route('rapor.print-kelas', $this->kelasLain))
        ->assertForbidden();
});

it('menolak guru mapel yang bukan wali kelas', function () {
    $userGuruMapel = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guruMapel = Guru::create([
        'user_id' => $userGuruMapel->id,
        'nama' => 'Guru Mapel',
        'nip' => '198001012005011004',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
    Mengajar::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Genap',
        'kelas_id' => $this->kelas->id,
        'mata_pelajaran_id' => MataPelajaran::create(['kode' => 'IPA', 'nama_mapel' => 'IPA'])->id,
        'guru_id' => $guruMapel->id,
    ]);

    $this->actingAs($userGuruMapel)
        ->withSession($this->sesi)
        ->get(route('rapor.print-kelas', $this->kelas))
        ->assertForbidden();
});

it('menampilkan pesan ramah untuk kelas tanpa siswa aktif', function () {
    $kelasKosong = Kelas::create(['nama' => '1C', 'tingkat' => '1', 'tahun_ajaran_id' => $this->tahun->id, 'guru_id' => null]);

    $html = $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('rapor.print-kelas', $kelasKosong))
        ->assertOk()
        ->assertSee('Belum ada siswa aktif di kelas ini.')
        ->assertSee('0 siswa')
        ->getContent();

    expect($html)->not->toContain('class="rapor-siswa');
});

it('tetap merender cetak rapor per siswa seperti sebelumnya', function () {
    $this->actingAs($this->admin)
        ->get(route('rapor.print', [
            'siswa' => $this->siswas[1]->id,
            'tahun_ajaran_id' => $this->tahun->id,
            'semester' => 'Genap',
        ]))
        ->assertOk()
        ->assertSee('onload="window.print()"', false)
        ->assertSee('Ahmad Fauzi')
        ->assertSee('Kepala Madrasah')
        ->assertDontSee('Budi Santoso')
        ->assertDontSee('Cetak / Simpan PDF');
});

it('menampilkan tombol cetak rapor satu kelas untuk kelas terpilih di halaman rapor admin', function () {
    $sesi = ['selected_tahun_ajaran_id' => $this->tahun->id, 'selected_semester' => 'Genap'];

    $this->actingAs($this->admin)
        ->withSession($sesi)
        ->get(route('rapor.index', ['kelas_id' => $this->kelas->id]))
        ->assertOk()
        ->assertSee('Cetak rapor satu kelas')
        ->assertSee(route('rapor.print-kelas', $this->kelas), false)
        ->assertDontSee(route('rapor.print-kelas', $this->kelasLain), false);
});

it('menampilkan tombol cetak rapor satu kelas untuk kelas wali di halaman rapor', function () {
    $sesi = ['selected_tahun_ajaran_id' => $this->tahun->id, 'selected_semester' => 'Genap'];

    $this->actingAs($this->userWali)
        ->withSession($sesi)
        ->get(route('rapor.index'))
        ->assertOk()
        ->assertSee(route('rapor.print-kelas', $this->kelas), false)
        ->assertDontSee(route('rapor.print-kelas', $this->kelasLain), false);
});

it('tidak menampilkan tombol cetak satu kelas untuk guru yang bukan wali', function () {
    $userGuruMapel = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    Guru::create([
        'user_id' => $userGuruMapel->id,
        'nama' => 'Guru Mapel',
        'nip' => '198001012005011005',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
    $sesi = ['selected_tahun_ajaran_id' => $this->tahun->id, 'selected_semester' => 'Genap'];

    $this->actingAs($userGuruMapel)
        ->withSession($sesi)
        ->get(route('rapor.index', ['kelas_id' => $this->kelas->id]))
        ->assertOk()
        ->assertDontSee('Cetak rapor satu kelas');
});
