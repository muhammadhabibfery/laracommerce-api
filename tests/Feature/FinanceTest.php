<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\MerchantAccount;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;

class FinanceTest extends TestCase
{
    public User $userAdmin;
    public User $userMerchant;
    public User $userCustomer;
    public MerchantAccount $merchantAccount;
    public Category $category;
    public Product $product1;
    public Product $product2;
    public Order $order;
    public string $merchantAccountName = 'Example Merchant';

    public const INCOMING_FUNDS = 'Incoming funds from #OrderId-test-123',
        MERCHANT_TAX = '20% merchant tax from #OrderId-test-123',
        REVENUE_ADMIN = "Revenue from merchant tax #Merchant-example-merchant #OrderId-test-123";

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->userAdmin = $this->createUser(['role' => 'ADMIN']);
        $this->userCustomer = $this->createUser(['role' => 'CUSTOMER']);
        $this->userMerchant = $this->authenticatedUser(['role' => 'MERCHANT']);
        $this->category = $this->createCategory();
        $this->createBanking();
        $this->merchantAccount = $this->createMerchantAccount(['user_id' => $this->userMerchant->id]);
        $this->product1 = $this->createProduct(['merchant_account_id' => $this->merchantAccount, 'price' => 100000]);
        $this->product2 = $this->createProduct(['merchant_account_id' => $this->merchantAccount, 'price' => 200000]);
        $this->order = $this->createOrder(['user_id' => $this->userCustomer->id, 'invoice_number' => 'test-123', 'total_price' => $this->product1->price + $this->product2->price]);
        $this->order->products()
            ->attach([
                $this->product1->id => ['quantity' => 1, 'total_price' => $this->product1->price],
                $this->product2->id => ['quantity' => 1, 'total_price' => $this->product2->price],
            ]);
        $this->userMerchant
            ->finance()
            ->create(['type' => 'DEBIT', 'order_id' => $this->order->invoice_number, 'description' => self::INCOMING_FUNDS, 'amount' => $this->order->total_price, 'status' => 'SUCCESS', 'balance' => $this->order->total_price]);
        $merchantTax = $this->order->total_price * 20 / 100;
        $this->userMerchant
            ->finance()
            ->create(['type' => 'KREDIT', 'order_id' => $this->order->invoice_number, 'description' => self::MERCHANT_TAX, 'amount' => $merchantTax, 'status' => 'SUCCESS', 'balance' => $this->userMerchant->finance()->latest()->first()->balance - $merchantTax]);
        $this->userAdmin
            ->finance()
            ->create(['type' => 'DEBIT', 'order_id' => $this->order->invoice_number, 'description' => self::REVENUE_ADMIN, 'amount' => $merchantTax, 'status' => 'SUCCESS', 'balance' => $merchantTax]);
    }

    /** @test */
    public function show_all_finances_related_for_merchant()
    {
        $res = $this->getJson(route('finances.index'), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->count('data', 2)
            )
            ->assertJsonPath('data.0.balance', 'Rp. 300.000')
            ->assertJsonPath('data.1.balance', 'Rp. 240.000');

        $this->assertDatabaseCount('finances', 3);
    }

    /** @test */
    public function show_the_finances_by_type_related_for_merchant()
    {
        $res = $this->getJson(route('finances.index', ['type' => 'DEBIT']), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->count('data', 1)
            )
            ->assertJsonPath('data.0.balance', 'Rp. 300.000');
    }

    /** @test */
    public function show_the_finances_by_status_related_for_merchant()
    {
        $res = $this->getJson(route('finances.index', ['status' => 'SUCCESS']), [], $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'pages'])
                    ->count('data', 2)
            )
            ->assertJsonPath('data.0.balance', 'Rp. 300.000')
            ->assertJsonPath('data.1.balance', 'Rp. 240.000');
    }
}
