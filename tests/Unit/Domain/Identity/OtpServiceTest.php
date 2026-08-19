<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Identity;

use App\Domain\Identity\Mail\OtpVerificationMail;
use App\Domain\Identity\Models\OtpVerification;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    private OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Cache::flush();
        $this->otpService = app(OtpService::class);
    }

    /**
     * generate() creates a 6-digit OTP, persists it, and sends the email.
     *
     * FR-004: OTP is 6 digits, stored in DB, and emailed.
     */
    public function test_generate_creates_otp_and_sends_email(): void
    {
        $user = User::factory()->unverified()->create();

        $code = $this->otpService->generate($user);

        $this->assertSame(6, strlen($code));
        $this->assertTrue(ctype_digit($code));

        // OTP codes are stored as SHA-256 hashes in the database.
        $this->assertDatabaseHas('otp_verifications', [
            'user_id' => $user->id,
            'otp_code' => hash('sha256', $code),
            'used_at' => null,
        ]);

        Mail::assertSent(OtpVerificationMail::class, function ($mail) use ($user, $code): bool {
            return $mail->user->is($user) && $mail->code === $code;
        });
    }

    /**
     * verify() returns true for a valid code and marks the email verified.
     *
     * FR-004: Valid OTP sets email_verified_at and used_at.
     */
    public function test_verify_with_valid_code(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->otpService->generate($user);

        $result = $this->otpService->verify($user, $code);

        $this->assertTrue($result);
        $this->assertNotNull($user->fresh()->email_verified_at);

        $otp = OtpVerification::where('user_id', $user->id)->first();
        $this->assertNotNull($otp->used_at);
    }

    /**
     * verify() returns false for an incorrect code.
     */
    public function test_verify_with_invalid_code(): void
    {
        $user = User::factory()->unverified()->create();
        $this->otpService->generate($user);

        $result = $this->otpService->verify($user, '000000');

        $this->assertFalse($result);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    /**
     * verify() with markEmailVerified=false consumes the OTP without
     * setting email_verified_at (used by password reset, FR-130).
     */
    public function test_verify_with_mark_email_verified_false(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->otpService->generate($user);

        $result = $this->otpService->verify($user, $code, false);

        $this->assertTrue($result);
        $this->assertNull($user->fresh()->email_verified_at);

        $otp = OtpVerification::where('user_id', $user->id)->first();
        $this->assertNotNull($otp->used_at);
    }

    /**
     * generate() with password-reset purpose sends the reset subject.
     */
    public function test_generate_with_password_reset_purpose_sends_reset_subject(): void
    {
        $user = User::factory()->unverified()->create();

        $this->otpService->generate($user, 'password-reset');

        Mail::assertSent(OtpVerificationMail::class, function ($mail) use ($user): bool {
            return $mail->user->is($user)
                && $mail->purpose === 'password-reset'
                && $mail->hasSubject('[SewaKost] Kode Reset Password Anda');
        });
    }

    /**
     * generate() default purpose sends the verification subject.
     */
    public function test_generate_default_purpose_sends_verification_subject(): void
    {
        $user = User::factory()->unverified()->create();

        $this->otpService->generate($user);

        Mail::assertSent(OtpVerificationMail::class, function ($mail) use ($user): bool {
            return $mail->user->is($user)
                && $mail->purpose === 'email-verification'
                && $mail->hasSubject('[SewaKost] Kode Verifikasi Email Anda');
        });
    }

    /**
     * verify() returns false for an expired OTP.
     *
     * FR-005: Expired OTPs cannot be used.
     */
    public function test_verify_with_expired_code(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->otpService->generate($user);

        $otp = OtpVerification::where('user_id', $user->id)->first();
        $otp->update(['expires_at' => now()->subMinute()]);

        $result = $this->otpService->verify($user, $code);

        $this->assertFalse($result);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    /**
     * verify() returns false for a code that has already been used.
     */
    public function test_verify_with_used_code(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $this->otpService->generate($user);

        $this->assertTrue($this->otpService->verify($user, $code));

        $result = $this->otpService->verify($user, $code);

        $this->assertFalse($result);
    }

    /**
     * resend() generates a new code and marks the previous OTP as used.
     *
     * FR-005: Resending expires the old code.
     */
    public function test_resend_generates_new_code_and_expires_old(): void
    {
        $user = User::factory()->unverified()->create();
        $code1 = $this->otpService->generate($user);

        $code2 = $this->otpService->resend($user);

        $this->assertNotSame($code1, $code2);

        // The old OTP (identified by its hashed code) should now be marked used.
        $oldOtp = OtpVerification::where('user_id', $user->id)
            ->where('otp_code', hash('sha256', $code1))
            ->first();
        $this->assertNotNull($oldOtp->used_at);
    }

    /**
     * hasValidOtp() returns true after generate, false after expiry.
     */
    public function test_has_valid_otp(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertFalse($this->otpService->hasValidOtp($user));

        $this->otpService->generate($user);
        $this->assertTrue($this->otpService->hasValidOtp($user));

        OtpVerification::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['expires_at' => now()->subMinute()]);
        $this->assertFalse($this->otpService->hasValidOtp($user));
    }

    /**
     * getOtpExpiry() returns a Carbon matching the OTP's expires_at.
     */
    public function test_get_otp_expiry(): void
    {
        $user = User::factory()->unverified()->create();
        $this->otpService->generate($user);

        $expiry = $this->otpService->getOtpExpiry($user);

        $this->assertNotNull($expiry);

        $otp = OtpVerification::where('user_id', $user->id)->latest('id')->first();
        $this->assertSame(
            $otp->expires_at->timestamp,
            $expiry->timestamp,
        );
    }
}
