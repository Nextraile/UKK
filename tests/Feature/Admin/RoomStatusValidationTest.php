<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test FR-046: Room status management.
 *
 * Room can only be set unavailable if no active/reserved rentals exist.
 */
class RoomStatusValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Admin can set room available.
     */
    public function test_admin_can_set_room_available(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'status' => 'unavailable',
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.rooms.set-status', [$kost, $room]), [
                'status' => 'available',
            ]);

        $response->assertRedirect(route('admin.rooms.index', $kost));
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'available',
        ]);
    }

    /**
     * Admin can set room unavailable when no rentals exist.
     */
    public function test_admin_can_set_room_unavailable_when_empty(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'status' => 'available',
        ]);

        // FR-046: Admin can set unavailable when room is empty (no rentals)
        $response = $this->actingAs($admin)
            ->patch(route('admin.rooms.set-status', [$kost, $room]), [
                'status' => 'unavailable',
            ]);

        $response->assertRedirect(route('admin.rooms.index', $kost));
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'unavailable',
        ]);
    }

    /**
     * Status validation only accepts available or unavailable.
     */
    public function test_status_validation_only_accepts_available_or_unavailable(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.rooms.set-status', [$kost, $room]), [
                'status' => 'invalid_status',
            ]);

        $response->assertSessionHasErrors('status');
    }

    /**
     * Unauthorized admin cannot set status.
     */
    public function test_unauthorized_admin_cannot_set_status(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.rooms.set-status', [$kost, $room]), [
                'status' => 'unavailable',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Admin cannot set room unavailable when active or reserved rentals exist.
     *
     * FR-046: Room can only be set unavailable if no active/reserved rentals exist.
     * ADR-017: Room occupancy calculated real-time from rentals.
     */
    public function test_admin_cannot_set_unavailable_with_rentals(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'status' => 'available',
        ]);

        // Create active rental (blocks setUnavailable)
        Rental::factory()->active()->create(['room_id' => $room->id]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.rooms.set-status', [$kost, $room]), [
                'status' => 'unavailable',
            ]);

        $response->assertStatus(403); // Policy denies
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => 'available', // Unchanged
        ]);
    }
}
