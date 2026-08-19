<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\OtpService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Handles OTP-based email verification, replacing Breeze's link-based
 * verification flow.
 *
 * Exposes three actions:
 *  - {@see show()} — display the OTP entry page (PAGE-006)
 *  - {@see verify()} — validate a submitted 6-digit code (FR-004)
 *  - {@see resend()} — generate and dispatch a fresh OTP (FR-005),
 *    throttled to one request per minute.
 */
class EmailVerificationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly OtpService $otpService,
    ) {}

    /**
     * Show the OTP verification page.
     *
     * OTP is generated lazily (FR-003/FR-004 on-demand): the verification
     * email is sent the first time this page is opened, and again once the
     * previous OTP has expired — NOT at registration. The `alreadyVerified`
     * branch above returns early, so no OTP is ever generated for verified
     * users.
     *
     * When the user's email is already verified, the page renders an
     * "already verified" state instead of redirecting, so the user can
     * follow a link to their role-based dashboard.
     *
     * @param  Request  $request  The incoming HTTP request.
     */
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return view('auth.verify-email', [
                'alreadyVerified' => true,
                'maskedEmail' => $user->maskedEmail(),
                'expiresAt' => null,
            ]);
        }

        // Lazy generation: send the OTP email only when the page is opened
        // and no valid OTP exists yet (first visit or after expiry).
        if (! $this->otpService->hasValidOtp($user)) {
            $this->otpService->generate($user);
        }

        /** @var Carbon|null $expiresAt */
        $expiresAt = $this->otpService->getOtpExpiry($user);

        return view('auth.verify-email', [
            'alreadyVerified' => false,
            'maskedEmail' => $user->maskedEmail(),
            'expiresAt' => $expiresAt,
        ]);
    }

    /**
     * Verify the OTP code submitted by the user.
     *
     * On success the user's `email_verified_at` is set and they are
     * redirected to their role-based dashboard. On failure the user is
     * returned to the form with an error and the old input.
     *
     * @param  OtpVerificationRequest  $request  The validated request.
     */
    public function verify(OtpVerificationRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($this->otpService->isLockedOut($user)) {
            return back()
                ->withErrors(['otp_code' => 'Terlalu banyak percobaan. Coba lagi dalam 15 menit.'])
                ->withInput();
        }

        $code = $request->string('otp_code')->toString();

        if ($this->otpService->verify($user, $code)) {
            return redirect()
                ->intended($user->dashboardRoute())
                ->with('status', 'Email berhasil diverifikasi!');
        }

        return back()
            ->withErrors(['otp_code' => 'Kode OTP tidak valid atau sudah expired.'])
            ->withInput();
    }

    /**
     * Resend the OTP code to the authenticated user.
     *
     * Throttled to one request per minute via a cache lock keyed on the
     * user id. Requests received within the throttle window are rejected
     * with a friendly error message.
     *
     * @param  Request  $request  The incoming HTTP request.
     */
    public function resend(Request $request): RedirectResponse
    {
        $request->validateWithBag('resend', [
            'email' => ['sometimes', 'email'],
        ]);

        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($user->dashboardRoute());
        }

        // Throttle: 1 resend per minute.
        $key = 'otp.resend.'.$user->id;
        if (cache()->has($key)) {
            return back()
                ->with('error', 'Anda dapat meminta OTP baru dalam beberapa saat lagi.');
        }

        $this->otpService->resend($user);
        cache()->put($key, true, 60); // 60 seconds throttle.

        return back()->with('status', 'OTP baru telah dikirim ke email Anda.');
    }
}
