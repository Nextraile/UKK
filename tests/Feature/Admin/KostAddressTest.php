<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Address;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for Kost Address configuration.
 *
 * Tests address CRUD operations in kost edit form.
 */
class KostAddressTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Kost $kost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->kost = Kost::factory()->draft()->create(['user_id' => $this->admin->id]);
    }

    /**
     * Test admin can create address for kost.
     */
    public function test_admin_can_create_address_for_kost(): void
    {
        $addressData = [
            'name' => $this->kost->name,
            'contact_number' => $this->kost->contact_number,
            'full_address' => 'Jl. Merdeka No. 123',
            'district' => 'Coblong',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40132',
            'country' => 'Indonesia',
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ];

        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), $addressData);

        $response->assertRedirect(route('admin.kosts.show', $this->kost));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('addresses', [
            'kost_id' => $this->kost->id,
            'full_address' => 'Jl. Merdeka No. 123',
            'district' => 'Coblong',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40132',
            'country' => 'Indonesia',
            'latitude' => -6.917464,
            'longitude' => 107.619123,
        ]);
    }

    /**
     * Test admin can update existing address.
     */
    public function test_admin_can_update_existing_address(): void
    {
        // Create initial address
        $address = Address::factory()->create([
            'kost_id' => $this->kost->id,
            'full_address' => 'Old Address',
            'district' => 'Old District',
            'city' => 'Old City',
            'province' => 'Old Province',
        ]);

        $updatedData = [
            'name' => $this->kost->name,
            'contact_number' => $this->kost->contact_number,
            'full_address' => 'New Address Updated',
            'district' => 'New District',
            'city' => 'New City',
            'province' => 'New Province',
            'postal_code' => '12345',
            'country' => 'Indonesia',
        ];

        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), $updatedData);

        $response->assertRedirect(route('admin.kosts.show', $this->kost));

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'kost_id' => $this->kost->id,
            'full_address' => 'New Address Updated',
            'district' => 'New District',
            'city' => 'New City',
            'province' => 'New Province',
            'postal_code' => '12345',
        ]);
    }

    /**
     * Test latitude validation range (-90 to 90).
     */
    public function test_latitude_validation_range(): void
    {
        // Test latitude > 90
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), [
                'name' => $this->kost->name,
                'contact_number' => $this->kost->contact_number,
                'full_address' => 'Test Address',
                'district' => 'Test',
                'city' => 'Test',
                'province' => 'Test',
                'latitude' => 91.0,
            ]);

        $response->assertSessionHasErrors('latitude');

        // Test latitude < -90
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), [
                'name' => $this->kost->name,
                'contact_number' => $this->kost->contact_number,
                'full_address' => 'Test Address',
                'district' => 'Test',
                'city' => 'Test',
                'province' => 'Test',
                'latitude' => -91.0,
            ]);

        $response->assertSessionHasErrors('latitude');
    }

    /**
     * Test longitude validation range (-180 to 180).
     */
    public function test_longitude_validation_range(): void
    {
        // Test longitude > 180
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), [
                'name' => $this->kost->name,
                'contact_number' => $this->kost->contact_number,
                'full_address' => 'Test Address',
                'district' => 'Test',
                'city' => 'Test',
                'province' => 'Test',
                'longitude' => 181.0,
            ]);

        $response->assertSessionHasErrors('longitude');

        // Test longitude < -180
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), [
                'name' => $this->kost->name,
                'contact_number' => $this->kost->contact_number,
                'full_address' => 'Test Address',
                'district' => 'Test',
                'city' => 'Test',
                'province' => 'Test',
                'longitude' => -181.0,
            ]);

        $response->assertSessionHasErrors('longitude');
    }

    /**
     * Test valid latitude and longitude values are accepted.
     */
    public function test_valid_coordinates_are_accepted(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), [
                'name' => $this->kost->name,
                'contact_number' => $this->kost->contact_number,
                'full_address' => 'Test Address',
                'district' => 'Test',
                'city' => 'Test',
                'province' => 'Test',
                'latitude' => -6.917464,
                'longitude' => 107.619123,
            ]);

        $response->assertRedirect(route('admin.kosts.show', $this->kost));
        $response->assertSessionHasNoErrors();
    }

    /**
     * Test non-owner admin cannot update kost address.
     */
    public function test_non_owner_admin_cannot_update_kost_address(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        $response = $this->actingAs($otherAdmin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), [
                'name' => $this->kost->name,
                'contact_number' => $this->kost->contact_number,
                'full_address' => 'Hacker Address',
                'district' => 'Hacker District',
                'city' => 'Hacker City',
                'province' => 'Hacker Province',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test address fields are optional for draft kost.
     */
    public function test_address_fields_optional_for_draft(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), [
                'name' => 'Updated Kost Name',
                'contact_number' => $this->kost->contact_number,
                // No address fields provided
            ]);

        $response->assertRedirect(route('admin.kosts.show', $this->kost));
        $response->assertSessionHasNoErrors();

        // Kost should be updated but no address created
        $this->kost->refresh();
        $this->assertEquals('Updated Kost Name', $this->kost->name);
        $this->assertNull($this->kost->address);
    }

    /**
     * Test address without coordinates (optional).
     */
    public function test_address_without_coordinates(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), [
                'name' => $this->kost->name,
                'contact_number' => $this->kost->contact_number,
                'full_address' => 'Jl. Tanpa Koordinat',
                'district' => 'Test District',
                'city' => 'Test City',
                'province' => 'Test Province',
                // latitude and longitude omitted
            ]);

        $response->assertRedirect(route('admin.kosts.show', $this->kost));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('addresses', [
            'kost_id' => $this->kost->id,
            'full_address' => 'Jl. Tanpa Koordinat',
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    /**
     * Test tenant cannot update kost address.
     */
    public function test_tenant_cannot_update_kost_address(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($tenant)
            ->from(route('admin.kosts.edit', $this->kost))
            ->patch(route('admin.kosts.update', $this->kost), [
                'name' => $this->kost->name,
                'contact_number' => $this->kost->contact_number,
                'full_address' => 'Tenant Address',
            ]);

        $response->assertForbidden();
    }
}
