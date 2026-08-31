<?php

declare(strict_types=1);

namespace App\Domain\Identity\Models;

use App\Domain\Kost\Models\Kost;
use App\Domain\Rental\Models\Rental;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property string $first_name
 * @property string|null $last_name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property Carbon|null $phone_verified_at
 * @property string $role
 * @property string|null $avatar_path
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string|null $avatar_url Avatar image URL (supports external URLs and local storage)
 */
#[Fillable(['first_name', 'last_name', 'email', 'password', 'phone', 'avatar_path', 'role', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
#[UseFactory(UserFactory::class)]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Determine if the user is a tenant (regular user).
     */
    public function isTenant(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Determine if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Determine if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Get the dashboard route URL based on the user's role.
     */
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'superadmin' => '/super-admin/kost-submissions',
            'admin' => '/admin/kosts',
            default => '/rentals',
        };
    }

    /**
     * Get a masked representation of the user's email for display,
     *
     * e.g. `john.doe@gmail.com` -> `j***@gmail.com`.
     *
     * @return string The masked email address.
     */
    public function maskedEmail(): string
    {
        $atPos = strpos($this->email, '@');

        if ($atPos === false) {
            return $this->email;
        }

        return substr($this->email, 0, 1).'***'.substr($this->email, $atPos);
    }

    /**
     * Get the OTP verifications associated with this user.
     *
     * @return HasMany<OtpVerification, $this>
     */
    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }

    /**
     * Get the rentals created by this user (tenant).
     *
     * @return HasMany<Rental, $this>
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Get the kosts owned by this user (admin).
     *
     * @return HasMany<Kost, $this>
     */
    public function kosts(): HasMany
    {
        return $this->hasMany(Kost::class);
    }

    /**
     * Get the avatar image URL for this user.
     *
     * Returns the image URL for the user's avatar.
     * Supports both external URLs and local storage paths via image_url() helper.
     * Returns null if no avatar is set.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return \image_url($this->avatar_path);
    }
}
