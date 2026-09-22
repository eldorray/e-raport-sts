<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\TahunAjaran;
use App\Models\User;

/**
 * Membuat tahun ajaran untuk pengujian salin mengajar.
 */
function buatTahunSalin(string $nama, bool $aktif): TahunAjaran
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
function buatKelasSalin(TahunAjaran $tahun, string $nama, string $tingkat): Kelas
{
    return Kelas::create([
        'nama' => $nama,
        'tingkat' => $tingkat,
        'tahun_ajaran_id' => $tahun->id,
    ]);
}

/**
 * Membuat guru untuk pengujian.
 */
function buatGuruSalin(string $nama): Guru
{
    return Guru::create([
        'user_id' => User::factory()->create(['role' => 'guru'])->id,
        'nama' => $nama,
        'nip' => fake()->unique()->numerify('##########'),
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
}

/**
 * Membuat mata pelajaran untuk pengujian.
 */
function buatMapelSalin(string $nama, string $kode): MataPelajaran
{
    return MataPelajaran::create([
        'nama_mapel' => $nama,
        'kode' => $kode,
    ]);
}

/**
 * Membuat jadwal mengajar.
 */
function buatJadwalSalin(int $tahunId, int $kelasId, int $mapelId, int $guruId, int $jtm = 2): Mengajar
{
    return Mengajar::create([
        'tahun_ajaran_id' => $tahunId,
        'semester' => 'Ganjil',
        'kelas_id' => $kelasId,
        'mata_pelajaran_id' => $mapelId,
        'guru_id' => $guruId,
        'jtm' => $jtm,
    ]);
}

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->tahunTarget = buatTahunSalin('2026/2027', true);
    $this->tahunSumber = buatTahunSalin('2025/2026', false);
});

