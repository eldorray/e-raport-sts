<?php

declare(strict_types=1);

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Skenario yang ditanyakan: buat tahun ajaran baru, aktifkan, lalu hapus semua siswanya.
 * Data tahun ajaran sebelumnya (termasuk foto) harus tetap utuh.
 */
function siswaTahun(TahunAjaran $tahun, string $nis, ?string $foto = null): Siswa
{
    $kelas = Kelas::firstOrCreate(['nama' => '1A', 'tahun_ajaran_id' => $tahun->id], ['tingkat' => 'I']);

    return Siswa::create([
        'tahun_ajaran_id' => $tahun->id,
        'kelas_id' => $kelas->id,
        'nis' => $nis,
        'nama' => 'Siswa '.$nis,
        'jenis_kelamin' => 'L',
        'is_active' => true,
        'photo_path' => $foto,
    ]);
}

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $this->lama = TahunAjaran::create(['nama' => '2026/2027', 'tahun_mulai' => 2026, 'tahun_selesai' => 2027, 'semester' => 'Ganjil', 'is_active' => true]);
    $this->baru = TahunAjaran::create(['nama' => '2026/2027', 'tahun_mulai' => 2026, 'tahun_selesai' => 2027, 'semester' => 'Genap', 'is_active' => false]);
    $this->sesiLama = [
        'selected_tahun_ajaran_id' => $this->lama->id,
        'selected_semester' => 'Ganjil',
        'selected_tahun_ajaran_is_active' => true,
    ];
});

it('memindahkan pilihan tahun ajaran saat tahun ajaran diaktifkan dari tombol aktif', function () {
    $this->actingAs($this->admin)
        ->withSession($this->sesiLama)
        ->patch(route('tahun-ajaran.toggle-active', $this->baru))
        ->assertSessionHas('selected_tahun_ajaran_id', $this->baru->id)
        ->assertSessionHas('selected_semester', 'Genap')
        ->assertSessionHas('selected_tahun_ajaran_is_active', true)
        ->assertSessionHas('status', fn (string $pesan) => str_contains($pesan, '2026/2027 Genap'));
});

it('memindahkan pilihan tahun ajaran saat tahun ajaran baru dibuat langsung aktif', function () {
    $this->actingAs($this->admin)
        ->withSession($this->sesiLama)
        ->post(route('tahun-ajaran.store'), [
            'nama' => '2027/2028',
            'tahun_mulai' => 2027,
            'tahun_selesai' => 2028,
            'semester' => 'Ganjil',
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('selected_tahun_ajaran_id', TahunAjaran::where('nama', '2027/2028')->value('id'));
});

it('menandai pilihan sesi tidak aktif saat tahun ajaran yang dipilih dinonaktifkan', function () {
    $this->actingAs($this->admin)
        ->withSession($this->sesiLama)
        ->patch(route('tahun-ajaran.toggle-active', $this->lama))
        ->assertSessionHas('selected_tahun_ajaran_id', $this->lama->id)
        ->assertSessionHas('selected_tahun_ajaran_is_active', false);
});

it('tidak menghapus siswa bila nama tahun ajaran tidak diketik dengan benar', function (?string $diketik) {
    siswaTahun($this->lama, '1001');

    $this->actingAs($this->admin)
        ->withSession($this->sesiLama)
        ->delete(route('siswa.destroy-all'), array_filter(['konfirmasi' => $diketik]))
        ->assertSessionHasErrors('konfirmasi');

    expect(Siswa::count())->toBe(1);
})->with([
    'kosong' => [null],
    'tanpa semester' => ['2026/2027'],
    'semester lain' => ['2026/2027 Genap'],
]);

it('hanya menghapus siswa tahun ajaran baru setelah diaktifkan, tahun ajaran lama tetap utuh', function () {
    $siswaLama = siswaTahun($this->lama, '1001');
    siswaTahun($this->baru, '1001');

    // Aktifkan tahun ajaran baru: pilihan sesi ikut pindah
    $this->actingAs($this->admin)
        ->withSession($this->sesiLama)
        ->patch(route('tahun-ajaran.toggle-active', $this->baru));

    $this->delete(route('siswa.destroy-all'), ['konfirmasi' => ' 2026/2027   genap '])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    expect(Siswa::where('tahun_ajaran_id', $this->baru->id)->count())->toBe(0)
        ->and(Siswa::whereKey($siswaLama->id)->exists())->toBeTrue();
});

it('tidak menghapus file foto yang masih dipakai salinan siswa tahun ajaran lain', function () {
    Storage::fake('public');
    Storage::disk('public')->put('siswa/foto.jpg', 'isi');

    siswaTahun($this->lama, '1001', 'siswa/foto.jpg');
    $salinanBaru = siswaTahun($this->baru, '1001', 'siswa/foto.jpg');

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->baru->id])
        ->delete(route('siswa.destroy-all'), ['konfirmasi' => '2026/2027 Genap'])
        ->assertSessionHasNoErrors();

    expect(Siswa::whereKey($salinanBaru->id)->exists())->toBeFalse();
    Storage::disk('public')->assertExists('siswa/foto.jpg');
});

it('menghapus file foto setelah tidak ada siswa lain yang memakainya', function () {
    Storage::fake('public');
    Storage::disk('public')->put('siswa/foto.jpg', 'isi');

    $siswa = siswaTahun($this->lama, '1001', 'siswa/foto.jpg');

    $this->actingAs($this->admin)
        ->withSession($this->sesiLama)
        ->delete(route('siswa.destroy', $siswa))
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing('siswa/foto.jpg');
});

it('mengganti foto salinan baru tanpa menghapus foto yang dipakai rapor tahun lalu', function () {
    Storage::fake('public');
    Storage::disk('public')->put('siswa/foto.jpg', 'isi');

    siswaTahun($this->lama, '1001', 'siswa/foto.jpg');
    $salinanBaru = siswaTahun($this->baru, '1001', 'siswa/foto.jpg');

    $this->actingAs($this->admin)
        ->withSession(['selected_tahun_ajaran_id' => $this->baru->id, 'selected_semester' => 'Genap'])
        ->put(route('siswa.update', $salinanBaru), [
            'nis' => '1001',
            'nama' => 'Siswa 1001',
            'jenis_kelamin' => 'L',
            'kelas_id' => $salinanBaru->kelas_id,
            'photo' => UploadedFile::fake()->image('baru.jpg'),
        ])
        ->assertSessionHasNoErrors();

    expect($salinanBaru->fresh()->photo_path)->not->toBe('siswa/foto.jpg');
    Storage::disk('public')->assertExists('siswa/foto.jpg');
});
