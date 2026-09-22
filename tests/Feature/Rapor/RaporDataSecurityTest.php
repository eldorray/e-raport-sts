<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

function buatKonteksWaliKelas(): array
{
    $tahun = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $userWali = User::factory()->create(['role' => 'guru']);
    $guruWali = Guru::create([
        'user_id' => $userWali->id,
        'nama' => 'Guru Wali',
        'nip' => '19800101001',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $kelas = Kelas::create([
        'nama' => '1A',
        'tingkat' => '1',
        'guru_id' => $guruWali->id,
        'tahun_ajaran_id' => $tahun->id,
    ]);

    $siswa = Siswa::create([
        'tahun_ajaran_id' => $tahun->id,
        'kelas_id' => $kelas->id,
        'nis' => '1001',
        'nama' => 'Siswa Satu',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    return compact('tahun', 'userWali', 'guruWali', 'kelas', 'siswa');
}

test('guru yang bukan wali kelas tidak bisa menyimpan absen kelas orang lain', function () {
    ['tahun' => $tahun, 'kelas' => $kelas, 'siswa' => $siswa] = buatKonteksWaliKelas();

    // Guru lain (bukan wali kelas dari $kelas)
    $userLain = User::factory()->create(['role' => 'guru']);
    Guru::create([
        'user_id' => $userLain->id,
        'nama' => 'Guru Lain',
        'nip' => '19800101002',
        'jenis_kelamin' => 'P',
        'is_active' => true,
    ]);

    $response = $this->actingAs($userLain)
        ->withSession([
            'selected_tahun_ajaran_id' => $tahun->id,
            'selected_semester' => 'Ganjil',
        ])
        ->post(route('rapor.absen.store'), [
            'kelas_id' => $kelas->id,
            'absen' => [
                $siswa->id => ['sakit' => 3, 'izin' => 1, 'alpa' => 0],
            ],
        ]);

    $response->assertForbidden();

    // Pastikan tidak ada data absen yang tersimpan
    $this->assertDatabaseMissing('rapor_metadatas', [
        'siswa_id' => $siswa->id,
        'sakit' => 3,
    ]);
});

test('wali kelas yang sah dapat menyimpan absen kelasnya', function () {
    ['tahun' => $tahun, 'userWali' => $userWali, 'kelas' => $kelas, 'siswa' => $siswa] = buatKonteksWaliKelas();

    $response = $this->actingAs($userWali)
        ->withSession([
            'selected_tahun_ajaran_id' => $tahun->id,
            'selected_semester' => 'Ganjil',
        ])
        ->post(route('rapor.absen.store'), [
            'kelas_id' => $kelas->id,
            'absen' => [
                $siswa->id => ['sakit' => 2, 'izin' => 1, 'alpa' => 0],
            ],
        ]);

    $response->assertSessionHas('status');

    $this->assertDatabaseHas('rapor_metadatas', [
        'siswa_id' => $siswa->id,
        'kelas_id' => $kelas->id,
        'sakit' => 2,
        'izin' => 1,
        'alpa' => 0,
    ]);
});

test('absen store mengabaikan siswa yang bukan anggota kelas', function () {
    ['tahun' => $tahun, 'userWali' => $userWali, 'kelas' => $kelas, 'siswa' => $siswa] = buatKonteksWaliKelas();

    // Siswa dari kelas lain (tahun yang sama)
    $kelasLain = Kelas::create([
        'nama' => '1B',
        'tingkat' => '1',
        'guru_id' => null,
        'tahun_ajaran_id' => $tahun->id,
    ]);
    $siswaLuar = Siswa::create([
        'tahun_ajaran_id' => $tahun->id,
        'kelas_id' => $kelasLain->id,
        'nis' => '1002',
        'nama' => 'Siswa Luar',
        'jenis_kelamin' => 'P',
        'is_active' => true,
    ]);

    $response = $this->actingAs($userWali)
        ->withSession([
            'selected_tahun_ajaran_id' => $tahun->id,
            'selected_semester' => 'Ganjil',
        ])
        ->post(route('rapor.absen.store'), [
            'kelas_id' => $kelas->id,
            'absen' => [
                $siswa->id => ['sakit' => 1, 'izin' => 0, 'alpa' => 0],
                $siswaLuar->id => ['sakit' => 9, 'izin' => 9, 'alpa' => 9], // harus diabaikan
            ],
        ]);

    $response->assertSessionHas('status');

    // Siswa anggota kelas tersimpan
    $this->assertDatabaseHas('rapor_metadatas', [
        'siswa_id' => $siswa->id,
        'sakit' => 1,
    ]);

    // Siswa luar kelas TIDAK tersimpan lewat kelas ini
    $this->assertDatabaseMissing('rapor_metadatas', [
        'siswa_id' => $siswaLuar->id,
        'sakit' => 9,
    ]);
});
