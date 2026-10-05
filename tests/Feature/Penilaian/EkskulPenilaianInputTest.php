<?php

declare(strict_types=1);

use App\Models\Ekskul;
use App\Models\EkskulPenilaian;
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

    $this->guru = Guru::factory()->create(['nama' => 'Pembina Pramuka', 'is_active' => true]);
    $this->userGuru = User::findOrFail($this->guru->user_id);
    $this->ekskul = Ekskul::create(['nama' => 'Pramuka', 'guru_id' => $this->guru->id]);

    $kelasLama = Kelas::create(['nama' => '3A', 'tingkat' => '3', 'tahun_ajaran_id' => $this->tahunLama->id]);
    $kelas = Kelas::create(['nama' => '4A', 'tingkat' => '4', 'tahun_ajaran_id' => $this->tahun->id]);

    // Siswa yang sama tercatat sekali per tahun ajaran
    $this->siswaLama = Siswa::create([
        'tahun_ajaran_id' => $this->tahunLama->id,
        'kelas_id' => $kelasLama->id,
        'nis' => '3001',
        'nama' => 'Ahmad Lama',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
    $this->siswa = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $kelas->id,
        'nis' => '3001',
        'nama' => 'Ahmad Baru',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
    $this->siswaNonaktif = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $kelas->id,
        'nis' => '3002',
        'nama' => 'Budi Nonaktif',
        'jenis_kelamin' => 'L',
        'is_active' => false,
    ]);
    $this->siswaLain = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $kelas->id,
        'nis' => '3003',
        'nama' => 'Citra Bukan Peserta',
        'jenis_kelamin' => 'P',
        'is_active' => true,
    ]);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];
});

it('hanya menawarkan siswa aktif dari tahun ajaran sesi sebagai calon peserta', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.ekskul.show', $this->ekskul))
        ->assertOk()
        ->assertViewHas('availableSiswas', function ($siswas): bool {
            return $siswas->pluck('id')->sort()->values()->all() === [$this->siswa->id, $this->siswaLain->id];
        });
});

it('menampilkan nama tahun ajaran di header dan mencegah Enter men-submit pencarian', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->get(route('guru.ekskul.show', $this->ekskul))
        ->assertOk()
        ->assertSee('Tahun ajaran: 2025/2026', false)
        ->assertSee('id="searchSiswa" @keydown.enter.prevent', false);
});

it('mengisi ulang nilai dan catatan ekskul dari input lama', function () {
    EkskulPenilaian::create([
        'ekskul_id' => $this->ekskul->id,
        'guru_id' => $this->guru->id,
        'siswa_id' => $this->siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'nilai' => 90,
        'catatan' => 'Catatan tersimpan',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi + [
            '_old_input' => [
                'nilai' => [$this->siswa->id => '42'],
                'catatan' => [$this->siswa->id => 'Catatan ketikan'],
            ],
        ])
        ->get(route('guru.ekskul.show', $this->ekskul))
        ->assertOk()
        ->assertSee('value="42"', false)
        ->assertSee('value="Catatan ketikan"', false)
        ->assertDontSee('value="Catatan tersimpan"', false);
});

it('menolak menambah peserta dari tahun ajaran lain', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.ekskul.store', $this->ekskul), [
            'action' => 'add',
            'siswa_ids' => [$this->siswaLama->id],
        ])
        ->assertSessionHasErrors(['siswa_ids.0' => 'Siswa yang dipilih tidak terdaftar pada tahun ajaran yang sedang dipilih.']);

    expect(EkskulPenilaian::count())->toBe(0);
});

it('menambah peserta dari tahun ajaran sesi', function () {
    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.ekskul.store', $this->ekskul), [
            'action' => 'add',
            'siswa_ids' => [$this->siswa->id],
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('ekskul_penilaians', [
        'ekskul_id' => $this->ekskul->id,
        'siswa_id' => $this->siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
    ]);
});

it('hanya menyimpan nilai untuk siswa yang sudah menjadi peserta', function () {
    EkskulPenilaian::create([
        'ekskul_id' => $this->ekskul->id,
        'guru_id' => $this->guru->id,
        'siswa_id' => $this->siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.ekskul.store', $this->ekskul), [
            'nilai' => [$this->siswa->id => 88, $this->siswaLain->id => 77, $this->siswaLama->id => 66],
            'catatan' => [$this->siswa->id => 'Rajin', $this->siswaLain->id => 'Tidak boleh tersimpan'],
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    expect(EkskulPenilaian::count())->toBe(1);
    $this->assertDatabaseHas('ekskul_penilaians', [
        'siswa_id' => $this->siswa->id,
        'nilai' => 88,
        'catatan' => 'Rajin',
    ]);
});

it('menolak simpan nilai ekskul dari form yang dibuka sebelum semester diganti di tab lain', function () {
    $peserta = EkskulPenilaian::create([
        'ekskul_id' => $this->ekskul->id,
        'guru_id' => $this->guru->id,
        'siswa_id' => $this->siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->from(route('guru.ekskul.show', $this->ekskul))
        ->post(route('guru.ekskul.store', $this->ekskul), [
            'action' => 'save',
            'tahun_ajaran_id' => $this->tahunLama->id,
            'semester' => 'Genap',
            'nilai' => [$this->siswa->id => '88'],
        ])
        ->assertRedirect(route('guru.ekskul.show', $this->ekskul))
        ->assertSessionHasErrors('tahun_ajaran')
        ->assertSessionHasInput('nilai');

    expect($peserta->fresh()->nilai)->toBeNull();
});

it('tetap menyimpan nilai ekskul bila tahun ajaran dan semester form sama dengan sesi', function () {
    $peserta = EkskulPenilaian::create([
        'ekskul_id' => $this->ekskul->id,
        'guru_id' => $this->guru->id,
        'siswa_id' => $this->siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('guru.ekskul.store', $this->ekskul), [
            'action' => 'save',
            'tahun_ajaran_id' => $this->tahun->id,
            'semester' => 'Ganjil',
            'nilai' => [$this->siswa->id => '88'],
        ])
        ->assertSessionHasNoErrors();

    expect((float) $peserta->fresh()->nilai)->toBe(88.0);
});
