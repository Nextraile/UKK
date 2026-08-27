<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

use App\Domain\Rental\Models\Rental;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Room model for physical room units.
 *
 * @property int $id
 * @property int $kost_id
 * @property int $room_type_id
 * @property string $code
 * @property string $status
 * @property string|null $internal_notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Kost $kost
 * @property-read RoomType $roomType
 * @property-read int $reserved_count
 * @property-read int $occupied_count
 * @property-read int $used_slots
 * @property-read int $free_slots
 * @property-read string $calculated_status
 */
class Room extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<static>
     *
     * @phpstan-return RoomFactory
     */
    protected static function newFactory()
    {
        return RoomFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rooms';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kost_id',
        'room_type_id',
        'code',
        'status',
        'internal_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the kost that owns this room.
     */
    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }

    /**
     * Get the room type of this room.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get rentals for this room.
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Get count of reserved rentals (pending, paid, confirmed with future start dates).
     *
     * ADR-018: Reserved = rentals with status pending/paid/confirmed
     */
    public function getReservedCountAttribute(): int
    {
        return $this->rentals()
            ->whereIn('status', ['pending', 'paid', 'confirmed'])
            ->where('start_date', '>', now())
            ->count();
    }

    /**
     * Get count of occupied rentals (current active rentals).
     *
     * ADR-018: Occupied = rentals with status active
     */
    public function getOccupiedCountAttribute(): int
    {
        return $this->rentals()
            ->where('status', 'active')
            ->count();
    }

    /**
     * Get total used slots (reserved + occupied).
     *
     * Used for availability check in CreateRental Action.
     * ADR-018: 1 rental = 1 person
     */
    public function getUsedSlotsAttribute(): int
    {
        return $this->rentals()
            ->whereIn('status', ['pending', 'paid', 'confirmed', 'active'])
            ->count();
    }

    /**
     * Get free slots available for new rentals.
     *
     * Returns 0 if no capacity left.
     * ADR-018: free_slots = max_occupants - used_slots
     */
    public function getFreeSlotsAttribute(): int
    {
        return max(0, $this->roomType->max_occupants - $this->used_slots);
    }

    /**
     * Get calculated availability status.
     *
     * Returns 'unavailable' if manually set, 'full' if no slots, 'available' otherwise.
     * Respects manual unavailability flag.
     */
    public function getCalculatedStatusAttribute(): string
    {
        if ($this->status === 'unavailable') {
            return 'unavailable';
        }

        return $this->free_slots > 0 ? 'available' : 'full';
    }
}
