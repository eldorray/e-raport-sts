<?php

use App\Models\Ekskul;
use App\Models\EkskulPenilaian;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\RaporMetadata;
use App\Models\Siswa;
use App\Models\TahfidzPenilaian;
use App\Models\TahunAjaran;
use App\Models\User;

/**
 * Membuat tahun ajaran baru.
 *
 * @param  string  $nama  Nama tahun ajaran
 * @param  bool  $isActive  Status aktif
 * @return TahunAjaran Instance tahun ajaran
 */
function buatTahunAjaranPenghapusan(string $nama, bool $isActive = false): TahunAjaran
{
    return TahunAjaran::create([
        'nama' => $nama,
        'tahun_mulai' => (int) substr($nama, 0, 4),
        'tahun_selesai' => (int) substr($nama, 0, 4) + 1,
        'semester' => 'Ganjil',
        'is_active' => $isActive,
    ]);
}

/**
 * Menyiapkan konteks lengkap: kelas, mapel, guru, siswa, jadwal, dan nilai.
 *
 * @param  TahunAjaran  $tahun  Tahun ajaran tempat data dibuat
 * @param  string  $namaSiswa  Nama siswa
 * @return array<string, mixed> Instance model yang dibuat
 */
function buatKonteksNilai(TahunAjaran $tahun, string $namaSiswa = 'Siswa Satu'): array
{
    $userGuru = User::factory()->create(['role' => 'guru']);

    $guru = Guru::create([
        'user_id' => $userGuru->id,
        'nama' => 'Guru '.$namaSiswa,
        'nip' => '19800101'.fake()->unique()->numerify('###'),
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $kelas = Kelas::create([
        'nama' => 'Kelas '.$namaSiswa,
        'tingkat' => 'I',
        'tahun_ajaran_id' => $tahun->id,
    ]);

    $mapel = MataPelajaran::create([
        'kode' => 'MTK'.fake()->unique()->numerify('##'),
        'nama_mapel' => 'Matematika '.$namaSiswa,
    ]);

    $siswa = Siswa::create([
        'tahun_ajaran_id' => $tahun->id,
        'kelas_id' => $kelas->id,
        'nis' => fake()->unique()->numerify('####'),
        'nama' => $namaSiswa,
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $mengajar = Mengajar::create([
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $kelas->id,
        'mata_pelajaran_id' => $mapel->id,
        'guru_id' => $guru->id,
    ]);

    $nilai = Penilaian::create([
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $kelas->id,
        'siswa_id' => $siswa->id,
        'mata_pelajaran_id' => $mapel->id,
        'guru_id' => $guru->id,
        'mengajar_id' => $mengajar->id,
        'nilai_sumatif' => 85,
        'nilai_sts' => 90,
    ]);

    $rapor = RaporMetadata::create([
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
        'siswa_id' => $siswa->id,
        'kelas_id' => $kelas->id,
        'sakit' => 1,
    ]);

    $tahfidz = TahfidzPenilaian::create([
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
        'predikat_adab' => 'A',
    ]);

    $ekskul = Ekskul::create(['nama' => 'Pramuka '.$namaSiswa, 'guru_id' => $guru->id]);

    $ekskulPenilaian = EkskulPenilaian::create([
        'ekskul_id' => $ekskul->id,
        'guru_id' => $guru->id,
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
        'nilai' => 88,
    ]);

    return compact(
        'userGuru',
        'guru',
        'kelas',
        'mapel',
        'siswa',
        'mengajar',
        'nilai',
        'rapor',
        'tahfidz',
        'ekskul',
        'ekskulPenilaian',
    );
}

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->tahun = buatTahunAjaranPenghapusan('2026/2027', true);
});

it('hanya menghapus siswa pada tahun ajaran yang dipilih', function () {
    $lain = buatKonteksNilai(buatTahunAjaranPenghapusan('2025/2026'), 'Siswa Lama');
    $aktif = buatKonteksNilai($this->tahun, 'Siswa Baru');

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->delete(route('siswa.destroy-all'))
        ->assertRedirect();

    expect(Siswa::whereKey($aktif['siswa']->id)->exists())->toBeFalse()
        ->and(Siswa::whereKey($lain['siswa']->id)->exists())->toBeTrue()
        ->and(Penilaian::whereKey($aktif['nilai']->id)->exists())->toBeFalse()
        ->and(Penilaian::whereKey($lain['nilai']->id)->exists())->toBeTrue()
        ->and(Siswa::count())->toBe(1);
});

it('membersihkan nilai, rapor, tahfidz, dan ekskul siswa yang dihapus massal', function () {
    $konteks = buatKonteksNilai($this->tahun);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->delete(route('siswa.destroy-all'))
        ->assertRedirect()
        ->assertSessionHas('status', fn (string $status) => str_contains($status, '1 siswa'));

    $this->assertDatabaseMissing('penilaians', ['id' => $konteks['nilai']->id]);
    $this->assertDatabaseMissing('rapor_metadatas', ['id' => $konteks['rapor']->id]);
    $this->assertDatabaseMissing('tahfidz_penilaians', ['id' => $konteks['tahfidz']->id]);
    $this->assertDatabaseMissing('ekskul_penilaians', ['id' => $konteks['ekskulPenilaian']->id]);
});

it('membersihkan nilai saat satu siswa dihapus', function () {
    $konteks = buatKonteksNilai($this->tahun);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->delete(route('siswa.destroy', $konteks['siswa']))
        ->assertRedirect();

    $this->assertDatabaseMissing('siswas', ['id' => $konteks['siswa']->id]);
    $this->assertDatabaseMissing('penilaians', ['id' => $konteks['nilai']->id]);
    $this->assertDatabaseMissing('tahfidz_penilaians', ['id' => $konteks['tahfidz']->id]);
});

it('hanya menampilkan siswa tahun ajaran terpilih di halaman siswa', function () {
    buatKonteksNilai(buatTahunAjaranPenghapusan('2025/2026'), 'Siswa Lama');
    buatKonteksNilai($this->tahun, 'Siswa Baru');

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->get(route('siswa.index'))
        ->assertSuccessful()
        ->assertSee('Siswa Baru')
        ->assertDontSee('Siswa Lama');
});

it('menolak hapus kelas yang masih menyimpan nilai', function () {
    $konteks = buatKonteksNilai($this->tahun);

    $this->actingAs($this->admin)
        ->delete(route('kelas.destroy', $konteks['kelas']))
        ->assertRedirect()
        ->assertSessionHasErrors('kelas');

    $this->assertDatabaseHas('kelas', ['id' => $konteks['kelas']->id]);
    $this->assertDatabaseHas('penilaians', ['id' => $konteks['nilai']->id]);
});

it('mengizinkan hapus kelas yang belum punya nilai', function () {
    $kelas = Kelas::create([
        'nama' => 'Kelas Kosong',
        'tingkat' => 'I',
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('kelas.destroy', $kelas))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('kelas', ['id' => $kelas->id]);
});

it('menolak hapus mata pelajaran yang masih menyimpan nilai', function () {
    $konteks = buatKonteksNilai($this->tahun);

    $this->actingAs($this->admin)
        ->delete(route('mata-pelajaran.destroy', $konteks['mapel']))
        ->assertRedirect()
        ->assertSessionHasErrors('mata_pelajaran');

    $this->assertDatabaseHas('mata_pelajarans', ['id' => $konteks['mapel']->id]);
});

it('menolak hapus guru yang masih menyimpan nilai', function () {
    $konteks = buatKonteksNilai($this->tahun);

    $this->actingAs($this->admin)
        ->delete(route('guru.destroy', $konteks['guru']))
        ->assertRedirect()
        ->assertSessionHasErrors('guru');

    $this->assertDatabaseHas('gurus', ['id' => $konteks['guru']->id]);
});

it('menolak hapus semua guru selama masih ada nilai', function () {
    $konteks = buatKonteksNilai($this->tahun);

    $this->actingAs($this->admin)
        ->delete(route('guru.destroy-all'))
        ->assertRedirect()
        ->assertSessionHasErrors('guru');

    $this->assertDatabaseHas('gurus', ['id' => $konteks['guru']->id]);
    $this->assertDatabaseHas('penilaians', ['id' => $konteks['nilai']->id]);
});

it('menolak hapus jadwal mengajar yang masih menyimpan nilai', function () {
    $konteks = buatKonteksNilai($this->tahun);

    $this->actingAs($this->admin)
        ->delete(route('mengajar.destroy', $konteks['mengajar']))
        ->assertRedirect()
        ->assertSessionHasErrors('mengajar');

    $this->assertDatabaseHas('mengajars', ['id' => $konteks['mengajar']->id]);
});

it('menolak hapus ekskul yang masih menyimpan nilai', function () {
    $konteks = buatKonteksNilai($this->tahun);

    $this->actingAs($this->admin)
        ->delete(route('ekskul.destroy', $konteks['ekskul']))
        ->assertRedirect()
        ->assertSessionHasErrors('ekskul');

    $this->assertDatabaseHas('ekskuls', ['id' => $konteks['ekskul']->id]);
});

it('menolak hapus tahun ajaran yang masih berisi data', function () {
    $tahunLama = buatTahunAjaranPenghapusan('2024/2025');
    buatKonteksNilai($tahunLama);

    $this->actingAs($this->admin)
        ->delete(route('tahun-ajaran.destroy', $tahunLama))
        ->assertRedirect()
        ->assertSessionHasErrors('tahun_ajaran');

    $this->assertDatabaseHas('tahun_ajarans', ['id' => $tahunLama->id]);

    // Setelah datanya dibersihkan, tahun ajaran boleh dihapus.
    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $tahunLama->id])
        ->delete(route('siswa.destroy-all'));

    Penilaian::query()->delete();
    RaporMetadata::query()->delete();
    TahfidzPenilaian::query()->delete();
    EkskulPenilaian::query()->delete();
    Mengajar::query()->delete();
    Kelas::query()->delete();

    $this->actingAs($this->admin)
        ->delete(route('tahun-ajaran.destroy', $tahunLama->fresh()))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('tahun_ajarans', ['id' => $tahunLama->id]);
});

it('menampilkan tombol hapus terkunci di halaman master yang punya nilai', function () {
    buatKonteksNilai($this->tahun);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->get(route('kelas.index'))
        ->assertSuccessful()
        ->assertSee('Tidak bisa dihapus: masih terhubung ke 1 nilai');

    $this->actingAs($this->admin)->get(route('guru.index'))->assertSuccessful()
        ->assertSee('Tidak bisa dihapus: masih terhubung ke 1 nilai');
    $this->actingAs($this->admin)->get(route('mata-pelajaran.index'))->assertSuccessful()
        ->assertSee('Tidak bisa dihapus: masih terhubung ke 1 nilai');
    $this->actingAs($this->admin)->get(route('ekskul.index'))->assertSuccessful()
        ->assertSee('Tidak bisa dihapus: masih terhubung ke 1 nilai');
    $this->actingAs($this->admin)->get(route('tahun-ajaran.index'))->assertSuccessful()
        ->assertSee('Tidak bisa dihapus: masih terhubung ke 6 nilai');
});

it('mengizinkan tombol hapus tampil saat data master belum punya nilai', function () {
    $kelas = Kelas::create([
        'nama' => 'Kelas Kosong',
        'tingkat' => 'I',
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->get(route('kelas.index'))
        ->assertSuccessful()
        ->assertDontSee('Tidak bisa dihapus')
        ->assertSee(route('kelas.destroy', $kelas));
});

it('tidak menyentuh data tahun ajaran lama saat semua siswa tahun ajaran baru dihapus', function () {
    $tahunLama = buatTahunAjaranPenghapusan('2025/2026');
    $konteksLama = buatKonteksNilai($tahunLama, 'Siswa Lama');
    buatKonteksNilai($this->tahun, 'Siswa Baru');

    // Tahun ajaran baru aktif dan sedang dipilih, lalu "Hapus Semua" ditekan.
    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->tahun->id])
        ->delete(route('siswa.destroy-all'))
        ->assertRedirect()
        ->assertSessionHas('status', fn (string $status) => str_contains($status, '1 siswa'));

    expect(Siswa::where('tahun_ajaran_id', $this->tahun->id)->count())->toBe(0)
        ->and(Penilaian::where('tahun_ajaran_id', $this->tahun->id)->count())->toBe(0);

    // Seluruh data tahun ajaran lama harus utuh.
    expect(Siswa::whereKey($konteksLama['siswa']->id)->exists())->toBeTrue()
        ->and(Penilaian::whereKey($konteksLama['nilai']->id)->exists())->toBeTrue()
        ->and(RaporMetadata::whereKey($konteksLama['rapor']->id)->exists())->toBeTrue()
        ->and(TahfidzPenilaian::whereKey($konteksLama['tahfidz']->id)->exists())->toBeTrue()
        ->and(EkskulPenilaian::whereKey($konteksLama['ekskulPenilaian']->id)->exists())->toBeTrue()
        ->and(Mengajar::whereKey($konteksLama['mengajar']->id)->exists())->toBeTrue()
        ->and(Kelas::whereKey($konteksLama['kelas']->id)->exists())->toBeTrue()
        ->and(MataPelajaran::whereKey($konteksLama['mapel']->id)->exists())->toBeTrue()
        ->and(Guru::whereKey($konteksLama['guru']->id)->exists())->toBeTrue();

    // Tahun ajaran lama tetap bisa dibuka lengkap.
    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $tahunLama->id])
        ->get(route('siswa.index'))
        ->assertSuccessful()
        ->assertSee('Siswa Lama')
        ->assertDontSee('Siswa Baru');
});

it('memakai tahun ajaran aktif sebagai cadangan bila sesi tahun ajaran kosong', function () {
    $konteksLama = buatKonteksNilai(buatTahunAjaranPenghapusan('2025/2026'), 'Siswa Lama');
    buatKonteksNilai($this->tahun, 'Siswa Baru');

    $this->actingAs($this->admin)
        ->delete(route('siswa.destroy-all'))
        ->assertRedirect();

    expect(Siswa::where('tahun_ajaran_id', $this->tahun->id)->count())->toBe(0)
        ->and(Siswa::whereKey($konteksLama['siswa']->id)->exists())->toBeTrue();
});
