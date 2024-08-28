<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $orderGroupId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($orderGroupId)
    {
        $this->orderGroupId = $orderGroupId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.order_created')
                    ->with([
                        'orderGroupId' => $this->orderGroupId,
                    ]);
    }
}
