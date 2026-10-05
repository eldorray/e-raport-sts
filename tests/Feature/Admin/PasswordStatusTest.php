<?php

declare(strict_types=1);

use App\Models\User;

test('ganti kata sandi menampilkan pesan yang mudah dibaca', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/settings/password')
        ->put('/settings/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', __('Kata sandi berhasil diperbarui.'));

    $this->actingAs($user)
        ->get('/settings/password')
        ->assertDontSee('password-updated');
});
