<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test room with no rentals shows available.
     */
    public function test_room_with_no_rentals_shows_available(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 2,
        ]);
        Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $roomType->load('rooms.rentals');

        $this->assertEquals(1, $roomType->available_count);
    }

    /**
     * Test room fully booked shows unavailable.
     */
    public function test_room_fully_booked_shows_unavailable(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 2,
        ]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        Rental::factory()->count(2)->create([
            'room_id' => $room->id,
            'status' => 'active',
        ]);

        $roomType->load('rooms.rentals');

        $this->assertEquals(0, $roomType->available_count);
    }

    /**
     * Test room partially booked shows available.
     */
    public function test_room_partially_booked_shows_available(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 3,
        ]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'active',
        ]);

        $roomType->load('rooms.rentals');

        $this->assertEquals(1, $roomType->available_count);
        $this->assertEquals(2, $room->free_slots);
    }

    /**
     * Test inactive room excluded from count.
     */
    public function test_inactive_room_excluded_from_count(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 2,
        ]);

        Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'unavailable',
        ]);

        $roomType->load('rooms.rentals');

        $this->assertEquals(1, $roomType->available_count);
    }

    /**
     * Test cancelled and rejected rentals don't consume slots.
     */
    public function test_cancelled_rejected_rentals_dont_consume_slots(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 2,
        ]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'cancelled',
        ]);

        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'rejected',
        ]);

        $roomType->load('rooms.rentals');

        $this->assertEquals(1, $roomType->available_count);
        $this->assertEquals(2, $room->free_slots);
    }

    /**
     * Test multiple rooms aggregation.
     */
    public function test_multiple_rooms_aggregation(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 2,
        ]);

        $room1 = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $room2 = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $room3 = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        Rental::factory()->count(2)->create([
            'room_id' => $room1->id,
            'status' => 'active',
        ]);

        Rental::factory()->create([
            'room_id' => $room2->id,
            'status' => 'active',
        ]);

        $roomType->load('rooms.rentals');

        $this->assertEquals(2, $roomType->available_count);
    }

    /**
     * Test pending rentals consume slots.
     */
    public function test_pending_rentals_consume_slots(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 2,
        ]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        $roomType->load('rooms.rentals');

        $this->assertEquals(1, $room->free_slots);
        $this->assertEquals(1, $roomType->available_count);
    }

    /**
     * Test empty rooms collection returns zero.
     */
    public function test_empty_rooms_collection_returns_zero(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 2,
        ]);

        $roomType->load('rooms.rentals');

        $this->assertEquals(0, $roomType->available_count);
    }

    /**
     * Test max_occupants zero returns zero.
     */
    public function test_max_occupants_zero_returns_zero(): void
    {
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'max_occupants' => 0,
        ]);
        Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        $roomType->load('rooms.rentals');

        $this->assertEquals(0, $roomType->available_count);
    }
}
