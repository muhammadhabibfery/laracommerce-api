<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\MerchantAccount;
use Tests\Validations\CouponValidation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;

class CouponTest extends TestCase
{
    use CouponValidation;

    public User $user;
    public MerchantAccount $merchant;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->user = $this->authenticatedUser(['role' => 'MERCHANT']);
        $this->createBanking();
        $this->merchant = $this->createMerchantAccount(['user_id' => $this->user->id, 'balance' => 2000]);
    }

    /** @test */
    public function merchant_can_create_coupon()
    {
        $coupon = [
            'name' => 'Coupon 1',
            'discount_amount' => 1000,
            'expired' => Carbon::parse(now()->addDays(3))->format(config('app.date_format'))
        ];

        $res = $this->postJson(route('coupons.store'), $coupon, $this->header);

        $res->assertCreated()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            );

        $this->assertDatabaseHas('coupons', Arr::except($coupon, ['expired']));
    }

    /** @test */
    public function show_all_coupons_collection()
    {
        $coupon1 = $this->createCoupon(['merchant_account_id' => $this->merchant->id]);
        $coupon2 = $this->createCoupon(['merchant_account_id' => $this->merchant->id]);

        $res = $this->getJson(route('coupons.index'), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->count('data', 2)
            )->assertJsonPath('data.0.name', $coupon1->name)
            ->assertJsonPath('data.1.name', $coupon2->name);

        $this->assertDatabaseCount('coupons', 2)
            ->assertDatabaseHas('coupons', Arr::except($coupon1->toArray(), ['created_at', 'updated_at', 'expired']))
            ->assertDatabaseHas('coupons', Arr::except($coupon2->toArray(), ['created_at', 'updated_at', 'expired']));
    }

    /** @test */
    public function show_the_coupons_collection_by_search()
    {
        $coupon1 = $this->createCoupon(['merchant_account_id' => $this->merchant->id]);
        $coupon2 = $this->createCoupon(['merchant_account_id' => $this->merchant->id]);

        $res = $this->getJson(route('coupons.index', ['keyword' => $coupon2->name]), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->count('data', 1)
            )->assertJsonPath('data.0.name', $coupon2->name);

        $this->assertDatabaseCount('coupons', 2)
            ->assertDatabaseHas('coupons', Arr::except($coupon2->toArray(), ['created_at', 'updated_at', 'expired']));
    }

    /** @test */
    public function merchant_can_delete_coupon()
    {
        $coupon = $this->createCoupon(['merchant_account_id' => $this->merchant->id]);

        $res = $this->deleteJson(route('coupons.destroy', $coupon), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message'])
            )->assertJsonPath('message', 'The coupon deleted successfully.');

        $this->assertDatabaseMissing('products', Arr::only($coupon->toArray(), ['name']));
    }
}
