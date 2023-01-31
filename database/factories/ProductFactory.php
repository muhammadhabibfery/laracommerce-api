<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\MerchantAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = fake()->words(2, true);
        $stock = rand(1, 10);

        return [
            'category_id' => (Category::inRandomOrder()->first())->id,
            'merchant_account_id' => MerchantAccount::factory(),
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => fake()->paragraphs(3, true),
            'price' => rand(5000, 999000),
            'weight' => rand(500, 10000),
            'stock' => $stock,
            'sold' => rand(1, $stock)
        ];
    }
}
