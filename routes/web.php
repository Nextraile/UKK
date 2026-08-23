<?php

use App\Http\Controllers\Admin\KostController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\KostSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public marketplace — no auth required (users can browse unverified, FR-006).
Route::get('/marketplace', [MarketplaceController::class, 'index'])
    ->name('marketplace.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Note: The 'verified' middleware (custom EnsureEmailIsVerified) should be attached
// ONLY to routes that require email verification per FR-006 (e.g., rental creation).
// It should NOT be applied globally — users can browse marketplace and login without verification.
// Example (when rental routes are implemented in COMP-006):
// Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
//     Route::get('/rentals/create', ...);
//     Route::post('/rentals', ...);
// });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Kost Management (COMP-002: Kost Publication)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('kosts', KostController::class);

    // State transition routes
    Route::post('kosts/{kost}/submit', [KostController::class, 'submit'])
        ->name('kosts.submit');
    Route::delete('kosts/{kost}/cancel', [KostController::class, 'cancel'])
        ->name('kosts.cancel');
    Route::post('kosts/{kost}/publish', [KostController::class, 'publish'])
        ->name('kosts.publish');
});

// Super Admin - Kost Submissions Review (COMP-002: Kost Publication)
Route::middleware(['auth', 'role:superadmin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/kost-submissions', [KostSubmissionController::class, 'index'])
        ->name('kost-submissions.index');
    Route::get('/kost-submissions/{submission}', [KostSubmissionController::class, 'show'])
        ->name('kost-submissions.show');
    Route::post('/kost-submissions/{submission}/approve', [KostSubmissionController::class, 'approve'])
        ->name('kost-submissions.approve');
    Route::post('/kost-submissions/{submission}/reject', [KostSubmissionController::class, 'reject'])
        ->name('kost-submissions.reject');
});

require __DIR__.'/auth.php';
