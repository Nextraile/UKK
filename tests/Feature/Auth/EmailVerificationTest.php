<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Domain\Identity\Mail\OtpVerificationMail;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_visiting_verification_screen_sends_otp_lazily(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();
        $otpService = app(OtpService::class);

        $this->assertFalse($otpService->hasValidOtp($user));

        $this->actingAs($user)->get('/verify-email');

        // FR-004 on-demand: no OTP at registration, generated when the page is visited.
        $this->assertTrue($otpService->hasValidOtp($user));
        Mail::assertSent(OtpVerificationMail::class, function ($mail) use ($user): bool {
            return $mail->user->is($user);
        });
    }

    public function test_email_can_be_verified_with_valid_otp(): void
    {
        $user = User::factory()->unverified()->create();

        /** @var OtpService $otpService */
        $otpService = app(OtpService::class);
        $code = $otpService->generate($user);

        $response = $this->actingAs($user)->post('/verify-email', [
            'otp_code' => $code,
        ]);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect();
    }

    public function test_email_cannot_be_verified_with_invalid_otp(): void
    {
        $user = User::factory()->unverified()->create();

        /** @var OtpService $otpService */
        $otpService = app(OtpService::class);
        $otpService->generate($user);

        $response = $this->actingAs($user)->post('/verify-email', [
            'otp_code' => '000000',
        ]);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $response->assertSessionHasErrors(['otp_code']);
    }

    public function test_verified_user_sees_already_verified_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertSee('sudah terverifikasi');
    }
}
