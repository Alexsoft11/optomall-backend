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
        return (new MailMessage)
            ->subject('Optomall.uz Email Verification')
            ->view('emails.register', ['token' => $this->token]);
    }


    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
