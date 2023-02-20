<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => (User::inRandomOrder()->first())->id,
            'invoice_number' => 'test-' . rand(111, 999),
            'total_price' => 650000,
            'coupons' => fake()->words(3, true),
            'courier_services' => fake()->sentence(),
            'status' => 'SUCCESS'
        ];
    }
}
