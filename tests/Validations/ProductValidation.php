<?php

namespace Tests\Validations;

use Illuminate\Support\Arr;

trait ProductValidation
{
    /** @test */
    public function all_fields_are_required()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('products.store'), [], $this->header);

        $res->assertUnprocessable()
            ->assertJsonCount(6, 'errors');
    }

    /** @test */
    public function the_category_id_field_should_be_exists()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('products.store'), ['category_id' => 99], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.category_id.0', 'The selected category is invalid.');
    }

    /** @test */
    public function the_name_field_should_be_unique()
    {
        $this->withExceptionHandling();

        $product = Arr::except($this->createProduct()->toArray(), ['merchant_account_id']);
        $product = array_merge($product, ['categoryId' => $product['category_id']]);

        $res = $this->postJson(route('products.store'), $product, $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'The name has already been taken.');
    }
}
