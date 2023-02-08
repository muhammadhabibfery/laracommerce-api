<?php

namespace Tests\Validations;

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

        $product = $this->createProduct(['name' => 'Product 1', 'merchant_account_id' => $this->merchant->id])->toArray();
        $product = array_merge($product, ['categoryId' => $product['category_id']]);

        $res = $this->postJson(route('products.store'), $product, $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'The name has already been taken.');
    }

    /** @test */
    public function show_the_product_not_found()
    {
        $this->withExceptionHandling();

        $product1 = $this->createProduct(['merchant_account_id' => $this->merchant->id]);
        $product2 = $this->createProduct(['merchant_account_id' => $this->merchant->id]);

        $res = $this->getJson(route('products.index', ['keyword' => 'xxx']), $this->header);

        $res->assertNotFound()
            ->assertJsonPath('message', 'Product not found.');
    }
}
