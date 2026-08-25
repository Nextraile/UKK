<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\RoomType;
use App\Policies\PriceSchemePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceSchemePolicyTest extends TestCase
{
    use RefreshDatabase;

    private PriceSchemePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PriceSchemePolicy;
    }

    /** @test */
    public function admin_can_view_any_price_schemes_for_own_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertTrue($this->policy->viewAny($admin, $roomType));
    }

    /** @test */
    public function admin_cannot_view_any_price_schemes_for_other_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->viewAny($admin, $roomType));
    }

    /** @test */
    public function admin_can_create_price_scheme_for_own_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertTrue($this->policy->create($admin, $roomType));
    }

    /** @test */
    public function admin_cannot_create_price_scheme_for_other_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertFalse($this->policy->create($admin, $roomType));
    }

    /** @test */
    public function admin_can_update_own_price_scheme(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $priceScheme = PriceScheme::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertTrue($this->policy->update($admin, $priceScheme));
    }

    /** @test */
    public function admin_cannot_update_other_admin_price_scheme(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $priceScheme = PriceScheme::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertFalse($this->policy->update($admin, $priceScheme));
    }

    /** @test */
    public function admin_can_delete_own_price_scheme(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $priceScheme = PriceScheme::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertTrue($this->policy->delete($admin, $priceScheme));
    }

    /** @test */
    public function admin_cannot_delete_other_admin_price_scheme(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $priceScheme = PriceScheme::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertFalse($this->policy->delete($admin, $priceScheme));
    }

    /** @test */
    public function tenant_cannot_perform_any_action(): void
    {
        $tenant = User::factory()->tenant()->create();
        $kost = Kost::factory()->create();
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $priceScheme = PriceScheme::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertFalse($this->policy->viewAny($tenant, $roomType));
        $this->assertFalse($this->policy->create($tenant, $roomType));
        $this->assertFalse($this->policy->update($tenant, $priceScheme));
        $this->assertFalse($this->policy->delete($tenant, $priceScheme));
    }
}
