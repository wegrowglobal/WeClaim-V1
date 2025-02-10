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
        if ($data instanceof \App\Models\Claim) {
            $this->claim = $data;
            $this->locations = $data->locations;
        } else {
            $this->claim = $data['claim'];
            $this->locations = $data['locations'];
        }
    }

    public function build()
    {
        return $this->view('emails.claim-action')
                    ->subject('Claim Review Required - WeClaim')
                    ->with([
                        'claim' => $this->claim,
                        'locations' => $this->locations,
                    ]);
    }
}
