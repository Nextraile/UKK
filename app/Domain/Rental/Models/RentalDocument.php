<?php

declare(strict_types=1);

namespace App\Domain\Rental\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\KostDocumentRequirement;
use Database\Factories\RentalDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalDocument extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return RentalDocumentFactory::new();
    }

    protected $fillable = [
        'rental_id',
        'document_type',
        'document_path',
        'uploaded_at',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the rental this document belongs to.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Get the admin who verified this document.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the document requirement definition.
     *
     * Note: This relationship uses document_type as foreign key
     * to match against KostDocumentRequirement.
     */
    public function documentRequirement(): BelongsTo
    {
        return $this->belongsTo(
            KostDocumentRequirement::class,
            'document_type',
            'document_type'
        );
    }
}
