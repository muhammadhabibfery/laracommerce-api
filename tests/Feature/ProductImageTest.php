<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\MerchantAccount;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Validations\ProductImageValidation;

class ProductImageTest extends TestCase
{
    use ProductImageValidation;

    public User $user;
    public MerchantAccount $merchant;
    public Category $category;
    public Product $product;
    public string $directory = 'product-images';

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->user = $this->authenticatedUser(['role' => 'MERCHANT']);
        $this->category = $this->createCategory();
        $this->createBanking();
        $this->merchant = $this->createMerchantAccount(['user_id' => $this->user->id]);
        $this->product = $this->createProduct(['merchant_account_id' => $this->merchant->id]);
    }

    /** @test */
    public function merchant_can_create_product_image()
    {
        Storage::fake($this->directory);
        $file = UploadedFile::fake()->image('beatles.jpg')->size(2000);

        $res = $this->postJson(route('product-images.store'), ['productId' => $this->product->id, 'image' => $file]);

        $res->assertCreated()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            );

        $name = last(explode('/', $res->json()['data']['name']));
        $this->assertDatabaseHas('product_images', ['name' => $name]);
        $this->deleteDirectory($this->directory, $name);
    }

    /** @test */
    public function show_all_product_images_collection_by_product_name()
    {
        $productImage1 = $this->createProductImage(['product_id' => $this->product->id]);
        $productImage2 = $this->createProductImage(['product_id' => $this->product->id]);

        $res = $this->getJson(route('product-images.index', $this->product), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'product'])
                    ->count('data', 2)
            )->assertJsonPath('data.0.name', $productImage1->getImage())
            ->assertJsonPath('data.1.name', $productImage2->getImage())
            ->assertJsonPath('product.name', $this->product->name);
    }

    /** @test */
    public function merchant_can_delete_product_image()
    {
        $directory = 'product-images';
        Storage::fake($directory);
        $file = UploadedFile::fake()->image('beatles.jpg')->size(2000);
        $productImage = $this->postJson(route('product-images.store'), ['productId' => $this->product->id, 'image' => $file]);
        $name = last(explode('/', $productImage->json()['data']['name']));

        $res = $this->deleteJson(route('product-images.destroy', str($name)->slug()->value()), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message'])
            )->assertJsonPath('message', 'The product image deleted successfully.');

        $this->assertDatabaseMissing('product_images', ['name' => $name]);
        $this->deleteDirectory($this->directory, $name, true);
    }
}
