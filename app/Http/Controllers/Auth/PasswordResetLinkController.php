<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\OtpService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Handles the first two steps of the OTP-based password reset flow
 * (Flow A): requesting a reset code and verifying it.
 *
 * All actions run in the guest context — the flow is driven entirely by
 * session state (`password_reset_email`, `password_reset_verified`), never
 * by an authenticated user. Responses are deliberately generic whether or
 * not the email exists, to prevent user enumeration.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly OtpService $otpService,
    ) {}

    /**
     * Display the password reset link request view.
     *
     * @return View The forgot-password form.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming forgot-password request.
     *
     * Generates a password-reset OTP and emails it when the address exists
     * (soft-deleted users are excluded), storing the target email in the
     * session for the next steps. The redirect is identical whether or not
     * the user was found, preventing email enumeration.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return RedirectResponse Always towards the OTP entry page.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user instanceof User) {
            $this->otpService->generate($user, 'password-reset');
            $request->session()->put('password_reset_email', $user->email);
        }

        return redirect()->route('password.otp');
    }

    /**
     * Display the OTP entry page for the password reset flow.
     *
     * When no reset is in progress the page renders in an "email unknown"
     * state; otherwise it shows the masked email and OTP expiry for context.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return View The reset-password OTP form.
     */
    public function otp(Request $request): View
    {
        $email = $request->session()->get('password_reset_email');

        if (! is_string($email) || $email === '') {
            return $this->emailUnknown();
        }

        $user = User::where('email', $email)->first();

        if (! $user instanceof User) {
            return $this->emailUnknown();
        }

        return view('auth.reset-password', [
            'emailUnknown' => false,
            'maskedEmail' => $user->maskedEmail(),
            'expiresAt' => $this->otpService->getOtpExpiry($user),
        ]);
    }

    /**
     * Verify the OTP code submitted during password reset.
     *
     * On success the session is flagged as verified and the user proceeds
     * to the new-password step; the email stays unverified in the database
     * (`markEmailVerified = false`). Failures return a generic error.
     *
     * @param  OtpVerificationRequest  $request  The validated request.
     * @return RedirectResponse Towards the new-password form, or back on failure.
     */
    public function verifyOtp(OtpVerificationRequest $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');

        if (! is_string($email) || $email === '') {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak valid atau sudah expired.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user instanceof User) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak valid atau sudah expired.']);
        }

        if ($this->otpService->isLockedOut($user)) {
            return back()->withErrors(['otp_code' => 'Terlalu banyak percobaan. Coba lagi dalam 15 menit.']);
        }

        $code = $request->string('otp_code')->toString();

        if ($this->otpService->verify($user, $code, false)) {
            $request->session()->put('password_reset_verified', true);

            return redirect()
                ->route('password.reset')
                ->with('status', 'Kode OTP valid. Silakan buat password baru.');
        }

        return back()->withErrors(['otp_code' => 'Kode OTP tidak valid atau sudah expired.']);
    }

    /**
     * Render the OTP form in the "email unknown" state.
     *
     * No email is revealed and no expiry is shown, keeping the response
     * identical to the case where the email exists.
     *
     * @return View The reset-password OTP form.
     */
    private function emailUnknown(): View
    {
        return view('auth.reset-password', [
            'emailUnknown' => true,
            'maskedEmail' => null,
            'expiresAt' => null,
        ]);
    }
}
