<?php

namespace App\Mail;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimActionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $claim;
    public $locations;

    public function __construct($data)
    {
        $this->claim = $data['claim'];
        $this->locations = $data['locations'];
    }

    public function build()
    {
        return $this->view('emails.claim-action')
                    ->with([
                        'claim' => $this->claim,
                        'locations' => $this->locations,
                    ]);
    }
}
