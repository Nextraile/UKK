<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

use Database\Factories\PriceSchemeFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * PriceScheme model for room type pricing.
 *
 * @property int $id
 * @property int $room_type_id
 * @property string $name
 * @property string|null $description
 * @property string $price
 * @property int $duration_value
 * @property string $duration_unit
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read RoomType $roomType
 */
class PriceScheme extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<static>
     *
     * @phpstan-return PriceSchemeFactory
     */
    protected static function newFactory()
    {
        return PriceSchemeFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'price_schemes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_type_id',
        'name',
        'description',
        'price',
        'duration_value',
        'duration_unit',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'duration_value' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the room type that owns this price scheme.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
