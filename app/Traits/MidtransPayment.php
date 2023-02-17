<?php

namespace App\Traits;

use Midtrans\Snap;
use ErrorException;
use Midtrans\Config;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait MidtransPayment
{
    /**
     * the list of available payments
     *
     * @var array
     */
    public array $availablePayments = ['bri_va', 'bni_va', 'gopay', 'shopeepay'];

    /**
     * set midtrans configuration
     *
     * @return void
     */
    private function configuration(): void
    {
        Config::$serverKey = config('midtrans.midtrans_serverkey');
        Config::$isProduction = config('midtrans.midtrans_production');
        Config::$isSanitized = config('midtrans.midtrans_sanitized');
        Config::$is3ds = config('midtrans.midtrans_3ds');
    }

    /**
     * set data for payment credentials
     *
     * @param  Order $order
     * @return array
     */
    private function setData(Order $order): array
    {
        return [
            'transaction_details' => ['order_id' => $order->invoice_number, 'gross_amount' => (int) $order->total_price],
            'customer_details' => ['first_name' => $order->user->name, 'email' => $order->user->email],
            'enabled_payments' => $this->availablePayments,
            'vtweb' => []
        ];
    }

    /**
     * create (midtrans) payment link
     *
     * @param  Order $order
     * @return string
     */
    private function createPaymentLink(Order $order): string
    {
        $this->configuration();

        try {
            return Snap::createTransaction($this->setData($order))->redirect_url;
        } catch (\Exception $e) {
            throw new ErrorException('Failed to create payment link', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * Update the product stock.
     *
     * @param  string $invoiceNumber
     * @param  bool $append
     * @return Order|null
     */
    private function updateProductStock(string $invoiceNumber, ?bool $append = true): Order|null
    {
        $order = $this->getOrderByInvoiceNumber($invoiceNumber);

        foreach ($order->products as $product) {
            if ($append) {
                $product->stock += $product->pivot->quantity;
                $product->sold += $product->pivot->quantity;
            } else {
                $product->stock -= $product->pivot->quantity;
                if ($product->sold > 0) $product->sold -= $product->pivot->quantity;
            }

            if (!$product->save()) throw new ErrorException('Failed to update the product stock', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $append ? null : $order;
    }

    /**
     * Get the order by invoice number.
     *
     * @param  string $invoiceNumber
     * @return Order
     */
    private function getOrderByInvoiceNumber(string $invoiceNumber): Order
    {
        return Order::where('invoice_number', $invoiceNumber)
            ->firstOrFail();
    }

    /**
     * wrap a result into json response.
     *
     * @param  int $code
     * @param  string $message
     * @param  array $resource
     * @return JsonResponse
     */
    private function wrapResponse(int $code, string $message, ?array $resource = []): JsonResponse
    {
        $result = [
            'code' => $code,
            'message' => $message,
            'data' => $resource
        ];

        // if (count($resource)) {
        //     $result = array_merge($result, ['data' => $resource['data']]);

        //     if (count($resource) > 1)
        //         $result = array_merge($result, ['pages' => ['links' => $resource['links'], 'meta' => $resource['meta']]]);
        // }

        return response()->json($result, $code);
    }
}
