<?php

namespace Database\Factories;

use App\Models\MerchantAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = fake()->words(2, true);

        return [
            'merchant_account_id' => MerchantAccount::factory(),
            'name' => $name,
            'slug' => str($name)->slug(),
            'expired' => now()->addDays(rand(1, 5))
        ];
    }
}
