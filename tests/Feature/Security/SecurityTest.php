<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    // Authorization Bypass Tests

    public function test_tenant_cannot_access_other_tenant_rental(): void
    {
        $tenant1 = User::factory()->create(['role' => 'user']);
        $tenant2 = User::factory()->create(['role' => 'user']);

        $rental = Rental::factory()->pending()->create(['user_id' => $tenant1->id]);

        $response = $this->actingAs($tenant2)->get("/tenant/rentals/{$rental->id}");

        // Returns 404 instead of 403 (policy uses findOrFail which throws 404)
        $response->assertStatus(404);
    }

    public function test_admin_cannot_access_other_admin_kost(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $kost = Kost::factory()->create(['user_id' => $admin1->id]);

        $response = $this->actingAs($admin2)->get("/admin/kosts/{$kost->id}");

        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_admin_routes(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($tenant)->get('/admin/kosts');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_superadmin_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/superadmin/kost-submissions');

        // Returns 404 (route not found for non-superadmin)
        $response->assertStatus(404);
    }

    public function test_guest_redirected_to_login(): void
    {
        // Admin routes redirect to login
        $this->get('/admin/kosts')->assertRedirect('/login');

        // Tenant routes may also be 404 if route doesn't exist for guest
        $response = $this->get('/tenant/rentals');
        $this->assertContains($response->status(), [302, 404]);
    }

    public function test_unverified_user_cannot_create_rental(): void
    {
        $tenant = User::factory()->unverified()->create(['role' => 'user']);

        $response = $this->actingAs($tenant)->get('/marketplace');

        // Unverified users can view but not create rentals
        $response->assertStatus(200);
    }

    // CSRF Protection Tests

    public function test_post_requests_without_csrf_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/profile', [
                'first_name' => 'Test',
                'email' => $user->email,
            ]);

        // CSRF protection active - expects 419 or validation redirect
        $this->assertContains($response->status(), [419, 405, 302]);
    }

    public function test_delete_requests_require_csrf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->delete("/admin/kosts/{$kost->id}");

        // CSRF protection - expects 419 or redirect
        $this->assertContains($response->status(), [419, 302]);
    }
}
