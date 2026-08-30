<?php

use App\Domain\Rental\Models\Payment;
use App\Http\Controllers\Admin\DocumentRequirementController;
use App\Http\Controllers\Admin\DocumentVerificationController;
use App\Http\Controllers\Admin\KostController;
use App\Http\Controllers\Admin\KostImageController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\PriceSchemeController;
use App\Http\Controllers\Admin\RentalManagementController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Admin\RoomTypeImageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KostDetailController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\AdminManagementController;
use App\Http\Controllers\SuperAdmin\CategoryController;
use App\Http\Controllers\SuperAdmin\KostSubmissionController;
use App\Http\Controllers\Tenant\PaymentController;
use App\Http\Controllers\Tenant\RentalController;
use App\Http\Controllers\Tenant\ReviewController;
use Illuminate\Support\Facades\Route;

// Route model bindings with eager loading (VULN-002 fix)
Route::bind('payment', function ($value) {
    return Payment::with('rental.room.roomType.kost')->findOrFail($value);
});

Route::get('/', [HomeController::class, 'index'])->name('home');

// Public marketplace — no auth required (users can browse unverified, FR-006).
Route::get('/marketplace', [MarketplaceController::class, 'index'])
    ->name('marketplace.index');

Route::get('/marketplace/kosts/{kost:slug}', [KostDetailController::class, 'show'])
    ->name('marketplace.show');

