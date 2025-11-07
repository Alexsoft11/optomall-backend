<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\UserMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdateNotification extends Notification
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        if ($this->order->getStatus->message_uz) {
            return (new MailMessage)
                ->subject('Обновление статуса заказа')
                ->view('vendor.mail.html.message', ['order' => $this->order]);
        }
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
        ];
    }

    public function toDatabase($notifiable)
    {
        if ($this->order->getStatus->message_uz) {
            UserMessage::create([
                'user_id' => $notifiable->id,
                'message_uz' => "{$this->order->getStatus->message_uz}",
                'message_en' => "{$this->order->getStatus->message_en}",
                'message_ru' => "{$this->order->getStatus->message_ru}",
                'order_id' => $this->order->unique_id,
            ]);
        }

        return [
            'order_id' => $this->order->id,
            'status' => $this->order->getStatus->name_uz,
        ];
    }
}
