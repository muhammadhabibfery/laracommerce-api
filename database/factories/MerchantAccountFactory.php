<?php

namespace Database\Factories;

use App\Models\Banking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MerchantAccount>
 */
class MerchantAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = fake()->unique()->name();

        return [
            'banking_id' => (Banking::inRandomOrder()->first())->id,
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => str($name)->slug()->value(),
            'address' => fake()->address(),
            'bank_account_name' => fake()->unique()->name(),
            'bank_account_number' => rand(111111111111111, 999999999999999),
            'bank_branch_name' => fake()->city(),
            'balance' => 0
        ];
    }
}
