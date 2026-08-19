<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegistrationRequest $request): RedirectResponse
    {
        $user = User::create([
            'first_name' => $request->string('first_name')->toString(),
            'last_name' => $request->string('last_name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'email_verified_at' => null,
        ]);

        // Set role explicitly (not via mass-assignment).
        $user->role = 'user';
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        // NOTE: No OTP is sent here (FR-003/FR-004 on-demand). The OTP is
        // generated lazily the first time the user opens the verification
        // page (EmailVerificationController::show). New users stay
        // unverified but can browse the marketplace freely.
        return redirect()
            ->route('marketplace.index')
            ->with('status', 'Akun Anda berhasil dibuat. Selamat datang di SewaKost!');
    }
}
