<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRentalViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_rentals_for_own_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.rentals.index'));

        $response->assertOk();
        $response->assertSee('#'.$rental->id);
        $response->assertSee($tenant->name);
    }

    public function test_admin_cannot_see_rentals_from_other_kost(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $kost1 = Kost::factory()->create(['user_id' => $admin1->id]);
        $kost2 = Kost::factory()->create(['user_id' => $admin2->id]);

        $roomType1 = RoomType::factory()->create(['kost_id' => $kost1->id]);
        $roomType2 = RoomType::factory()->create(['kost_id' => $kost2->id]);

        $room1 = Room::factory()->create(['room_type_id' => $roomType1->id]);
        $room2 = Room::factory()->create(['room_type_id' => $roomType2->id]);

        $tenant = User::factory()->create(['role' => 'user']);

        $rental1 = Rental::factory()->create(['user_id' => $tenant->id, 'room_id' => $room1->id]);
        $rental2 = Rental::factory()->create(['user_id' => $tenant->id, 'room_id' => $room2->id]);

        $response = $this->actingAs($admin1)->get(route('admin.rentals.index'));

        $response->assertOk();
        $response->assertSee('#'.$rental1->id);
        $response->assertDontSee('#'.$rental2->id);
    }

    public function test_admin_can_view_rental_detail_for_own_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.rentals.show', $rental));

        $response->assertOk();
        $response->assertSee('#'.$rental->id);
        $response->assertSee($tenant->name);
        $response->assertSee($kost->name);
    }

    public function test_admin_cannot_view_rental_from_other_kost(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $kost2 = Kost::factory()->create(['user_id' => $admin2->id]);
        $roomType2 = RoomType::factory()->create(['kost_id' => $kost2->id]);
        $room2 = Room::factory()->create(['room_type_id' => $roomType2->id]);
        $tenant = User::factory()->create(['role' => 'user']);
        $rental2 = Rental::factory()->create(['user_id' => $tenant->id, 'room_id' => $room2->id]);

        $response = $this->actingAs($admin1)->get(route('admin.rentals.show', $rental2));

        $response->assertForbidden();
    }

    public function test_tenant_cannot_access_admin_rental_management(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($tenant)->get(route('admin.rentals.index'));

        $response->assertForbidden();
    }
}
