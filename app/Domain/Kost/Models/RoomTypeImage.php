<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

use Database\Factories\RoomTypeImageFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * RoomTypeImage model for room type gallery photos.
 *
 * @property int $id
 * @property int $room_type_id
 * @property string $image_path
 * @property bool $is_thumbnail
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read RoomType $roomType
 */
class RoomTypeImage extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<static>
     */
    protected static function newFactory()
    {
        return RoomTypeImageFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'room_type_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_type_id',
        'image_path',
        'is_thumbnail',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_thumbnail' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the room type that owns this image.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
