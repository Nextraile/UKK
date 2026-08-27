<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Address model for Kost location details.
 *
 * @property int $id
 * @property int $kost_id
 * @property string $full_address
 * @property string $district
 * @property string $city
 * @property string $province
 * @property string|null $postal_code
 * @property string $country
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Address extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<static>
     *
     * @phpstan-return AddressFactory
     */
    protected static function newFactory()
    {
        return AddressFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kost_id',
        'full_address',
        'district',
        'city',
        'province',
        'postal_code',
        'country',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the kost that owns the address.
     */
    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }
}
