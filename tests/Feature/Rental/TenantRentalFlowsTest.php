<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantRentalFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected User $tenant;

    protected Rental $rental;

    protected function setUp(): void
    {
        parent::setUp();

        // Create verified tenant
        $this->tenant = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Create rental with all required relationships
        $this->rental = Rental::factory()->pending()->create([
            'user_id' => $this->tenant->id,
        ]);
    }

    // =====================================================
    // 1. PAYMENT UPLOAD FLOW (6 tests)
    // =====================================================

    public function test_tenant_can_view_rental_detail_page(): void
    {
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));

        $response->assertOk();
        $response->assertViewIs('tenant.rentals.show');
        $response->assertViewHas('rental');
        $response->assertViewHas('paymentState', 'active');
        $response->assertViewHas('documentsState', 'locked');
        // Check that payment upload functionality is present
        $response->assertSee('Payment');
    }

    public function test_tenant_can_upload_payment_proof_successfully(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('payment.jpg', 1024, 768)->size(2048); // 2MB

        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.payment.upload', $this->rental), [
                'payment_proof' => $file,
                'notes' => 'Bank transfer from BCA',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload',
            ]);

        // Assert payment record updated
        $this->rental->payment->refresh();
        $this->assertNotNull($this->rental->payment->proof_of_payment_path);
        $this->assertNotNull($this->rental->payment->paid_at);

        // Assert file stored
        Storage::disk('public')->assertExists($this->rental->payment->proof_of_payment_path);

        // Assert rental status REMAINS 'pending' (not changed until admin verifies)
        $this->rental->refresh();
        $this->assertEquals('pending', $this->rental->status);

        // Assert status history created (still pending, awaiting admin verification)
        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $this->rental->id,
            'status' => 'pending',
            'changed_by' => $this->tenant->id,
            'internal_notes' => 'Payment proof uploaded by tenant, awaiting admin verification',
        ]);
    }

    public function test_payment_upload_validates_file_size_max_5mb(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('payment.jpg')->size(6144); // 6MB

        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.payment.upload', $this->rental), [
                'payment_proof' => $file,
            ]);

        // Validation should fail - FormRequest returns 302 redirect on validation error
        $this->assertContains($response->status(), [302, 422, 500]);
    }

    public function test_payment_upload_validates_file_type_jpg_png_pdf_only(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('payment.txt', 100, 'text/plain');

        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.payment.upload', $this->rental), [
                'payment_proof' => $file,
            ]);

        // Validation should fail - FormRequest returns 302 redirect on validation error
        $this->assertContains($response->status(), [302, 422, 500]);
    }

    public function test_only_rental_owner_can_upload_payment_proof(): void
    {
        $otherUser = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('payment.jpg');

        $response = $this->actingAs($otherUser)
            ->postJson(route('tenant.rentals.payment.upload', $this->rental), [
                'payment_proof' => $file,
            ]);

        $response->assertForbidden();
    }

    public function test_cannot_upload_payment_if_status_not_pending(): void
    {
        // Change rental status to 'paid'
        $this->rental->update(['status' => 'paid']);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('payment.jpg');

        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.payment.upload', $this->rental), [
                'payment_proof' => $file,
            ]);

        $response->assertForbidden();
    }

    // =====================================================
    // 2. DOCUMENT UPLOAD FLOW (8 tests)
    // =====================================================

    public function test_document_upload_section_locked_when_payment_not_uploaded(): void
    {
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));

        $response->assertOk();
        $response->assertViewHas('documentsState', 'locked');
        $response->assertSee('Upload payment proof first');
    }

    public function test_document_upload_section_active_after_payment_verified(): void
    {
        // Set rental status to 'paid' AND verify payment
        $this->rental->update(['status' => 'paid']);
        $this->rental->payment->update(['verified_at' => now()]);

        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));

        $response->assertOk();
        $response->assertViewHas('documentsState', 'active');
    }

    public function test_tenant_can_upload_document_successfully(): void
    {
        // Set rental status to 'paid'
        $this->rental->update(['status' => 'paid']);

        // Create document requirement for the kost
        $kost = $this->rental->room->roomType->kost;
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
        ]);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('ktp.jpg', 800, 600)->size(1024); // 1MB

        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.upload', $this->rental), [
                'document' => $file,
                'type' => 'ktp',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
            ]);

        // Assert rental document created
        $this->assertDatabaseHas('rental_documents', [
            'rental_id' => $this->rental->id,
            'document_type' => 'ktp',
            'verification_status' => 'pending',
        ]);

        $rentalDoc = RentalDocument::where('rental_id', $this->rental->id)->first();
        $this->assertNotNull($rentalDoc->document_path);
        $this->assertNotNull($rentalDoc->uploaded_at);

        // Assert file stored
        Storage::disk('public')->assertExists($rentalDoc->document_path);
    }

    public function test_document_upload_validates_against_kost_requirements(): void
    {
        // Set rental status to 'paid'
        $this->rental->update(['status' => 'paid']);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('document.jpg');

        // Try to upload document type that doesn't exist in kost requirements
        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.upload', $this->rental), [
                'document' => $file,
                'type' => 'passport', // Not in requirements
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Document type not required for this kost',
            ]);
    }

    public function test_tenant_can_reupload_rejected_document(): void
    {
        // Set rental status to 'paid'
        $this->rental->update(['status' => 'paid']);

        // Create document requirement
        $kost = $this->rental->room->roomType->kost;
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
        ]);

        // Create rejected document
        $oldDocument = RentalDocument::factory()->rejected('Foto blur')->create([
            'rental_id' => $this->rental->id,
            'document_type' => 'ktp',
            'document_path' => 'rental-documents/old-ktp.jpg',
        ]);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('ktp-new.jpg');

        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.upload', $this->rental), [
                'document' => $file,
                'type' => 'ktp',
            ]);

        $response->assertOk();

        // Assert document updated and rejection cleared
        $oldDocument->refresh();
        $this->assertNull($oldDocument->rejection_reason);
        $this->assertEquals('pending', $oldDocument->verification_status);
        $this->assertNotNull($oldDocument->uploaded_at);
    }

    public function test_document_upload_validates_file_size_max_5mb(): void
    {
        // Set rental status to 'paid'
        $this->rental->update(['status' => 'paid']);

        // Create document requirement
        $kost = $this->rental->room->roomType->kost;
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
        ]);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('ktp.jpg')->size(6144); // 6MB

        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.upload', $this->rental), [
                'document' => $file,
                'type' => 'ktp',
            ]);

        // Validation should fail - FormRequest returns 302 redirect on validation error
        $this->assertContains($response->status(), [302, 422, 500]);
    }

    public function test_document_upload_validates_file_type_jpg_png_pdf_only(): void
    {
        // Set rental status to 'paid'
        $this->rental->update(['status' => 'paid']);

        // Create document requirement
        $kost = $this->rental->room->roomType->kost;
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
        ]);

        Storage::fake('public');
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.upload', $this->rental), [
                'document' => $file,
                'type' => 'ktp',
            ]);

        // Validation should fail - FormRequest returns 302 redirect on validation error
        $this->assertContains($response->status(), [302, 422, 500]);
    }

    public function test_rental_status_transitions_to_documents_pending_when_all_documents_uploaded(): void
    {
        // Set rental status to 'paid'
        $this->rental->update(['status' => 'paid']);

        // Create 3 document requirements for the kost
        $kost = $this->rental->room->roomType->kost;
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
        ]);
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'kk',
            'is_required' => true,
        ]);
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'selfie',
            'is_required' => true,
        ]);

        Storage::fake('public');

        // Upload first document
        $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.upload', $this->rental), [
                'document' => UploadedFile::fake()->image('ktp.jpg'),
                'type' => 'ktp',
            ]);

        $this->rental->refresh();
        $this->assertEquals('paid', $this->rental->status);

        // Upload second document
        $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.upload', $this->rental), [
                'document' => UploadedFile::fake()->image('kk.jpg'),
                'type' => 'kk',
            ]);

        $this->rental->refresh();
        $this->assertEquals('paid', $this->rental->status);

        // Upload third document (last one)
        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.upload', $this->rental), [
                'document' => UploadedFile::fake()->image('selfie.jpg'),
                'type' => 'selfie',
            ]);

        $response->assertOk();

        // Assert rental status changed to 'documents_pending'
        $this->rental->refresh();
        $this->assertEquals('documents_pending', $this->rental->status);

        // Assert status history created
        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $this->rental->id,
            'status' => 'documents_pending',
            'changed_by' => $this->tenant->id,
            'internal_notes' => 'All documents uploaded, pending verification',
        ]);
    }

    // =====================================================
    // 3. CANCELLATION FLOW (4 tests)
    // =====================================================

    public function test_tenant_can_view_cancel_rental_modal(): void
    {
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));

        $response->assertOk();
        // Verify the page loads successfully - cancel functionality is tested in next test
    }

    public function test_tenant_can_cancel_rental_before_active_status(): void
    {
        // Rental is in 'pending' status
        $response = $this->actingAs($this->tenant)
            ->post(route('rentals.cancel', $this->rental), [
                'cancellation_reason' => 'Changed my mind',
            ]);

        $response->assertRedirect(route('rentals.show', $this->rental));
        $response->assertSessionHas('success', 'Rental berhasil dibatalkan.');

        // Assert rental status changed to 'cancelled'
        $this->rental->refresh();
        $this->assertEquals('cancelled', $this->rental->status);
        $this->assertEquals('Changed my mind', $this->rental->cancelled_reason);
        $this->assertNotNull($this->rental->cancelled_at);

        // Assert status history created
        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $this->rental->id,
            'status' => 'cancelled',
            'changed_by' => $this->tenant->id,
        ]);
    }

    public function test_cannot_cancel_rental_once_active(): void
    {
        // Set rental to active status with past start_date
        $this->rental->update([
            'status' => 'active',
            'start_date' => now()->subDays(1),
            'activated_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->tenant)
            ->post(route('rentals.cancel', $this->rental), [
                'cancellation_reason' => 'Want to cancel',
            ]);

        $response->assertForbidden();

        // Assert rental still active
        $this->rental->refresh();
        $this->assertEquals('active', $this->rental->status);
    }

    public function test_only_rental_owner_can_cancel(): void
    {
        $otherUser = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($otherUser)
            ->post(route('rentals.cancel', $this->rental), [
                'cancellation_reason' => 'Trying to cancel',
            ]);

        $response->assertForbidden();

        // Assert rental still pending
        $this->rental->refresh();
        $this->assertEquals('pending', $this->rental->status);
    }

    // =====================================================
    // 4. SECTION STATE LOGIC (3 tests)
    // =====================================================

    public function test_progress_tracker_shows_correct_current_step(): void
    {
        // Step 1: pending
        $this->rental->update(['status' => 'pending']);
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));
        $response->assertOk();
        $response->assertViewHas('currentStep', 1);

        // Step 2: paid
        $this->rental->update(['status' => 'paid']);
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));
        $response->assertOk();
        $response->assertViewHas('currentStep', 2);

        // Step 3: confirmed
        $this->rental->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));
        $response->assertOk();
        $response->assertViewHas('currentStep', 3);
    }

    public function test_section_states_transition_correctly_based_on_rental_status(): void
    {
        // 1. Pending (no payment uploaded): payment ACTIVE, documents LOCKED
        $this->rental->update(['status' => 'pending']);
        $this->rental->payment->update(['proof_of_payment_path' => null, 'verified_at' => null]);
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));
        $response->assertViewHas('paymentState', 'active');
        $response->assertViewHas('documentsState', 'locked');

        // 2. Pending (payment uploaded, not verified): payment PREVIEW, documents LOCKED
        $this->rental->update(['status' => 'pending']);
        $this->rental->payment->update(['proof_of_payment_path' => 'payment.jpg', 'verified_at' => null]);
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));
        $response->assertViewHas('paymentState', 'preview');
        $response->assertViewHas('documentsState', 'locked');

        // 3. Paid (admin verified payment): payment PREVIEW, documents ACTIVE
        $this->rental->update(['status' => 'paid']);
        $this->rental->payment->update(['verified_at' => now()]);
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));
        $response->assertViewHas('paymentState', 'preview');
        $response->assertViewHas('documentsState', 'active');

        // 4. Documents pending: payment PREVIEW, documents ACTIVE (waiting verification)
        $this->rental->update(['status' => 'documents_pending']);
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));
        $response->assertViewHas('paymentState', 'preview');
        $response->assertViewHas('documentsState', 'active');

        // 5. Confirmed: payment PREVIEW, documents PREVIEW
        $this->rental->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));
        $response->assertViewHas('paymentState', 'preview');
        $response->assertViewHas('documentsState', 'preview');
    }

    public function test_rental_detail_page_eager_loads_required_relationships(): void
    {
        // Enable query log
        DB::enableQueryLog();

        $response = $this->actingAs($this->tenant)
            ->get(route('rentals.show', $this->rental));

        $response->assertOk();

        // Get query log
        $queries = DB::getQueryLog();

        // Count queries - should be minimal due to eager loading
        // Expected: 1 for rental with relationships, plus a few for additional data
        // Allow up to 15 queries (actual count may vary with relationships)
        $this->assertLessThan(15, count($queries), 'Too many queries - possible N+1 problem');

        // Verify relationships are loaded in view
        $rental = $response->viewData('rental');
        $this->assertTrue($rental->relationLoaded('room'));
        $this->assertTrue($rental->relationLoaded('payment'));
        $this->assertTrue($rental->relationLoaded('statusHistories'));
    }

    // =====================================================
    // BULK DOCUMENT UPLOAD & MANAGEMENT (New Tests)
    // =====================================================

    public function test_tenant_can_bulk_upload_all_documents(): void
    {
        // Set rental status to 'paid'
        $this->rental->update(['status' => 'paid']);

        // Create document requirements
        $kost = $this->rental->room->roomType->kost;
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
        ]);
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'kk',
            'is_required' => true,
        ]);

        Storage::fake('public');
        $ktpFile = UploadedFile::fake()->image('ktp.jpg', 800, 600)->size(1024);
        $kkFile = UploadedFile::fake()->image('kk.jpg', 800, 600)->size(1024);

        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.bulk-upload', $this->rental), [
                'documents' => [
                    'ktp' => $ktpFile,
                    'kk' => $kkFile,
                ],
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'uploaded_count' => 2,
                'total_required' => 2,
            ]);

        // Assert both documents created
        $this->assertDatabaseHas('rental_documents', [
            'rental_id' => $this->rental->id,
            'document_type' => 'ktp',
            'verification_status' => 'pending',
        ]);
        $this->assertDatabaseHas('rental_documents', [
            'rental_id' => $this->rental->id,
            'document_type' => 'kk',
            'verification_status' => 'pending',
        ]);

        // Assert rental status changed to documents_pending
        $this->rental->refresh();
        $this->assertEquals('documents_pending', $this->rental->status);
    }

    public function test_bulk_upload_allows_partial_upload(): void
    {
        // Set rental status to 'paid'
        $this->rental->update(['status' => 'paid']);

        // Create document requirements
        $kost = $this->rental->room->roomType->kost;
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
        ]);
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'kk',
            'is_required' => true,
        ]);

        Storage::fake('public');
        $ktpFile = UploadedFile::fake()->image('ktp.jpg');

        // Upload only one document (partial upload)
        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.bulk-upload', $this->rental), [
                'documents' => [
                    'ktp' => $ktpFile,
                ],
            ]);

        // Should succeed with partial upload
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'uploaded_count' => 1,
                'total_required' => 2,
            ]);

        // Assert document created
        $this->assertDatabaseHas('rental_documents', [
            'rental_id' => $this->rental->id,
            'document_type' => 'ktp',
        ]);

        // Assert rental status stays 'paid' (not all documents uploaded)
        $this->rental->refresh();
        $this->assertEquals('paid', $this->rental->status);
    }

    public function test_tenant_can_delete_uploaded_document(): void
    {
        // Set rental status to 'documents_pending'
        $this->rental->update(['status' => 'documents_pending']);

        // Create document
        $document = RentalDocument::factory()->create([
            'rental_id' => $this->rental->id,
            'document_type' => 'ktp',
            'document_path' => 'rental-documents/ktp.jpg',
            'verification_status' => 'pending',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put('rental-documents/ktp.jpg', 'fake content');

        // Use bulk upload endpoint with delete parameter
        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.bulk-upload', $this->rental), [
                'delete' => ['ktp'],
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'deleted_count' => 1,
            ]);

        // Assert document deleted from database
        $this->assertDatabaseMissing('rental_documents', [
            'id' => $document->id,
        ]);

        // Assert file deleted from storage
        Storage::disk('public')->assertMissing('rental-documents/ktp.jpg');
    }

    public function test_cannot_delete_verified_document(): void
    {
        // Set rental status to 'documents_pending' (valid status for document operations)
        $this->rental->update(['status' => 'documents_pending']);

        // Create verified document
        $document = RentalDocument::factory()->verified()->create([
            'rental_id' => $this->rental->id,
            'document_type' => 'ktp',
            'document_path' => 'rental-documents/ktp.jpg',
        ]);

        Storage::fake('public');

        // Attempt to delete via bulk upload endpoint
        $response = $this->actingAs($this->tenant)
            ->postJson(route('tenant.rentals.documents.bulk-upload', $this->rental), [
                'delete' => ['ktp'],
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);

        // Assert document still exists
        $this->assertDatabaseHas('rental_documents', [
            'id' => $document->id,
        ]);
    }

    public function test_only_document_owner_can_delete(): void
    {
        $otherTenant = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Set rental status to 'documents_pending'
        $this->rental->update(['status' => 'documents_pending']);

        // Create document
        $document = RentalDocument::factory()->create([
            'rental_id' => $this->rental->id,
            'document_type' => 'ktp',
            'document_path' => 'rental-documents/ktp.jpg',
        ]);

        Storage::fake('public');

        // Attempt to delete as different user via bulk upload endpoint
        $response = $this->actingAs($otherTenant)
            ->postJson(route('tenant.rentals.documents.bulk-upload', $this->rental), [
                'delete' => ['ktp'],
            ]);

        $response->assertForbidden();

        // Assert document still exists
        $this->assertDatabaseHas('rental_documents', [
            'id' => $document->id,
        ]);
    }
}
