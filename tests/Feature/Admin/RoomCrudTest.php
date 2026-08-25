<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
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

    /** @test */
    public function occupancy_stub_displays_zero(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        // Until COMP-006: reserved_count, occupied_count, used_slots all return 0
        $this->assertEquals(0, $room->reserved_count);
        $this->assertEquals(0, $room->occupied_count);
        $this->assertEquals(0, $room->used_slots);
    }

    /** @test */
    public function free_slots_equals_max_occupants_stub(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id, 'max_occupants' => 3]);
        $room = Room::factory()->create(['kost_id' => $kost->id, 'room_type_id' => $roomType->id]);

        // Until COMP-006: free_slots = max_occupants (no rentals exist)
        $this->assertEquals(3, $room->free_slots);
    }

    /** @test */
    public function calculated_status_always_available_stub(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        // Until COMP-006: calculated_status always returns 'available'
        $this->assertEquals('available', $room->calculated_status);
    }
}
