<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('marketplace.index'));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Test', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame('user', $user->role);
        $this->assertNull($user->email_verified_at);

        // FR-003 on-demand: registration must NOT send an OTP email.
        Mail::assertNothingSent();
    }

    public function test_marketplace_stub_can_be_rendered(): void
    {
        $response = $this->get('/marketplace');

        $response->assertStatus(200);
    }

    public function test_registration_with_existing_email_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'taken@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => true,
        ]);

        $response->assertSessionHasErrors(['email' => 'Email tidak dapat digunakan.']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_with_deleted_account_email_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'deleted@example.com']);
        $user->delete();

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'deleted@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => true,
        ]);

        $response->assertSessionHasErrors(['email' => 'Email tidak dapat digunakan.']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_requires_first_name(): void
    {
        $response = $this->post('/register', [
            'first_name' => '',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => true,
        ]);

        $response->assertSessionHasErrors(['first_name']);
        $this->assertGuest();
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => false,
        ]);

        $response->assertSessionHasErrors(['terms']);
        $this->assertGuest();
    }
}
