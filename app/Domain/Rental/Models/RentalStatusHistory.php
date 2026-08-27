<?php

declare(strict_types=1);

namespace App\Domain\Rental\Models;

use App\Domain\Identity\Models\User;
use Database\Factories\RentalStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Rental Status History Model.
 *
 * Immutable audit trail of all rental status transitions.
 * Every status change MUST record here with who/why/when context.
 *
 * Status Transition Map (Complete):
 * - NULL → pending: CreateRental (by tenant)
 * - pending → paid: VerifyPayment (by admin)
 * - pending → cancelled: Auto-cancel overdue OR manual cancel (by system/tenant)
 * - paid → rejected: RejectPayment (by admin)
 * - paid → documents_pending: First document upload (by tenant)
 * - paid → cancelled: Manual cancel (by tenant)
 * - rejected → pending: Re-upload payment proof (NOT IMPLEMENTED YET)
 * - documents_pending → confirmed: Auto-confirm all docs approved (by system)
 * - documents_pending → cancelled: Manual cancel (by tenant)
 * - confirmed → active: Auto-activate on start_date (by system)
 * - confirmed → cancelled: Manual cancel (by tenant)
 * - active → completed: Auto-complete on end_date (by system)
 *
 * Implementation Notes:
 * - Append-only: Never update/delete existing records
 * - Transaction safety: Always created in same transaction as rental status update
 * - System user: Use changed_by=1 for automated transitions (scheduled jobs, auto-confirm)
 * - Meaningful notes: Explain WHY transition happened, include context (rejection reason, cancellation reason, etc.)
 *
 * @property int $id
 * @property int $rental_id
 * @property string $status - One of: pending, paid, rejected, documents_pending, confirmed, active, completed, cancelled
 * @property int $changed_by - User ID (tenant/admin) or 1 (system)
 * @property string|null $internal_notes - Human-readable reason for transition
 * @property Carbon $created_at
 */
class RentalStatusHistory extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return RentalStatusHistoryFactory::new();
    }

    public $timestamps = false; // Only created_at, no updated_at

    protected $fillable = [
        'rental_id',
        'status',
        'changed_by',
        'internal_notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the rental this history record belongs to.
     */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Get the user who changed the status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
