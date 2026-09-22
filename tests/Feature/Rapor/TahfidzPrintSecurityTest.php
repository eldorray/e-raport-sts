<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MengajarTahfidz;
use App\Models\Siswa;
use App\Models\TahfidzPenilaian;
use App\Models\TahunAjaran;
use App\Models\User;

function buatKonteksTahfidz(): array
{
    $tahun = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $kelas = Kelas::create([
        'nama' => '1A',
        'tingkat' => '1',
        'guru_id' => null,
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

    // Ada penilaian tahfidz agar halaman print bisa dirender
    TahfidzPenilaian::create([
        'siswa_id' => $siswa->id,
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
        'surah_hafalan' => ['an-nas'],
    ]);

    return compact('tahun', 'kelas', 'siswa');
}

function buatGuru(string $nip): array
{
    $user = User::factory()->create(['role' => 'guru']);
    $guru = Guru::create([
        'user_id' => $user->id,
        'nama' => 'Guru '.$nip,
        'nip' => $nip,
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    return [$user, $guru];
}

test('guru tanpa akses tidak bisa mencetak rapor tahfidz siswa', function () {
    ['tahun' => $tahun, 'siswa' => $siswa] = buatKonteksTahfidz();
    [$userLain] = buatGuru('19800101009');

    $response = $this->actingAs($userLain)
        ->withSession([
            'selected_tahun_ajaran_id' => $tahun->id,
            'selected_semester' => 'Ganjil',
        ])
        ->get(route('tahfidz.print', $siswa));

    $response->assertForbidden();
});

test('pembimbing tahfidz kelas tersebut bisa mencetak rapor tahfidz siswa', function () {
    ['tahun' => $tahun, 'kelas' => $kelas, 'siswa' => $siswa] = buatKonteksTahfidz();
    [$userPembimbing, $guruPembimbing] = buatGuru('19800101010');

    MengajarTahfidz::create([
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $kelas->id,
        'guru_id' => $guruPembimbing->id,
    ]);

    $response = $this->actingAs($userPembimbing)
        ->withSession([
            'selected_tahun_ajaran_id' => $tahun->id,
            'selected_semester' => 'Ganjil',
        ])
        ->get(route('tahfidz.print', $siswa));

    $response->assertOk();
});

test('wali kelas bisa mencetak rapor tahfidz siswa di kelasnya', function () {
    ['tahun' => $tahun, 'kelas' => $kelas, 'siswa' => $siswa] = buatKonteksTahfidz();
    [$userWali, $guruWali] = buatGuru('19800101011');

    $kelas->update(['guru_id' => $guruWali->id]);

    $response = $this->actingAs($userWali)
        ->withSession([
            'selected_tahun_ajaran_id' => $tahun->id,
            'selected_semester' => 'Ganjil',
        ])
        ->get(route('tahfidz.print', $siswa));

    $response->assertOk();
});
