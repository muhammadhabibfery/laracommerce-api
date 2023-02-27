<?php

namespace Tests;

use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Banking;
use App\Models\Finance;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Laravel\Sanctum\Sanctum;
use App\Models\MerchantAccount;
use Illuminate\Database\Eloquent\Collection;
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
     * @param  int $count
     * @return User|Collection
     */
    public function createUser(?array $data = [], ?int $count = 1): User|Collection
    {
        $users = User::factory()->count($count)->create($data);

        return $count < 2 ? $users->first() : $users;
    }

    /**
     * Create authenticated user.
     *
     * @param  array $data
     * @param  bool $api
     * @return User
     */
    public function authenticatedUser(?array $data = [], ?bool $api = true): User
    {
        $user = $this->createUser($data);
        $api ? Sanctum::actingAs($user, ['*']) : $this->actingAs($user);
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
     * @param  int $count
     * @return Category|Collection
     */
    public function createCategory(?array $data = [], ?int $count = 1): Category|Collection
    {
        $categories = Category::factory()->count($count)->create($data);

        return $count < 2 ? $categories->first() : $categories;
    }

    /**
     * Create a banking instance.
     *
     * @param  array $data
     * @param  int $count
     * @return Banking|Collection
     */
    public function createBanking(?array $data = [], ?int $count = 1): Banking|Collection
    {
        $bankings = Banking::factory()->count($count)->create($data);

        return $count < 2 ? $bankings->first() : $bankings;
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
    /**
     * Create a finance instance.
     *
     * @param  array $data
     * @param  int $count
     * @return Finance|Collection
     */
    public function createFinance(?array $data = [], ?int $count = 1): Finance|Collection
    {
        $finances = Finance::factory()->count($count)->create($data);

        return $count < 2 ? $finances->first() : $finances;
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
