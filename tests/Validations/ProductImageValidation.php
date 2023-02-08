<?php

namespace Tests\Validations;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait ProductImageValidation
{
    /** @test */
    public function all_fields_are_required()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('product-images.store'), [], $this->header);

        $res->assertUnprocessable()
            ->assertJsonCount(2, 'errors');
    }

    /** @test */
    public function the_product_id_field_should_be_exists()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('product-images.store'), ['product_id' => 99], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.product_id.0', 'The selected product is invalid.');
    }

    /** @test */
    public function the_image_field_should_be_follow_the_image_rules()
    {
        Storage::fake('product-images');
        $this->withExceptionHandling();
        $file = UploadedFile::fake()->create('test', 3000, 'txt');

        $res = $this->postJson(route('product-images.store'), ['product_id' => $this->product->id, 'image' => $file], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.image.0', 'The image must be an image.')
            ->assertJsonPath('errors.image.1', 'The image must not be greater than 2500 kilobytes.');
    }

    /** @test */
    public function the_images_should_be_less_than_5()
    {
        Storage::fake('product-images');
        $this->withExceptionHandling();
        $this->createProductImage(['product_id' => $this->product->id]);
        $this->createProductImage(['product_id' => $this->product->id]);
        $this->createProductImage(['product_id' => $this->product->id]);
        $this->createProductImage(['product_id' => $this->product->id]);
        $this->createProductImage(['product_id' => $this->product->id]);
        $file = UploadedFile::fake()->image('beatles.jpg');

        $res = $this->postJson(route('product-images.store'), ['product_id' => $this->product->id, 'image' => $file], $this->header);

        $res->assertBadRequest()
            ->assertJsonPath('message', 'The amount of product images has exceeded capacity (max 5 items).');
    }
}
