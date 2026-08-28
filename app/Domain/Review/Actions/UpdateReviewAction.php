<?php

declare(strict_types=1);

namespace App\Domain\Review\Actions;

use App\Domain\Review\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateReviewAction
{
    /**
     * Update an existing review.
     *
     * Business rules:
     * - Can edit anytime (no time restrictions)
     * - Replace ALL images on edit (not individual add/remove)
     * - Delete old images from storage before uploading new ones
     *
     * @param  Review  $review  The review to update
     * @param  array  $data  Review data (kost_rating, kost_comment, room_rating, room_comment, images)
     *
     * @throws \Exception If update fails
     */
    public function execute(Review $review, array $data): Review
    {
        return DB::transaction(function () use ($review, $data) {
            // Delete old images from storage
            if ($review->hasImages()) {
                $directory = "review-images/{$review->id}";
                Storage::disk('public')->deleteDirectory($directory);
            }

            // Upload new images if provided
            $imagePaths = [];
            if (! empty($data['images'])) {
                $directory = "review-images/{$review->id}";

                foreach ($data['images'] as $image) {
                    $filename = Str::uuid().'.'.$image->getClientOriginalExtension();
                    $path = $image->storeAs($directory, $filename, 'public');
                    $imagePaths[] = $path;
                }
            }

            // Update review record
            $review->update([
                'kost_rating' => $data['kost_rating'] ?? null,
                'kost_comment' => $data['kost_comment'] ?? null,
                'room_rating' => $data['room_rating'] ?? null,
                'room_comment' => $data['room_comment'] ?? null,
                'images' => $imagePaths ?: null,
            ]);

            return $review->fresh();
        });
    }
}
