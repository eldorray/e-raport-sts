<?php

namespace Database\Factories;

use App\Models\SchoolProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SchoolProfile>
 */
class SchoolProfileFactory extends Factory
{
    protected $model = SchoolProfile::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' School',
            'nsm' => $this->faker->unique()->numerify('###########'),
            'npsn' => $this->faker->unique()->numerify('########'),
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->streetAddress,
            'district' => $this->faker->citySuffix,
            'city' => $this->faker->city,
            'province' => $this->faker->state,
            'headmaster' => $this->faker->name,
            'nip_headmaster' => $this->faker->optional()->numerify('###############'),
            'logo' => $this->faker->optional()->lexify('logo_????????.png'),
        ];
    }
}
