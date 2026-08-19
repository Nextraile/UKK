<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use Database\Factories\OtpVerificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $otp_code
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 * @property Carbon $created_at
 */
#[Fillable(['user_id', 'otp_code', 'expires_at', 'used_at'])]
#[UseFactory(OtpVerificationFactory::class)]
class OtpVerification extends Model
{
    /** @use HasFactory<OtpVerificationFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this OTP verification.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if this OTP has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Determine if this OTP has been used.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
