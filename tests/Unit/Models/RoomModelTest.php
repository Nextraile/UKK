<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function room_belongs_to_kost(): void
    {
        $kost = Kost::factory()->create();
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertInstanceOf(Kost::class, $room->kost);
        $this->assertEquals($kost->id, $room->kost->id);
    }

    /** @test */
    public function room_belongs_to_room_type(): void
    {
        $roomType = RoomType::factory()->create();
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertInstanceOf(RoomType::class, $room->roomType);
        $this->assertEquals($roomType->id, $room->roomType->id);
    }

    /** @test */
    public function reserved_count_returns_zero_until_comp_006(): void
    {
        $room = Room::factory()->create();

        $this->assertEquals(0, $room->reserved_count);
    }

    /** @test */
    public function occupied_count_returns_zero_until_comp_006(): void
    {
        $room = Room::factory()->create();

        $this->assertEquals(0, $room->occupied_count);
    }

    /** @test */
    public function used_slots_sums_reserved_and_occupied(): void
    {
        $room = Room::factory()->create();

        // Until COMP-006: both are 0, so used_slots = 0
        $this->assertEquals(0, $room->used_slots);
    }

    /** @test */
    public function free_slots_calculated_from_max_occupants(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 3]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        // Until COMP-006: used_slots = 0, so free_slots = max_occupants
        $this->assertEquals(3, $room->free_slots);
    }

    /** @test */
    public function calculated_status_returns_available_until_comp_006(): void
    {
        $room = Room::factory()->create();

        $this->assertEquals('available', $room->calculated_status);
    }

    /** @test */
    public function room_status_defaults_to_available(): void
    {
        $room = Room::factory()->create(['status' => 'available']);

        $this->assertEquals('available', $room->status);
    }

    /** @test */
    public function room_can_be_unavailable(): void
    {
        $room = Room::factory()->create(['status' => 'unavailable']);

        $this->assertEquals('unavailable', $room->status);
    }

    /** @test */
    public function room_uses_soft_deletes(): void
    {
        $room = Room::factory()->create();
        $id = $room->id;

        $room->delete();

        $this->assertSoftDeleted('rooms', ['id' => $id]);
        $this->assertNotNull($room->fresh()->deleted_at);
    }
}
