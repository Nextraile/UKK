<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

use Database\Factories\KostImageFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Kost image model for gallery and thumbnail.
 *
 * @property int $id
 * @property int $kost_id
 * @property string $image_path
 * @property bool $is_thumbnail
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Kost $kost
 */
class KostImage extends Model
{
    use HasFactory;

    protected $table = 'kost_images';

    protected $fillable = [
        'kost_id',
        'image_path',
        'is_thumbnail',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_thumbnail' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function newFactory(): Factory
    {
        return KostImageFactory::new();
    }

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }
}
