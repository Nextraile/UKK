<?php

declare(strict_types=1);

namespace App\Domain\Review\Actions;

use App\Domain\Review\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteReviewAction
{
    /**
     * Delete a review and its associated images.
     *
     * Business rules:
     * - Hard delete (no soft delete)
     * - Delete all images from storage
     * - Delete review directory
     *
     * @param  Review  $review  The review to delete
     *
     * @throws \Exception If deletion fails
     */
    public function execute(Review $review): bool
    {
        return DB::transaction(function () use ($review) {
            // Delete images from storage
            if ($review->hasImages()) {
                $directory = "review-images/{$review->id}";
                Storage::disk('public')->deleteDirectory($directory);
            }

            // Hard delete review record
            return $review->delete();
        });
    }
}
