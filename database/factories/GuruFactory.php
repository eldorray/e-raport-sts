<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guru>
 */
class GuruFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();
        $nip = $this->faker->unique()->numerify('###############');
        $defaultPassword = 'password123';
        return [
            'user_id' => \App\Models\User::factory()->create([
                'role' => 'guru',
                'name' => $name,
                'nip' => $nip,
                'password' => Hash::make($defaultPassword),
            ])->id,
            'nama' => $name,
            'nip' => $nip,
            'nik' => $this->faker->unique()->numerify('##################'),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'tempat_lahir' => $this->faker->city,
            'tanggal_lahir' => $this->faker->date(),
            'pendidikan' => $this->faker->randomElement(['S1', 'S2', 'S3']),
            'wali_kelas' => null,
            'jtm' => $this->faker->numberBetween(12, 24),
            'initial_password' => $defaultPassword,
            'foto_path' => null,
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
