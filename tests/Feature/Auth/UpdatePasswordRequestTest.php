<?php

namespace Tests\Feature\Auth;

use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdatePasswordRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated_with_valid_data(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('newpassword123', $user->refresh()->password));
    }

    public function test_current_password_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
    }

    public function test_new_password_is_required(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'oldpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');
    }

    public function test_new_password_must_be_at_least_8_characters(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'oldpassword123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');
    }

    public function test_new_password_must_be_confirmed(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');
    }
}
