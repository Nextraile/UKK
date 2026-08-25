<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_marketplace_without_login(): void
    {
        $response = $this->get(route('marketplace.index'));

        $response->assertOk();
        $response->assertViewIs('marketplace.index');
    }

    public function test_only_active_kosts_displayed(): void
    {
        $activeKost = Kost::factory()->create(['status' => 'active']);
        $draftKost = Kost::factory()->create(['status' => 'draft']);
        $pendingKost = Kost::factory()->create(['status' => 'pending_review']);
        $rejectedKost = Kost::factory()->create(['status' => 'rejected']);

        $response = $this->get(route('marketplace.index'));

        $response->assertSee($activeKost->name);
        $response->assertDontSee($draftKost->name);
        $response->assertDontSee($pendingKost->name);
        $response->assertDontSee($rejectedKost->name);
    }

    public function test_soft_deleted_kosts_not_visible(): void
    {
        $activeKost = Kost::factory()->create(['status' => 'active']);
        $deletedKost = Kost::factory()->create(['status' => 'active', 'deleted_at' => now()]);

        $response = $this->get(route('marketplace.index'));

        $response->assertSee($activeKost->name);
        $response->assertDontSee($deletedKost->name);
    }

    public function test_pagination_works_correctly(): void
    {
        Kost::factory()->count(25)->create(['status' => 'active']);

        $response = $this->get(route('marketplace.index'));

        $response->assertOk();
        $response->assertViewHas('kosts');
        $kosts = $response->viewData('kosts');
        $this->assertEquals(20, $kosts->perPage());
    }

    public function test_search_by_name_works(): void
    {
        $kostA = Kost::factory()->create(['status' => 'active', 'name' => 'Kost Mawar Indah']);
        $kostB = Kost::factory()->create(['status' => 'active', 'name' => 'Kost Melati Sejahtera']);

        $response = $this->get(route('marketplace.index', ['search' => 'Mawar']));

        $response->assertSee($kostA->name);
        $response->assertDontSee($kostB->name);
    }

    public function test_search_by_city_works(): void
    {
        $kostBandung = Kost::factory()->create(['status' => 'active']);
        $kostBandung->address()->create([
            'full_address' => 'Jl. Merdeka No. 1',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'district' => 'Coblong',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
        ]);

        $kostJakarta = Kost::factory()->create(['status' => 'active']);
        $kostJakarta->address()->create([
            'full_address' => 'Jl. Sudirman No. 2',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '10220',
            'district' => 'Menteng',
            'latitude' => -6.208763,
            'longitude' => 106.845599,
        ]);

        $response = $this->get(route('marketplace.index', ['search' => 'Bandung']));

        $response->assertSee($kostBandung->name);
        $response->assertDontSee($kostJakarta->name);
    }

    public function test_filter_by_price_range_works(): void
    {
        $kostCheap = Kost::factory()->create(['status' => 'active']);
        $roomTypeCheap = $kostCheap->roomTypes()->create([
            'name' => 'Standard',
            'slug' => 'standard',
            'room_size' => 12.5,
            'max_occupants' => 1,
            'security_deposit' => 500000,
        ]);
        $roomTypeCheap->priceSchemes()->create([
            'name' => 'Monthly',
            'duration_days' => 30,
            'duration_value' => 1,
            'price' => 500000,
            'is_active' => true,
        ]);

        $kostExpensive = Kost::factory()->create(['status' => 'active']);
        $roomTypeExpensive = $kostExpensive->roomTypes()->create([
            'name' => 'Deluxe',
            'slug' => 'deluxe',
            'room_size' => 20.0,
            'max_occupants' => 1,
            'security_deposit' => 2000000,
        ]);
        $roomTypeExpensive->priceSchemes()->create([
            'name' => 'Monthly',
            'duration_days' => 30,
            'duration_value' => 1,
            'price' => 2000000,
            'is_active' => true,
        ]);

        $response = $this->get(route('marketplace.index', ['price_min' => 400000, 'price_max' => 1000000]));

        $response->assertSee($kostCheap->name);
        $response->assertDontSee($kostExpensive->name);
    }

    public function test_filter_by_category_works(): void
    {
        $categoryPutra = Category::factory()->create(['name' => 'Kost Putra']);
        $categoryPutri = Category::factory()->create(['name' => 'Kost Putri']);

        $kostPutra = Kost::factory()->create(['status' => 'active']);
        $kostPutra->categories()->attach($categoryPutra->id);

        $kostPutri = Kost::factory()->create(['status' => 'active']);
        $kostPutri->categories()->attach($categoryPutri->id);

        $response = $this->get(route('marketplace.index', ['categories' => [$categoryPutra->id]]));

        $response->assertSee($kostPutra->name);
        $response->assertDontSee($kostPutri->name);
    }

    public function test_empty_state_when_no_kosts(): void
    {
        $response = $this->get(route('marketplace.index'));

        $response->assertOk();
        $response->assertViewHas('kosts');
        $this->assertCount(0, $response->viewData('kosts'));
    }

    public function test_empty_state_when_no_results_from_filter(): void
    {
        Kost::factory()->create(['status' => 'active']);

        $response = $this->get(route('marketplace.index', ['search' => 'NonExistentKost']));

        $response->assertOk();
        $response->assertViewHas('kosts');
        $this->assertCount(0, $response->viewData('kosts'));
    }
}
