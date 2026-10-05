<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Mengajar;
use App\Models\TahunAjaran;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->tahun = TahunAjaran::create([
        'nama' => '2026/2027',
        'tahun_mulai' => 2026,
        'tahun_selesai' => 2027,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);
    $this->kelas = Kelas::create(['nama' => '1A', 'tingkat' => 'I', 'tahun_ajaran_id' => $this->tahun->id]);
    $this->mapel = MataPelajaran::create(['nama_mapel' => 'Matematika', 'kode' => 'MTK']);
    $this->guru = Guru::create([
        'user_id' => User::factory()->create(['role' => 'guru'])->id,
        'nama' => 'Guru Matematika',
        'nip' => '19800101',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);
    $this->sesi = ['selected_tahun_ajaran_id' => $this->tahun->id, 'selected_semester' => 'Ganjil'];
});

it('modal tambah jadwal mengirim field dalam format items yang diminta store', function () {
    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->get(route('mengajar.index'))
        ->assertOk()
        ->assertSee('name="items[0][mata_pelajaran_id]"', false)
        ->assertSee('name="items[0][guru_id]"', false)
        ->assertSee('name="items[0][jtm]"', false)
        ->assertDontSee('name="mata_pelajaran_id"', false)
        ->assertDontSee('name="guru_id"', false)
        ->assertDontSee('name="jtm"', false);
});

it('menyimpan jadwal dari payload modal tambah jadwal', function () {
    $this->actingAs($this->admin)
        ->withSession($this->sesi)
        ->post(route('mengajar.store'), [
            'kelas_id' => $this->kelas->id,
            'items' => [
                ['mata_pelajaran_id' => $this->mapel->id, 'guru_id' => $this->guru->id, 'jtm' => 4],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $jadwal = Mengajar::sole();

    expect($jadwal->kelas_id)->toBe($this->kelas->id)
        ->and($jadwal->mata_pelajaran_id)->toBe($this->mapel->id)
        ->and($jadwal->guru_id)->toBe($this->guru->id)
        ->and($jadwal->jtm)->toBe(4);
});
