<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /** Panjang minimum kata sandi admin awal. */
    private const PANJANG_MINIMUM = 12;

    /**
     * Membuat akun admin pertama dari ADMIN_EMAIL dan ADMIN_PASSWORD di .env.
     *
     * Tidak ada kredensial bawaan: tanpa kedua nilai itu seeder tidak membuat apa pun.
     * Akun yang sudah ada tidak disentuh, jadi menjalankan ulang seeder tidak mengembalikan
     * kata sandi, peran, atau status aktif akun tersebut.
     */
    public function run(): void
    {
        $email = (string) config('auth.admin_awal.email');
        $sandi = (string) config('auth.admin_awal.password');

        if ($email === '' || $sandi === '') {
            $this->command->warn('AdminSeeder dilewati: isi ADMIN_EMAIL dan ADMIN_PASSWORD di .env lebih dulu.');

            return;
        }

        if (mb_strlen($sandi) < self::PANJANG_MINIMUM) {
            $this->command->warn('AdminSeeder dilewati: ADMIN_PASSWORD minimal '.self::PANJANG_MINIMUM.' karakter.');

            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command->info('AdminSeeder: akun '.$email.' sudah ada, tidak diubah.');

            return;
        }

        User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => $sandi,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
