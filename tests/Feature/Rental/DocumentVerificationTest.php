<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Mail\DocumentRejectedMail;
use App\Domain\Rental\Mail\DocumentVerifiedMail;
use App\Domain\Rental\Mail\RentalConfirmedMail;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Tenant can upload document for paid rental
     */
    public function test_tenant_can_upload_document_for_paid_rental(): void
    {
        Storage::fake('public');

        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        // Create document requirement
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'paid',
        ]);

        $file = UploadedFile::fake()->image('ktp.jpg');

        $response = $this->actingAs($tenant)->post(
            route('rentals.documents.upload', $rental),
            [
                'document_type' => 'KTP',
                'file' => $file,
            ]
        );

        $response->assertRedirect(route('rentals.show', $rental));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rental_documents', [
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'pending',
        ]);

        $document = $rental->rentalDocuments()->first();
        $this->assertTrue(Storage::disk('public')->exists($document->document_path));
    }

    /**
     * Test: First upload transitions status to documents_pending
     */
    public function test_first_document_upload_transitions_status_to_documents_pending(): void
    {
        Storage::fake('public');

        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'paid',
        ]);

        $file = UploadedFile::fake()->image('ktp.jpg');

        $this->actingAs($tenant)->post(
            route('rentals.documents.upload', $rental),
            [
                'document_type' => 'KTP',
                'file' => $file,
            ]
        );

        $rental->refresh();
        $this->assertEquals('documents_pending', $rental->status);

        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $rental->id,
            'status' => 'documents_pending',
        ]);
    }

    /**
     * Test: Tenant cannot upload document for non-paid rental
     */
    public function test_tenant_cannot_upload_document_for_pending_rental(): void
    {
        Storage::fake('public');

        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'pending', // Not paid yet
        ]);

        $file = UploadedFile::fake()->image('ktp.jpg');

        $response = $this->actingAs($tenant)->post(
            route('rentals.documents.upload', $rental),
            [
                'document_type' => 'KTP',
                'file' => $file,
            ]
        );

        $response->assertForbidden();
    }

    /**
     * Test: Tenant can re-upload rejected document
     */
    public function test_tenant_can_reupload_rejected_document(): void
    {
        Storage::fake('public');

        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'documents_pending',
        ]);

        // Create rejected document
        $rejectedDoc = RentalDocument::factory()->rejected()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'document_path' => 'old-ktp.jpg',
        ]);

        $oldPath = $rejectedDoc->document_path;

        $file = UploadedFile::fake()->image('new-ktp.jpg');

        $this->actingAs($tenant)->post(
            route('rentals.documents.upload', $rental),
            [
                'document_type' => 'KTP',
                'file' => $file,
            ]
        );

        $rejectedDoc->refresh();
        $this->assertEquals('pending', $rejectedDoc->verification_status);
        $this->assertNull($rejectedDoc->rejection_reason);
        $this->assertNull($rejectedDoc->verified_at);
        $this->assertNull($rejectedDoc->verified_by);
        $this->assertNotEquals($oldPath, $rejectedDoc->document_path);
    }

    /**
     * Test: Upload validates document_type against kost requirements
     */
    public function test_upload_validates_document_type_against_requirements(): void
    {
        Storage::fake('public');

        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        // Only KTP required
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'paid',
        ]);

        $file = UploadedFile::fake()->image('passport.jpg');

        $response = $this->actingAs($tenant)->post(
            route('rentals.documents.upload', $rental),
            [
                'document_type' => 'Passport', // Not required
                'file' => $file,
            ]
        );

        $response->assertSessionHasErrors('document_type');
    }

    /**
     * Test: Admin can approve document
     */
    public function test_admin_can_approve_document(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'documents_pending',
        ]);

        $document = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.documents.approve', $document),
            ['approved' => true]
        );

        $response->assertRedirect();

        $document->refresh();
        $this->assertEquals('approved', $document->verification_status);
        $this->assertNotNull($document->verified_at);
        $this->assertEquals($admin->id, $document->verified_by);

        Mail::assertQueued(DocumentVerifiedMail::class, function ($mail) use ($tenant) {
            return $mail->hasTo($tenant->email);
        });
    }

    /**
     * Test: Admin can reject document with reason
     */
    public function test_admin_can_reject_document_with_reason(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'documents_pending',
        ]);

        $document = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.documents.reject', $document),
            [
                'approved' => false,
                'rejection_reason' => 'Foto KTP tidak jelas, mohon upload dengan pencahayaan lebih baik',
            ]
        );

        $response->assertRedirect();

        $document->refresh();
        $this->assertEquals('rejected', $document->verification_status);
        $this->assertEquals('Foto KTP tidak jelas, mohon upload dengan pencahayaan lebih baik', $document->rejection_reason);
        $this->assertNotNull($document->verified_at);
        $this->assertEquals($admin->id, $document->verified_by);

        Mail::assertQueued(DocumentRejectedMail::class, function ($mail) use ($tenant) {
            return $mail->hasTo($tenant->email);
        });
    }

    /**
     * Test: Admin cannot verify documents for other admin's kost
     */
    public function test_admin_cannot_verify_other_kost_documents(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user']);

        $kost = Kost::factory()->create(['user_id' => $admin2->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'documents_pending',
        ]);

        $document = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
        ]);

        $response = $this->actingAs($admin1)->post(
            route('admin.documents.approve', $document),
            ['approved' => true]
        );

        $response->assertForbidden();
    }

    /**
     * Test: Auto-confirm when all required documents approved
     */
    public function test_auto_confirm_when_all_required_documents_approved(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        // Create 2 required documents
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'KTP',
            'is_required' => true,
        ]);
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'Passport',
            'is_required' => true,
        ]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'documents_pending',
        ]);

        // Create 2 pending documents
        $doc1 = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'pending',
        ]);
        $doc2 = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'Passport',
            'verification_status' => 'pending',
        ]);

        // Approve first document - should NOT confirm yet
        $this->actingAs($admin)->post(
            route('admin.documents.approve', $doc1),
            ['approved' => true]
        );

        $rental->refresh();
        $this->assertEquals('documents_pending', $rental->status);
        $this->assertNull($rental->confirmed_at);

        // Approve second document - should auto-confirm
        $this->actingAs($admin)->post(
            route('admin.documents.approve', $doc2),
            ['approved' => true]
        );

        $rental->refresh();
        $this->assertEquals('confirmed', $rental->status);
        $this->assertNotNull($rental->confirmed_at);

        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $rental->id,
            'status' => 'confirmed',
            'changed_by' => $admin->id, // Admin who verified last document
        ]);

        Mail::assertQueued(RentalConfirmedMail::class, function ($mail) use ($tenant) {
            return $mail->hasTo($tenant->email);
        });
    }

    /**
     * Test: Partial approval does not trigger confirmation
     */
    public function test_partial_approval_does_not_trigger_confirmation(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        // Create 2 required documents
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'KTP',
            'is_required' => true,
        ]);
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'Passport',
            'is_required' => true,
        ]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'documents_pending',
        ]);

        // Create only 1 document
        $doc1 = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'pending',
        ]);

        // Approve only one - should NOT confirm
        $this->actingAs($admin)->post(
            route('admin.documents.approve', $doc1),
            ['approved' => true]
        );

        $rental->refresh();
        $this->assertEquals('documents_pending', $rental->status);
        $this->assertNull($rental->confirmed_at);

        Mail::assertNotQueued(RentalConfirmedMail::class);
    }

    /**
     * Test: Rejection reason required when rejecting
     */
    public function test_rejection_reason_required_when_rejecting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $rental = Rental::factory()->create([
            'room_id' => $room->id,
            'status' => 'documents_pending',
        ]);

        $document = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.documents.reject', $document),
            [
                'approved' => false,
                // Missing rejection_reason
            ]
        );

        $response->assertSessionHasErrors('rejection_reason');
    }

    /**
     * Test: Guest cannot upload documents
     */
    public function test_guest_cannot_upload_documents(): void
    {
        $rental = Rental::factory()->create(['status' => 'paid']);

        $response = $this->post(
            route('rentals.documents.upload', $rental),
            [
                'document_type' => 'KTP',
                'file' => UploadedFile::fake()->image('ktp.jpg'),
            ]
        );

        $response->assertRedirect(route('login'));
    }

    /**
     * Test: Tenant cannot upload document for other tenant's rental
     */
    public function test_tenant_cannot_upload_document_for_other_rental(): void
    {
        Storage::fake('public');

        $tenant1 = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $tenant2 = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);

        $kost = Kost::factory()->create(['status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant2->id, // Belongs to tenant2
            'room_id' => $room->id,
            'status' => 'paid',
        ]);

        $file = UploadedFile::fake()->image('ktp.jpg');

        $response = $this->actingAs($tenant1)->post( // tenant1 tries to upload
            route('rentals.documents.upload', $rental),
            [
                'document_type' => 'KTP',
                'file' => $file,
            ]
        );

        $response->assertForbidden();
    }
}
