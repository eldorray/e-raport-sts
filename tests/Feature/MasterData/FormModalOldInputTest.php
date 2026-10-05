<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

/**
 * Membuat siswa minimal untuk pengujian form.
 */
function formModalSiswa(TahunAjaran $tahun, string $nis, string $nama, ?int $kelasId = null): Siswa
{
    return Siswa::create([
        'tahun_ajaran_id' => $tahun->id,
        'kelas_id' => $kelasId,
        'nis' => $nis,
        'nama' => $nama,
        'jenis_kelamin' => 'L',
    ]);
}

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->tahun = TahunAjaran::create([
        'nama' => '2026/2027',
        'tahun_mulai' => 2026,
        'tahun_selesai' => 2027,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);
    $this->sesi = ['selected_tahun_ajaran_id' => $this->tahun->id, 'selected_semester' => 'Ganjil'];
});

it('membuka ulang modal tambah siswa dengan isian lama setelah validasi gagal', function () {
    $html = $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->from(route('siswa.index'))
        ->followingRedirects()
        ->post(route('siswa.store'), [
            '_modal' => 'create',
            'nis' => '20249999',
            'nama' => '',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Kota Isian Lama',
            'alamat_wali' => 'Alamat Wali Lama',
        ])
        ->assertOk()
        ->assertSee('data-open-modal="create"', false)
        ->getContent();

    expect(substr_count($html, 'value="20249999"'))->toBe(1)
        ->and(substr_count($html, 'value="Kota Isian Lama"'))->toBe(1)
        ->and($html)->toContain('>Alamat Wali Lama</textarea>')
        ->and($html)->toContain('value="P" selected');
});

it('membuka ulang modal edit siswa yang gagal disimpan dengan isian lama', function () {
    formModalSiswa($this->tahun, '1001', 'Siswa Pertama');
    $siswa = formModalSiswa($this->tahun, '1002', 'Siswa Kedua');

    $html = $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->from(route('siswa.index'))
        ->followingRedirects()
        ->put(route('siswa.update', $siswa), [
            '_modal' => 'edit-'.$siswa->id,
            'nis' => '1001',
            'nama' => 'Nama Belum Tersimpan',
            'jenis_kelamin' => 'L',
        ])
        ->assertOk()
        ->assertSee('data-open-modal="edit-'.$siswa->id.'"', false)
        ->assertSee('data-modal="edit-'.$siswa->id.'"', false)
        ->getContent();

    expect(substr_count($html, 'value="Nama Belum Tersimpan"'))->toBe(1);
});

it('tidak membuka modal apa pun saat tidak ada error', function () {
    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('siswa.index'))
        ->assertOk()
        ->assertSee('data-open-modal=""', false);
});

it('membuka ulang modal tambah guru dengan isian lama setelah validasi gagal', function () {
    $html = $this->actingAs($this->admin)
        ->from(route('guru.index'))
        ->followingRedirects()
        ->post(route('guru.store'), [
            '_modal' => 'create',
            'nama' => 'Guru Isian Lama',
            'nip' => '',
            'jenis_kelamin' => 'P',
            'pendidikan' => 'S2 Pendidikan',
            'password' => 'rahasia',
            'is_active' => '0',
        ])
        ->assertOk()
        ->assertSee('data-open-modal="create"', false)
        ->getContent();

    expect(substr_count($html, 'value="Guru Isian Lama"'))->toBe(1)
        ->and(substr_count($html, 'value="S2 Pendidikan"'))->toBe(1)
        ->and($html)->toContain('value="P" selected')
        ->and($html)->not->toContain('value="rahasia"');
});

it('membuka ulang modal tambah kelas dengan isian lama setelah validasi gagal', function () {
    $guru = Guru::create([
        'user_id' => User::factory()->create(['role' => 'guru'])->id,
        'nama' => 'Wali Pilihan',
        'nip' => '5551',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $html = $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->from(route('kelas.index'))
        ->followingRedirects()
        ->post(route('kelas.store'), [
            '_modal' => 'create',
            'nama' => 'Kelas Isian Lama',
            'tingkat' => '',
            'jurusan' => 'IPA',
            'guru_id' => $guru->id,
        ])
        ->assertOk()
        ->assertSee('data-open-modal="create"', false)
        ->getContent();

    expect(substr_count($html, 'value="Kelas Isian Lama"'))->toBe(1)
        ->and(substr_count($html, 'value="IPA"'))->toBe(1)
        ->and($html)->toContain('value="'.$guru->id.'" selected');
});

it('membuka ulang modal edit user dan tidak mengisi form tambah dengan isian edit', function () {
    $user = User::factory()->create(['role' => 'guru', 'name' => 'User Lama']);
    $lain = User::factory()->create(['role' => 'guru']);

    $html = $this->actingAs($this->admin)
        ->from(route('users.index'))
        ->followingRedirects()
        ->put(route('users.update', $user), [
            '_modal' => 'edit-'.$user->id,
            'name' => 'Nama Edit Lama',
            'email' => $lain->email,
            'role' => 'guru',
            'is_active' => '1',
        ])
        ->assertOk()
        ->assertSee('data-open-modal="edit-'.$user->id.'"', false)
        ->getContent();

    expect(substr_count($html, 'value="Nama Edit Lama"'))->toBe(1)
        ->and($html)->toContain('id="edit_name" name="name" type="text" value="Nama Edit Lama"');
});

it('membuka ulang modal tambah siswa wali kelas dan tidak menduplikasi flash', function () {
    $userGuru = User::factory()->create(['role' => 'guru']);
    $guru = Guru::create([
        'user_id' => $userGuru->id,
        'nama' => 'Wali Kelas Uji',
        'nip' => '7771',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
    $kelas = Kelas::create([
        'nama' => '1A',
        'tingkat' => 'I',
        'tahun_ajaran_id' => $this->tahun->id,
        'guru_id' => $guru->id,
    ]);
    formModalSiswa($this->tahun, '3001', 'Siswa Wali', $kelas->id);

    $html = $this->actingAs($userGuru)
        ->withSession($this->sesi)
        ->from(route('wali-kelas.siswa.index'))
        ->followingRedirects()
        ->post(route('wali-kelas.siswa.store'), [
            '_modal' => 'create',
            'nis' => '3002',
            'nama' => '',
            'jenis_kelamin' => 'L',
            'nama_ayah' => 'Ayah Isian Lama',
        ])
        ->assertOk()
        ->assertSee('data-open-modal="create"', false)
        ->assertSee(__('Keluarkan dari kelas'))
        ->getContent();

    expect(substr_count($html, 'value="Ayah Isian Lama"'))->toBe(1);

    $flash = $this->actingAs($userGuru)
        ->withSession($this->sesi + ['status' => 'Pesan Status Tunggal'])
        ->get(route('wali-kelas.siswa.index'))
        ->assertOk()
        ->getContent();

    expect(substr_count($flash, 'Pesan Status Tunggal'))->toBe(1);
});
