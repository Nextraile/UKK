<?php

use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
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

require __DIR__.'/auth.php';
