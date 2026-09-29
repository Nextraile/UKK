<?php

namespace Tests\Feature\Auth;

use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConfirmPasswordRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_confirmed_with_valid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', []);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_be_correct(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
