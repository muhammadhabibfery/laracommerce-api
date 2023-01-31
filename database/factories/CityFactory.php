<?php

namespace Database\Factories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'province_id' => Province::factory(),
            'name' => fake()->words(rand(2, 3), true),
            'type' => fake()->randomElement(['Kota', 'Kabupaten']),
            'postal_code' => rand(11111, 99999)
        ];
    }
}
