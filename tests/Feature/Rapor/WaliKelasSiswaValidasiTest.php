<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

beforeEach(function () {
    $tahunLama = TahunAjaran::create([
        'nama' => '2024/2025',
        'tahun_mulai' => 2024,
        'tahun_selesai' => 2025,
        'semester' => 'Genap',
        'is_active' => false,
    ]);
    $this->tahun = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->guru = Guru::factory()->create(['nama' => 'Wali Validasi', 'is_active' => true]);
    $this->userWali = User::findOrFail($this->guru->user_id);

    $kelasLama = Kelas::create(['nama' => '2A', 'tingkat' => '2', 'tahun_ajaran_id' => $tahunLama->id]);
    $this->kelas = Kelas::create([
        'nama' => '3A',
        'tingkat' => '3',
        'guru_id' => $this->guru->id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    // Salinan tahun lalu dengan NIS & NISN yang sama
    Siswa::create([
        'tahun_ajaran_id' => $tahunLama->id,
        'kelas_id' => $kelasLama->id,
        'nis' => '7001',
        'nisn' => '0070010001',
        'nama' => 'Siswa Naik Kelas',
        'jenis_kelamin' => 'P',
        'is_active' => true,
    ]);
    $this->siswa = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '7001',
        'nisn' => '0070010001',
        'nama' => 'Siswa Naik Kelas',
        'jenis_kelamin' => 'P',
        'is_active' => true,
    ]);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];

    $this->dataSiswa = [
        'nis' => '7001',
        'nisn' => '0070010001',
        'nama' => 'Siswa Naik Kelas',
        'jenis_kelamin' => 'P',
        'is_active' => '1',
    ];
});

it('menyimpan ulang siswa tanpa perubahan walau NIS sama dengan salinan tahun lalu', function () {
    $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->put(route('wali-kelas.siswa.update', $this->siswa), $this->dataSiswa)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Siswa berhasil diperbarui.');
});

it('menolak NIS atau NISN yang sudah dipakai siswa lain pada tahun ajaran yang sama', function (string $field) {
    Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '7002',
        'nisn' => '0070020002',
        'nama' => 'Siswa Lain',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $data = $this->dataSiswa;
    $data[$field] = $field === 'nis' ? '7002' : '0070020002';

    $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->put(route('wali-kelas.siswa.update', $this->siswa), $data)
        ->assertSessionHasErrors($field);

    expect($this->siswa->fresh()->{$field})->toBe($this->dataSiswa[$field]);
})->with(['nis', 'nisn']);

it('menambah siswa baru dengan NIS yang hanya dipakai pada tahun ajaran lain', function () {
    $this->siswa->delete();

    $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->post(route('wali-kelas.siswa.store'), $this->dataSiswa)
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Siswa berhasil ditambahkan.');

    expect(Siswa::where('tahun_ajaran_id', $this->tahun->id)->where('nis', '7001')->count())->toBe(1);
});
