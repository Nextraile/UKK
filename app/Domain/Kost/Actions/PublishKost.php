<?php

declare(strict_types=1);

namespace App\Domain\Kost\Actions;

use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Models\Kost;
use Illuminate\Support\Facades\DB;

/**
 * Publish an approved kost (make it visible to tenants).
 *
 * Transition: Approved → Active
 * Side effects: Set published_at timestamp
 *
 * FR-021: Admin can publish approved kost
 */
class PublishKost
{
    /**
     * Publish approved kost to marketplace.
     *
     * @param  Kost  $kost  The kost being published
     * @return Kost The published kost instance
     *
     * @throws InvalidKostTransitionException If status != approved
     */
    public function execute(Kost $kost): Kost
    {
        // Guard: only approved kosts can be published
        if ($kost->status !== 'approved') {
            throw InvalidKostTransitionException::cannotPublish($kost);
        }

        return DB::transaction(function () use ($kost) {
            $kost->status = 'active';
            $kost->published_at = now();
            $kost->save();

            return $kost->fresh();
        });
    }
}
