<?php

declare(strict_types=1);

namespace App\Domain\Rental\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
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
}
