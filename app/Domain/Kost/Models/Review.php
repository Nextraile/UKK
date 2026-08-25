<?php

declare(strict_types=1);

namespace App\Domain\Kost\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Review model placeholder for COMP-008.
 *
 * This is a STUB class to satisfy type hints in Kost::reviews() relationship.
 * The full Review model (with migrations, factory, relationships) will be
 * implemented in COMP-008 (Review & Rating Management).
 *
 * @property int $id
 * @property int $kost_id
 * @property int $user_id
 * @property int $kost_rating
 * @property string|null $comment
 */
class Review extends Model
{
    // Stub class — no table exists yet, will error if queried directly
}
