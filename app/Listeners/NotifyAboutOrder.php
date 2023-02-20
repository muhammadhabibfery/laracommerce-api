<?php

namespace App\Listeners;

use App\Events\HasNewOrder;
use App\Mail\CustomerOrderMail;
use App\Notifications\MerchantOrderNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class NotifyAboutOrder
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
        Notification::send($event->merchants, new MerchantOrderNotification($event->order->invoice_number, $event->status));

        if ($event->status === 'SUCCESS') Mail::to($event->order->user)->send(new CustomerOrderMail($event->order));
    }
}
