<?php

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Auth\RegistrationRequest;

class RegistrationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $request;

    public function __construct(RegistrationRequest $request)
    {
        $this->request = $request;
    }

    public function build()
    {
        return $this->view('emails.registration-rejected')
                    ->subject('Registration Request Rejected');
    }
} 