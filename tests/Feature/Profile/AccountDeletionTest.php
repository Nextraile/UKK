<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A user can soft-delete their own account after confirming their password.
     *
     * FR-012: Account deletion is a soft delete.
     */
    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', ['password' => 'password']);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    /**
     * A soft-deleted user cannot log back in.
     *
     * FR-013: Inactive (soft-deleted) users are rejected at login.
     */
    public function test_deleted_user_cannot_login(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * The ActiveUser middleware force-logs-out a user whose account was
     * soft-deleted mid-session (e.g. by an admin).
     *
     * FR-013: Active middleware checks trashed() on every request.
     */
    public function test_deleted_user_is_logged_out_by_active_middleware(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        // Soft delete the user directly (simulating admin action).
        $user->delete();

        $response = $this->get('/profile');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /**
     * Deleting an account requires the password field.
     */
    public function test_user_cannot_delete_account_without_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile/edit')
            ->delete('/profile', []);

        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull($user->fresh());
    }

    /**
     * Deleting an account with the wrong password is rejected.
     */
    public function test_user_cannot_delete_account_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile/edit')
            ->delete('/profile', ['password' => 'wrong-password']);

        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull($user->fresh());
    }
}
