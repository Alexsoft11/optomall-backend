<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

class TemporaryUser
{
    use Notifiable;

    public $email;
    public $token;

    public function __construct($email, $token)
    {
        $this->email = $email;
        $this->token = $token;
    }
}
