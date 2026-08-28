<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\AdminAccountRequest;
use App\Mail\AdminAccountCreated;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminManagementController extends Controller
{
    /**
     * Display a listing of admin accounts.
     */
    public function index(Request $request): View
    {
        $showDeleted = $request->boolean('show_deleted');

        $query = User::where('role', 'admin')
            ->orderBy('created_at', 'desc');

        if ($showDeleted) {
            $query->onlyTrashed();
        }

        $admins = $query->paginate(20);

        return view('super-admin.admins.index', compact('admins', 'showDeleted'));
    }

    /**
     * Show the form for creating a new admin account.
     */
    public function create(): View
    {
        return view('super-admin.admins.create');
    }

    /**
     * Store a newly created admin account in storage.
     */
    public function store(AdminAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $plainPassword = $validated['password'];

        $admin = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($plainPassword),
            'role' => 'admin',
            'email_verified_at' => null, // Admin must verify via OTP on first login
        ]);

        // Send welcome email with credentials (synchronous)
        Mail::to($admin->email)->send(new AdminAccountCreated($admin, $plainPassword));

        return redirect()
            ->route('super-admin.admins.index')
            ->with('success', 'Akun admin berhasil dibuat dan email kredensial telah dikirim.');
    }

    /**
     * Show the form for editing the specified admin account.
     */
    public function edit(User $admin): View
    {
        abort_if($admin->role !== 'admin', 404);

        return view('super-admin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin account in storage.
     */
    public function update(AdminAccountRequest $request, User $admin): RedirectResponse
    {
        abort_if($admin->role !== 'admin', 404);

        $validated = $request->validated();

        $admin->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
        ]);

        return redirect()
            ->route('super-admin.admins.index')
            ->with('success', 'Data admin berhasil diperbarui.');
    }

    /**
     * Remove the specified admin account from storage (soft delete).
     */
    public function destroy(User $admin): RedirectResponse
    {
        // Prevent self-deletion (check first before role check)
        if ($admin->id === auth()->id()) {
            return redirect()
                ->route('super-admin.admins.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        abort_if($admin->role !== 'admin', 404);

        $admin->delete();

        return redirect()
            ->route('super-admin.admins.index')
            ->with('success', 'Akun admin berhasil dihapus.');
    }
}
