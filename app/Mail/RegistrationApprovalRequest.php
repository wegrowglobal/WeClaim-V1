<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\RegistrationRequest;

class RegistrationApprovalRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $request;

    public function __construct(RegistrationRequest $request)
    {
        $this->request = $request;
    }

    public function build()
    {
        return $this->view('emails.registration-approval-request')
                    ->subject('New Registration Request');
    }
} 