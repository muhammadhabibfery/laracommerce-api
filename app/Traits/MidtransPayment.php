<?php

namespace App\Traits;

use App\Events\HasNewOrder;
use Midtrans\Snap;
use ErrorException;
use Midtrans\Config;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Midtrans\Notification;
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
        try {
            $this->configuration();
            return Snap::createTransaction($this->setData($order))->redirect_url;
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle notification data from midtrans.
     *
     * @return JsonResponse
     */
    public function notificationHandler(): JsonResponse
    {
        try {
            $this->configuration();
            $notification = new Notification();

            if (in_array($notification->transaction_status, ['deny', 'expire', 'cancel'])) $status = 'FAILED';
            if ($notification->transaction_status === 'pending') $status = 'PENDING';
            if ($notification->transaction_status === 'settlement') $status = 'SUCCESS';

            event(new HasNewOrder($this->getOrderByInvoiceNumber($notification->order_id), $status));

            $orderStatusMessage = "The order with invoice number {$notification->order_id} is $status";
            return $this->wrapResponse(Response::HTTP_OK, strtolower($orderStatusMessage));
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
        $result = ['code' => $code, 'message' => $message];

        if (count($resource))
            $result = array_merge($result, ['data' => $resource]);

        return response()->json($result, $code);
    }
}
