<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MengajarTahfidz;
use App\Models\Siswa;
use App\Models\TahfidzPenilaian;
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

    $this->guru = Guru::factory()->create(['nama' => 'Ustadz Tahfidz', 'is_active' => true]);
    $this->userGuru = User::findOrFail($this->guru->user_id);

    $kelas = Kelas::create(['nama' => '2A', 'tingkat' => '2', 'tahun_ajaran_id' => $this->tahun->id]);

    MengajarTahfidz::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'kelas_id' => $kelas->id,
        'guru_id' => $this->guru->id,
    ]);

    $this->siswa = Siswa::create([
        'tahun_ajaran_id' => $this->tahun->id,
        'kelas_id' => $kelas->id,
        'nis' => '4001',
        'nama' => 'Siswa Tahfidz',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $this->sesi = [
        'selected_tahun_ajaran_id' => $this->tahun->id,
        'selected_semester' => 'Ganjil',
    ];
});

it('memakai flash status yang ditampilkan layout setelah reset', function () {
    TahfidzPenilaian::create([
        'siswa_id' => $this->siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'surah_hafalan' => ['an-nas'],
    ]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->delete(route('tahfidz.reset', $this->siswa))
        ->assertRedirect(route('tahfidz.show', $this->siswa))
        ->assertSessionHas('status', 'Penilaian tahfidz berhasil direset.')
        ->assertSessionMissing('success');

    expect(TahfidzPenilaian::count())->toBe(0);
});

it('mengisi ulang form tahfidz dari input lama dan membatasi panjang deskripsi', function () {
    TahfidzPenilaian::create([
        'siswa_id' => $this->siswa->id,
        'tahun_ajaran_id' => $this->tahun->id,
        'semester' => 'Ganjil',
        'predikat_adab' => 'A',
        'deskripsi_adab' => 'Deskripsi tersimpan',
        'surah_hafalan' => ['an-nas'],
    ]);

    $response = $this->actingAs($this->userGuru)
        ->withSession($this->sesi + [
            '_old_input' => [
                'juz' => '30',
                'pembimbing_id' => (string) $this->guru->id,
                'predikat_adab' => 'C',
                'deskripsi_adab' => 'Deskripsi ketikan',
                'surah_hafalan' => ['al-falaq'],
            ],
        ])
        ->get(route('tahfidz.show', $this->siswa))
        ->assertOk()
        ->assertSee('value="Deskripsi ketikan"', false)
        ->assertDontSee('value="Deskripsi tersimpan"', false)
        ->assertSee('<option value="C" selected>', false)
        ->assertSee('name="deskripsi_adab" maxlength="100"', false)
        ->assertSee('name="deskripsi_tajwid" maxlength="100"', false)
        ->assertSee('name="deskripsi_makhorijul" maxlength="100"', false);

    $html = $response->getContent();
    expect($html)->toMatch('/value="al-falaq"\s+[^>]*checked/')
        ->not->toMatch('/value="an-nas"\s+[^>]*checked/');
});

it('mempertahankan input ketika tahun ajaran tidak aktif', function () {
    $this->tahun->update(['is_active' => false]);

    $this->actingAs($this->userGuru)
        ->withSession($this->sesi)
        ->post(route('tahfidz.store', $this->siswa), [
            'juz' => 30,
            'deskripsi_adab' => 'Sopan',
        ])
        ->assertSessionHasErrors('tahun_ajaran')
        ->assertSessionHasInput('deskripsi_adab', 'Sopan');
});
