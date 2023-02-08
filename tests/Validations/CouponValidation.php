<?php

namespace Tests\Validations;

use Carbon\Carbon;

trait CouponValidation
{
    /** @test */
    public function all_fields_are_required()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('coupons.store'), [], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.expired.0', 'The expired field is required.')
            ->assertJsonPath('errors.name.0', 'The name field is required.')
            ->assertJsonCount(2, 'errors');
    }

    /** @test */
    public function the_name_field_should_be_unique()
    {
        $this->withExceptionHandling();

        $coupon = $this->createCoupon(['name' => 'Coupon 1', 'merchant_account_id' => $this->merchant->id])->toArray();
        $coupon = array_merge($coupon, ['expired' => Carbon::parse(now()->addDays(3)->format(config('app.date_format')))]);

        $res = $this->postJson(route('coupons.store'), $coupon, $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'The name has already been taken.');
    }

    /** @test */
    public function the_expired_date_field_should_be_a_valid_format()
    {
        $this->withExceptionHandling();

        $this->createCoupon(['name' => 'Coupon 1', 'merchant_account_id' => $this->merchant->id])->toArray();
        $coupon = [
            'name' => 'Coupon 2',
            'expired' => Carbon::parse(now())->format('Y-m-d')
        ];

        $res = $this->postJson(route('coupons.store'), $coupon, $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.expired.0', 'The expired does not match the format Y-m-d H:i (example: 2023-12-30 00:00).');
    }

    /** @test */
    public function the_expired_date_field_should_be_after_tomorrow()
    {
        $this->withExceptionHandling();

        $this->createCoupon(['name' => 'Coupon 1', 'merchant_account_id' => $this->merchant->id])->toArray();
        $coupon = [
            'name' => 'Coupon 2',
            'expired' => Carbon::parse(now())->format(config('app.date_format'))
        ];

        $res = $this->postJson(route('coupons.store'), $coupon, $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.expired.0', 'The expired must be a date after tomorrow.');
    }

    /** @test */
    public function show_the_coupon_not_found()
    {
        $this->withExceptionHandling();

        $this->createCoupon(['merchant_account_id' => $this->merchant->id]);
        $this->createCoupon(['merchant_account_id' => $this->merchant->id]);

        $res = $this->getJson(route('coupons.index', ['keyword' => 'xxx']), $this->header);

        $res->assertNotFound()
            ->assertJsonPath('message', 'Coupon not found.');
    }
}
