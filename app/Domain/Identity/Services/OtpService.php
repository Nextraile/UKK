<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Identity\Mail\OtpVerificationMail;
use App\Domain\Identity\Models\OtpVerification;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Service responsible for generating, storing, verifying, and resending
 * one-time-password (OTP) codes used for email verification.
 *
 * Implements a dual-storage strategy per the architecture spec:
 *  - `otp_verifications` database table (audit trail)
 *  - Redis cache (fast lookup)
 *
 * OTP codes are 6-digit numeric strings with a 15-minute expiry (FR-128).
 * Timing-safe comparison (`hash_equals`) is used when verifying codes to
 * mitigate timing attacks.
 *
 * @see OtpVerification
 */
class OtpService
{
    /** Cache key prefix for storing the active OTP per user. */
    private const string CACHE_PREFIX = 'otp:';

    /** OTP validity window in minutes (FR-128). */
    private const int CACHE_TTL_MINUTES = 15;

    /** Number of digits in the OTP code. */
    private const int OTP_LENGTH = 6;

    /** Maximum failed OTP verification attempts before lockout. */
    private const int MAX_VERIFY_ATTEMPTS = 5;

    /** Lockout duration in minutes after exceeding max failed attempts. */
    private const int LOCKOUT_MINUTES = 15;

    /** Cache key prefix for tracking failed verification attempts. */
    private const string ATTEMPT_PREFIX = 'otp:attempts:';

    /**
     * Generate a new OTP for the given user, store it in the database and
     * cache, then dispatch the verification email.
     *
     * Any existing unused OTP for this user is marked as used first, so only
     * one active OTP exists at a time (FR-005: "OTP lama expired saat OTP
     * baru dikirim").
     *
     * @param  User  $user  The user to generate the OTP for.
     * @param  string  $purpose  The OTP purpose: 'email-verification' or 'password-reset'.
     * @return string The generated 6-digit OTP code.
     */
    public function generate(User $user, string $purpose = 'email-verification'): string
    {
        // Expire any existing unused OTPs for this user.
        OtpVerification::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate a zero-padded 6-digit code.
        $code = str_pad(
            (string) random_int(0, 999_999),
            self::OTP_LENGTH,
            '0',
            STR_PAD_LEFT,
        );

        // Persist to the database (audit trail — hashed for security).
        OtpVerification::create([
            'user_id' => $user->id,
            'otp_code' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(self::CACHE_TTL_MINUTES),
        ]);

        // Store in Redis cache for fast lookup during verification.
        Cache::put(
            self::CACHE_PREFIX.$user->id,
            $code,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
        );

        // Send the OTP email (queued implicitly by the mailable if configured).
        Mail::to($user)->send(new OtpVerificationMail($user, $code, $purpose));

        return $code;
    }

    /**
     * Verify the supplied OTP code for the given user.
     *
     * The cache is consulted first for a fast lookup; if unavailable, the
     * database is queried directly as a fallback. A timing-safe comparison
     * (`hash_equals`) guards against timing side-channels.
     *
     * @param  User  $user  The user attempting verification.
     * @param  string  $code  The 6-digit code submitted by the user.
     * @param  bool  $markEmailVerified  Whether to also mark the email as verified on success.
     * @return bool True when the code is valid, unused, and not expired.
     */
    public function verify(User $user, string $code, bool $markEmailVerified = true): bool
    {
        // Refuse verification while the user is locked out.
        if ($this->isLockedOut($user)) {
            return false;
        }

        $cachedCode = Cache::get(self::CACHE_PREFIX.$user->id);

        if ($cachedCode !== null && hash_equals((string) $cachedCode, $code)) {
            $otp = $this->findActiveOtpByCode($user, $code);

            if ($otp instanceof OtpVerification && ! $otp->isExpired()) {
                $this->markVerified($user, $otp, $markEmailVerified);
                $this->clearFailedAttempts($user);

                return true;
            }
        }

        // Fallback: query the database directly (cache may have been evicted).
        $otp = $this->findActiveOtpByCode($user, $code);

        if ($otp instanceof OtpVerification && ! $otp->isExpired()) {
            $this->markVerified($user, $otp, $markEmailVerified);
            $this->clearFailedAttempts($user);

            return true;
        }

        $this->recordFailedAttempt($user);

        return false;
    }

