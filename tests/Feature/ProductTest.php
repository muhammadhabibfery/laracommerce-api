<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Arr;
use App\Models\MerchantAccount;
use Tests\Validations\ProductValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;

class ProductTest extends TestCase
{
    use ProductValidation;

    public User $user;
    public MerchantAccount $merchant;
    public Category $category;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->user = $this->authenticatedUser(['role' => 'MERCHANT']);
        $this->category = $this->createCategory();
        $this->createBanking();
        $this->merchant = $this->createMerchantAccount(['user_id' => $this->user->id]);
    }

    /** @test */
    public function merchant_can_create_product()
    {
        $product = [
            'categoryId' => $this->category->id,
            'name' => 'Product 1',
            'description' => 'lorem ipsum',
            'price' => 100000,
            'weight' => 100,
            'stock' => 3
        ];

        $res = $this->postJson(route('products.store'), $product, $this->header);

        $res->assertCreated()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            );

        $this->assertDatabaseHas('products', Arr::except($product, ['categoryId']));
    }

    /** @test */
    public function show_all_products_collection()
    {
        $product1 = $this->createProduct(['merchant_account_id' => $this->merchant->id]);
        $product2 = $this->createProduct(['merchant_account_id' => $this->merchant->id]);

        $res = $this->getJson(route('products.index'), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->count('data', 2)
            )->assertJsonPath('data.0.name', $product1->name)
            ->assertJsonPath('data.1.name', $product2->name);

        $this->assertDatabaseCount('products', 2)
            ->assertDatabaseHas('products', Arr::only($product1->toArray(), ['merchant_account_id', 'name', 'price', 'weight', 'stock']))
            ->assertDatabaseHas('products', Arr::only($product2->toArray(), ['merchant_account_id', 'name', 'price', 'weight', 'stock']));
    }

    /** @test */
    public function show_the_products_collection_by_search()
    {
        $product1 = $this->createProduct(['merchant_account_id' => $this->merchant->id]);
        $product2 = $this->createProduct(['merchant_account_id' => $this->merchant->id]);

        $res = $this->getJson(route('products.index', ['keyword' => $product2->name]), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->count('data', 1)
            )->assertJsonPath('data.0.name', $product2->name);

        $this->assertDatabaseCount('products', 2)
            ->assertDatabaseHas('products', Arr::only($product2->toArray(), ['merchant_account_id', 'name', 'price', 'weight', 'stock']));
    }

    /** @test */
    public function show_a_product()
    {
        $product1 = $this->createProduct(['merchant_account_id' => $this->merchant->id]);
        $product2 = $this->createProduct(['merchant_account_id' => $this->merchant->id]);

        $res = $this->getJson(route('products.show', $product1), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            )->assertJsonPath('data.name', $product1->name);

        $this->assertDatabaseCount('products', 2)
            ->assertDatabaseHas('products', Arr::only($product2->toArray(), ['merchant_account_id', 'name', 'price', 'weight', 'stock']));
    }

    /** @test */
    public function merchant_can_update_product()
    {
        $name = ['name' => 'Product 100'];
        $product = $this->createProduct(['merchant_account_id' => $this->merchant->id]);
        $data = array_merge($product->toArray(), $name);

        $res = $this->patchJson(route('products.update', $product), $data, $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            )->assertJsonPath('data.name', head($name));

        $this->assertDatabaseHas('products', $name);
    }

    /** @test */
    public function merchant_can_delete_product()
    {
        $product = $this->createProduct(['merchant_account_id' => $this->merchant->id]);

        $res = $this->deleteJson(route('products.destroy', $product), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message'])
            )->assertJsonPath('message', 'The product deleted successfully.');

        $this->assertDatabaseMissing('products', Arr::only($product->toArray(), ['name']));
    }
}
