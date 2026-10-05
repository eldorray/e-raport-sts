<?php

declare(strict_types=1);

use App\Models\User;

it('menampilkan pesan flash warning di layout', function () {
    $this->actingAs(User::factory()->create())
        ->withSession(['warning' => 'Tidak ada kelas di tahun ajaran sumber.'])
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Tidak ada kelas di tahun ajaran sumber.');
});

it('menampilkan pesan flash success di layout', function () {
    $this->actingAs(User::factory()->create())
        ->withSession(['success' => 'Penilaian tahfidz berhasil disimpan.'])
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Penilaian tahfidz berhasil disimpan.');
});
