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
 * Email notification to kost owner when submission is rejected.
 */
class KostRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  Kost  $kost  The kost that was rejected
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
            subject: "Kost Rejected: {$this->kost->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.kost-rejected',
        );
    }
}
