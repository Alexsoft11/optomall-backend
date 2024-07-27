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
        return (new MailMessage)
            ->line('Your order status has been updated.')
            ->line('Order ID: ' . $this->order->id)
            ->line('New Status: ' . $this->order->getStatus->name_uz)
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status->name_uz,
        ];
    }

    public function toDatabase($notifiable)
    {
        UserMessage::create([
            'user_id' => $notifiable->id,
            'message' => 'Your order status has been updated to: ' . $this->order->getStatus->name_uz,
        ]);

        return [
            'order_id' => $this->order->id,
            'status' => $this->order->getStatus->name_uz,
        ];
    }
}
