<?php

declare(strict_types=1);

namespace App\Domain\Review\Actions;

use App\Domain\Rental\Models\Rental;
use App\Domain\Review\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubmitReviewAction
{
    /**
     * Submit a new review for a completed rental.
     *
     * Business rules:
     * - Rental must be in 'completed' status
     * - Rental must not already have a review
     * - At least one rating (kost_rating OR room_rating) required
     * - Upload images to public/review-images/{review_id}/
     *
     * @param  Rental  $rental  The rental being reviewed
     * @param  array  $data  Review data (kost_rating, kost_comment, room_rating, room_comment, images)
     *
     * @throws \Exception If rental is not completed or already has review
     */
    public function execute(Rental $rental, array $data): Review
    {
        // Validate rental eligibility
        if ($rental->status !== 'completed') {
            throw new \Exception('Only completed rentals can be reviewed.');
        }

        if ($rental->review()->exists()) {
            throw new \Exception('This rental already has a review.');
        }

        return DB::transaction(function () use ($rental, $data) {
            // Create review record without images first
            $review = Review::create([
                'rental_id' => $rental->id,
                'kost_rating' => $data['kost_rating'] ?? null,
                'kost_comment' => $data['kost_comment'] ?? null,
                'room_rating' => $data['room_rating'] ?? null,
                'room_comment' => $data['room_comment'] ?? null,
                'images' => null,
            ]);

            // Upload images if provided
            $imagePaths = [];
            if (! empty($data['images'])) {
                $directory = "review-images/{$review->id}";

                foreach ($data['images'] as $image) {
                    $filename = Str::uuid().'.'.$image->getClientOriginalExtension();
                    $path = $image->storeAs($directory, $filename, 'public');
                    $imagePaths[] = $path;
                }

                // Update review with image paths
                $review->update(['images' => $imagePaths]);
            }

            return $review->fresh();
        });
    }
}
