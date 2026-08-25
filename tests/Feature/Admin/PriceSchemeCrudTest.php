<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceSchemeCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_price_schemes_index(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        PriceScheme::factory()->count(3)->create(['room_type_id' => $roomType->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.price-schemes.index', $roomType));

        $response->assertStatus(200);
        $response->assertViewIs('admin.price-schemes.index');
        $response->assertViewHas('roomType');
    }

    /** @test */
    public function admin_can_create_price_scheme(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.price-schemes.store', $roomType), [
                'name' => 'Paket Bulanan',
                'description' => 'Sewa per bulan',
                'price' => 1500000,
                'duration_value' => 1,
                'duration_unit' => 'month',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('admin.price-schemes.index', $roomType));
        $this->assertDatabaseHas('price_schemes', [
            'room_type_id' => $roomType->id,
            'name' => 'Paket Bulanan',
            'price' => '1500000.00',
            'duration_unit' => 'month',
        ]);
    }

    /** @test */
    public function admin_can_update_price_scheme(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $priceScheme = PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'name' => 'Old Name',
            'price' => 1000000,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.price-schemes.update', [$roomType, $priceScheme]), [
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'price' => 1800000,
                'duration_value' => 2,
                'duration_unit' => 'week',
                'is_active' => false,
            ]);

        $response->assertRedirect(route('admin.price-schemes.index', $roomType));
        $this->assertDatabaseHas('price_schemes', [
            'id' => $priceScheme->id,
            'name' => 'Updated Name',
            'price' => '1800000.00',
            'duration_value' => 2,
        ]);
    }

    /** @test */
    public function admin_can_delete_price_scheme(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $priceScheme = PriceScheme::factory()->create(['room_type_id' => $roomType->id]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.price-schemes.destroy', [$roomType, $priceScheme]));

        $response->assertRedirect(route('admin.price-schemes.index', $roomType));
        $this->assertSoftDeleted('price_schemes', ['id' => $priceScheme->id]);
    }

    /** @test */
    public function admin_can_toggle_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $priceScheme = PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.price-schemes.toggle-active', [$roomType, $priceScheme]));

        $response->assertRedirect(route('admin.price-schemes.index', $roomType));
        $this->assertDatabaseHas('price_schemes', [
            'id' => $priceScheme->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function price_validation_required(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.price-schemes.store', $roomType), [
                'name' => 'Test Package',
                'price' => '',
                'duration_value' => 1,
                'duration_unit' => 'month',
            ]);

        $response->assertSessionHasErrors('price');
    }

    /** @test */
    public function price_validation_min_zero(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.price-schemes.store', $roomType), [
                'name' => 'Test Package',
                'price' => -100,
                'duration_value' => 1,
                'duration_unit' => 'month',
            ]);

        $response->assertSessionHasErrors('price');
    }

    /** @test */
    public function duration_unit_validation_enum(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.price-schemes.store', $roomType), [
                'name' => 'Test Package',
                'price' => 1000000,
                'duration_value' => 1,
                'duration_unit' => 'invalid_unit',
            ]);

        $response->assertSessionHasErrors('duration_unit');
    }

    /** @test */
    public function unauthorized_admin_cannot_access(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.price-schemes.index', $roomType));

        $response->assertStatus(403);
    }

    /** @test */
    public function inactive_price_schemes_shown_in_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $inactiveScheme = PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.price-schemes.index', $roomType));

        $response->assertStatus(200);
        $response->assertSee($inactiveScheme->name);
    }
}
