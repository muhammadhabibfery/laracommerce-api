<?php

namespace App\Listeners;

use App\Events\HasNewOrder;
use App\Models\Coupon;
use App\Models\Finance;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use ErrorException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class GenerateMerchantFinances
{
    private string $incomingFundsDesc = 'Incoming funds from #OrderId-',
        $merchantTaxDesc = '20% merchant tax from #OrderId-',
        $revenueAdminDesc = 'Revenue from merchant tax #Merchant-';
    private const FAILED_UPDATE_BALANCE = 'Failed to update balance';


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\HasNewOrder  $event
     * @return void
     */
    public function handle(HasNewOrder $event): void
    {
        if ($event->status === 'SUCCESS') {
            $invoiceNumber = $event->order->invoice_number;
            $products = $event->order->products()->with(['merchantAccount'])->get();
            $coupons = $this->getOrderCoupons($event->order);

            foreach ($products as $product) {
                $merchantAccount = $product->merchantAccount()->first();
                $totalBalance = $merchantAccount->balance;
                $totalBalance += $product->pivot->total_price;

                $this->createFinance(
                    $invoiceNumber,
                    $product,
                    ['description' => $this->incomingFundsDesc .= $invoiceNumber, 'type' => 'DEBIT', 'balance' => $totalBalance]
                );
                $merchantTax = $product->pivot->total_price * 20 / 100;
                $totalBalance -= $merchantTax;
                $this->createFinance(
                    $invoiceNumber,
                    $product,
                    ['description' => $this->merchantTaxDesc .= $invoiceNumber, 'type' => 'KREDIT', 'amount' => $merchantTax, 'balance' => $totalBalance]
                );

                $admin = $this->getAdmin();
                $totalBalanceAdmin = isset($admin->balance) ? $admin->balance + $merchantTax : $merchantTax;
                $this->createFinance(
                    $invoiceNumber,
                    $product,
                    ['user_id' => $admin->id, 'description' => $this->revenueAdminDesc .= $merchantAccount->slug, 'type' => 'DEBIT', 'amount' => $merchantTax, 'balance' => $totalBalanceAdmin, 'status' => 'SUCCESS']
                );
                if (!$admin->update(['balance' => $totalBalanceAdmin]))
                    throw new ErrorException(self::FAILED_UPDATE_BALANCE, Response::HTTP_INTERNAL_SERVER_ERROR);

                if (count($coupons)) {
                    foreach ($coupons as $coupon_key => $coupon) {
                        if ($coupon['merchant_account_id'] === $product->merchant_account_id) {
                            $totalBalance -= $coupon['discount_amount'];
                            $couponName = str($coupon['name'])->slug()->value();
                            $this->createFinance(
                                $invoiceNumber,
                                $product,
                                [
                                    'description' => "Coupon discount from #Coupon-$couponName for #OrderId-$invoiceNumber",
                                    'type' => 'KREDIT',
                                    'amount' => $coupon['discount_amount'],
                                    'status' => 'SUCCESS',
                                    'balance' => $totalBalance
                                ]
                            );

                            unset($coupons[$coupon_key]);
                        }
                    }
                }
                if (!$merchantAccount->update(['balance' => $totalBalance]))
                    throw new ErrorException(self::FAILED_UPDATE_BALANCE, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    /**
     * Create finance for each merchant.
     *
     * @param  string $orderId
     * @param  Product $product
     * @param  array $additionalData
     * @return void
     */
    private function createFinance(string $orderId, Product $product, ?array $additionalData = []): void
    {
        $data = [
            'user_id' => $product->merchantAccount->user_id,
            'order_id' => $orderId,
            'amount' => $product->pivot->total_price,
            'status' => 'SUCCESS',
        ];

        $payload = array_merge($data, $additionalData);

        if (!Finance::create($payload))
            throw new ErrorException('Failed to create finance', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Get order coupons.
     *
     * @param  Order $order
     * @return array
     */
    private function getOrderCoupons(Order $order): array
    {
        $coupons = json_decode($order->coupons);
        foreach ($coupons as $coupon)
            $result[] = Arr::only($this->getCoupon($coupon)->toArray(), ['merchant_account_id', 'name', 'discount_amount']);

        return $result;
    }

    /**
     * Get coupon.
     *
     * @param  string $name
     * @return Coupon
     */
    private function getCoupon(string $name): Coupon
    {
        return Coupon::where('name', $name)->first();
    }

    /**
     * Get admin.
     *
     * @return User
     */
    private function getAdmin(): User
    {
        return User::whereJsonContains('role', 'ADMIN')->first();
    }
}
