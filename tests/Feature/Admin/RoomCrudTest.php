<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_rooms_index(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        Room::factory()->count(3)->create(['kost_id' => $kost->id, 'room_type_id' => $roomType->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.rooms.index', $kost));

        $response->assertStatus(200);
        $response->assertViewIs('admin.rooms.index');
        $response->assertViewHas('roomTypes');
    }

    /** @test */
    public function admin_can_create_room(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.rooms.store', $kost), [
                'kost_id' => $kost->id,
                'room_type_id' => $roomType->id,
                'code' => 'A101',
                'status' => 'available',
                'internal_notes' => 'Test notes',
            ]);

        $response->assertRedirect(route('admin.rooms.index', $kost));
        $this->assertDatabaseHas('rooms', [
            'kost_id' => $kost->id,
            'room_type_id' => $roomType->id,
            'code' => 'A101',
            'status' => 'available',
        ]);
    }

    /** @test */
    public function admin_can_update_room(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'room_type_id' => $roomType->id,
            'code' => 'A101',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.rooms.update', [$kost, $room]), [
                'kost_id' => $kost->id,
                'room_type_id' => $roomType->id,
                'code' => 'A102',
                'status' => 'unavailable',
                'internal_notes' => 'Updated notes',
            ]);

        $response->assertRedirect(route('admin.rooms.index', $kost));
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'code' => 'A102',
            'status' => 'unavailable',
        ]);
    }

    /** @test */
    public function admin_can_delete_room(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.rooms.destroy', [$kost, $room]));

        $response->assertRedirect(route('admin.rooms.index', $kost));
        $this->assertSoftDeleted('rooms', ['id' => $room->id]);
    }

    /** @test */
    public function code_must_be_unique_per_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        Room::factory()->create([
            'kost_id' => $kost->id,
            'room_type_id' => $roomType->id,
            'code' => 'A101',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.rooms.store', $kost), [
                'kost_id' => $kost->id,
                'room_type_id' => $roomType->id,
                'code' => 'A101',
                'status' => 'available',
            ]);

        $response->assertSessionHasErrors('code');
    }

    /** @test */
    public function room_type_must_belong_to_same_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $otherKost = Kost::factory()->create();
        $roomType = RoomType::factory()->create(['kost_id' => $otherKost->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.rooms.store', $kost), [
                'kost_id' => $kost->id,
                'room_type_id' => $roomType->id,
                'code' => 'A101',
                'status' => 'available',
            ]);

        $response->assertSessionHasErrors('room_type_id');
    }

    /** @test */
    public function default_status_is_available(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->actingAs($admin)
            ->post(route('admin.rooms.store', $kost), [
                'kost_id' => $kost->id,
                'room_type_id' => $roomType->id,
                'code' => 'A101',
            ]);

        $this->assertDatabaseHas('rooms', [
            'code' => 'A101',
            'status' => 'available',
        ]);
    }

    /** @test */
    public function unauthorized_admin_cannot_access(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.rooms.index', $kost));

        $response->assertStatus(403);
    }

    /** @test */
    public function rooms_grouped_by_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType1 = RoomType::factory()->create(['kost_id' => $kost->id, 'name' => 'Type A']);
        $roomType2 = RoomType::factory()->create(['kost_id' => $kost->id, 'name' => 'Type B']);
        Room::factory()->count(2)->create(['kost_id' => $kost->id, 'room_type_id' => $roomType1->id]);
        Room::factory()->count(3)->create(['kost_id' => $kost->id, 'room_type_id' => $roomType2->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.rooms.index', $kost));

        $response->assertStatus(200);
        $roomTypes = $response->viewData('roomTypes');
        $this->assertCount(2, $roomTypes);
        $this->assertCount(2, $roomTypes[0]->rooms);
        $this->assertCount(3, $roomTypes[1]->rooms);
    }

    /**
     * Occupancy counts active and reserved rentals.
     *
     * FR-046: Room occupancy calculated from active rentals.
     * ADR-018: reserved_count = pending+paid+confirmed, occupied_count = active.
     */
    public function test_occupancy_counts_active_and_reserved_rentals(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id, 'max_occupants' => 3]);
        $room = Room::factory()->create(['kost_id' => $kost->id, 'room_type_id' => $roomType->id]);

        // Create rentals in different states
        Rental::factory()->pending()->create(['room_id' => $room->id]); // reserved
        Rental::factory()->active()->create(['room_id' => $room->id]); // occupied

        $room->refresh();

        $this->assertEquals(1, $room->reserved_count);
        $this->assertEquals(1, $room->occupied_count);
        $this->assertEquals(2, $room->used_slots);
        $this->assertEquals(1, $room->free_slots); // 3 max - 2 used
    }

    /**
     * Free slots calculated as max_occupants minus used_slots.
     *
     * FR-046: Room availability based on max_occupants and current rentals.
     * ADR-018: free_slots = max_occupants - used_slots.
     */
    public function test_free_slots_calculated_correctly(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id, 'max_occupants' => 3]);
        $room = Room::factory()->create(['kost_id' => $kost->id, 'room_type_id' => $roomType->id]);

        // Create 2 active rentals (uses 2 slots)
        Rental::factory()->active()->create(['room_id' => $room->id]);
        Rental::factory()->confirmed()->create(['room_id' => $room->id]);

        $room->refresh();

        // free_slots = max_occupants(3) - used_slots(2) = 1
        $this->assertEquals(1, $room->free_slots);
    }

    /**
     * Calculated status reflects real-time availability.
     *
     * FR-046: Room status calculated from occupancy.
     * ADR-018: calculated_status = 'unavailable' | 'full' | 'available'.
     */
    public function test_calculated_status_reflects_availability(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id, 'max_occupants' => 2]);
        $room = Room::factory()->create(['kost_id' => $kost->id, 'room_type_id' => $roomType->id, 'status' => 'available']);

        // Empty room -> available
        $this->assertEquals('available', $room->calculated_status);

        // Fill room completely -> full
        Rental::factory()->active()->create(['room_id' => $room->id]);
        Rental::factory()->active()->create(['room_id' => $room->id]);
        $room->refresh();
        $this->assertEquals('full', $room->calculated_status);

        // Manual unavailable status -> unavailable
        $room->update(['status' => 'unavailable']);
        $this->assertEquals('unavailable', $room->calculated_status);
    }
}
