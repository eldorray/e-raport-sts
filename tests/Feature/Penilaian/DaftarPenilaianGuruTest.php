<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\TahunAjaran;
use App\Models\User;

it('menautkan tombol Isi Nilai ke halaman penilaian yang benar (bukan 404)', function () {
    $tahun = TahunAjaran::create(['nama' => '2025/2026', 'tahun_mulai' => 2025, 'tahun_selesai' => 2026, 'semester' => 'Ganjil', 'is_active' => true]);
    $guru = Guru::factory()->create(['is_active' => true]);
    $kelas = Kelas::create(['nama' => '4A', 'tingkat' => '4', 'tahun_ajaran_id' => $tahun->id]);
    $mengajar = Mengajar::create([
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $kelas->id,
        'mata_pelajaran_id' => MataPelajaran::create(['nama_mapel' => 'Matematika', 'kode' => 'MTK'])->id,
        'guru_id' => $guru->id,
        'jtm' => 4,
    ]);

    $sesi = ['selected_tahun_ajaran_id' => $tahun->id, 'selected_semester' => 'Ganjil'];
    $tautan = route('guru.penilaian.show', ['mengajar' => $mengajar, 'jenis' => 'sumatif']);

    $this->actingAs(User::findOrFail($guru->user_id))
        ->withSession($sesi)
        ->get(route('guru.penilaian.index'))
        ->assertOk()
        ->assertSee($tautan, false);

    $this->get($tautan)->assertOk();
});
