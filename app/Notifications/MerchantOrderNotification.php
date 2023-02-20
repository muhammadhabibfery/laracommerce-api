<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MerchantOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $invoiceNumber;
    public string $status;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($invoiceNumber, $status)
    {
        $this->invoiceNumber = $invoiceNumber;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subjectStatus = $this->status === 'PENDING' ? 'new' : 'the';
        $greeting = $this->status === 'FAILED'
            ? 'Whoops Sorry!'
            : ($this->status === 'SUCCESS' ? 'Congratulations!' : 'hello!');

        return (new MailMessage)
            ->greeting($greeting)
            ->subject("Notification about $subjectStatus order")
            ->lineIf($this->status === 'PENDING', 'You have a new order')
            ->line("The order with invoice number {$this->invoiceNumber} is {$this->status}.")
            ->line("for detailed order information, you can visit the order menu in your merchant account")
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
