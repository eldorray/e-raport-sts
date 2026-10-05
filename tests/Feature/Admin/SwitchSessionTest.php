<?php

declare(strict_types=1);

use App\Models\TahunAjaran;
use App\Models\User;

test('ganti tahun ajaran lewat GET ditolak', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $tahun = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Genap',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get("/tahun-ajaran/switch-session?tahun_ajaran_id={$tahun->id}")
        ->assertStatus(405);

    $this->assertFalse(session()->has('selected_tahun_ajaran_id'));
});

test('ganti tahun ajaran memakai semester dari record, bukan dari input', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $tahun = TahunAjaran::create([
        'nama' => '2025/2026',
        'tahun_mulai' => 2025,
        'tahun_selesai' => 2026,
        'semester' => 'Genap',
        'is_active' => false,
    ]);

    $this->actingAs($user)
        ->from('/dashboard')
        ->patch('/tahun-ajaran/switch-session', [
            'tahun_ajaran_id' => $tahun->id,
            'semester' => 'Ganjil',
        ])
        ->assertRedirect('/dashboard')
        ->assertSessionHasNoErrors()
        ->assertSessionHas('selected_tahun_ajaran_id', $tahun->id)
        ->assertSessionHas('selected_semester', 'Genap')
        ->assertSessionHas('selected_tahun_ajaran_is_active', false);
});
