<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

use Database\Factories\RoomTypeFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * RoomType model for Kost room variants.
 *
 * @property int $id
 * @property int $kost_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $room_size
 * @property int $max_occupants
 * @property string $security_deposit
 * @property array|null $facilities
 * @property array|null $rules
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Kost $kost
 */
class RoomType extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): RoomTypeFactory
    {
        return RoomTypeFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'room_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kost_id',
        'name',
        'slug',
        'description',
        'room_size',
        'max_occupants',
        'security_deposit',
        'facilities',
        'rules',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'facilities' => 'array',
        'rules' => 'array',
        'security_deposit' => 'decimal:2',
    ];

    /**
     * Get the kost that owns the room type.
     */
    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }

    /**
     * Get room type images.
     */
    public function roomTypeImages(): HasMany
    {
        return $this->hasMany(RoomTypeImage::class);
    }

    /**
     * Get thumbnail image.
     */
    public function thumbnailImage(): HasOne
    {
        return $this->hasOne(RoomTypeImage::class)->where('is_thumbnail', true);
    }

    /**
     * Get price schemes for this room type.
     */
    public function priceSchemes(): HasMany
    {
        return $this->hasMany(PriceScheme::class);
    }

    /**
     * Get rooms of this room type.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
