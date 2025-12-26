<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SchoolProfile;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            AdminSeeder::class,
        ]);

        SchoolProfile::factory()->create([
            'name' => 'MI Daarul hikmah',
            'nsm' => '111236710070',
            'npsn' => '69357623',
            'email' => 'mi.daarulhikmah@example.com',
            'address' => 'Jl. Pembangunann 3, Rt. 05/05',
            'district' => 'Neglsari',
            'city' => 'Tangerang',
            'province' => 'Banten',
            'headmaster' => 'Dra. Nurjanah',
            'nip_headmaster' => '1987654321',
            'logo' => 'logo_mi_daarul_hikmah.png',
        ]);
    }
}
