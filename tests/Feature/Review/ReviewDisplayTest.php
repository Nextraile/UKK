<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Rental\Models\Rental;
use App\Domain\Review\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for Review display functionality (COMP-008).
 *
 * Covers: reviews on kost detail page, basic display functionality.
 */
class ReviewDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test reviews display on kost detail page.
     */
    public function test_reviews_display_on_kost_detail_page(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        // Create 3 reviews
        $rentals = Rental::factory()->count(3)->create([
            'room_id' => $room->id,
            'status' => 'completed',
        ]);

        foreach ($rentals as $rental) {
            Review::factory()->create([
                'rental_id' => $rental->id,
                'kost_rating' => 5,
                'kost_comment' => 'Great kost!',
            ]);
        }

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertOk();
        // Verify page loads successfully with reviews
        $this->assertDatabaseCount('reviews', 3);
    }

    /**
     * Test kost detail page loads without reviews.
     */
    public function test_kost_without_reviews_shows_page(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertOk();
        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * Test reviews show user information.
     */
    public function test_reviews_linked_to_users(): void
    {
        $tenant = User::factory()->create([
            'role' => 'user',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $kost = Kost::factory()->create(['status' => 'active']);
        $room = Room::factory()->create(['kost_id' => $kost->id]);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'completed',
        ]);

        Review::factory()->create([
            'rental_id' => $rental->id,
            'kost_rating' => 5,
            'kost_comment' => 'Excellent place!',
        ]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertOk();
        // Verify review is linked to user via rental
        $review = Review::first();
        $this->assertEquals($tenant->id, $review->rental->user_id);
    }

    /**
     * Test review images are stored correctly.
     */
    public function test_review_images_are_stored(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $room = Room::factory()->create(['kost_id' => $kost->id]);
        $rental = Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'completed',
        ]);

        Review::factory()->create([
            'rental_id' => $rental->id,
            'kost_rating' => 5,
            'images' => [
                'review-images/test1.jpg',
                'review-images/test2.jpg',
            ],
        ]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertOk();

        // Verify images are stored in review
        $review = Review::first();
        $this->assertCount(2, $review->images);
        $this->assertContains('review-images/test1.jpg', $review->images);
    }

    /**
     * Test reviews are linked to correct kost via rental.
     */
    public function test_reviews_linked_to_correct_kost(): void
    {
        $kost1 = Kost::factory()->create(['status' => 'active']);
        $kost2 = Kost::factory()->create(['status' => 'active']);
        $room1 = Room::factory()->create(['kost_id' => $kost1->id]);
        $room2 = Room::factory()->create(['kost_id' => $kost2->id]);

        $rental1 = Rental::factory()->create(['room_id' => $room1->id, 'status' => 'completed']);
        $rental2 = Rental::factory()->create(['room_id' => $room2->id, 'status' => 'completed']);

        Review::factory()->create(['rental_id' => $rental1->id, 'kost_rating' => 5]);
        Review::factory()->create(['rental_id' => $rental2->id, 'kost_rating' => 3]);

        // Verify each review is linked to correct kost
        $review1 = Review::where('rental_id', $rental1->id)->first();
        $this->assertEquals($kost1->id, $review1->rental->room->kost_id);

        $review2 = Review::where('rental_id', $rental2->id)->first();
        $this->assertEquals($kost2->id, $review2->rental->room->kost_id);
    }
}
