<?php

namespace App\Mail\Auth;

use Illuminate\Mail\Mailable;
use App\Models\User\User;

class AccountCreated extends Mailable
{
    public function __construct(
        public readonly User $user,
        public readonly string $token
    ) {}

    public function build()
    {
        return $this->view('emails.account-created');
    }
} 