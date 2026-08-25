<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test FR-046: Room status management (stub until COMP-006).
 *
 * Current behavior: Validation always passes (no rentals exist).
 * TODO: COMP-006 - Add tests for actual rental validation.
 */
class RoomStatusValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_set_room_available(): void
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

    /** @test */
    public function admin_can_set_room_unavailable_stub(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create([
            'kost_id' => $kost->id,
            'status' => 'available',
        ]);

        // FR-046: Stub always allows (no rentals exist yet)
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

    /** @test */
    public function status_validation_only_accepts_available_or_unavailable(): void
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

    /** @test */
    public function unauthorized_admin_cannot_set_status(): void
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

    /** @test */
    public function set_unavailable_validation_stub_always_passes(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        // FR-046: Until COMP-006, used_slots always 0, so validation always passes
        $this->assertEquals(0, $room->used_slots);

        $response = $this->actingAs($admin)
            ->patch(route('admin.rooms.set-status', [$kost, $room]), [
                'status' => 'unavailable',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }
}
