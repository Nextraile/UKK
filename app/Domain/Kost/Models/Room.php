<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

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
     * @var array<int, string>
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
     *
     * TODO: COMP-006 - Rental model will be created in COMP-006.
     * This relation is a placeholder and will be uncommented when Rental exists.
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(self::class, 'id', 'id')->where('id', 0);
    }

    /**
     * Get count of reserved rentals (pending, paid, confirmed).
     *
     * TODO: COMP-006 - Implement after Rental model exists.
     * Formula: COUNT(rentals WHERE room_id = X AND status IN ('pending','paid','confirmed'))
     */
    public function getReservedCountAttribute(): int
    {
        return 0; // Stub: always return 0 until COMP-006
    }

    /**
     * Get count of occupied rentals (active).
     *
     * TODO: COMP-006 - Implement after Rental model exists.
     * Formula: COUNT(rentals WHERE room_id = X AND status = 'active')
     */
    public function getOccupiedCountAttribute(): int
    {
        return 0; // Stub: always return 0 until COMP-006
    }

    /**
     * Get total used slots (reserved + occupied).
     *
     * TODO: COMP-006 - This calculation will be accurate after Rental model exists.
     * Formula: reserved_count + occupied_count
     */
    public function getUsedSlotsAttribute(): int
    {
        return $this->reserved_count + $this->occupied_count;
    }

    /**
     * Get free slots available for new rentals.
     *
     * TODO: COMP-006 - This calculation will be accurate after Rental model exists.
     * Formula: room_type.max_occupants - used_slots
     */
    public function getFreeSlotsAttribute(): int
    {
        return $this->roomType->max_occupants - $this->used_slots;
    }

    /**
     * Get calculated status based on occupancy (occupied/reserved/available).
     *
     * TODO: COMP-006 - This calculation will be accurate after Rental model exists.
     * Logic:
     *   - 'occupied' if used_slots >= max_occupants
     *   - 'reserved' if used_slots > 0
     *   - 'available' otherwise
     */
    public function getCalculatedStatusAttribute(): string
    {
        return 'available'; // Stub: always return 'available' until COMP-006
    }
}
