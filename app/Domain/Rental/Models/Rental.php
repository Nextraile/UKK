<?php

declare(strict_types=1);

namespace App\Domain\Rental\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Review\Models\Review;
use Database\Factories\RentalFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Room $room
 * @property-read User $user
 * @property-read PriceScheme $priceScheme
 * @property-read Payment $payment
 * @property-read Review|null $review
 * @property-read Collection<int, RentalDocument> $rentalDocuments
 * @property-read Collection<int, RentalStatusHistory> $statusHistories
 */
class Rental extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return RentalFactory::new();
    }

    protected $fillable = [
        'room_id',
        'user_id',
        'price_scheme_id',
        'duration_value',
        'duration_unit',
        'room_price',
        'security_deposit',
        'grand_total',
        'start_date',
        'end_date',
        'status',
        'cancelled_reason',
        'cancelled_at',
        'confirmed_at',
        'activated_at',
        'completed_at',
    ];

    protected $casts = [
        'room_price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the room being rented.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the tenant who created this rental.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the price scheme snapshot used for this rental.
     */
    public function priceScheme(): BelongsTo
    {
        return $this->belongsTo(PriceScheme::class);
    }

    /**
     * Get the payment record for this rental.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get all documents uploaded for this rental.
     */
    public function rentalDocuments(): HasMany
    {
        return $this->hasMany(RentalDocument::class);
    }

    /**
     * Alias for rentalDocuments relationship.
     */
    public function documents(): HasMany
    {
        return $this->rentalDocuments();
    }

    /**
     * Get all status change history records.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(RentalStatusHistory::class);
    }

    /**
     * Get the review for this rental.
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Get current step in rental progress (1-4).
     *
     * Used by progress tracker component.
     */
    public function getCurrentStep(): int
    {
        return match ($this->status) {
            'pending' => 1,
            'paid', 'documents_pending' => 2,
            'confirmed', 'active' => 3,
            'completed' => 4,
            default => 0, // cancelled, rejected, or other statuses
        };
    }

    /**
     * Get payment section state (active/preview/locked).
     *
     * Used for section visual state styling.
     */
    public function getPaymentSectionState(): string
    {
        // Active only if status is pending AND no payment proof uploaded yet
        if ($this->status === 'pending' && ! $this->payment->proof_of_payment_path) {
            return 'active';
        }

        // Preview for all other cases (payment uploaded, admin verified, or other statuses)
        return 'preview';
    }

    /**
     * Get documents section state (active/preview/locked).
     *
     * Used for section visual state styling.
     */
    public function getDocumentsSectionState(): string
    {
        // Locked until payment is VERIFIED by admin
        if ($this->status === 'pending' || ($this->status === 'paid' && ! $this->payment->verified_at)) {
            return 'locked';
        }

        // Active when payment verified and documents not yet confirmed
        if (in_array($this->status, ['paid', 'documents_pending']) && $this->payment->verified_at) {
            return 'active';
        }

        // Preview for all other statuses (confirmed, active, completed, cancelled)
        return 'preview';
    }

    /**
     * Check if rental can be cancelled by tenant.
     *
     * Business rule: Can cancel before start_date in specific statuses.
     */
    public function canBeCancelled(): bool
    {
        // Cannot cancel after start_date
        if ($this->start_date->isPast()) {
            return false;
        }

        // Can only cancel in specific statuses
        $cancellableStatuses = ['pending', 'paid', 'documents_pending', 'confirmed'];

        return in_array($this->status, $cancellableStatuses);
    }
}