// Global rate limiting for all authenticated routes (60 req/min per IP)
// Auth routes in auth.php have stricter limits (5/min, 1/min) for security-sensitive operations
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // COMP-006: Rental Lifecycle Management
    // Note: The 'verified' middleware (custom EnsureEmailIsVerified) should be attached
    // ONLY to routes that require email verification per FR-006 (e.g., rental creation).
    // It should NOT be applied globally — users can browse marketplace and login without verification.
    Route::middleware(['verified', 'role:user'])->group(function () {
        Route::get('/rentals', [RentalController::class, 'index'])->name('rentals.index');
        Route::get('/rentals/create', [RentalController::class, 'create'])->name('rentals.create');
        Route::post('/rentals', [RentalController::class, 'store'])->name('rentals.store');
        Route::get('/rentals/{rental}', [RentalController::class, 'show'])->name('rentals.show');

        // Payment upload (TASK-051)
        Route::get('/rentals/{rental}/payment', [PaymentController::class, 'show'])->name('rentals.payment.show');
        Route::post('/rentals/{rental}/payment/upload', [PaymentController::class, 'uploadProof'])->name('rentals.payment.upload');

        // Payment proof and QRIS download with authorization (VULN-003 fix)
        Route::get('/rentals/{rental}/payment/proof', [PaymentController::class, 'downloadProof'])
            ->name('rentals.payment.proof');
        Route::get('/rentals/{rental}/payment/qris', [PaymentController::class, 'downloadQris'])
            ->name('rentals.payment.qris');

        // Document upload (TASK-053)
        Route::post('/rentals/{rental}/documents', [RentalController::class, 'uploadDocument'])
            ->name('rentals.documents.upload');

        // Document download with authorization (VULN-001 fix)
        Route::get('/rentals/documents/{document}/download', [RentalController::class, 'downloadDocument'])
            ->name('rentals.documents.download');

        // Rental cancellation (TASK-054)
        Route::get('/rentals/{rental}/cancel', [RentalController::class, 'cancelForm'])
            ->name('rentals.cancel.form');
        Route::post('/rentals/{rental}/cancel', [RentalController::class, 'cancel'])
            ->name('rentals.cancel');

        // Review Management (COMP-008)
        Route::get('/rentals/{rental}/reviews/create', [ReviewController::class, 'create'])
            ->name('rentals.reviews.create');
        Route::post('/rentals/{rental}/reviews', [ReviewController::class, 'store'])
            ->name('rentals.reviews.store');
        Route::get('/rentals/{rental}/reviews/edit', [ReviewController::class, 'edit'])
            ->name('rentals.reviews.edit');
        Route::patch('/rentals/{rental}/reviews', [ReviewController::class, 'update'])
            ->name('rentals.reviews.update');
        Route::delete('/rentals/{rental}/reviews', [ReviewController::class, 'destroy'])
            ->name('rentals.reviews.destroy');
    });

    // Admin Kost Management (COMP-002: Kost Publication)
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('kosts', KostController::class);

        // State transition routes
        Route::post('kosts/{kost}/submit', [KostController::class, 'submit'])
            ->name('kosts.submit');
        Route::delete('kosts/{kost}/cancel', [KostController::class, 'cancel'])
            ->name('kosts.cancel');
        Route::post('kosts/{kost}/publish', [KostController::class, 'publish'])
            ->name('kosts.publish');

        // Kost Image Management (COMP-003: Kost Configuration)
        Route::post('kosts/{kost}/images', [KostImageController::class, 'store'])
            ->name('kosts.images.store');
        Route::delete('kosts/{kost}/images/{image}', [KostImageController::class, 'destroy'])
            ->name('kosts.images.destroy');
        Route::patch('kosts/{kost}/images/{image}/thumbnail', [KostImageController::class, 'setThumbnail'])
            ->name('kosts.images.set-thumbnail');
        Route::patch('kosts/{kost}/images/sort-order', [KostImageController::class, 'updateSortOrder'])
            ->name('kosts.images.sort-order');
        Route::get('kosts/{kost}/images', [KostImageController::class, 'index'])
            ->name('kosts.images.index');

        // Category assignment (COMP-003: Kost Configuration)
        Route::get('kosts/{kost}/categories', [KostController::class, 'editCategories'])
            ->name('kosts.categories.edit');
        Route::patch('kosts/{kost}/categories', [KostController::class, 'updateCategories'])
            ->name('kosts.categories.update');

        // Payment information (QRIS + bank account)
        Route::get('kosts/{kost}/payment', [KostController::class, 'editPayment'])
            ->name('kosts.payment.edit');
        Route::patch('kosts/{kost}/payment', [KostController::class, 'updatePayment'])
            ->name('kosts.payment.update');

        // Document Requirements (COMP-003: Kost Configuration)
        Route::prefix('kosts/{kost}')->group(function () {
            Route::get('document-requirements', [DocumentRequirementController::class, 'index'])
                ->name('kosts.document-requirements.index');
            Route::post('document-requirements', [DocumentRequirementController::class, 'store'])
                ->name('kosts.document-requirements.store');
            Route::patch('document-requirements/{requirement}', [DocumentRequirementController::class, 'update'])
                ->name('kosts.document-requirements.update');
            Route::delete('document-requirements/{requirement}', [DocumentRequirementController::class, 'destroy'])
                ->name('kosts.document-requirements.destroy');
        });

        // Room Type Management (COMP-004: Room Inventory)
        Route::resource('kosts/{kost}/room-types', RoomTypeController::class)
            ->names('room-types');

        // Room Type Image Deletion (manual cleanup only)
        Route::delete('room-type-images/{roomTypeImage}', [RoomTypeImageController::class, 'destroy'])
            ->name('room-type-images.destroy');

        // Price Scheme Management (COMP-004: Room Inventory)
        Route::get('room-types/{roomType}/price-schemes', [PriceSchemeController::class, 'index'])
            ->name('price-schemes.index');
        Route::post('room-types/{roomType}/price-schemes', [PriceSchemeController::class, 'store'])
            ->name('price-schemes.store');
        Route::put('room-types/{roomType}/price-schemes/{priceScheme}', [PriceSchemeController::class, 'update'])
            ->name('price-schemes.update');
        Route::delete('room-types/{roomType}/price-schemes/{priceScheme}', [PriceSchemeController::class, 'destroy'])
            ->name('price-schemes.destroy');
        Route::patch('room-types/{roomType}/price-schemes/{priceScheme}/toggle-active', [PriceSchemeController::class, 'toggleActive'])
            ->name('price-schemes.toggle-active');

        // Room Management (COMP-004: Room Inventory)
        Route::get('kosts/{kost}/rooms', [RoomController::class, 'index'])
            ->name('rooms.index');
        Route::post('kosts/{kost}/rooms', [RoomController::class, 'store'])
            ->name('rooms.store');
        Route::put('kosts/{kost}/rooms/{room}', [RoomController::class, 'update'])
            ->name('rooms.update');
        Route::delete('kosts/{kost}/rooms/{room}', [RoomController::class, 'destroy'])
            ->name('rooms.destroy');
        Route::patch('kosts/{kost}/rooms/{room}/status', [RoomController::class, 'setStatus'])
            ->name('rooms.set-status');

        // Rental Management (COMP-006: Rental Lifecycle)
        Route::get('rentals', [RentalManagementController::class, 'index'])
            ->name('rentals.index');
        Route::get('rentals/{rental}', [RentalManagementController::class, 'show'])
            ->name('rentals.show');

        // Payment Verification (TASK-051)
        Route::post('payments/{payment}/approve', [PaymentVerificationController::class, 'approve'])
            ->name('payments.approve');
        Route::post('payments/{payment}/reject', [PaymentVerificationController::class, 'reject'])
            ->name('payments.reject');

        // Document Verification (TASK-053)
        Route::post('documents/{document}/approve', [DocumentVerificationController::class, 'approve'])
            ->name('documents.approve');
        Route::post('documents/{document}/reject', [DocumentVerificationController::class, 'reject'])
            ->name('documents.reject');

        // Document Viewing (FR-087: Admin view submitted documents)
        Route::get('rentals/documents/{document}', [RentalManagementController::class, 'viewDocument'])
            ->name('rentals.documents.show');
    });

    // Super Admin - Kost Submissions Review (COMP-002: Kost Publication)
    Route::middleware('role:superadmin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/kost-submissions', [KostSubmissionController::class, 'index'])
            ->name('kost-submissions.index');
        Route::get('/kost-submissions/{submission}', [KostSubmissionController::class, 'show'])
            ->name('kost-submissions.show');
        Route::post('/kost-submissions/{submission}/approve', [KostSubmissionController::class, 'approve'])
            ->name('kost-submissions.approve');
        Route::post('/kost-submissions/{submission}/reject', [KostSubmissionController::class, 'reject'])
            ->name('kost-submissions.reject');

        // Category Management (COMP-003: Kost Configuration)
        Route::resource('categories', CategoryController::class);

        // Admin Account Management (COMP-009, FR-111—FR-116)
        Route::resource('admins', AdminManagementController::class)
            ->except(['show']);
    });
});

require __DIR__.'/auth.php';
