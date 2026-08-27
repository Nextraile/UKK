<?php

declare(strict_types=1);

namespace App\Domain\Rental\Models;

use App\Domain\Identity\Models\User;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Rental $rental
 * @property-read User|null $verifier
 */
class Payment extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return PaymentFactory::new();
    }

    protected $fillable = [
        'rental_id',
        'qris_image_path',
        'amount',
        'proof_of_payment_path',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'expired_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the rental this payment belongs to.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Get the admin who verified this payment.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
