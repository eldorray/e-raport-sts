<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed the admin user.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'fahmie@gmail.com'],
            [
                'name' => 'Admin',
                'email' => 'fahmie@gmail.com',
                'password' => 'password123',
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
