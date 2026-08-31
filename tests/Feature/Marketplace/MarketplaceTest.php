<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domain\Kost\Models\Address;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\RoomType;
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

    /**
     * @test
     */
    public function test_it_displays_kost_category_badges_in_card(): void
    {
        $category1 = Category::factory()->create(['name' => 'Kost Putra']);
        $category2 = Category::factory()->create(['name' => 'Kost Campur']);

        $kost = Kost::factory()->create(['status' => 'active']);
        $kost->categories()->attach([$category1->id, $category2->id]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('Kost Putra');
        $response->assertSee('Kost Campur');
    }

    /**
     * @test
     */
    public function test_it_displays_full_address_in_kost_card(): void
    {
        $kost = Kost::factory()->create(['status' => 'active', 'name' => 'Kost Test']);
        Address::factory()->create([
            'kost_id' => $kost->id,
            'city' => 'Bandung',
            'district' => 'Coblong',
            'province' => 'Jawa Barat',
            'postal_code' => '40132',
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('Coblong');
        $response->assertSee('Bandung');
        // Province and postal code SHOULD be displayed (fixed in marketplace controller)
        $response->assertSee('Jawa Barat');
        $response->assertSee('40132');
    }

    /**
     * @test
     */
    public function test_it_displays_minimum_price_with_correct_format(): void
    {
        $kost = Kost::factory()->create(['status' => 'active', 'name' => 'Kost Murah']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'price' => 1200000,
            'is_active' => true,
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        // Should display formatted price "Mulai dari Rp 1.200,0jt"
        $response->assertSee('Mulai dari');
        $response->assertSee('1.200,0jt');
    }

    /**
     * @test
     */
    public function test_it_uses_kost_card_component_not_inline_html(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        // Verify component-specific classes (from kost-card.blade.php)
        $response->assertSee('bg-white dark:bg-surface-raised-dark rounded-xl', false);
    }

    /**
     * @test
     */
    public function test_it_displays_multiple_categories_for_single_kost(): void
    {
        $category1 = Category::factory()->create(['name' => 'Kost Putra']);
        $category2 = Category::factory()->create(['name' => 'Kost Campur']);
        $category3 = Category::factory()->create(['name' => 'Kost Strategis']);

        $kost = Kost::factory()->create(['status' => 'active']);
        $kost->categories()->attach([$category1->id, $category2->id, $category3->id]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('Kost Putra');
        $response->assertSee('Kost Campur');
        $response->assertSee('Kost Strategis');
    }

    /**
     * @test
     */
    public function test_it_handles_kost_without_categories_gracefully(): void
    {
        $kost = Kost::factory()->create(['status' => 'active', 'name' => 'Kost No Category']);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('Kost No Category');
        // Should not throw error when no categories exist
    }

    /**
     * @test
     */
    public function test_it_handles_kost_without_address_gracefully(): void
    {
        $kost = Kost::factory()->create(['status' => 'active', 'name' => 'Kost No Address']);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('Kost No Address');
        // Should not throw error when address is null
    }

    /**
     * @test
     */
    public function test_it_handles_kost_without_price_schemes_gracefully(): void
    {
        $kost = Kost::factory()->create(['status' => 'active', 'name' => 'Kost No Price']);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('Kost No Price');
        // Should not throw error when min_price is null
    }

    /**
     * @test
     */
    public function test_it_displays_lowest_price_from_multiple_room_types(): void
    {
        $kost = Kost::factory()->create(['status' => 'active', 'name' => 'Kost Multi Room']);

        // Room type 1: min 1500000
        $roomType1 = RoomType::factory()->create(['kost_id' => $kost->id]);
        PriceScheme::factory()->create([
            'room_type_id' => $roomType1->id,
            'price' => 1500000,
            'is_active' => true,
        ]);

        // Room type 2: min 900000 (lowest)
        $roomType2 = RoomType::factory()->create(['kost_id' => $kost->id]);
        PriceScheme::factory()->create([
            'room_type_id' => $roomType2->id,
            'price' => 900000,
            'is_active' => true,
        ]);

        // Room type 3: min 2000000
        $roomType3 = RoomType::factory()->create(['kost_id' => $kost->id]);
        PriceScheme::factory()->create([
            'room_type_id' => $roomType3->id,
            'price' => 2000000,
            'is_active' => true,
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        // Should display the lowest price (900k = 900,0jt)
        $response->assertSee('900,0jt');
    }
}
