<?php

namespace App\Mail;

use App\Models\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Otp $otp
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->otp->type) {
            'login' => 'Uw inlogcode voor Goitom Finance',
            'registration' => 'Verifieer uw account bij Goitom Finance',
            'password_reset' => 'Reset uw wachtwoord bij Goitom Finance',
            default => 'Uw verificatiecode voor Goitom Finance'
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'type' => $this->otp->type,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}