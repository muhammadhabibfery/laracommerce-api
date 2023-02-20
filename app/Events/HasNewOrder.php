<?php

namespace App\Events;

use App\Models\Order;
use App\Models\MerchantAccount;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Collection;

class HasNewOrder
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public string $status;
    public Collection $merchants;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($order, $status)
    {
        $this->order = $order;
        $this->status = $status;
        $this->merchants = $this->getMerchantsHasTheOrders($this->order);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    /**
     * Get merchants where has the orders.
     *
     * @param Order $order
     * @return Collection
     */
    private function getMerchantsHasTheOrders(Order $order): Collection
    {
        return MerchantAccount::whereHas('products.orders', fn ($query) => $query->where('order_id', $order->id))
            ->get()
            ->map(fn ($merchant) => $merchant->user);
    }
}
