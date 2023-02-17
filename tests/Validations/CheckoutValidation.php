<?php

namespace Tests\Validations;

use Illuminate\Support\Arr;

trait CheckoutValidation
{
    /** @test */
    public function the_phone_field_should_be_between_12_or_13_and_the_city_id_field_should_be_exists_in_shipping_address_feature()
    {
        $data = array_merge(Arr::only($this->userCustomer->toArray(), ['name', 'address']), ['phone' => '123', 'city_id' => 1000]);

        $res = $this->postJson(route('checkout.shipping'), $data, $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.phone.0', 'The phone must be between 11 and 13 digits.')
            ->assertJsonPath('errors.city_id.0', 'The selected city id is invalid.');
    }
}