    /**
     * Check if the user is locked out from OTP verification.
     *
     * @param  User  $user  The user to check.
     * @return bool True when the user is currently locked out.
     */
    public function isLockedOut(User $user): bool
    {
        return Cache::has(self::ATTEMPT_PREFIX.$user->id.':locked');
    }

    /**
     * Record a failed OTP verification attempt.
     *
     * Locks out the user after {@see MAX_VERIFY_ATTEMPTS} failed attempts
     * for {@see LOCKOUT_MINUTES} minutes.
     *
     * @param  User  $user  The user who failed verification.
     */
    public function recordFailedAttempt(User $user): void
    {
        $key = self::ATTEMPT_PREFIX.$user->id;
        $attempts = Cache::get($key, 0) + 1;

        if ($attempts >= self::MAX_VERIFY_ATTEMPTS) {
            // Lock out for 15 minutes.
            Cache::put($key.':locked', true, now()->addMinutes(self::LOCKOUT_MINUTES));
            Cache::forget($key);
        } else {
            Cache::put($key, $attempts, now()->addMinutes(self::LOCKOUT_MINUTES));
        }
    }

    /**
     * Clear failed attempts after successful verification.
     *
     * @param  User  $user  The user who verified successfully.
     */
    public function clearFailedAttempts(User $user): void
    {
        Cache::forget(self::ATTEMPT_PREFIX.$user->id);
        Cache::forget(self::ATTEMPT_PREFIX.$user->id.':locked');
    }

    /**
     * Resend the OTP — generates a fresh code and emails it.
     *
     * Throttling (1 request per minute) is enforced by the calling
     * controller, not by this service, to keep the service focused on
     * OTP lifecycle concerns.
     *
     * @param  User  $user  The user requesting a new OTP.
     * @return string The newly generated 6-digit OTP code.
     */
    public function resend(User $user): string
    {
        return $this->generate($user);
    }

    /**
     * Determine whether the user currently has a valid (non-expired,
     * unused) OTP.
     *
     * @param  User  $user  The user to check.
     */
    public function hasValidOtp(User $user): bool
    {
        return OtpVerification::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Get the expiry timestamp of the latest unused OTP for the user, if any.
     *
     * Used by the UI to render a countdown timer.
     *
     * @param  User  $user  The user to look up.
     * @return Carbon|null The expiry time, or null when no unused OTP exists.
     */
    public function getOtpExpiry(User $user): ?Carbon
    {
        /** @var OtpVerification|null $otp */
        $otp = OtpVerification::where('user_id', $user->id)
            ->whereNull('used_at')
            ->latest()
            ->first();

        return $otp?->expires_at;
    }

    /**
     * Find an unused, non-expired OTP record matching the supplied code.
     *
     * OTP codes are stored as SHA-256 hashes in the database, so this
     * method fetches all active OTPs for the user and compares them in
     * PHP using a timing-safe `hash_equals()` comparison.
     */
    private function findActiveOtpByCode(User $user, string $code): ?OtpVerification
    {
        $hashedCode = hash('sha256', $code);

        /** @var Collection<int, OtpVerification> $otps */
        $otps = OtpVerification::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->get();

        foreach ($otps as $otp) {
            if (hash_equals($otp->otp_code, $hashedCode)) {
                return $otp;
            }
        }

        return null;
    }

    /**
     * Mark the OTP as used and, optionally, the user's email as verified
     * inside a single database transaction, then clear the cache entry.
     *
     * @param  User  $user  The user verifying the OTP.
     * @param  OtpVerification  $otp  The OTP record to mark as used.
     * @param  bool  $markEmailVerified  Whether to set `email_verified_at`.
     */
    private function markVerified(User $user, OtpVerification $otp, bool $markEmailVerified): void
    {
        DB::transaction(function () use ($user, $otp, $markEmailVerified): void {
            $otp->update(['used_at' => now()]);

            if ($markEmailVerified) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        });

        Cache::forget(self::CACHE_PREFIX.$user->id);
    }
}
