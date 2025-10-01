<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

final class MonthlyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly array $reportData,
        public readonly Carbon $reportMonth
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $monthName = $this->reportMonth->format('F Y');
        return new Envelope(
            subject: "Maandelijks Rapport - {$monthName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly_report',
            with: [
                'user' => $this->user,
                'reportData' => $this->reportData,
                'reportMonth' => $this->reportMonth,
                'monthName' => $this->reportMonth->format('F Y'),
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