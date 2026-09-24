<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Rental\Models\Rental;
use App\Domain\RoomInventory\Models\Room;
use App\Domain\RoomInventory\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalDateOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_book_room_when_dates_dont_overlap(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Rental 1: Oct 1-15
        Rental::factory()->active()->create([
            'room_id' => $room->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-15',
        ]);

        // Rental 2: Nov 1-15
        Rental::factory()->active()->create([
            'room_id' => $room->id,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-15',
        ]);

        // Check: Oct 20-25 (gap between rentals)
        $freeSlots = $room->getFreeSlotsForPeriod(
            Carbon::parse('2026-10-20'),
            Carbon::parse('2026-10-25')
        );

        $this->assertEquals(2, $freeSlots); // Both slots free during gap
        $this->assertTrue($room->isAvailableForPeriod(
            Carbon::parse('2026-10-20'),
            Carbon::parse('2026-10-25')
        ));
    }

    public function test_cannot_book_when_period_overlaps_existing_rental(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 1]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Existing rental: Oct 1-31
        Rental::factory()->active()->create([
            'room_id' => $room->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]);

        // Try: Oct 15-20 (overlaps)
        $freeSlots = $room->getFreeSlotsForPeriod(
            Carbon::parse('2026-10-15'),
            Carbon::parse('2026-10-20')
        );

        $this->assertEquals(0, $freeSlots);
        $this->assertFalse($room->isAvailableForPeriod(
            Carbon::parse('2026-10-15'),
            Carbon::parse('2026-10-20')
        ));
    }

    public function test_multiple_overlapping_rentals_consume_multiple_slots(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 3]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Rental 1: Oct 1-15
        Rental::factory()->active()->create([
            'room_id' => $room->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-15',
        ]);

        // Rental 2: Oct 10-20 (overlaps with Rental 1)
        Rental::factory()->active()->create([
            'room_id' => $room->id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-20',
        ]);

        // Check: Oct 12 (both rentals active)
        $freeSlots = $room->getFreeSlotsForPeriod(
            Carbon::parse('2026-10-12'),
            Carbon::parse('2026-10-13')
        );

        $this->assertEquals(1, $freeSlots); // 3 total - 2 occupied = 1 free
    }

    public function test_documents_pending_status_consumes_slot(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Rental with documents_pending status
        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'documents_pending',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]);

        // Check availability for same period
        $freeSlots = $room->getFreeSlotsForPeriod(
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-31')
        );

        $this->assertEquals(1, $freeSlots); // 2 total - 1 (documents_pending) = 1 free
    }

    public function test_edge_case_adjacent_periods_no_overlap(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 1]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Rental: Oct 1-15 (ends at end of Oct 15)
        Rental::factory()->active()->create([
            'room_id' => $room->id,
            'start_date' => '2026-10-01 00:00:00',
            'end_date' => '2026-10-15 23:59:59',
        ]);

        // Check: Oct 16 onwards (no overlap, should be available)
        $freeSlots = $room->getFreeSlotsForPeriod(
            Carbon::parse('2026-10-16 00:00:00'),
            Carbon::parse('2026-10-31 23:59:59')
        );

        $this->assertEquals(1, $freeSlots);
    }

    public function test_all_active_statuses_consume_slots(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 5]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Create rentals with different active statuses
        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'payment_pending',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]);

        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'paid',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]);

        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'documents_pending',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]);

        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'confirmed',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]);

        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'active',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]);

        // Check availability for same period
        $freeSlots = $room->getFreeSlotsForPeriod(
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-31')
        );

        $this->assertEquals(0, $freeSlots); // All 5 slots consumed
    }

    public function test_cancelled_and_completed_rentals_do_not_consume_slots(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 2]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Cancelled rental
        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'cancelled',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]);

        // Completed rental
        Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'completed',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]);

        // Check availability for same period
        $freeSlots = $room->getFreeSlotsForPeriod(
            Carbon::parse('2026-10-01'),
            Carbon::parse('2026-10-31')
        );

        $this->assertEquals(2, $freeSlots); // Both slots free (cancelled/completed ignored)
    }

    public function test_partial_overlap_consumes_slot(): void
    {
        $roomType = RoomType::factory()->create(['max_occupants' => 1]);
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);

        // Existing rental: Oct 10-20
        Rental::factory()->active()->create([
            'room_id' => $room->id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-20',
        ]);

        // Try: Oct 15-25 (partial overlap: Oct 15-20)
        $freeSlots = $room->getFreeSlotsForPeriod(
            Carbon::parse('2026-10-15'),
            Carbon::parse('2026-10-25')
        );

        $this->assertEquals(0, $freeSlots); // Slot consumed due to partial overlap
    }
}
