<?php

declare(strict_types=1);

namespace App\Domain\Kost\Mail;

use App\Domain\Kost\Models\Kost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email notification to Super Admin when kost is submitted for review.
 */
class KostSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  Kost  $kost  The kost that was submitted for review
     */
    public function __construct(
        public Kost $kost
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Kost Submission: {$this->kost->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.kost-submitted',
        );
    }
}
