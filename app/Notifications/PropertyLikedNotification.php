<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PropertyLikedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user, public Property $property)
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
                    ->subject('Your Property Was Liked!')
                    ->line("{$this->user->name} liked your property \"{$this->property->title}\" on Maxaba.")
                    ->action('View Property', url("/property/{$this->property->slug}"))
                    ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Property Liked',
            'message' => "{$this->user->name} liked your property \"{$this->property->title}\".",
            'data' => [
                'user' => $this->user,
                'property' => $this->property,
            ],
        ];
    }
}
