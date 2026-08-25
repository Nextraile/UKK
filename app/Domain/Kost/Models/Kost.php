<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

use App\Domain\Identity\Models\User;
use Database\Factories\KostFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Kost entity representing a boarding house property.
 *
 * Status lifecycle: draft → pending_review → approved/rejected → active
 *
 * @property int $id
 * @property int $user_id Admin owner
 * @property string $slug URL-friendly identifier
 * @property string $name Kost name
 * @property string|null $description
 * @property string $contact_number
 * @property array|null $facilities JSON array of strings
 * @property array|null $rules JSON array of strings
 * @property string|null $qris_image_path
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property string|null $account_holder_name
 * @property string $status Enum: draft|pending_review|approved|active|rejected
 * @property Carbon|null $published_at
 * @property Carbon|null $approved_at
 * @property int|null $approved_by Super Admin user ID
 * @property Carbon|null $rejected_at
 * @property string|null $rejected_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $owner Admin who owns this kost
 * @property-read User|null $approver Super Admin who approved
 * @property-read Address|null $address 1:1 relation
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, RoomType> $roomTypes
 */
class Kost extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * Note: 'status' is intentionally excluded (TASK-016, ADR-009).
     * Status can only be changed via Action classes using direct assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'slug',
        'name',
        'description',
        'contact_number',
        'facilities',
        'rules',
        'qris_image_path',
        'bank_name',
        'account_number',
        'account_holder_name',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_reason',
        // 'status' - EXCLUDED: Only Action classes can modify status
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<static>
     */
    protected static function newFactory()
    {
        return KostFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'rules' => 'array',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Kost $kost) {
            if (empty($kost->slug)) {
                $kost->slug = Str::slug($kost->name);
            }
        });
    }

    /**
     * Get the admin user who owns this kost.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the super admin who approved this kost.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the address for this kost (1:1).
     *
     * @return HasOne<Address, $this>
     */
    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    /**
     * Get the categories assigned to this kost (M:N).
     *
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_kost');
    }

    /**
     * Get the room types for this kost (1:N).
     *
     * @return HasMany<RoomType, $this>
     */
    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    /**
     * Get the images for this kost (1:N).
     *
     * @return HasMany<KostImage, $this>
     */
    public function kostImages(): HasMany
    {
        return $this->hasMany(KostImage::class);
    }

    /**
     * Get the document requirements for this kost (1:N).
     *
     * @return HasMany<KostDocumentRequirement, $this>
     */
    public function documentRequirements(): HasMany
    {
        return $this->hasMany(KostDocumentRequirement::class);
    }

    /**
     * Get the rooms for this kost (1:N).
     *
     * @return HasMany<Room, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the reviews for this kost (1:N).
     *
     * Placeholder relationship for COMP-008 (Review & Rating).
     * Returns empty collection until Review model is implemented.
     *
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if kost is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if kost is pending review.
     */
    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }

    /**
     * Check if kost is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if kost is active (published).
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if kost is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
