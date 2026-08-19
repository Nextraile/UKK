<?php

declare(strict_types=1);

namespace App\Domain\Identity\Mail;

use App\Domain\Identity\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for the OTP message (EMAIL-001, plus the password-reset variant).
 *
 * Renders the `emails.otp-verification` Blade view with the recipient user,
 * their 6-digit OTP code, and the OTP purpose. The subject line depends on
 * the purpose: `[SewaKost] Kode Verifikasi Email Anda` for email verification
 * and `[SewaKost] Kode Reset Password Anda` for password reset.
 */
class OtpVerificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  User  $user  The recipient user.
     * @param  string  $code  The 6-digit OTP code to display.
     * @param  string  $purpose  The OTP purpose: 'email-verification' or 'password-reset'.
     */
    public function __construct(
        public readonly User $user,
        public readonly string $code,
        public readonly string $purpose = 'email-verification',
    ) {}

    /**
     * Get the message envelope (subject, from, etc.).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->purpose === 'password-reset'
                ? '[SewaKost] Kode Reset Password Anda'
                : '[SewaKost] Kode Verifikasi Email Anda',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-verification',
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
