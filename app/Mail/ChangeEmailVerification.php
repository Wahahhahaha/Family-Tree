<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChangeEmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $oldEmail;
    public $newEmail;
    public $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $oldEmail, $newEmail, $verificationUrl)
    {
        $this->userName = $userName;
        $this->oldEmail = $oldEmail;
        $this->newEmail = $newEmail;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm Your Email Address Change',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.change_email_verification',
            with: [
                'userName' => $this->userName,
                'oldEmail' => $this->oldEmail,
                'newEmail' => $this->newEmail,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
