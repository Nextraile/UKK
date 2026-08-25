<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;
use App\Policies\RoomTypePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTypePolicyTest extends TestCase
{
    use RefreshDatabase;

    private RoomTypePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RoomTypePolicy;
    }

    /** @test */
    public function admin_can_view_any_room_types_for_own_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->assertTrue($this->policy->viewAny($admin, $kost));
    }

    /** @test */
    public function admin_cannot_view_any_room_types_for_other_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);

        $this->assertFalse($this->policy->viewAny($admin, $kost));
    }

    /** @test */
    public function non_admin_cannot_view_any_room_types(): void
    {
        $tenant = User::factory()->tenant()->create();
        $kost = Kost::factory()->create();

        $this->assertFalse($this->policy->viewAny($tenant, $kost));
    }

    /** @test */
    public function admin_can_view_own_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertTrue($this->policy->view($admin, $roomType));
    }

    /** @test */
    public function admin_cannot_view_other_admin_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->view($admin, $roomType));
    }

    /** @test */
    public function admin_can_create_room_type_for_own_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->assertTrue($this->policy->create($admin, $kost));
    }

    /** @test */
    public function admin_cannot_create_room_type_for_other_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);

        $this->assertFalse($this->policy->create($admin, $kost));
    }

    /** @test */
    public function admin_can_update_own_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertTrue($this->policy->update($admin, $roomType));
    }

    /** @test */
    public function admin_cannot_update_other_admin_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->update($admin, $roomType));
    }

    /** @test */
    public function admin_can_delete_own_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertTrue($this->policy->delete($admin, $roomType));
    }

    /** @test */
    public function admin_cannot_delete_other_admin_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->delete($admin, $roomType));
    }

    /** @test */
    public function tenant_cannot_perform_any_action(): void
    {
        $tenant = User::factory()->tenant()->create();
        $kost = Kost::factory()->create();
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->viewAny($tenant, $kost));
        $this->assertFalse($this->policy->view($tenant, $roomType));
        $this->assertFalse($this->policy->create($tenant, $kost));
        $this->assertFalse($this->policy->update($tenant, $roomType));
        $this->assertFalse($this->policy->delete($tenant, $roomType));
    }
}
