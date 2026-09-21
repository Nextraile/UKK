<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Identity\Models\User;
use App\Domain\Identity\Services\OtpService;
use App\Domain\Shared\Services\SecureFileUploadService;
use App\Http\Requests\AvatarUploadRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
    ) {}

    /**
     * Display the user's profile. (FR-009)
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the profile edit form. (FR-009, FR-010)
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information. (FR-010, FR-129)
     *
     * If email is changed, nullify email_verified_at and trigger OTP re-verification.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            // FR-129: Email change requires re-verification
            $user->email_verified_at = null;

            // Save first so the new email is persisted
            $user->save();

            // Generate and send OTP to the new email
            $this->otpService->generate($user);

            return Redirect::route('verification.notice')
                ->with('status', 'Email berhasil diubah. Silakan verifikasi email baru Anda.');
        }

        $user->save();

        return Redirect::route('profile.edit')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * Upload and update user avatar. (FR-011)
     */
    public function updateAvatar(AvatarUploadRequest $request): RedirectResponse
    {
        $service = app(SecureFileUploadService::class);

        DB::transaction(function () use ($request, $service) {
            $user = $request->user();

            // Lock user row to prevent concurrent updates
            $user = User::lockForUpdate()->findOrFail($user->id);

            // Delete old avatar if exists
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Store new avatar with UUID filename
            $path = $service->store($request->file('avatar'), 'avatars', 'public');

            $user->update(['avatar_path' => $path]);
        });

        return Redirect::route('profile.edit')
            ->with('status', 'Avatar berhasil diperbarui.');
    }

    /**
     * Delete the user's account (soft delete). (FR-012)
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Soft delete — sets deleted_at (User model uses SoftDeletes trait)
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')
            ->with('status', 'Akun Anda berhasil dihapus.');
    }
}
