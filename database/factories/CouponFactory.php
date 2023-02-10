<?php

namespace Database\Factories;

use App\Models\MerchantAccount;
use Carbon\Carbon;
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
            'discount_amount' => rand(1111, 250000),
            'expired' => Carbon::parse(now()->addDays(rand(3, 10)))->format(config('app.date_format'))
        ];
    }
}
