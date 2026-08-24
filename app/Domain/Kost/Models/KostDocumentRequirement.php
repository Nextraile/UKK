<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

use Database\Factories\KostDocumentRequirementFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Kost document requirement model.
 *
 * @property int $id
 * @property int $kost_id
 * @property string $document_type
 * @property bool $is_required
 * @property string|null $reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Kost $kost
 * @property-read string $document_type_label
 */
class KostDocumentRequirement extends Model
{
    use HasFactory;

    protected $table = 'kost_document_requirements';

    protected $fillable = [
        'kost_id',
        'document_type',
        'is_required',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    protected static function newFactory(): Factory
    {
        return KostDocumentRequirementFactory::new();
    }

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return config('kost.document_types')[$this->document_type] ?? $this->document_type;
    }
}
