<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\MerchantAccount;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;

class OrderTest extends TestCase
{
    public User $userMerchant1;
    public User $userMerchant2;
    public User $userCustomer;
    public MerchantAccount $merchantAccount1;
    public MerchantAccount $merchantAccount2;
    public Category $category;
    public Product $product1;
    public Product $product2;
    public Product $product3;
    public Order $order1;
    public Order $order2;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->userCustomer = $this->createUser(['role' => 'CUSTOMER']);
        $this->userMerchant1 = $this->createUser(['role' => 'MERCHANT']);
        $this->userMerchant2 = $this->authenticatedUser(['role' => 'MERCHANT']);
        $this->category = $this->createCategory();
        $this->createBanking();
        $this->merchantAccount1 = $this->createMerchantAccount(['user_id' => $this->userMerchant1->id]);
        $this->merchantAccount2 = $this->createMerchantAccount(['user_id' => $this->userMerchant2->id]);
        $this->product1 = $this->createProduct(['merchant_account_id' => $this->merchantAccount1, 'price' => 150000]);
        $this->product2 = $this->createProduct(['merchant_account_id' => $this->merchantAccount2, 'price' => 200000]);
        $this->product3 = $this->createProduct(['merchant_account_id' => $this->merchantAccount2, 'price' => 300000]);
        $this->order1 = $this->createOrder(['user_id' => $this->userCustomer->id, 'invoice_number' => 'test-123']);
        $this->order1->products()
            ->attach([
                $this->product1->id => ['quantity' => 1, 'total_price' => $this->product1->price],
                $this->product2->id => ['quantity' => 1, 'total_price' => $this->product2->price],
                $this->product3->id => ['quantity' => 1, 'total_price' => $this->product3->price],
            ]);
        $this->merchantAccount1->balance += $this->product1->price;
        $this->merchantAccount1->save();
        $this->merchantAccount2->balance += ($this->product2->price + $this->product3->price);
        $this->merchantAccount2->save();
        $this->order2 = $this->createOrder(['user_id' => $this->userCustomer->id, 'invoice_number' => 'test-234', 'total_price' => 150000]);
        $this->order2->products()
            ->attach([
                $this->product1->id => ['quantity' => 1, 'total_price' => $this->product1->price],
            ]);
        $this->merchantAccount1->balance += $this->product1->price;
        $this->merchantAccount1->save();
    }

    /** @test */
    public function show_all_orders_related_for_merchant()
    {
        $res = $this->getJson(route('orders.index'), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->count('data', 1)
            )
            ->assertJsonPath('data.0.invoiceNumber', 'test-123')
            ->assertJsonPath('data.0.totalPrice', 'Rp. 500.000');
    }

    /** @test */
    public function show_an_order_related_for_merchant()
    {
        $res = $this->getJson(route('orders.show', $this->order1), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
                    ->count('data.products', 2)
            )
            ->assertJsonPath('data.totalPrice', 'Rp. 500.000')
            ->assertJsonPath('data.products.0.totalPrice', 'Rp. 200.000')
            ->assertJsonPath('data.products.1.totalPrice', 'Rp. 300.000')
            ->assertJsonPath('data.products.0.name', $this->product2->name)
            ->assertJsonPath('data.products.1.name', $this->product3->name);
    }

    /** @test */
    public function an_order_that_not_related_for_merchant_should_be_not_found()
    {
        $this->withExceptionHandling();

        $res = $this->getJson(route('orders.show', $this->order2), [], $this->header);

        $res->assertNotFound()
            ->assertJsonPath('message', 'Order. not found.');
    }
}
