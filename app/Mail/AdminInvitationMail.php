<?php

namespace App\Mail;

use App\Models\AdminInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AdminInvitation $invitation,
        public string $activationUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Undangan Aktivasi Akun Admin Vocaseek',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-invitation',
            with: [
                'invitation' => $this->invitation,
                'activationUrl' => $this->activationUrl,
            ],
        );
    }
}
