<?php

namespace App\Listeners;

use ErrorException;
use App\Models\Order;
use App\Events\HasNewOrder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrderProduct
{
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
        if ($event->status === 'PENDING') {
            $this->updateStatusOrder($event->order, $event->status);
            $this->updateProductStock($event->order, false);
        } elseif ($event->status === 'FAILED') {
            $this->updateStatusOrder($event->order, $event->status);
            $this->updateProductStock($event->order);
        } else {
            $this->updateStatusOrder($event->order, $event->status);
        }
    }

    /**
     * Update the status order.
     *
     * @param  Order $order
     * @param  string $status
     * @return void
     */
    public function updateStatusOrder(Order $order, string $status): void
    {
        if (!$order->update(['status' => $status]))
            throw new ErrorException('Failed to update status order.', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Update the product stock.
     *
     * @param  Order $order
     * @param  bool $append
     * @return void
     */
    private function updateProductStock(Order $order, ?bool $append = true): void
    {

        foreach ($order->products as $product) {
            if ($append) {
                $product->stock += $product->pivot->quantity;
                if ($product->sold > 0) $product->sold -= $product->pivot->quantity;
            } else {
                $product->stock -= $product->pivot->quantity;
                $product->sold += $product->pivot->quantity;
            }

            if (!$product->save()) throw new ErrorException('Failed to update the product stock', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
