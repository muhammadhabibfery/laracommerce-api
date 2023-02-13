<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LandingPageTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $userMerchant = $this->authenticatedUser(['role' => 'MERCHANT']);
        $this->createBanking();
        $merchantAccount = $this->createMerchantAccount(['user_id' => $userMerchant->id]);
        $this->createCategory();
        $this->createCategory();
        $this->createCategory();
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 2]);
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 4]);
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 5]);
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 6]);
    }

    /** @test */
    public function the_resource_for_landing_page_should_available()
    {
        $res = $this->getJson(route('landing-page'), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'data.categories', 'data.products', 'data.products.pages'])
                    ->count('data.categories', 3)
                    ->count('data.products.data', 3)
            );
    }
}
