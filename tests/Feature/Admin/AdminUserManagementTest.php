<?php

declare(strict_types=1);

use App\Models\Guru;
use App\Models\User;

test('admin tidak dapat menonaktifkan akun sendiri saat checkbox aktif tidak dikirim', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->from('/users')
        ->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'admin',
        ])
        ->assertRedirect('/users')
        ->assertSessionHasErrors('is_active');

    expect($admin->fresh()->is_active)->toBeTrue();
});

test('admin tidak dapat mengubah peran akun sendiri', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'guru',
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('role');

    expect($admin->fresh()->role)->toBe('admin');
});

test('admin dapat memperbarui akun sendiri tanpa mengubah peran dan status', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->put("/users/{$admin->id}", [
            'name' => 'Nama Baru',
            'email' => $admin->email,
            'role' => 'admin',
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors();

    expect($admin->fresh()->name)->toBe('Nama Baru')
        ->and($admin->fresh()->is_active)->toBeTrue();
});

test('admin dapat menonaktifkan dan menurunkan admin lain selama masih ada admin aktif', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $lain = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)
        ->put("/users/{$lain->id}", [
            'name' => $lain->name,
            'email' => $lain->email,
            'role' => 'guru',
        ])
        ->assertSessionHasNoErrors();

    $lain->refresh();
    expect($lain->role)->toBe('guru')
        ->and($lain->is_active)->toBeFalse();
});

test('admin aktif terakhir tidak dapat diturunkan atau dinonaktifkan', function () {
    // Sesi admin yang sudah dinonaktifkan masih bisa mengakses halaman.
    $adminNonaktif = User::factory()->create(['role' => 'admin', 'is_active' => false]);
    $adminTerakhir = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($adminNonaktif)
        ->put("/users/{$adminTerakhir->id}", [
            'name' => $adminTerakhir->name,
            'email' => $adminTerakhir->email,
            'role' => 'guru',
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('user');

    $this->actingAs($adminNonaktif)
        ->put("/users/{$adminTerakhir->id}", [
            'name' => $adminTerakhir->name,
            'email' => $adminTerakhir->email,
            'role' => 'admin',
        ])
        ->assertSessionHasErrors('user');

    $adminTerakhir->refresh();
    expect($adminTerakhir->role)->toBe('admin')
        ->and($adminTerakhir->is_active)->toBeTrue();
});

test('admin aktif terakhir tidak dapat dihapus', function () {
    $adminNonaktif = User::factory()->create(['role' => 'admin', 'is_active' => false]);
    $adminTerakhir = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($adminNonaktif)
        ->delete("/users/{$adminTerakhir->id}")
        ->assertSessionHasErrors('user');

    expect($adminTerakhir->fresh())->not->toBeNull();
});

test('pengguna yang terhubung dengan data guru tidak dapat dihapus', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $userGuru = User::factory()->create(['role' => 'guru']);
    $guru = Guru::create([
        'user_id' => $userGuru->id,
        'nama' => 'Guru Terhubung',
        'nip' => '198001010001',
        'jenis_kelamin' => 'L',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->delete("/users/{$userGuru->id}")
        ->assertSessionHasErrors('user');

    expect($userGuru->fresh())->not->toBeNull()
        ->and($guru->fresh())->not->toBeNull();
});

test('admin dapat menghapus pengguna tanpa data guru', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $adminLain = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $userBiasa = User::factory()->create(['role' => 'guru']);

    $this->actingAs($admin)->delete("/users/{$userBiasa->id}")->assertSessionHasNoErrors();
    $this->actingAs($admin)->delete("/users/{$adminLain->id}")->assertSessionHasNoErrors();

    expect($userBiasa->fresh())->toBeNull()
        ->and($adminLain->fresh())->toBeNull();
});

test('guru tidak dapat mengubah atau menghapus pengguna', function () {
    $guru = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($guru)
        ->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'guru',
        ])
        ->assertForbidden();

    $this->actingAs($guru)->delete("/users/{$admin->id}")->assertForbidden();

    expect($admin->fresh()->role)->toBe('admin');
});
