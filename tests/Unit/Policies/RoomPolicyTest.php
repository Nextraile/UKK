<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Policies\RoomPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomPolicyTest extends TestCase
{
    use RefreshDatabase;

    private RoomPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RoomPolicy;
    }

    /** @test */
    public function admin_can_view_any_rooms_for_own_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->assertTrue($this->policy->viewAny($admin, $kost));
    }

    /** @test */
    public function admin_cannot_view_any_rooms_for_other_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);

        $this->assertFalse($this->policy->viewAny($admin, $kost));
    }

    /** @test */
    public function admin_can_view_own_room(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertTrue($this->policy->view($admin, $room));
    }

    /** @test */
    public function admin_cannot_view_other_admin_room(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->view($admin, $room));
    }

    /** @test */
    public function admin_can_create_room_for_own_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->assertTrue($this->policy->create($admin, $kost));
    }

    /** @test */
    public function admin_cannot_create_room_for_other_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);

        $this->assertFalse($this->policy->create($admin, $kost));
    }

    /** @test */
    public function admin_can_update_own_room(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertTrue($this->policy->update($admin, $room));
    }

    /** @test */
    public function admin_cannot_update_other_admin_room(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->update($admin, $room));
    }

    /** @test */
    public function admin_can_delete_own_room(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertTrue($this->policy->delete($admin, $room));
    }

    /** @test */
    public function admin_cannot_delete_other_admin_room(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->delete($admin, $room));
    }

    /** @test */
    public function set_unavailable_stub_always_passes_for_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        // Until COMP-006: stub always returns true
        $this->assertTrue($this->policy->setUnavailable($admin, $room));
    }

    /** @test */
    public function set_unavailable_fails_for_non_owner(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->setUnavailable($admin, $room));
    }

    /** @test */
    public function tenant_cannot_perform_any_action(): void
    {
        $tenant = User::factory()->tenant()->create();
        $kost = Kost::factory()->create();
        $room = Room::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->viewAny($tenant, $kost));
        $this->assertFalse($this->policy->view($tenant, $room));
        $this->assertFalse($this->policy->create($tenant, $kost));
        $this->assertFalse($this->policy->update($tenant, $room));
        $this->assertFalse($this->policy->delete($tenant, $room));
        $this->assertFalse($this->policy->setUnavailable($tenant, $room));
    }
}
