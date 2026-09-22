<?php

test('registration is disabled and redirected to login', function () {
    $response = $this->get('/register');

    $response->assertRedirect('/login');
});

test('new users cannot register publicly', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // POST ke /register mengikuti redirect ke /login, tidak membuat user baru
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});
