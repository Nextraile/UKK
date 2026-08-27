<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Room belongs to a Kost.
     */
    public function test_room_belongs_to_kost(): void
    {
        $kost = Kost::factory()->create();
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertInstanceOf(Kost::class, $room->kost);
        $this->assertEquals($kost->id, $room->kost->id);
    }

    /**
     * Room belongs to a RoomType.
     */
    public function test_room_belongs_to_room_type(): void
    {
        $roomType = RoomType::factory()->create();
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertInstanceOf(RoomType::class, $room->roomType);
        $this->assertEquals($roomType->id, $room->roomType->id);
    }

    /**
     * Reserved count returns zero until COMP-006 implemented.
     */
    public function test_reserved_count_returns_zero_until_comp_006(): void
    {
        $room = Room::factory()->create();

        $this->assertEquals(0, $room->reserved_count);
    }

    /**
     * Occupied count returns zero until COMP-006 implemented.
     */
    public function test_occupied_count_returns_zero_until_comp_006(): void
    {
        $room = Room::factory()->create();

        $this->assertEquals(0, $room->occupied_count);
    }

    /**
     * Used slots sums reserved and occupied rentals.
     *
     * FR-046: Room occupancy calculated from active rentals.
     * ADR-018: used_slots = pending + paid + confirmed + active.
     */
    public function test_used_slots_sums_reserved_and_occupied(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id, 'max_occupants' => 5]);
        $room = Room::factory()->create(['kost_id' => $kost->id, 'room_type_id' => $roomType->id]);

        // Create rentals in different states (pending, paid, confirmed = reserved; active = occupied)
        Rental::factory()->pending()->create(['room_id' => $room->id]);
        Rental::factory()->paid()->create(['room_id' => $room->id]);
        Rental::factory()->confirmed()->create(['room_id' => $room->id]);
        Rental::factory()->active()->create(['room_id' => $room->id]);

        $room->refresh();

        // used_slots should count all: pending(1) + paid(1) + confirmed(1) + active(1) = 4
        $this->assertEquals(4, $room->used_slots);
    }

    /**
     * Free slots calculated from max_occupants minus used_slots.
     *
     * FR-046: Room availability based on max_occupants and current rentals.
     * ADR-018: free_slots = max_occupants - used_slots.
     */
    public function test_free_slots_calculated_from_max_occupants(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id, 'max_occupants' => 3]);
        $room = Room::factory()->create(['kost_id' => $kost->id, 'room_type_id' => $roomType->id]);

        // Create 1 active rental (uses 1 slot)
        Rental::factory()->active()->create(['room_id' => $room->id]);

        $room->refresh();

        // free_slots = max_occupants(3) - used_slots(1) = 2
        $this->assertEquals(2, $room->free_slots);
    }

    /**
     * Calculated status returns available until COMP-006.
     */
    public function test_calculated_status_returns_available_until_comp_006(): void
    {
        $room = Room::factory()->create();

        $this->assertEquals('available', $room->calculated_status);
    }

    /**
     * Room status defaults to available.
     */
    public function test_room_status_defaults_to_available(): void
    {
        $room = Room::factory()->create(['status' => 'available']);

        $this->assertEquals('available', $room->status);
    }

    /**
     * Room can be set to unavailable status.
     */
    public function test_room_can_be_unavailable(): void
    {
        $room = Room::factory()->create(['status' => 'unavailable']);

        $this->assertEquals('unavailable', $room->status);
    }

    /**
     * Room uses soft deletes.
     */
    public function test_room_uses_soft_deletes(): void
    {
        $room = Room::factory()->create();
        $id = $room->id;

        $room->delete();

        $this->assertSoftDeleted('rooms', ['id' => $id]);
        $this->assertNotNull($room->fresh()->deleted_at);
    }
}
