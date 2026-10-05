<?php

declare(strict_types=1);

use App\Models\TahunAjaran;
use App\Models\User;

test('pesan terlalu banyak percobaan login muncul di field login', function () {
    $this->freezeTime();

    $user = User::factory()->create();
    $tahun = TahunAjaran::create([
        'nama' => '2024/2025',
        'tahun_mulai' => 2024,
        'tahun_selesai' => 2025,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $payload = [
        'login' => $user->email,
        'password' => 'wrong-password',
        'tahun_ajaran_id' => $tahun->id,
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', $payload)->assertSessionHasErrors('login');
    }

    $this->post('/login', [...$payload, 'password' => 'password'])
        ->assertSessionHasErrors(['login' => trans('auth.throttle', [
            'seconds' => 60,
            'minutes' => 1,
        ])])
        ->assertSessionDoesntHaveErrors('email');

    $this->assertGuest();
});
