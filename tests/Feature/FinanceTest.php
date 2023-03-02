<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\MerchantAccount;
use App\Notifications\WithDrawRequestNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as NotificationFilament;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Validations\FinanceValidation;

use function Filament\Notifications\Testing\assertNotified;

class FinanceTest extends TestCase
{
    use FinanceValidation;

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
            ->finances()
            ->create(['type' => 'DEBIT', 'order_id' => $this->order->invoice_number, 'description' => self::INCOMING_FUNDS, 'amount' => $this->order->total_price, 'status' => 'SUCCESS', 'balance' => $this->order->total_price]);
        $this->merchantAccount->balance += $this->order->total_price;
        $this->merchantAccount->save();
        $merchantTax = $this->order->total_price * 20 / 100;
        $this->userMerchant
            ->finances()
            ->create(['type' => 'KREDIT', 'order_id' => $this->order->invoice_number, 'description' => self::MERCHANT_TAX, 'amount' => $merchantTax, 'status' => 'SUCCESS', 'balance' => $this->merchantAccount->balance - $merchantTax]);
        $this->merchantAccount->balance -= $merchantTax;
        $this->merchantAccount->save();
        $this->userAdmin
            ->finances()
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
            ->assertJsonPath('data.0.balance', currencyFormat($this->userMerchant->finances->first()->balance))
            ->assertJsonPath('data.1.balance', currencyFormat($this->userMerchant->finances->last()->balance));

        $this->assertDatabaseCount('finances', 3)
            ->assertDatabaseHas('merchant_accounts', Arr::only($this->merchantAccount->toArray(), ['user_id', 'balance']));
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
            ->assertJsonPath('data.0.balance', currencyFormat($this->userMerchant->finances->first()->balance));
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
            ->assertJsonPath('data.0.balance', currencyFormat($this->userMerchant->finances->first()->balance))
            ->assertJsonPath('data.1.balance', currencyFormat($this->userMerchant->finances->last()->balance));
    }

    /** @test */
    public function the_resource_for_create_withdraw_request_can_be_sent()
    {
        $res = $this->getJson(route('finance.wd'), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            )
            ->assertJsonPath('data.balance', currencyFormat($this->merchantAccount->balance));
    }

    /** @test */
    public function the_merchant_can_create_withdraw_request()
    {
        $wdData = ['name' => $this->merchantAccount->name, 'bankAccountName' => $this->merchantAccount->bank_account_name, 'bankAccountNumber' => "{$this->merchantAccount->bank_account_number}", 'amount' => 100000];

        $res = $this->postJson(route('finance.wd'), $wdData);

        $res->assertCreated()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            )
            ->assertJsonPath('data.amount', currencyFormat($wdData['amount']))
            ->assertJsonPath('data.status', 'PENDING')
            ->assertJsonPath('data.balance', currencyFormat($this->userMerchant->merchantAccount->balance));

        $this->assertDatabaseCount('finances', 4)
            ->assertDatabaseHas('merchant_accounts', Arr::only($this->userMerchant->merchantAccount->toArray(), ['user_id', 'balance']));
    }

    /** @test */
    public function the_withdraw_request_notification_can_be_sent_to_admin()
    {
        $wdData = ['name' => $this->merchantAccount->name, 'bankAccountName' => $this->merchantAccount->bank_account_name, 'bankAccountNumber' => "{$this->merchantAccount->bank_account_number}", 'amount' => 100000];

        Notification::fake();
        Notification::send($this->userAdmin, new WithDrawRequestNotification($wdData, $this->userMerchant));

        Notification::assertSentTo($this->userAdmin, WithDrawRequestNotification::class);
        NotificationFilament::assertNotified();
    }
}
