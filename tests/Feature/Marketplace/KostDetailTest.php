<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KostDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_active_kost_detail(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertOk();
        $response->assertViewIs('marketplace.show');
        $response->assertSee($kost->name);
    }

    public function test_non_active_kost_returns_404(): void
    {
        $draftKost = Kost::factory()->create(['status' => 'draft']);

        $response = $this->get(route('marketplace.show', $draftKost));

        $response->assertNotFound();
    }

    public function test_kost_detail_displays_all_info(): void
    {
        $kost = Kost::factory()->create([
            'status' => 'active',
            'description' => 'Test description',
            'facilities' => ['WiFi', 'AC'],
            'rules' => ['No smoking', 'No pets'],
        ]);

        $kost->address()->create([
            'full_address' => 'Jl. Test No. 123',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'district' => 'Coblong',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
        ]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertSee($kost->name);
        $response->assertSee($kost->description);
        $response->assertSee('WiFi');
        $response->assertSee('AC');
        $response->assertSee('No smoking');
        $response->assertSee('No pets');
        $response->assertSee('Jl. Test No. 123');
        $response->assertViewHas('kost');
        $this->assertEquals('Bandung', $response->viewData('kost')->address->city);
    }

    public function test_facilities_parsed_from_json(): void
    {
        $kost = Kost::factory()->create([
            'status' => 'active',
            'facilities' => ['WiFi', 'AC', 'Kamar Mandi Dalam'],
        ]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertSee('WiFi');
        $response->assertSee('AC');
        $response->assertSee('Kamar Mandi Dalam');
    }

    public function test_rules_parsed_from_json(): void
    {
        $kost = Kost::factory()->create([
            'status' => 'active',
            'rules' => ['No smoking', 'No pets', 'Curfew at 10 PM'],
        ]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertSee('No smoking');
        $response->assertSee('No pets');
        $response->assertSee('Curfew at 10 PM');
    }

    public function test_room_types_with_availability_displayed(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = $kost->roomTypes()->create([
            'name' => 'Standard',
            'slug' => 'standard',
            'room_size' => 12.5,
            'max_occupants' => 2,
            'security_deposit' => 1000000,
        ]);
        Room::factory()->count(3)->create(['kost_id' => $kost->id, 'room_type_id' => $roomType->id]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertSee('Standard');
    }

    public function test_map_coordinates_passed_to_view(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);

        $kost->address()->create([
            'full_address' => 'Jl. Test No. 123',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'district' => 'Coblong',
            'latitude' => -6.914744,
            'longitude' => 107.609810,
        ]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertViewHas('kost');
        $viewKost = $response->viewData('kost');
        $this->assertEquals(-6.914744, $viewKost->address->latitude);
        $this->assertEquals(107.609810, $viewKost->address->longitude);
    }

    public function test_room_availability_calculated_correctly(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = $kost->roomTypes()->create([
            'name' => 'Standard',
            'slug' => 'standard',
            'room_size' => 12.5,
            'max_occupants' => 2,
            'security_deposit' => 1000000,
        ]);

        // Create 3 rooms
        Room::factory()->count(3)->create(['kost_id' => $kost->id, 'room_type_id' => $roomType->id]);

        // Test that kost has room types with rooms
        $this->assertCount(1, $kost->fresh()->roomTypes);
        $this->assertEquals(3, $kost->fresh()->roomTypes->first()->rooms->count());
    }

    public function test_soft_deleted_kost_not_accessible(): void
    {
        $kost = Kost::factory()->create(['status' => 'active', 'deleted_at' => now()]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertNotFound();
    }

    public function test_kost_images_displayed(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);

        // Create kost images via relationship
        $kost->kostImages()->create([
            'image_path' => 'kost-images/image1.jpg',
            'caption' => 'Front view',
            'is_thumbnail' => true,
            'sort_order' => 1,
        ]);
        $kost->kostImages()->create([
            'image_path' => 'kost-images/image2.jpg',
            'caption' => 'Room view',
            'is_thumbnail' => false,
            'sort_order' => 2,
        ]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertOk();
        $response->assertViewHas('kost');
        $this->assertCount(2, $response->viewData('kost')->kostImages);
    }

    public function test_owner_contact_information_displayed(): void
    {
        $admin = User::factory()->admin()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '081234567890',
        ]);

        $kost = Kost::factory()->create([
            'status' => 'active',
            'user_id' => $admin->id,
        ]);

        $response = $this->get(route('marketplace.show', $kost));

        $response->assertOk();
        $response->assertViewHas('kost');
        $viewKost = $response->viewData('kost');
        $this->assertEquals($admin->id, $viewKost->owner->id);
        $this->assertEquals('081234567890', $viewKost->owner->phone);
    }
}
