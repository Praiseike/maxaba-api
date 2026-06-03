<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user, public Message $chatMessage)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $preview = $this->chatMessage->type === 'text' 
            ? "\"{$this->chatMessage->content}\"" 
            : "sent a media file";

        return (new MailMessage)
                    ->subject('New Message Received')
                    ->line("You have a new message from {$this->user->name}:")
                    ->line($preview)
                    ->action('Reply in Chat', rtrim(config('app.frontend_url'), '/') . '/inbox')
                    ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Message',
            'message' => "{$this->user->name} sent you a message.",
            'data' => [
                'user' => $this->user,
                'message' => $this->chatMessage,
            ],
        ];
    }
}
