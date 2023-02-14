<?php

namespace Tests\Feature;

use App\Models\MerchantAccount;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ProductCustomerTest extends TestCase
{
    public MerchantAccount $merchantAccount;
    public Product $product;

    public function setUp(): void
    {
        parent::setUp();
        $userMerchant = $this->authenticatedUser(['role' => 'MERCHANT']);
        $this->createBanking();
        $this->merchantAccount = $this->createMerchantAccount(['user_id' => $userMerchant->id]);
        $this->createCategory();
        $this->createCategory();
        $this->createCategory();
        $this->createProduct(['merchant_account_id' => $this->merchantAccount->id, 'sold' => 2]);
        $this->createProduct(['merchant_account_id' => $this->merchantAccount->id, 'sold' => 4]);
        $this->createProduct(['merchant_account_id' => $this->merchantAccount->id, 'sold' => 5]);
        $this->product = $this->createProduct(['merchant_account_id' => $this->merchantAccount->id, 'sold' => 6, 'description' => 'lorem ipsum rerum']);
    }

    /** @test */
    public function show_detail_merchant_with_the_products_related()
    {
        $res = $this->getJson(route('products.merchant', $this->merchantAccount), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'data.products.data', 'data.products.pages'])
                    ->count('data.products.data', 4)
            );
    }

    /** @test */
    public function show_detail_product_with_category_and_merchant_account_related()
    {
        $res = $this->getJson(route('products.detail', $this->product), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'data.merchant', 'data.category'])
            )
            ->assertJsonPath('data.merchant.name', $this->merchantAccount->name);
    }

    /** @test */
    public function search_products_by_keyword()
    {
        $res = $this->getJson(route('products.search', ['q' => 'rerum']), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->whereContains('data.0', $this->product->description)
            );
    }

    /** @test */
    public function show_the_message_not_found_if_the_search_products_does_not_exists()
    {
        $res = $this->getJson(route('products.search', ['q' => 'xxx']), $this->header);

        $res->assertNotFound()
            ->assertJsonPath('message', 'Products xxx not found.');
    }
}
