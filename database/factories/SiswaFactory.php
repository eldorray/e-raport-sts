<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Siswa>
 */
class SiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Buat Nama Palsu Indonesia
            'nama' => $this->faker->name(),
            // Buat NISN 10 digit angka unik
            'nisn' => $this->faker->unique()->numerify('##########'),
            'tahun_ajaran_id' => \App\Models\TahunAjaran::inRandomOrder()->first()->id,
            'nis' => $this->faker->unique()->numerify('##########'),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'tempat_lahir' => $this->faker->city,
            'tanggal_lahir' => $this->faker->date(),
            'alamat' => $this->faker->streetAddress,
            'nama_ayah' => $this->faker->name,
            'nama_ibu' => $this->faker->name,
            'alamat_orang_tua' => $this->faker->streetAddress,
            'agama' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            // PENTING: Ambil acak ID dari tabel classes yang sudah ada
            // (Pastikan kamu sudah punya data Kelas dulu ya!)
            'kelas_id' => \App\Models\Kelas::inRandomOrder()->first()->id,
        ];
    }
}
