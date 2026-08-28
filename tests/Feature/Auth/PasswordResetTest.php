<?php

namespace Tests\Feature\Auth;

use App\Domain\Identity\Mail\OtpVerificationMail;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_otp_email_can_be_requested(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect(route('password.otp'));

        Mail::assertQueued(OtpVerificationMail::class, function ($mail) use ($user): bool {
            return $mail->user->is($user)
                && $mail->purpose === 'password-reset'
                && $mail->hasSubject('[SewaKost] Kode Reset Password Anda');
        });
    }

    public function test_reset_otp_screen_can_be_rendered(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $this->get('/reset-password')
            ->assertStatus(200);
    }

    public function test_unknown_email_does_not_send_otp_but_still_renders_otp_screen(): void
    {
        Mail::fake();

        $this->post('/forgot-password', ['email' => 'nonexistent@example.com'])
            ->assertRedirect(route('password.otp'));

        Mail::assertNothingSent();

        $this->get('/reset-password')
            ->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $this->assertNotNull($code = Cache::get('otp:'.$user->id));

        $this->post('/reset-password/verify', ['otp_code' => $code])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('password.reset'));

        $this->get('/reset-password/change')
            ->assertStatus(200);

        $this->post('/reset-password/change', [
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));

        // Reset must not mark the email as verified.
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_change_screen_requires_prior_otp_verification(): void
    {
        $this->get('/reset-password/change')
            ->assertRedirect(route('password.request'));
    }

    public function test_password_cannot_be_reset_with_invalid_otp(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create(['password' => Hash::make('oldpassword123')]);

        $this->post('/forgot-password', ['email' => $user->email]);

        $this->post('/reset-password/verify', ['otp_code' => '000000'])
            ->assertSessionHasErrors('otp_code');

        // Verification flag must remain unset.
        $this->get('/reset-password/change')
            ->assertRedirect(route('password.request'));

        $this->assertTrue(Hash::check('oldpassword123', $user->fresh()->password));
    }

    public function test_change_requires_email_matching_the_verified_session(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $code = Cache::get('otp:'.$user->id);
        $this->post('/reset-password/verify', ['otp_code' => $code]);

        $before = $user->fresh()->password;

        $this->post('/reset-password/change', [
            'email' => 'other@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors('email');

        $this->assertSame($before, $user->fresh()->password);
    }
}
