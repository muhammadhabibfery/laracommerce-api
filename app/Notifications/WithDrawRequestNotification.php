<?php

namespace App\Notifications;

use App\Filament\Resources\WithdrawResource;
use Filament\Notifications\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Filament\Notifications\Notification as NotificationFilament;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WithDrawRequestNotification extends Notification
{
    use Queueable;

    public $wd;
    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($wd, $user)
    {
        $this->wd = $wd;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // return ['broadcast'];
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    // /**
    //  * Get the array representation of the notification.
    //  *
    //  * @param  mixed  $notifiable
    //  * @return array
    //  */
    // public function toArray($notifiable)
    // {
    //     return Arr::only($this->wd->toArray(), ['id', 'user_id', 'amount', 'balance', 'status']);
    // }

    public function toDatabase($notifiable): array
    {
        return NotificationFilament::make()
            ->title('New Withdraw Request')
            ->body("The merchant {$this->user->merchantAccount->name} just has made a withdraw request")
            ->actions([
                Action::make('visit')
                    ->url(WithdrawResource::getUrl('index'))
            ])
            ->getDatabaseMessage();
    }

    // public function toBroadcast($notifiable): BroadcastMessage
    // {
    //     return NotificationFilament::make()
    //         ->title('New Withdraw Request')
    //         ->body("The merchant {$this->user->merchantAccount->name} just has made a withdraw request")
    //         ->getBroadcastMessage();
    // }
}
