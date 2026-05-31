<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MissedCallNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user, public string $callType = 'voice')
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Missed {$this->callType} call")
                    ->line("You missed a {$this->callType} call from {$this->user->name}.")
                    ->action('Call Back', url("/chat"))
                    ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Missed Call',
            'message' => "You missed a {$this->callType} call from {$this->user->name}.",
            'data' => [
                'user' => $this->user,
                'call_type' => $this->callType,
            ],
        ];
    }
}
