<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTypeFacilitiesRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_facilities_via_main_form(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->for($kost)->create(['facilities' => ['AC']]);

        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'facilities' => ['AC', 'TV', 'Lemari'],
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));

        $roomType->refresh();
        $this->assertCount(3, $roomType->facilities);
        $this->assertContains('AC', $roomType->facilities);
        $this->assertContains('TV', $roomType->facilities);
        $this->assertContains('Lemari', $roomType->facilities);
    }

    public function test_admin_can_update_rules_via_main_form(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->for($kost)->create(['rules' => ['No smoking']]);

        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'rules' => ['No smoking', 'Max jam malam 22:00', 'No pets'],
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));

        $roomType->refresh();
        $this->assertCount(3, $roomType->rules);
        $this->assertContains('No smoking', $roomType->rules);
        $this->assertContains('Max jam malam 22:00', $roomType->rules);
    }

    public function test_admin_can_clear_facilities(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->for($kost)->create(['facilities' => ['AC', 'TV']]);

        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'facilities' => [], // Empty array clears
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));

        $roomType->refresh();
        $this->assertEmpty($roomType->facilities);
    }

    public function test_admin_can_clear_rules(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->for($kost)->create(['rules' => ['No smoking', 'No pets']]);

        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'rules' => [], // Empty array clears
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));

        $roomType->refresh();
        $this->assertEmpty($roomType->rules);
    }

    public function test_validates_facility_max_length(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->for($kost)->create();

        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'facilities' => [str_repeat('a', 256)], // Max 255
            ]);

        $response->assertSessionHasErrors('facilities.0');
    }

    public function test_validates_rule_max_length(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->for($kost)->create();

        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'rules' => [str_repeat('a', 256)], // Max 255
            ]);

        $response->assertSessionHasErrors('rules.0');
    }

    public function test_facilities_saved_as_json_array(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test description',
                'room_size' => '3x4 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'facilities' => ['WiFi', 'AC', 'TV'],
            ]);

        $roomType = RoomType::first();
        $this->assertIsArray($roomType->facilities);
        $this->assertCount(3, $roomType->facilities);
        $this->assertContains('WiFi', $roomType->facilities);
    }

    public function test_rules_saved_as_json_array(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test description',
                'room_size' => '3x4 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'rules' => ['No smoking', 'No pets'],
            ]);

        $roomType = RoomType::first();
        $this->assertIsArray($roomType->rules);
        $this->assertCount(2, $roomType->rules);
        $this->assertContains('No smoking', $roomType->rules);
    }

    public function test_facilities_nullable(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test description',
                'room_size' => '3x4 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'facilities' => null,
            ]);

        $roomType = RoomType::first();
        $this->assertNull($roomType->facilities);
    }

    public function test_rules_nullable(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test description',
                'room_size' => '3x4 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'rules' => null,
            ]);

        $roomType = RoomType::first();
        $this->assertNull($roomType->rules);
    }

    public function test_facilities_array_items_string_validation(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test description',
                'room_size' => '3x4 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'facilities' => [123], // Non-string item
            ]);

        $response->assertSessionHasErrors('facilities.0');
    }

    public function test_empty_array_accepted(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test description',
                'room_size' => '3x4 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'facilities' => [],
            ]);

        $roomType = RoomType::first();
        $this->assertIsArray($roomType->facilities);
        $this->assertCount(0, $roomType->facilities);
    }

    public function test_facilities_rules_retrieved_as_array(): void
    {
        $roomType = RoomType::factory()->create([
            'facilities' => ['WiFi', 'AC'],
            'rules' => ['No smoking'],
        ]);

        $retrieved = RoomType::find($roomType->id);

        $this->assertIsArray($retrieved->facilities);
        $this->assertIsArray($retrieved->rules);
        $this->assertEquals(['WiFi', 'AC'], $retrieved->facilities);
        $this->assertEquals(['No smoking'], $retrieved->rules);
    }
}
