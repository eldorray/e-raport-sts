<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

beforeEach(function () {
    $this->tahun = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->guru = Guru::factory()->create(['nama' => 'Ustadz Form', 'is_active' => true]);
    $this->userGuru = User::findOrFail($this->guru->user_id);

    $this->kelas = Kelas::create([
        'nama' => '5A',
        'tingkat' => '5',
        'tahun_ajaran_id' => $this->tahun->id,
    ]);

    $mapel = MataPelajaran::create(['nama_mapel' => 'IPA', 'kode' => 'IPA']);

    $this->mengajar = Mengajar::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $this->kelas->id,
        'mata_pelajaran_id' => $mapel->id,
        'guru_id' => $this->guru->id,
        'jtm' => 2,
    ]);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];

    $this->buatSiswaBernilai = function (string $nama, ?float $sumatif, ?float $sts): Siswa {
        $siswa = Siswa::create([
            'tahun_ajaran_id' => $this->tahun->id,
            'kelas_id' => $this->kelas->id,
            'nis' => 'F'.(Siswa::count() + 1),
            'nama' => $nama,
            'jenis_kelamin' => 'P',
            'is_active' => true,
        ]);

        Penilaian::create([
            'tahun_ajaran_id' => $this->tahun->id,
            'semester' => 'Ganjil',
            'kelas_id' => $this->kelas->id,
            'siswa_id' => $siswa->id,
            'mata_pelajaran_id' => $this->mengajar->mata_pelajaran_id,
            'guru_id' => $this->guru->id,
            'mengajar_id' => $this->mengajar->id,
            'materi_tp' => 'Bab Cahaya',
            'nilai_sumatif' => $sumatif,
            'nilai_sts' => $sts,
        ]);

        return $siswa;
    };
});

it('mengisi ulang form dengan input lama setelah gagal simpan', function () {
    $siswa = ($this->buatSiswaBernilai)('Siswa Lama', 80, 70);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi + [
            '_old_input' => [
                'materi_tp' => 'Bab Bunyi',
                'nilai_sumatif' => [$siswa->id => '55'],
                'nilai_sts' => [$siswa->id => '66'],
            ],
        ])
        ->get(route('guru.penilaian.show', $this->mengajar))
        ->assertOk()
        ->assertSee('value="55"', false)
        ->assertSee('value="66"', false)
        ->assertSee('value="Bab Bunyi"', false)
        ->assertDontSee('value="80"', false)
        ->assertDontSee('value="70"', false);
});

it('memuat field konteks, label aksesibel, batas materi, dan pengaman klik ganda', function () {
    ($this->buatSiswaBernilai)('Siswa Label', null, null);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.penilaian.show', $this->mengajar))
        ->assertOk()
        ->assertSee('name="tahun_ajaran_id" value="'.$this->tahun->id.'"', false)
        ->assertSee('name="semester" value="Ganjil"', false)
        ->assertSee('maxlength="255"', false)
        ->assertSee('aria-label="Nilai sumatif Siswa Label"', false)
        ->assertSee('aria-label="Nilai SAS / STS Siswa Label"', false)
        ->assertSee(':disabled="menyimpan"', false);
});

it('menampilkan predikat capaian sesuai batas nilai rapor', function () {
    ($this->buatSiswaBernilai)('Siswa A', 86, 86);
    ($this->buatSiswaBernilai)('Siswa B', 76, 76);
    ($this->buatSiswaBernilai)('Siswa C', 61, 61);
    ($this->buatSiswaBernilai)('Siswa D', 60.99, 60.99);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.penilaian.show', $this->mengajar))
        ->assertOk()
        ->assertSeeInOrder([
            'Siswa A', 'Sangat Baik • Sangat Menguasai',
            'Peserta didik menunjukkan penguasaan yang sangat baik dalam Bab Cahaya.',
            'Siswa B', 'Baik • Sudah Mampu',
            'Peserta didik menunjukkan penguasaan yang baik dalam Bab Cahaya.',
            'Siswa C', 'Cukup • Mulai Berkembang',
            'Peserta didik cukup mampu dalam Bab Cahaya, namun masih perlu bimbingan pada bagian tertentu.',
            'Siswa D', 'Perlu Bimbingan • Belum Mencapai',
            'Peserta didik memerlukan bimbingan dalam Bab Cahaya.',
        ]);
});