it('menyalin jadwal mengajar dari tahun ajaran lain ke kelas bernama sama', function () {
    $kelasSumber = buatKelasSalin($this->tahunSumber, '1A', 'I');
    $kelasTarget = buatKelasSalin($this->tahunTarget, '1A', 'I');

    $guruSatu = buatGuruSalin('Guru Satu');
    $guruDua = buatGuruSalin('Guru Dua');
    $mapelSatu = buatMapelSalin('Matematika', 'MTK');
    $mapelDua = buatMapelSalin('Bahasa Indonesia', 'BIN');

    buatJadwalSalin($this->tahunSumber->id, $kelasSumber->id, $mapelSatu->id, $guruSatu->id, 4);
    buatJadwalSalin($this->tahunSumber->id, $kelasSumber->id, $mapelDua->id, $guruDua->id, 3);

    $this->actingAs($this->admin)
        ->withSession([
            'selected_tahun_ajaran_id' => $this->tahunTarget->id,
            'selected_semester' => 'Ganjil',
        ])
        ->post(route('mengajar.copy'), [
            'source_tahun_ajaran_id' => $this->tahunSumber->id,
            'kelas_id' => $kelasTarget->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('status', fn (string $status) => str_contains($status, '2'));

    $tersalin = Mengajar::where('tahun_ajaran_id', $this->tahunTarget->id)
        ->where('kelas_id', $kelasTarget->id)
        ->get();

    expect($tersalin)->toHaveCount(2)
        ->and($tersalin->firstWhere('mata_pelajaran_id', $mapelSatu->id)->guru_id)->toBe($guruSatu->id)
        ->and($tersalin->firstWhere('mata_pelajaran_id', $mapelSatu->id)->jtm)->toBe(4)
        ->and($tersalin->firstWhere('mata_pelajaran_id', $mapelDua->id)->guru_id)->toBe($guruDua->id)
        ->and($tersalin->firstWhere('mata_pelajaran_id', $mapelDua->id)->jtm)->toBe(3);

    // Jadwal tahun ajaran sumber tidak boleh berubah.
    expect(Mengajar::where('tahun_ajaran_id', $this->tahunSumber->id)->count())->toBe(2);
});

it('mencocokkan kelas sumber walau penulisan nama berbeda', function () {
    buatKelasSalin($this->tahunSumber, 'KELAS 1A', 'I');
    $kelasTarget = buatKelasSalin($this->tahunTarget, '1A', 'I');

    $guru = buatGuruSalin('Guru Satu');
    $mapel = buatMapelSalin('Matematika', 'MTK');
    $kelasSumber = Kelas::where('tahun_ajaran_id', $this->tahunSumber->id)->firstOrFail();
    buatJadwalSalin($this->tahunSumber->id, $kelasSumber->id, $mapel->id, $guru->id, 4);

    $this->actingAs($this->admin)
        ->withSession([
            'selected_tahun_ajaran_id' => $this->tahunTarget->id,
            'selected_semester' => 'Ganjil',
        ])
        ->post(route('mengajar.copy'), [
            'source_tahun_ajaran_id' => $this->tahunSumber->id,
            'kelas_id' => $kelasTarget->id,
        ])
        ->assertRedirect();

    expect(Mengajar::where('tahun_ajaran_id', $this->tahunTarget->id)->count())->toBe(1);
});

it('memberi tahu bila kelas tujuan tidak ada di tahun ajaran sumber', function () {
    buatKelasSalin($this->tahunSumber, '2A', 'II');
    $kelasTarget = buatKelasSalin($this->tahunTarget, '1A', 'I');

    $this->actingAs($this->admin)
        ->withSession([
            'selected_tahun_ajaran_id' => $this->tahunTarget->id,
            'selected_semester' => 'Ganjil',
        ])
        ->post(route('mengajar.copy'), [
            'source_tahun_ajaran_id' => $this->tahunSumber->id,
            'kelas_id' => $kelasTarget->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error', fn (string $pesan) => str_contains($pesan, 'tidak ditemukan'));

    expect(Mengajar::where('tahun_ajaran_id', $this->tahunTarget->id)->count())->toBe(0);
});

it('memberi tahu bila kelas sumber belum punya jadwal mengajar', function () {
    buatKelasSalin($this->tahunSumber, '1A', 'I');
    $kelasTarget = buatKelasSalin($this->tahunTarget, '1A', 'I');

    $this->actingAs($this->admin)
        ->withSession([
            'selected_tahun_ajaran_id' => $this->tahunTarget->id,
            'selected_semester' => 'Ganjil',
        ])
        ->post(route('mengajar.copy'), [
            'source_tahun_ajaran_id' => $this->tahunSumber->id,
            'kelas_id' => $kelasTarget->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('error', fn (string $pesan) => str_contains($pesan, 'belum punya jadwal'));

    expect(Mengajar::where('tahun_ajaran_id', $this->tahunTarget->id)->count())->toBe(0);
});

it('tidak menggandakan jadwal saat disalin dua kali', function () {
    $kelasSumber = buatKelasSalin($this->tahunSumber, '1A', 'I');
    $kelasTarget = buatKelasSalin($this->tahunTarget, '1A', 'I');

    $guru = buatGuruSalin('Guru Satu');
    $mapel = buatMapelSalin('Matematika', 'MTK');
    buatJadwalSalin($this->tahunSumber->id, $kelasSumber->id, $mapel->id, $guru->id, 4);

    foreach (range(1, 2) as $ulang) {
        $this->actingAs($this->admin)
            ->withSession([
                'selected_tahun_ajaran_id' => $this->tahunTarget->id,
                'selected_semester' => 'Ganjil',
            ])
            ->post(route('mengajar.copy'), [
                'source_tahun_ajaran_id' => $this->tahunSumber->id,
                'kelas_id' => $kelasTarget->id,
            ])
            ->assertRedirect();
    }

    expect(Mengajar::where('tahun_ajaran_id', $this->tahunTarget->id)->count())->toBe(1);
});

it('menolak salin lintas tahun ajaran bila kelas tujuan bukan milik tahun ajaran aktif', function () {
    $kelasSumber = buatKelasSalin($this->tahunSumber, '1A', 'I');
    $guru = buatGuruSalin('Guru Satu');
    $mapel = buatMapelSalin('Matematika', 'MTK');
    buatJadwalSalin($this->tahunSumber->id, $kelasSumber->id, $mapel->id, $guru->id, 4);

    $this->actingAs($this->admin)
        ->withSession([
            'selected_tahun_ajaran_id' => $this->tahunTarget->id,
            'selected_semester' => 'Ganjil',
        ])
        ->post(route('mengajar.copy'), [
            'source_tahun_ajaran_id' => $this->tahunSumber->id,
            'kelas_id' => $kelasSumber->id,
        ])
        ->assertSessionHasErrors('kelas_id');

    expect(Mengajar::where('tahun_ajaran_id', $this->tahunTarget->id)->count())->toBe(0);
});

it('tetap bisa menyalin jadwal dari kelas lain di tahun ajaran yang sama', function () {
    $kelasSumber = buatKelasSalin($this->tahunTarget, '1A', 'I');
    $kelasTarget = buatKelasSalin($this->tahunTarget, '1B', 'I');

    $guru = buatGuruSalin('Guru Satu');
    $mapel = buatMapelSalin('Matematika', 'MTK');
    buatJadwalSalin($this->tahunTarget->id, $kelasSumber->id, $mapel->id, $guru->id, 4);

    $this->actingAs($this->admin)
        ->withSession([
            'selected_tahun_ajaran_id' => $this->tahunTarget->id,
            'selected_semester' => 'Ganjil',
        ])
        ->post(route('mengajar.copy-kelas'), [
            'source_kelas_id' => $kelasSumber->id,
            'target_kelas_id' => $kelasTarget->id,
        ])
        ->assertRedirect();

    expect(Mengajar::where('tahun_ajaran_id', $this->tahunTarget->id)->where('kelas_id', $kelasTarget->id)->count())->toBe(1);
});

it('menampilkan pesan error flash pada halaman mengajar', function () {
    buatKelasSalin($this->tahunTarget, '1A', 'I');

    $this->actingAs($this->admin)
        ->withSession([
            'selected_tahun_ajaran_id' => $this->tahunTarget->id,
            'selected_semester' => 'Ganjil',
            'error' => 'Kelas 1A tidak ditemukan pada tahun ajaran sumber.',
        ])
        ->get(route('mengajar.index'))
        ->assertSuccessful()
        ->assertSee('tidak ditemukan pada tahun ajaran sumber');
});

it('menampilkan jumlah jadwal tiap tahun ajaran pada modal salin', function () {
    $kelasSumber = buatKelasSalin($this->tahunSumber, '1A', 'I');
    buatKelasSalin($this->tahunTarget, '1A', 'I');

    $guru = buatGuruSalin('Guru Satu');
    $mapelSatu = buatMapelSalin('Matematika', 'MTK');
    $mapelDua = buatMapelSalin('Bahasa Indonesia', 'BIN');
    buatJadwalSalin($this->tahunSumber->id, $kelasSumber->id, $mapelSatu->id, $guru->id, 4);
    buatJadwalSalin($this->tahunSumber->id, $kelasSumber->id, $mapelDua->id, $guru->id, 3);

    $this->actingAs($this->admin)
        ->withSession([
            'selected_tahun_ajaran_id' => $this->tahunTarget->id,
            'selected_semester' => 'Ganjil',
        ])
        ->get(route('mengajar.index'))
        ->assertSuccessful()
        ->assertSee('2 jadwal semester Ganjil');
});

it('memberi tahu saat tahun ajaran aktif belum punya kelas pada modal salin', function () {
    buatKelasSalin($this->tahunSumber, '1A', 'I');
    $guru = buatGuruSalin('Guru Satu');
    $mapel = buatMapelSalin('Matematika', 'MTK');
    $kelasSumber = Kelas::where('tahun_ajaran_id', $this->tahunSumber->id)->firstOrFail();
    buatJadwalSalin($this->tahunSumber->id, $kelasSumber->id, $mapel->id, $guru->id, 4);

    $this->actingAs($this->admin)
        ->withSession([
            'selected_tahun_ajaran_id' => $this->tahunTarget->id,
            'selected_semester' => 'Ganjil',
        ])
        ->get(route('mengajar.index'))
        ->assertSuccessful()
        ->assertSee('Tahun ajaran aktif belum punya kelas')
        ->assertSee('1 jadwal semester Ganjil');
});
