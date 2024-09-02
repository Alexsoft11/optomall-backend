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
    protected $userId;

    public function __construct($token, $userId)
    {
        $this->token = $token;
        $this->userId = $userId;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url("/api/verify-email/{$this->token}");

        return (new MailMessage)
            ->subject('Optomall.uz')
            ->view('emails.register', ['url' => $url]);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
