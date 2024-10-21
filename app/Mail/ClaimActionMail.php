<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimActionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $claim;

    public function __construct($claim)
    {
        $this->claim = $claim;
    }

    public function build()
    {
        return $this->view('posts.home');
    }
}
