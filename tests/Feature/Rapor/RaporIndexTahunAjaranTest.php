<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

beforeEach(function () {
    $this->tahunLama = TahunAjaran::create([
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

    // Wali kelas yang sama memegang kelas di dua tahun ajaran
    $this->guru = Guru::factory()->create(['nama' => 'Wali Dua Tahun', 'is_active' => true]);
    $this->userWali = User::findOrFail($this->guru->user_id);

    $kelasLama = Kelas::create([
        'nama' => '1A',
        'tingkat' => '1',
        'guru_id' => $this->guru->id,
        'tahun_ajaran_id' => $this->tahunLama->id,
    ]);
    $kelas = Kelas::create([
        'nama' => '1A',
        'tingkat' => '1',
        'guru_id' => $this->guru->id,
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    // Satu anak tercatat sekali per tahun ajaran
    $this->siswaLama = Siswa::create([
        'tahun_ajaran_id' => $this->tahunLama->id,
        'kelas_id' => $kelasLama->id,
        'nis' => '6001',
        'nama' => 'Siswa Berulang',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
    $this->siswa = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $kelas->id,
        'nis' => '6001',
        'nama' => 'Siswa Berulang',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];
});

it('hanya menampilkan salinan siswa pada tahun ajaran sesi untuk admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->withSession($this->sesi)
        ->get(route('rapor.index'))
        ->assertOk()
        ->assertViewHas('siswas', fn ($siswas): bool => $siswas->pluck('id')->all() === [$this->siswa->id]);
});

it('hanya menampilkan salinan siswa pada tahun ajaran sesi untuk filter tingkat', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->withSession($this->sesi)
        ->get(route('rapor.index', ['tingkat' => '1']))
        ->assertOk()
        ->assertViewHas('siswas', fn ($siswas): bool => $siswas->pluck('id')->all() === [$this->siswa->id]);
});

it('hanya menampilkan salinan siswa pada tahun ajaran sesi untuk wali kelas', function () {
    $this->actingAs($this->userWali)
        ->withSession($this->sesi)
        ->get(route('rapor.index'))
        ->assertOk()
        ->assertViewHas('siswas', fn ($siswas): bool => $siswas->pluck('id')->all() === [$this->siswa->id]);
});
