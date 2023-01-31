<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Finance>
 */
class FinanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'description' => fake()->sentences(3, true),
            'type' => fake()->randomElement(['DEBIT', 'KREDIT']),
            'amount' => rand(100000, 999000),
            'status' => fake()->randomElement(['PENDING', 'SUCCESS', 'FAILED', 'ACCEPT', 'REJECT']),
            'balance' => rand(1000000, 5000000)
        ];
    }
}
