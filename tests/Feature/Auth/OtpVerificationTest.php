<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Domain\Identity\Models\OtpVerification;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Cache::flush();
    }

    /**
     * The OTP verification page is displayed for an authenticated unverified user.
     *
     * FR-004: GET /email/verify shows the OTP entry page.
     */
    public function test_otp_verification_page_is_displayed(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    /**
     * A user can verify their email with a valid OTP code.
     *
     * FR-004: Valid OTP marks email verified and redirects by role.
     */
    public function test_user_can_verify_email_with_valid_otp(): void
    {
        $user = User::factory()->unverified()->create();

        $code = $this->getOtpCode($user);

        $response = $this->actingAs($user)->post('/verify-email', [
            'otp_code' => $code,
        ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $response->assertRedirect('/rentals');
    }

    /**
     * A user cannot verify with an invalid OTP code.
     */
    public function test_user_cannot_verify_with_invalid_otp(): void
    {
        $user = User::factory()->unverified()->create();

        /** @var OtpService $otpService */
        $otpService = app(OtpService::class);
        $otpService->generate($user);

        $response = $this->actingAs($user)->post('/verify-email', [
            'otp_code' => '000000',
        ]);

        $this->assertNull($user->fresh()->email_verified_at);
        $response->assertSessionHasErrors(['otp_code']);
    }

    /**
     * A user cannot verify with an expired OTP code.
     */
    public function test_user_cannot_verify_with_expired_otp(): void
    {
        $user = User::factory()->unverified()->create();

        /** @var OtpService $otpService */
        $otpService = app(OtpService::class);
        $code = $otpService->generate($user);

        OtpVerification::where('user_id', $user->id)->update([
            'expires_at' => now()->subMinute(),
        ]);
        Cache::flush();

        $response = $this->actingAs($user)->post('/verify-email', [
            'otp_code' => $code,
        ]);

        $this->assertNull($user->fresh()->email_verified_at);
        $response->assertSessionHasErrors(['otp_code']);
    }

    /**
     * A user can request a resend of the OTP.
     *
     * FR-005: POST /email/resend generates a new OTP.
     */
    public function test_user_can_resend_otp(): void
    {
        $user = User::factory()->unverified()->create();

        /** @var OtpService $otpService */
        $otpService = app(OtpService::class);
        $otpService->generate($user);

        $response = $this->actingAs($user)->post('/email/resend');

        $response->assertSessionHas('status');
        $this->assertGreaterThanOrEqual(2, OtpVerification::where('user_id', $user->id)->count());
    }

    /**
     * Resend is throttled to one request per minute.
     *
     * FR-005: Rapid resend attempts are blocked.
     */
    public function test_resend_is_throttled(): void
    {
        $user = User::factory()->unverified()->create();

        $first = $this->actingAs($user)->post('/email/resend');
        $first->assertSessionHas('status');

        $second = $this->actingAs($user)->post('/email/resend');
        // Either the route-level throttle (429) or the controller-level
        // cache lock rejects the second request.
        $this->assertTrue(
            $second->getStatusCode() === 429 || session('error') !== null,
            'Expected the second resend attempt to be throttled.',
        );
    }

    /**
     * An already-verified user sees the already-verified state on the verify page.
     */
    public function test_already_verified_user_redirected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertSee('sudah terverifikasi');
    }

    /**
     * Post-verification redirect is role-based.
     *
     * FR-007: admin → /admin/kosts, superadmin → /superadmin/submissions.
     */
    public function test_otp_verification_redirects_by_role(): void
    {
        $admin = User::factory()->unverified()->admin()->create();
        $adminCode = $this->getOtpCode($admin);

        $adminResponse = $this->actingAs($admin)->post('/verify-email', [
            'otp_code' => $adminCode,
        ]);
        $adminResponse->assertRedirect('/admin/kosts');

        $superAdmin = User::factory()->unverified()->superAdmin()->create();
        $superCode = $this->getOtpCode($superAdmin);

        $superResponse = $this->actingAs($superAdmin)->post('/verify-email', [
            'otp_code' => $superCode,
        ]);
        $superResponse->assertRedirect('/superadmin/submissions');
    }

    /**
     * Retrieve the most recent OTP code for a user.
     *
     * The OTP code is stored as a SHA-256 hash in the database, so the
     * plaintext code is retrieved from the return value of generate().
     */
    private function getOtpCode(User $user): string
    {
        /** @var OtpService $otpService */
        $otpService = app(OtpService::class);

        return $otpService->generate($user);
    }
}
