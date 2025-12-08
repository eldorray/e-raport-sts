<?php

use App\Models\TahunAjaran;
use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    $tahun = TahunAjaran::create([
        'nama' => '2024/2025',
        'tahun_mulai' => 2024,
        'tahun_selesai' => 2025,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'login' => $user->email,
        'password' => 'password',
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();
    $tahun = TahunAjaran::create([
        'nama' => '2024/2025',
        'tahun_mulai' => 2024,
        'tahun_selesai' => 2025,
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->post('/login', [
        'login' => $user->email,
        'password' => 'wrong-password',
        'tahun_ajaran_id' => $tahun->id,
        'semester' => 'Ganjil',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
