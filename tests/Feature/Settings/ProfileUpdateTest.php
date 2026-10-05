<?php

use App\Models\User;

test('profile page is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put('/settings/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put('/settings/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can no longer delete their own account', function () {
    $user = User::factory()->create(['role' => 'guru']);

    $status = $this
        ->actingAs($user)
        ->delete('/settings/profile')
        ->status();

    expect($status)->toBeIn([404, 405]);

    $this->assertAuthenticatedAs($user);
    expect($user->fresh())->not->toBeNull();

    $this->actingAs($user)
        ->get('/settings/profile')
        ->assertOk()
        ->assertDontSee(__('Delete account'));
});
