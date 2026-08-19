<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Domain\Identity\Models\User;
use App\Domain\Identity\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Define test-only routes protected by the role middleware.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-admin', fn () => response('OK'))
            ->middleware(['auth', 'role:admin'])
            ->name('test.admin');

        Route::get('/test-multi', fn () => response('OK'))
            ->middleware(['auth', 'role:user,admin'])
            ->name('test.multi');

        Route::get('/test-verified', fn () => response('OK'))
            ->middleware(['auth', 'verified'])
            ->name('test.verified');
    }

    /**
     * The role middleware allows access for an authorized role.
     *
     * FR-008: Role-based access control via middleware.
     */
    public function test_role_middleware_allows_authorized_role(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/test-admin');

        $response->assertStatus(200);
        $response->assertSeeText('OK');
    }

    /**
     * The role middleware blocks access for an unauthorized role.
     */
    public function test_role_middleware_blocks_unauthorized_role(): void
    {
        $tenant = User::factory()->create();

        $response = $this->actingAs($tenant)->get('/test-admin');

        $response->assertStatus(403);
    }

    /**
     * The role middleware accepts multiple allowed roles.
     */
    public function test_role_middleware_accepts_multiple_roles(): void
    {
        $tenant = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($tenant)->get('/test-multi')->assertStatus(200);
        $this->actingAs($admin)->get('/test-multi')->assertStatus(200);
    }

    /**
     * The UserPolicy::viewAny() only allows superadmin.
     *
     * FR-008: Only superadmin can list/manage admin accounts.
     */
    public function test_user_policy_view_any_only_superadmin(): void
    {
        $tenant = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertFalse(Gate::forUser($tenant)->allows('viewAny', User::class));
        $this->assertFalse(Gate::forUser($admin)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('viewAny', User::class));
    }

    /**
     * A user can view their own profile (UserPolicy::view).
     */
    public function test_user_policy_view_own_profile(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('view', $user));
    }

    /**
     * A user cannot view another user's profile.
     */
    public function test_user_policy_cannot_view_other_profile(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->allows('view', $other));
    }

    /**
     * A user can update their own profile (UserPolicy::update).
     */
    public function test_user_policy_update_own_profile(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('update', $user));
    }

    /**
     * A user can delete their own account (UserPolicy::delete).
     */
    public function test_user_policy_delete_own_account(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Gate::forUser($user)->allows('delete', $user));
    }

    /**
     * The verified middleware asks unverified users to verify (modal prompt).
     *
     * FR-006: Custom verified middleware for email-verified-only features.
     */
    public function test_verified_middleware_blocks_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/test-verified');

        // Redirects back and flashes verify_email_prompt so the layout shows the modal.
        $response->assertRedirect('/')
            ->assertSessionHas('verify_email_prompt', true)
            ->assertSessionHas('error');
    }

    /**
     * The verified middleware allows verified users through.
     */
    public function test_verified_middleware_allows_verified_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/test-verified');

        $response->assertStatus(200);
        $response->assertSeeText('OK');
    }
}
