<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    public Category $category1;
    public Category $category2;

    public function setUp(): void
    {
        parent::setUp();
        $userMerchant = $this->authenticatedUser(['role' => 'MERCHANT']);
        $this->createBanking();
        $merchantAccount = $this->createMerchantAccount(['user_id' => $userMerchant->id]);
        $this->category1 = $this->createCategory(['name' => 'furniture', 'slug' => 'furniture']);
        $this->category2 = $this->createCategory(['name' => 'electronic', 'slug' => 'electronic']);
        $this->createCategory();
        $this->createCategory();
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 2, 'category_id' => $this->category1->id]);
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 4, 'category_id' => $this->category1->id]);
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 5, 'category_id' => $this->category2->id]);
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 6, 'category_id' => $this->category2->id]);
    }

    /** @test */
    public function show_all_categories()
    {
        $res = $this->getJson(route('categories'), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->count('data', 4)
            );
    }

    /** @test */
    public function show_a_category_with_the_products_related()
    {
        $res = $this->getJson(route('categories.show', $this->category1), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'data.products', 'data.products.pages'])
                    ->count('data.products.data', 2)
            );
    }

    /** @test */
    public function show_the_category_not_found()
    {
        $this->withExceptionHandling();

        $res = $this->getJson(route('categories.show', 'test'), $this->header);

        $res->assertNotFound()
            ->assertJsonPath('message', 'Category test not found.');
    }
}
