<?php

namespace App\Mail;

use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PasswordSetupInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $passwordSetupToken;
    public string $setupUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $passwordSetupToken)
    {
        $this->user = $user;
        $this->passwordSetupToken = $passwordSetupToken;
        // Generate the password setup URL here
        // Note: The token itself is passed, not embedded in the URL generation for the route parameter
        $this->setupUrl = URL::route('password.setup.form', ['token' => $this->passwordSetupToken]);
        // We don't need a signed route here as the token itself provides security and expiry is checked in controller
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set Up Your Account Password - WeClaim',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Point to the existing blade view instead of markdown
        return new Content(
            view: 'emails.account-created', // Use the existing blade view
            with: [
                'user' => $this->user, // Pass the user object
                'token' => $this->passwordSetupToken, // Pass the token (needed by the current view structure)
                'setupUrl' => $this->setupUrl, // Pass the pre-generated URL (Best Practice)
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
} 