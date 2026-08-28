<?php

declare(strict_types=1);

namespace App\Domain\Review\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Rental\Models\Rental;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Review entity representing tenant feedback on completed rentals.
 *
 * Business rules:
 * - One review per rental (rental_id unique)
 * - Must have at least one rating (kost_rating OR room_rating)
 * - Can edit anytime (no time restrictions)
 * - Hard delete only (no soft delete)
 * - Up to 5 images allowed
 *
 * @property int $id
 * @property int $rental_id
 * @property int|null $kost_rating 1-5 rating for kost
 * @property string|null $kost_comment
 * @property int|null $room_rating 1-5 rating for room
 * @property string|null $room_comment
 * @property array|null $images Array of image paths
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Rental $rental
 * @property-read User $user Via rental relationship
 * @property-read Kost $kost Via rental->room->kost relationship
 */
class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rental_id',
        'kost_rating',
        'kost_comment',
        'room_rating',
        'room_comment',
        'images',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ReviewFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'kost_rating' => 'integer',
            'room_rating' => 'integer',
        ];
    }

    /**
     * Get the rental that this review belongs to.
     *
     * @return BelongsTo<Rental, $this>
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Get the user who wrote this review (via rental).
     */
    public function getUserAttribute(): User
    {
        return $this->rental->user;
    }

    /**
     * Get the kost being reviewed (via rental->room->kost).
     */
    public function getKostAttribute(): Kost
    {
        return $this->rental->room->kost;
    }

    /**
     * Check if review has kost rating.
     */
    public function hasKostRating(): bool
    {
        return $this->kost_rating !== null;
    }

    /**
     * Check if review has room rating.
     */
    public function hasRoomRating(): bool
    {
        return $this->room_rating !== null;
    }

    /**
     * Check if review has images.
     */
    public function hasImages(): bool
    {
        return ! empty($this->images);
    }

    /**
     * Get the count of images.
     */
    public function getImageCountAttribute(): int
    {
        return count($this->images ?? []);
    }
}
