<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // $source = public_path('images');
        // $target = storage_path('app/public/product-images');
        // if (!file_exists($target)) mkdir($target, 666, true);
        // $name = fake()->file($source, $target, false);
        // $slug = str($name)->slug();

        $name = fake()->word();
        return [
            'product_id' => Product::factory(),
            'name' => $name,
            'slug' => str($name)->slug()
        ];
    }
}
