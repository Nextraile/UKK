<?php

namespace Tests\Feature\Public;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Rental\Models\Rental;
use App\Domain\Review\Models\Review;
use App\Domain\RoomInventory\Models\PriceScheme;
use App\Domain\RoomInventory\Models\Room;
use App\Domain\RoomInventory\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_rating_min_4_shows_only_kosts_with_avg_rating_4_or_above(): void
    {
        $kost1 = $this->createKostWithRating(5);
        $kost2 = $this->createKostWithRating(4);
        $kost3 = $this->createKostWithRating(3);

        $response = $this->get('/marketplace?rating_min=4');

        $response->assertOk();
        $response->assertSee($kost1->name);
        $response->assertSee($kost2->name);
        $response->assertDontSee($kost3->name);
    }

    public function test_filter_by_rating_min_3_shows_kosts_with_avg_rating_3_or_above(): void
    {
        $kost1 = $this->createKostWithRating(5);
        $kost2 = $this->createKostWithRating(3);
        $kost3 = $this->createKostWithRating(2);

        $response = $this->get('/marketplace?rating_min=3');

        $response->assertOk();
        $response->assertSee($kost1->name);
        $response->assertSee($kost2->name);
        $response->assertDontSee($kost3->name);
    }

    public function test_kosts_with_no_reviews_excluded_when_rating_filter_active(): void
    {
        $kostWithReviews = $this->createKostWithRating(4.0);
        $kostWithoutReviews = Kost::factory()->active()->create();

        $response = $this->get('/marketplace?rating_min=3');

        $response->assertOk();
        $response->assertSee($kostWithReviews->name);
        $response->assertDontSee($kostWithoutReviews->name);
    }

    public function test_kosts_with_only_room_rating_excluded_from_rating_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        $kost = Kost::factory()->create([
            'user_id' => $admin->id,
            'status' => 'active',
        ]);

        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'room_type_id' => $roomType->id,
        ]);

        $priceScheme = PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'price' => 1500000,
        ]);

        $rental = Rental::factory()->completed()->create([
            'room_id' => $room->id,
            'user_id' => $tenant->id,
            'price_scheme_id' => $priceScheme->id,
        ]);

        Review::factory()->roomOnly()->create([
            'rental_id' => $rental->id,
            'room_rating' => 5,
        ]);

        $response = $this->get('/marketplace?rating_min=4');

        $response->assertOk();
        $response->assertDontSee($kost->name);
    }

    public function test_rating_filter_combines_with_search_filter(): void
    {
        $kost1 = $this->createKostWithRating(5, ['name' => 'Kost Premium Jakarta']);
        $kost2 = $this->createKostWithRating(3, ['name' => 'Kost Budget Jakarta']);
        $kost3 = $this->createKostWithRating(5, ['name' => 'Kost Premium Bandung']);

        $response = $this->get('/marketplace?search=Jakarta&rating_min=4');

        $response->assertOk();
        $response->assertSee($kost1->name);
        $response->assertDontSee($kost2->name);
        $response->assertDontSee($kost3->name);
    }

    public function test_rating_filter_combines_with_price_filter(): void
    {
        $kost1 = $this->createKostWithRatingAndPrice(5, 2000000);
        $kost2 = $this->createKostWithRatingAndPrice(3, 1500000);
        $kost3 = $this->createKostWithRatingAndPrice(5, 3000000);

        $response = $this->get('/marketplace?rating_min=4&price_min=1000000&price_max=2500000');

        $response->assertOk();
        $response->assertSee($kost1->name);
        $response->assertDontSee($kost2->name);
        $response->assertDontSee($kost3->name);
    }

    public function test_rating_filter_combines_with_category_filter(): void
    {
        $category1 = Category::factory()->create(['name' => 'Putri']);
        $category2 = Category::factory()->create(['name' => 'Putra']);

        $kost1 = $this->createKostWithRating(5);
        $kost1->categories()->attach($category1->id);

        $kost2 = $this->createKostWithRating(3);
        $kost2->categories()->attach($category1->id);

        $kost3 = $this->createKostWithRating(5);
        $kost3->categories()->attach($category2->id);

        $response = $this->get('/marketplace?categories[]='.$category1->id.'&rating_min=4');

        $response->assertOk();
        $response->assertSee($kost1->name);
        $response->assertDontSee($kost2->name);
        $response->assertDontSee($kost3->name);
    }

    public function test_pagination_works_correctly_with_rating_filter(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->createKostWithRating(5);
        }

        $response = $this->get('/marketplace?rating_min=4');

        $response->assertOk();
        $response->assertViewHas('kosts', function ($kosts) {
            return $kosts->count() === 20 && $kosts->total() === 25;
        });
    }

    private function createKostWithRating(int $rating, array $attributes = []): Kost
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        $kost = Kost::factory()->create(array_merge([
            'user_id' => $admin->id,
            'status' => 'active',
        ], $attributes));

        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'room_type_id' => $roomType->id,
        ]);

        $priceScheme = PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'price' => 1500000,
        ]);

        $rental = Rental::factory()->completed()->create([
            'room_id' => $room->id,
            'user_id' => $tenant->id,
            'price_scheme_id' => $priceScheme->id,
        ]);

        Review::factory()->create([
            'rental_id' => $rental->id,
            'kost_rating' => (int) round($rating),
            'room_rating' => null,
        ]);

        return $kost;
    }

    private function createKostWithRatingAndPrice(int $rating, int $price): Kost
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        $kost = Kost::factory()->create([
            'user_id' => $admin->id,
            'status' => 'active',
        ]);

        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'room_type_id' => $roomType->id,
        ]);

        $priceScheme = PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'price' => $price,
            'is_active' => true,
        ]);

        $rental = Rental::factory()->completed()->create([
            'room_id' => $room->id,
            'user_id' => $tenant->id,
            'price_scheme_id' => $priceScheme->id,
        ]);

        Review::factory()->create([
            'rental_id' => $rental->id,
            'kost_rating' => $rating,
            'room_rating' => null,
        ]);

        return $kost;
    }
}
