<?php

namespace Tests;

use App\Models\User;
use App\Models\Coupon;
use App\Models\Banking;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Laravel\Sanctum\Sanctum;
use App\Models\MerchantAccount;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    public const RESET_PASSWORD_TOKEN = 'abc123';

    public array $header = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ];

    /**
     * Create a user instance.
     *
     * @param  array $data
     * @return User
     */
    public function createUser(?array $data = []): User
    {
        return User::factory()->create($data);
    }

    /**
     * Create authenticated user.
     *
     * @param  array $data
     * @return User
     */
    public function authenticatedUser(?array $data = []): User
    {
        $user = $this->createUser($data);
        Sanctum::actingAs($user, ['*']);
        return $user;
    }

    /**
     * Create a merchant account instance.
     *
     * @param  array $data
     * @return MerchantAccount
     */
    public function createMerchantAccount(?array $data = []): MerchantAccount
    {
        return MerchantAccount::factory()->create($data);
    }

    /**
     * Create a product instance.
     *
     * @param  array $data
     * @return Product
     */
    public function createProduct(?array $data = []): Product
    {
        return Product::factory()->create($data);
    }

    /**
     * Create a product image instance.
     *
     * @param  array $data
     * @return ProductImage
     */
    public function createProductImage(?array $data = []): ProductImage
    {
        return ProductImage::factory()->create($data);
    }

    /**
     * Create a coupon instance.
     *
     * @param  array $data
     * @return Coupon
     */
    public function createCoupon(?array $data = []): Coupon
    {
        return Coupon::factory()->create($data);
    }

    /**
     * Create a category instance.
     *
     * @param  array $data
     * @return Category
     */
    public function createCategory(?array $data = []): Category
    {
        return Category::factory()->create($data);
    }

    /**
     * Create a banking instance.
     *
     * @param  array $data
     * @return Banking
     */
    public function createBanking(?array $data = []): Banking
    {
        return Banking::factory()->create($data);
    }

    /**
     * Create an order instance.
     *
     * @param  array $data
     * @return Order
     */
    public function createOrder(?array $data = []): Order
    {
        return Order::factory()->create($data);
    }

    public function deleteDirectory(string $directory, string $fileName, ?bool $delete = false): void
    {
        if ($delete) {
            Storage::disk($directory)->assertMissing($fileName);
        } else {
            Storage::disk($directory)->exists($fileName);
            Storage::delete("$directory/$fileName");
        }
    }
}
