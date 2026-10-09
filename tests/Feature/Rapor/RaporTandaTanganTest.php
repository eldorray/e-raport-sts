<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\SchoolProfile;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Carbon;

function cetakRaporContoh(?string $nipKepala): string
{
    Carbon::setTestNow('2025-12-19 08:00:00');

    $tahun = TahunAjaran::create(['nama' => '2025/2026', 'tahun_mulai' => 2025, 'tahun_selesai' => 2026, 'semester' => 'Ganjil', 'is_active' => true]);
    SchoolProfile::create([
        'name' => 'MI Nurul Jihad',
        'nsm' => '111236710001',
        'npsn' => '60700001',
        'email' => 'mi@example.test',
        'address' => 'Jl. Contoh No. 1',
        'city' => 'Tangerang',
        'headmaster' => 'IDA ROSIDA, S.Pd',
        'nip_headmaster' => $nipKepala,
    ]);

    $wali = Guru::create([
        'user_id' => User::factory()->create(['role' => 'guru'])->id,
        'nama' => 'SATIYAH, S.Pd',
        'nip' => '198001012005011002',
        'jenis_kelamin' => 'P',
        'is_active' => true,
    ]);
    $kelas = Kelas::create(['nama' => '6A', 'tingkat' => '6', 'tahun_ajaran_id' => $tahun->id, 'guru_id' => $wali->id]);
    $siswa = Siswa::create(['tahun_ajaran_id' => $tahun->id, 'kelas_id' => $kelas->id, 'nis' => '1001', 'nama' => 'Ahmad', 'jenis_kelamin' => 'L', 'is_active' => true]);

    return test()->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]))
        ->get(route('rapor.print', ['siswa' => $siswa->id, 'tahun_ajaran_id' => $tahun->id, 'semester' => 'Ganjil']))
        ->assertOk()
        ->getContent();
}

afterEach(fn () => Carbon::setTestNow());

it('menyusun tanda tangan: orang tua kiri, tanggal dan wali kelas kanan, kepala madrasah di tengah bawah', function () {
    $html = cetakRaporContoh(null);
    $ttd = substr($html, (int) strpos($html, 'class="ttd"'));

    expect($ttd)->toContain('Orang Tua/Wali')
        ->and($ttd)->toContain('class="ttd-garis"');

    // Urutan di halaman: blok kiri, blok kanan (tanggal di atas Wali Kelas), lalu blok tengah bawah
    $urutan = ['Orang Tua/Wali', 'Tangerang, 19 Desember 2025', 'Wali Kelas', 'SATIYAH, S.Pd', 'NIP. 198001012005011002', 'Mengetahui', 'Kepala Madrasah', 'IDA ROSIDA, S.Pd', 'NIP. -'];
    $posisi = array_map(fn (string $teks) => strpos($ttd, $teks), $urutan);

    expect($posisi)->each->not->toBeFalse()
        ->and($posisi)->toBe(collect($posisi)->sort()->values()->all());
});

it('menampilkan NIP kepala madrasah dari profil sekolah', function () {
    expect(cetakRaporContoh('197001011995012001'))->toContain('NIP. 197001011995012001');
});
