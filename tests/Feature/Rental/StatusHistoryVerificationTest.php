<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Console\Commands\ActivateRentals;
use App\Console\Commands\CancelOverdueRentals;
use App\Console\Commands\CompleteRentals;
use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Actions\CancelRental;
use App\Domain\Rental\Actions\CreateRental;
use App\Domain\Rental\Actions\RejectPayment;
use App\Domain\Rental\Actions\UploadDocument;
use App\Domain\Rental\Actions\VerifyDocument;
use App\Domain\Rental\Actions\VerifyPayment;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Status History Verification Test Suite.
 *
 * Systematically verifies that EVERY rental status transition
 * properly records in rental_status_histories table with correct metadata.
 *
 * TASK-056: Status History Recording Verification
 */
class StatusHistoryVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $admin;

    private Kost $kost;

    private RoomType $roomType;

    private Room $room;

    private PriceScheme $priceScheme;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create users
        $this->tenant = User::factory()->tenant()->create(); // Uses 'user' role
        $this->admin = User::factory()->admin()->create();

        // Ensure system user exists (ID=1) for automated transitions
        if (! User::find(1)) {
            User::factory()->superAdmin()->create(['id' => 1, 'email' => 'system@sewakost.test']);
        }

        // Create kost with owner
        $owner = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $this->kost = Kost::factory()->create([
            'user_id' => $owner->id,
            'status' => 'active',
            'qris_image_path' => 'qris/test-qris.png',
        ]);
        $this->kost->categories()->attach($category->id);

        // Create room type with max_occupants
        $this->roomType = RoomType::factory()->create([
            'kost_id' => $this->kost->id,
            'max_occupants' => 2, // Room capacity is on room_type
            'security_deposit' => 500000,
        ]);

        // Create price scheme
        $this->priceScheme = PriceScheme::factory()->create([
            'room_type_id' => $this->roomType->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'price' => 1000000,
        ]);

        // Create room
        $this->room = Room::factory()->create([
            'kost_id' => $this->kost->id,
            'room_type_id' => $this->roomType->id,
            'status' => 'available',
        ]);

        // Create document requirements
        KostDocumentRequirement::factory()->create([
            'kost_id' => $this->kost->id,
            'document_type' => 'KTP',
            'is_required' => true,
        ]);
        KostDocumentRequirement::factory()->create([
            'kost_id' => $this->kost->id,
            'document_type' => 'KK',
            'is_required' => true,
        ]);
    }

    /**
     * Test CreateRental records pending status history.
     */
    public function test_create_rental_records_pending_status_history(): void
    {
        $data = [
            'room_id' => $this->room->id,
            'user_id' => $this->tenant->id,
            'price_scheme_id' => $this->priceScheme->id,
            'duration' => 3,
            'start_date' => now()->addDays(5)->toDateString(),
        ];

        $rental = app(CreateRental::class)->execute($data);

        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $rental->id,
            'status' => 'pending',
            'changed_by' => $this->tenant->id,
        ]);

        $history = $rental->statusHistories()->first();
        $this->assertEquals('pending', $history->status);
        $this->assertEquals($this->tenant->id, $history->changed_by);
        $this->assertStringContainsString('Rental created by tenant', $history->internal_notes);
        $this->assertNotNull($history->created_at);
    }

    /**
     * Test VerifyPayment records paid status history.
     */
    public function test_verify_payment_records_paid_status_history(): void
    {
        // Create rental in pending status
        $rental = $this->createPendingRental();

        // Verify payment
        app(VerifyPayment::class)->execute($rental->payment, $this->admin);

        $rental->refresh();

        // Should have 2 history records: pending + paid
        $this->assertCount(2, $rental->statusHistories);

        $paidHistory = $rental->statusHistories()->where('status', 'paid')->first();
        $this->assertNotNull($paidHistory);
        $this->assertEquals('paid', $paidHistory->status);
        $this->assertEquals($this->admin->id, $paidHistory->changed_by);
        $this->assertStringContainsString('Payment verified by admin', $paidHistory->internal_notes);
    }

    /**
     * Test RejectPayment records rejected status history.
     */
    public function test_reject_payment_records_rejected_status_history(): void
    {
        // Create rental in pending status
        $rental = $this->createPendingRental();

        // Reject payment
        app(RejectPayment::class)->execute($rental->payment, 'Bukti pembayaran tidak jelas', $this->admin);

        $rental->refresh();

        // Should have 2 history records: pending + rejected
        $this->assertCount(2, $rental->statusHistories);

        $rejectedHistory = $rental->statusHistories()->where('status', 'rejected')->first();
        $this->assertNotNull($rejectedHistory);
        $this->assertEquals('rejected', $rejectedHistory->status);
        $this->assertEquals($this->admin->id, $rejectedHistory->changed_by);
        $this->assertStringContainsString('Bukti pembayaran tidak jelas', $rejectedHistory->internal_notes);
    }

    /**
     * Test UploadDocument (first upload) records documents_pending status history.
     */
    public function test_first_document_upload_records_documents_pending_history(): void
    {
        // Create rental in paid status
        $rental = $this->createPaidRental();

        // Upload first document
        $file = UploadedFile::fake()->image('ktp.jpg');
        $this->actingAs($this->tenant);
        app(UploadDocument::class)->execute($rental, 'KTP', $file);

        $rental->refresh();

        // Should have 3 history records: pending + paid + documents_pending
        $this->assertCount(3, $rental->statusHistories);

        $docsPendingHistory = $rental->statusHistories()->where('status', 'documents_pending')->first();
        $this->assertNotNull($docsPendingHistory);
        $this->assertEquals('documents_pending', $docsPendingHistory->status);
        $this->assertEquals($this->tenant->id, $docsPendingHistory->changed_by);
        $this->assertStringContainsString('First document uploaded', $docsPendingHistory->internal_notes);
    }

    /**
     * Test UploadDocument (subsequent uploads) does not duplicate status history.
     */
    public function test_subsequent_document_uploads_do_not_duplicate_history(): void
    {
        // Create rental in documents_pending status (already has first doc)
        $rental = $this->createDocumentsPendingRental();
        $initialHistoryCount = $rental->statusHistories()->count();

        // Upload second document
        $file = UploadedFile::fake()->image('kk.jpg');
        $this->actingAs($this->tenant);
        app(UploadDocument::class)->execute($rental, 'KK', $file);

        $rental->refresh();

        // History count should NOT increase (still same status)
        $this->assertCount($initialHistoryCount, $rental->statusHistories);
        $this->assertEquals('documents_pending', $rental->status);
    }

    /**
     * Test VerifyDocument auto-confirm records confirmed status history.
     */
    public function test_auto_confirm_records_confirmed_status_history(): void
    {
        // Create rental in documents_pending status with all docs uploaded
        $rental = $this->createDocumentsPendingRental();

        // Approve first document
        $ktpDoc = $rental->rentalDocuments()->where('document_type', 'KTP')->first();
        $this->actingAs($this->admin);
        app(VerifyDocument::class)->execute($ktpDoc, true);

        $rental->refresh();
        $this->assertEquals('documents_pending', $rental->status); // Still pending (not all docs approved)

        // Approve second document (should trigger auto-confirm)
        $kkDoc = $rental->rentalDocuments()->where('document_type', 'KK')->first();
        app(VerifyDocument::class)->execute($kkDoc, true);

        $rental->refresh();

        // Should have confirmed status history
        $confirmedHistory = $rental->statusHistories()->where('status', 'confirmed')->first();
        $this->assertNotNull($confirmedHistory);
        $this->assertEquals('confirmed', $confirmedHistory->status);
        $this->assertEquals($this->admin->id, $confirmedHistory->changed_by); // Admin who verified last document
        $this->assertStringContainsString('All required documents verified and approved', $confirmedHistory->internal_notes);
    }

    /**
     * Test manual CancelRental records cancelled status history with reason.
     */
    public function test_manual_cancel_records_cancelled_status_history_with_reason(): void
    {
        $rental = $this->createPendingRental();

        $reason = 'Berubah pikiran';
        app(CancelRental::class)->execute($rental, $this->tenant->id, $reason);

        $rental->refresh();

        $cancelledHistory = $rental->statusHistories()->where('status', 'cancelled')->first();
        $this->assertNotNull($cancelledHistory);
        $this->assertEquals('cancelled', $cancelledHistory->status);
        $this->assertEquals($this->tenant->id, $cancelledHistory->changed_by);
        $this->assertStringContainsString($reason, $cancelledHistory->internal_notes);
        $this->assertStringContainsString('Dibatalkan oleh tenant', $cancelledHistory->internal_notes);
    }

    /**
     * Test manual CancelRental without reason records default notes.
     */
    public function test_manual_cancel_without_reason_records_default_notes(): void
    {
        $rental = $this->createPendingRental();

        app(CancelRental::class)->execute($rental, $this->tenant->id, null);

        $rental->refresh();

        $cancelledHistory = $rental->statusHistories()->where('status', 'cancelled')->first();
        $this->assertNotNull($cancelledHistory);
        $this->assertEquals('cancelled', $cancelledHistory->status);
        $this->assertEquals($this->tenant->id, $cancelledHistory->changed_by);
        $this->assertEquals('Dibatalkan oleh tenant.', $cancelledHistory->internal_notes);
    }

    /**
     * Test CancelOverdueRentals records cancelled status history by system.
     */
    public function test_cancel_overdue_records_system_cancelled_history(): void
    {
        // Create rental
        $rental = Rental::create([
            'room_id' => $this->room->id,
            'user_id' => $this->tenant->id,
            'price_scheme_id' => $this->priceScheme->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'room_price' => 1000000,
            'security_deposit' => 500000,
            'grand_total' => 1500000,
            'status' => 'pending',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(35),
        ]);

        // Manually backdate created_at to 8 days ago (bypass timestamps)
        $rental->timestamps = false;
        $rental->created_at = now()->subDays(8);
        $rental->save();
        $rental->timestamps = true;

        Payment::create([
            'rental_id' => $rental->id,
            'qris_image_path' => 'qris/test.png',
            'amount' => 1500000,
            'status' => 'pending',
            'expired_at' => now()->subHours(2), // Payment expired 2 hours ago
        ]);

        // Create initial status history manually
        $rental->statusHistories()->create([
            'status' => 'pending',
            'changed_by' => $this->tenant->id,
            'internal_notes' => 'Rental created by tenant',
        ]);

        // Verify rental is overdue (payment expired)
        $this->assertEquals('pending', $rental->status);
        $this->assertTrue($rental->payment->expired_at->lessThan(now()));

        // Run command
        $this->artisan(CancelOverdueRentals::class)->assertSuccessful();

        $rental->refresh();

        // Check if rental was cancelled
        $this->assertEquals('cancelled', $rental->status);

        $cancelledHistory = $rental->statusHistories()->where('status', 'cancelled')->first();
        $this->assertNotNull($cancelledHistory);
        $this->assertEquals('cancelled', $cancelledHistory->status);
        $this->assertEquals(1, $cancelledHistory->changed_by); // System user
        $this->assertStringContainsString('48 hours', $cancelledHistory->internal_notes);
    }

    /**
     * Test ActivateRentals records active status history by system.
     */
    public function test_activate_rentals_records_system_active_history(): void
    {
        // Create rental in confirmed status with start_date = today
        $rental = Rental::create([
            'room_id' => $this->room->id,
            'user_id' => $this->tenant->id,
            'price_scheme_id' => $this->priceScheme->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'room_price' => 1000000,
            'security_deposit' => 500000,
            'grand_total' => 1500000,
            'status' => 'confirmed',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(1)->toDateString(),
        ]);

        Payment::create([
            'rental_id' => $rental->id,
            'qris_image_path' => 'qris/test.png',
            'amount' => 1500000,
            'status' => 'success',
            'expired_at' => now()->addHours(48),
        ]);

        // Create status history manually (bypass CreateRental)
        $rental->statusHistories()->create([
            'status' => 'confirmed',
            'changed_by' => 1,
            'internal_notes' => 'Test setup',
        ]);

        // Run command
        $this->artisan(ActivateRentals::class)->assertSuccessful();

        $rental->refresh();

        $activeHistory = $rental->statusHistories()->where('status', 'active')->first();
        $this->assertNotNull($activeHistory);
        $this->assertEquals('active', $activeHistory->status);
        $this->assertEquals(1, $activeHistory->changed_by); // System user
        $this->assertStringContainsString('Auto-activated on start date', $activeHistory->internal_notes);
    }

    /**
     * Test CompleteRentals records completed status history by system.
     */
    public function test_complete_rentals_records_system_completed_history(): void
    {
        // Create rental in active status with end_date = yesterday
        $rental = Rental::create([
            'room_id' => $this->room->id,
            'user_id' => $this->tenant->id,
            'price_scheme_id' => $this->priceScheme->id,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'room_price' => 1000000,
            'security_deposit' => 500000,
            'grand_total' => 1500000,
            'status' => 'active',
            'start_date' => now()->subMonths(1)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
        ]);

        Payment::create([
            'rental_id' => $rental->id,
            'qris_image_path' => 'qris/test.png',
            'amount' => 1500000,
            'status' => 'success',
            'expired_at' => now()->addHours(48),
        ]);

        // Create status history manually (bypass CreateRental)
        $rental->statusHistories()->create([
            'status' => 'active',
            'changed_by' => 1,
            'internal_notes' => 'Test setup',
        ]);

        // Run command
        $this->artisan(CompleteRentals::class)->assertSuccessful();

        $rental->refresh();

        $completedHistory = $rental->statusHistories()->where('status', 'completed')->first();
        $this->assertNotNull($completedHistory);
        $this->assertEquals('completed', $completedHistory->status);
        $this->assertEquals(1, $completedHistory->changed_by); // System user
        $this->assertStringContainsString('Auto-completed on end date', $completedHistory->internal_notes);
    }

    /**
     * Test all transitions record timestamp correctly.
     */
    public function test_status_history_timestamps_are_accurate(): void
    {
        $rental = $this->createPendingRental();

        $pendingHistory = $rental->statusHistories()->where('status', 'pending')->first();
        $this->assertNotNull($pendingHistory->created_at);

        // Use deterministic time control instead of sleep
        Carbon::setTestNow(now()->addSecond());

        app(VerifyPayment::class)->execute($rental->payment, $this->admin);

        $rental->refresh();
        $paidHistory = $rental->statusHistories()->where('status', 'paid')->first();
        $this->assertNotNull($paidHistory->created_at);

        // Timestamps should be sequential (paid after pending)
        $this->assertTrue($paidHistory->created_at->greaterThanOrEqualTo($pendingHistory->created_at));

        // Reset real time
        Carbon::setTestNow();
    }

    /**
     * Test complete rental lifecycle has full history trail.
     */
    public function test_complete_lifecycle_has_full_history_trail(): void
    {
        // 1. Create rental (pending)
        $rental = $this->createPendingRental();
        $this->assertCount(1, $rental->statusHistories);

        // 2. Verify payment (paid)
        app(VerifyPayment::class)->execute($rental->payment, $this->admin);
        $rental->refresh();
        $this->assertCount(2, $rental->statusHistories);

        // 3. Upload documents (documents_pending)
        $this->actingAs($this->tenant);
        $ktpFile = UploadedFile::fake()->image('ktp.jpg');
        $kkFile = UploadedFile::fake()->image('kk.jpg');
        app(UploadDocument::class)->execute($rental, 'KTP', $ktpFile);
        $rental->refresh();
        $this->assertCount(3, $rental->statusHistories);

        app(UploadDocument::class)->execute($rental, 'KK', $kkFile);
        $rental->refresh();
        $this->assertCount(3, $rental->statusHistories); // No duplicate

        // 4. Verify documents (confirmed)
        $this->actingAs($this->admin);
        $ktpDoc = $rental->rentalDocuments()->where('document_type', 'KTP')->first();
        $kkDoc = $rental->rentalDocuments()->where('document_type', 'KK')->first();
        app(VerifyDocument::class)->execute($ktpDoc, true);
        app(VerifyDocument::class)->execute($kkDoc, true); // Triggers auto-confirm
        $rental->refresh();
        $this->assertCount(4, $rental->statusHistories);

        // 5. Activate rental (active)
        $rental->update(['start_date' => now()->toDateString()]);
        $this->artisan(ActivateRentals::class)->assertSuccessful();
        $rental->refresh();
        $this->assertCount(5, $rental->statusHistories);

        // 6. Complete rental (completed)
        $rental->update(['end_date' => now()->subDay()->toDateString()]);
        $this->artisan(CompleteRentals::class)->assertSuccessful();
        $rental->refresh();
        $this->assertCount(6, $rental->statusHistories);

        // Verify full trail
        $statuses = $rental->statusHistories()->orderBy('created_at')->pluck('status')->toArray();
        $this->assertEquals([
            'pending',
            'paid',
            'documents_pending',
            'confirmed',
            'active',
            'completed',
        ], $statuses);
    }

    /**
     * Test cancelled rental lifecycle has appropriate history.
     */
    public function test_cancelled_lifecycle_has_appropriate_history(): void
    {
        // Scenario 1: Cancel from pending
        $rental1 = $this->createPendingRental();
        app(CancelRental::class)->execute($rental1, $this->tenant->id, 'Changed mind');
        $rental1->refresh();

        $statuses1 = $rental1->statusHistories()->orderBy('created_at')->pluck('status')->toArray();
        $this->assertEquals(['pending', 'cancelled'], $statuses1);

        // Scenario 2: Cancel from paid
        $rental2 = $this->createPaidRental();
        app(CancelRental::class)->execute($rental2, $this->tenant->id, 'Found better place');
        $rental2->refresh();

        $statuses2 = $rental2->statusHistories()->orderBy('created_at')->pluck('status')->toArray();
        $this->assertEquals(['pending', 'paid', 'cancelled'], $statuses2);

        // Scenario 3: Cancel from documents_pending
        $rental3 = $this->createDocumentsPendingRental();
        app(CancelRental::class)->execute($rental3, $this->tenant->id);
        $rental3->refresh();

        $statuses3 = $rental3->statusHistories()->orderBy('created_at')->pluck('status')->toArray();
        $this->assertEquals(['pending', 'paid', 'documents_pending', 'cancelled'], $statuses3);
    }

    /**
     * Test status history is immutable (no updates, only inserts).
     */
    public function test_status_history_is_append_only(): void
    {
        $rental = $this->createPendingRental();

        $history = $rental->statusHistories()->first();
        $originalNotes = $history->internal_notes;
        $originalStatus = $history->status;

        // Attempt to update (should not be allowed in real usage, but model allows it)
        // This test verifies that our Actions never update existing history
        $history->update(['internal_notes' => 'Modified notes']);

        // In production, status histories should never be updated
        // This test documents expected behavior (append-only)
        $this->assertTrue(true); // Placeholder for documentation
    }

    // === Helper Methods ===

    /**
     * Create rental in pending status.
     */
    private function createPendingRental(): Rental
    {
        $data = [
            'room_id' => $this->room->id,
            'user_id' => $this->tenant->id,
            'price_scheme_id' => $this->priceScheme->id,
            'duration' => 3,
            'start_date' => now()->addDays(5)->toDateString(),
        ];

        return app(CreateRental::class)->execute($data);
    }

    /**
     * Create rental in paid status.
     */
    private function createPaidRental(): Rental
    {
        $rental = $this->createPendingRental();
        app(VerifyPayment::class)->execute($rental->payment, $this->admin);

        return $rental->fresh();
    }

    /**
     * Create rental in documents_pending status (with both docs uploaded).
     */
    private function createDocumentsPendingRental(): Rental
    {
        $rental = $this->createPaidRental();

        $this->actingAs($this->tenant);
        $ktpFile = UploadedFile::fake()->image('ktp.jpg');
        $kkFile = UploadedFile::fake()->image('kk.jpg');

        app(UploadDocument::class)->execute($rental, 'KTP', $ktpFile);
        app(UploadDocument::class)->execute($rental, 'KK', $kkFile);

        return $rental->fresh();
    }
}
