<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\SchoolProfile;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

beforeEach(function () {
    SchoolProfile::create([
        'name' => 'MI Uji',
        'nsm' => '111236710001',
        'npsn' => '60700001',
        'email' => 'mi@example.test',
        'address' => 'Jl. Uji',
        'city' => 'Tangerang',
        'headmaster' => 'Kepala Madrasah',
    ]);
    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $this->tahun = TahunAjaran::create([
        'nama' => '2026/2027',
        'tahun_mulai' => 2026,
        'tahun_selesai' => 2027,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);
    $this->kelas = Kelas::create([
        'nama' => '1B', 'tingkat' => '1', 'tahun_ajaran_id' => $this->tahun->id,
    ]);
    $this->siswa = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $this->kelas->id,
        'nis' => '1001',
        'nama' => 'Siswa Uji',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
    $this->mapel = MataPelajaran::create([
        'kode' => 'MTK', 'nama_mapel' => 'Matematika', 'kelompok' => 'Umum', 'urutan' => '1',
    ]);
    $this->gurus = collect(['Guru Lama', 'Guru Baru'])->map(fn (string $nama) => Guru::create([
        'user_id' => User::factory()->create(['role' => 'guru', 'bobot_sumatif' => 50, 'bobot_sts' => 50])->id,
        'nama' => $nama,
        'nip' => $nama === 'Guru Lama' ? '10001' : '10002',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]));
    $this->mengajar = Mengajar::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $this->kelas->id,
        'mata_pelajaran_id' => $this->mapel->id,
        'guru_id' => $this->gurus[1]->id,
    ]);
});

it('mencetak satu baris per mapel dengan memilih penilaian terlengkap lalu terbaru', function (array $lama, array $baru, ?float $rapor, string $materi) {
    foreach ([$lama, $baru] as $i => $nilai) {
        Penilaian::create([
            'tahun_ajaran_id' => $this->tahun->id,
            'semester' => 'Ganjil',
            'kelas_id' => $this->kelas->id,
            'siswa_id' => $this->siswa->id,
            'mata_pelajaran_id' => $this->mapel->id,
            'guru_id' => $this->gurus[$i]->id,
            'mengajar_id' => $this->mengajar->id,
            'nilai_sumatif' => $nilai[0],
            'nilai_sts' => $nilai[1],
            'materi_tp' => $i === 0 ? 'Materi lama' : 'Materi baru',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(1 - $i),
        ]);
    }

    $response = $this->actingAs($this->admin)->get(route('rapor.print', [
        'siswa' => $this->siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
    ]))->assertOk();

    $nilai = $response->viewData('nilai');
    expect($nilai)->toHaveCount(1)
        ->and($nilai[0]['rapor'])->toBe($rapor)
        ->and($nilai[0]['descriptor']['kalimat'] ?? $nilai[0]['deskripsi'])->toContain($materi)
        ->and(substr_count($response->getContent(), 'class="mapel">Matematika</td>'))->toBe(1);

    $kelasResponse = $this->get(route('rapor.print-kelas', $this->kelas))->assertOk();
    expect($kelasResponse->viewData('lembars')[0]['nilai'])->toHaveCount(1)
        ->and($kelasResponse->viewData('lembars')[0]['nilai'][0]['rapor'])->toBe($rapor)
        ->and(substr_count($kelasResponse->getContent(), 'class="mapel">Matematika</td>'))->toBe(1);

    // Pemilihan untuk cetak tidak menghapus riwayat nilai guru sebelumnya.
    $this->assertDatabaseCount('penilaians', 2);
})->with([
    'kosong lalu lengkap seperti laporan pengguna' => [[null, null], [90, 90], 90.0, 'Materi baru'],
    'lengkap lalu kosong' => [[80, 90], [null, null], 85.0, 'Materi lama'],
    'lengkap lalu sebagian' => [[80, 90], [100, null], 85.0, 'Materi lama'],
    'sebagian lalu kosong' => [[70, null], [null, null], null, 'Materi lama'],
    'dua penilaian lengkap' => [[70, 70], [90, 90], 90.0, 'Materi baru'],
    'nilai nol tetap terisi' => [[0, 0], [null, null], 0.0, 'Materi lama'],
    'semua kosong tetap tampil sekali' => [[null, null], [null, null], null, 'Materi baru'],
    'komponen guru berbeda tidak digabung' => [[90, null], [null, 80], null, 'Materi baru'],
]);
