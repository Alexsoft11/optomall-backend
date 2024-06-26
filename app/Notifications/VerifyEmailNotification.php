<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('/api/verify-email/' . $this->token);

        return (new MailMessage)
            ->subject('TaoBao')
            ->line('Please verify your email address.')
            ->action('Click here to verify', $url)
            ->line('If the button doesn\'t work, please use the following link:')
            ->line($url);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

