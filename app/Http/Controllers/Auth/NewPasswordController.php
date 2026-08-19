<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Handles the final step of the OTP-based password reset flow (Flow A):
 * choosing a new password once the OTP has been verified.
 *
 * Unlike Breeze's token-based flow there is no reset token; access is
 * gated by the session flags `password_reset_email` and
 * `password_reset_verified` set by PasswordResetLinkController.
 */
class NewPasswordController extends Controller
{
    /**
     * Display the new-password form.
     *
     * Only reachable after a successful OTP verification; any other access
     * is redirected back to the forgot-password request page.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return View|RedirectResponse The change-password form, or a redirect when unauthenticated.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_email')
            || ! $request->session()->get('password_reset_verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.change-password', ['request' => $request]);
    }

    /**
     * Update the user's password after successful OTP verification.
     *
     * The submitted email must match the email stored in the session during
     * the forgot-password step; this binds the password change to the user
     * who actually requested the reset.
     *
     * @param  ResetPasswordRequest  $request  The validated request.
     * @return RedirectResponse Toward the login page on success.
     *
     * @throws ValidationException When the submitted email does not match the session email.
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $emailSession = $request->session()->get('password_reset_email');

        if (! is_string($emailSession) || $request->email !== $emailSession) {
            throw ValidationException::withMessages([
                'email' => 'Email tidak sesuai dengan sesi reset password.',
            ]);
        }

        $user = User::where('email', $emailSession)->first();

        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'email' => 'Email tidak sesuai dengan sesi reset password.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $request->session()->forget(['password_reset_email', 'password_reset_verified']);

        return redirect()
            ->route('login')
            ->with('status', 'Password berhasil diubah. Silakan login.');
    }
}
