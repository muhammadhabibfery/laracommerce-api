<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\City;
use App\Models\User;
use App\Models\Province;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Validations\CheckoutValidation;

class CheckoutTest extends TestCase
{
    use CheckoutValidation;

    public User $userCustomer;
    public City $city;

    public function setUp(): void
    {
        parent::setUp();
        $this->province = Province::factory()->create();
        City::factory()->create(['province_id' => $this->province->id]);
        City::factory()->create(['province_id' => $this->province->id]);
        $this->city = City::factory()->create(['province_id' => $this->province->id]);
        $this->userCustomer = $this->authenticatedUser(['role' => 'CUSTOMER']);
        $userMerchant = $this->createUser(['role' => 'MERCHANT']);
        $this->createBanking();
        $merchantAccount = $this->createMerchantAccount(['user_id' => $userMerchant->id]);
        $this->createCategory();
        $this->createCategory();
        $this->createCategory();
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 2]);
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 4]);
        $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 5]);
        $product = $this->createProduct(['merchant_account_id' => $merchantAccount->id, 'sold' => 6, 'description' => 'lorem ipsum rerum']);
    }

    /** @test */
    public function a_customer_can_update_the_shipping_address()
    {
        $addressName = 'jl. test';
        $data = array_merge(
            Arr::only($this->userCustomer->toArray(), ['name', 'phone', 'address', 'city_id']),
            ['address' => $addressName, 'city_id' => $this->city->id]
        );


        $res = $this->postJson(route('checkout.shipping'), $data, $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            )
            ->assertJsonPath('data.address', $addressName)
            ->assertJsonPath('data.city.id', $this->city->id);
    }
}
