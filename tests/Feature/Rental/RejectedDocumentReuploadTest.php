<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RejectedDocumentReuploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_reupload_rejected_document(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'documents_pending',
        ]);

        $rental->payment->update(['verified_at' => now()]);

        // Add document requirement to kost
        $rental->room->roomType->kost->documentRequirements()->create([
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $rejectedDoc = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'rejected',
            'rejection_reason' => 'Foto tidak jelas',
            'verified_at' => now(),
            'verified_by' => User::factory()->create(['role' => 'admin'])->id,
        ]);

        $newFile = UploadedFile::fake()->image('ktp_new.jpg');

        $response = $this->actingAs($tenant)->post(route('tenant.rentals.documents.bulk-upload', $rental), [
            'documents' => [
                'KTP' => $newFile,
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $rejectedDoc->refresh();

        $this->assertEquals('pending', $rejectedDoc->verification_status);
        $this->assertNull($rejectedDoc->rejection_reason);
        $this->assertNull($rejectedDoc->verified_at);
        $this->assertNull($rejectedDoc->verified_by);
        $this->assertNotNull($rejectedDoc->document_path);
    }

    public function test_tenant_can_delete_rejected_document(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'documents_pending',
        ]);

        $rental->payment->update(['verified_at' => now()]);

        // Add document requirement to kost
        $rental->room->roomType->kost->documentRequirements()->create([
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $rejectedDoc = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'rejected',
            'rejection_reason' => 'Foto tidak jelas',
            'verified_at' => now(),
            'verified_by' => User::factory()->create(['role' => 'admin'])->id,
        ]);

        Storage::disk('private')->put($rejectedDoc->document_path, 'fake-content');

        $response = $this->actingAs($tenant)->post(route('tenant.rentals.documents.bulk-upload', $rental), [
            'delete' => ['KTP'],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('rental_documents', [
            'id' => $rejectedDoc->id,
        ]);

        Storage::disk('private')->assertMissing($rejectedDoc->document_path);
    }

    public function test_tenant_cannot_modify_approved_document(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'documents_pending',
        ]);

        $rental->payment->update(['verified_at' => now()]);

        // Add document requirement to kost
        $rental->room->roomType->kost->documentRequirements()->create([
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $approvedDoc = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verified_by' => User::factory()->create(['role' => 'admin'])->id,
        ]);

        $originalPath = $approvedDoc->document_path;
        Storage::disk('private')->put($originalPath, 'original-content');

        $newFile = UploadedFile::fake()->image('ktp_new.jpg');

        $response = $this->actingAs($tenant)->post(route('tenant.rentals.documents.bulk-upload', $rental), [
            'documents' => [
                'KTP' => $newFile,
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $approvedDoc->refresh();

        $this->assertEquals('approved', $approvedDoc->verification_status);
        $this->assertEquals($originalPath, $approvedDoc->document_path);
        $this->assertNotNull($approvedDoc->verified_at);
    }

    public function test_tenant_cannot_delete_approved_document(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'documents_pending',
        ]);

        $rental->payment->update(['verified_at' => now()]);

        // Add document requirement to kost
        $rental->room->roomType->kost->documentRequirements()->create([
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $approvedDoc = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verified_by' => User::factory()->create(['role' => 'admin'])->id,
        ]);

        Storage::disk('private')->put($approvedDoc->document_path, 'fake-content');

        $response = $this->actingAs($tenant)->post(route('tenant.rentals.documents.bulk-upload', $rental), [
            'delete' => ['KTP'],
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('rental_documents', [
            'id' => $approvedDoc->id,
            'verification_status' => 'approved',
        ]);

        Storage::disk('private')->assertExists($approvedDoc->document_path);
    }

    public function test_reupload_resets_verification_status_to_pending(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'documents_pending',
        ]);

        $rental->payment->update(['verified_at' => now()]);

        // Add document requirement to kost
        $rental->room->roomType->kost->documentRequirements()->create([
            'document_type' => 'KTP',
            'is_required' => true,
        ]);

        $rejectedDoc = RentalDocument::factory()->create([
            'rental_id' => $rental->id,
            'document_type' => 'KTP',
            'verification_status' => 'rejected',
            'rejection_reason' => 'Foto tidak jelas',
            'verified_at' => now()->subHour(),
            'verified_by' => $admin->id,
        ]);

        $newFile = UploadedFile::fake()->image('ktp_better.jpg');

        $response = $this->actingAs($tenant)->post(route('tenant.rentals.documents.bulk-upload', $rental), [
            'documents' => [
                'KTP' => $newFile,
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $rejectedDoc->refresh();

        $this->assertEquals('pending', $rejectedDoc->verification_status);
        $this->assertNull($rejectedDoc->rejection_reason);
        $this->assertNull($rejectedDoc->verified_at);
        $this->assertNull($rejectedDoc->verified_by);
    }
}
