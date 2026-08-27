<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_view_own_rentals_list(): void
    {
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        // Create rentals for this tenant
        $rental1 = Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'active']);
        $rental2 = Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'pending']);

        // Create rental for another tenant (should not appear)
        $otherRental = Rental::factory()->create(['status' => 'active']);

        $response = $this->actingAs($tenant)->get(route('rentals.index'));

        $response->assertOk();
        $response->assertViewIs('tenant.rentals.index');
        $response->assertViewHas('rentals');
        $response->assertViewHas('stats');
        $response->assertSee($rental1->room->roomType->kost->name);
        $response->assertSee($rental2->room->roomType->kost->name);
        $response->assertDontSee($otherRental->room->roomType->kost->name);
    }

    public function test_tenant_can_view_own_rental_detail(): void
    {
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        $rental = Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'active']);

        $response = $this->actingAs($tenant)->get(route('rentals.show', $rental));

        $response->assertOk();
        $response->assertViewIs('tenant.rentals.show');
        $response->assertViewHas('rental');
        $response->assertSee($rental->room->roomType->kost->name);
        $response->assertSee($rental->room->name);
        $response->assertSee('Riwayat Status');
    }

    public function test_tenant_cannot_view_other_rental(): void
    {
        $tenant1 = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $tenant2 = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        $rental = Rental::factory()->create(['user_id' => $tenant2->id]);

        $response = $this->actingAs($tenant1)->get(route('rentals.show', $rental));

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('rentals.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_rental_list_shows_correct_stats(): void
    {
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        // Create rentals with different statuses
        Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'active']);
        Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'active']);
        Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'pending']);
        Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'completed']);

        $response = $this->actingAs($tenant)->get(route('rentals.index'));

        $response->assertOk();
        $stats = $response->viewData('stats');

        $this->assertEquals(2, $stats['active']);
        $this->assertEquals(1, $stats['pending_actions']);
        $this->assertEquals(1, $stats['completed']);
    }

    public function test_rental_detail_shows_status_history(): void
    {
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        $rental = Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'paid']);

        $response = $this->actingAs($tenant)->get(route('rentals.show', $rental));

        $response->assertOk();
        $response->assertSee('Riwayat Status');
        // Should have at least the initial 'pending' status from factory (capitalized in view)
        $response->assertSee('Pending');
    }

    public function test_admin_cannot_access_tenant_rental_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get(route('rentals.index'));

        $response->assertForbidden();
    }
}
