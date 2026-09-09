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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TenantDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_view_own_uploaded_document(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user']);

        // Use existing rental factory which creates all relationships
        $rental = Rental::factory()
            ->for($tenant, 'user')
            ->paid()
            ->create();

        KostDocumentRequirement::factory()->create([
            'kost_id' => $rental->room->roomType->kost->id,
            'document_type' => 'ktp',
        ]);

        $file = UploadedFile::fake()->image('ktp.jpg', 800, 600)->size(500);
        $path = $file->storeAs('rental-documents', 'test-ktp.jpg', 'private');

        $document = RentalDocument::create([
            'rental_id' => $rental->id,
            'document_type' => 'ktp',
            'document_path' => $path,
            'uploaded_at' => now(),
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($tenant)
            ->get(route('rentals.documents.download', $document));

        $response->assertOk();
        $response->assertHeader('content-type', 'image/jpeg');
    }

    public function test_tenant_cannot_view_other_tenant_document(): void
    {
        Storage::fake('private');

        $tenant1 = User::factory()->create(['role' => 'user']);
        $tenant2 = User::factory()->create(['role' => 'user']);

        $rental = Rental::factory()
            ->for($tenant1, 'user')
            ->paid()
            ->create();

        $file = UploadedFile::fake()->image('ktp.jpg');
        $path = $file->storeAs('rental-documents', 'test-ktp.jpg', 'private');

        $document = RentalDocument::create([
            'rental_id' => $rental->id,
            'document_type' => 'ktp',
            'document_path' => $path,
            'uploaded_at' => now(),
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($tenant2)
            ->get(route('rentals.documents.download', $document));

        $response->assertForbidden();
    }

    public function test_admin_can_view_document_from_own_kost(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Create rental and manually assign kost to admin
        $rental = Rental::factory()
            ->for($tenant, 'user')
            ->paid()
            ->create();

        // Update kost owner to be the admin
        $rental->room->roomType->kost->update(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->image('ktp.jpg');
        $path = $file->storeAs('rental-documents', 'test-ktp.jpg', 'private');

        $document = RentalDocument::create([
            'rental_id' => $rental->id,
            'document_type' => 'ktp',
            'document_path' => $path,
            'uploaded_at' => now(),
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('rentals.documents.download', $document));

        $response->assertOk();
        $response->assertHeader('content-type', 'image/jpeg');
    }

    public function test_admin_cannot_view_document_from_other_kost(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user']);
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $rental = Rental::factory()
            ->for($tenant, 'user')
            ->paid()
            ->create();

        // Assign kost to admin1, so admin2 shouldn't have access
        $rental->room->roomType->kost->update(['user_id' => $admin1->id]);

        $file = UploadedFile::fake()->image('ktp.jpg');
        $path = $file->storeAs('rental-documents', 'test-ktp.jpg', 'private');

        $document = RentalDocument::create([
            'rental_id' => $rental->id,
            'document_type' => 'ktp',
            'document_path' => $path,
            'uploaded_at' => now(),
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($admin2)
            ->get(route('rentals.documents.download', $document));

        $response->assertForbidden();
    }

    public function test_guest_cannot_view_document(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user']);

        $rental = Rental::factory()
            ->for($tenant, 'user')
            ->paid()
            ->create();

        $file = UploadedFile::fake()->image('ktp.jpg');
        $path = $file->storeAs('rental-documents', 'test-ktp.jpg', 'private');

        $document = RentalDocument::create([
            'rental_id' => $rental->id,
            'document_type' => 'ktp',
            'document_path' => $path,
            'uploaded_at' => now(),
            'verification_status' => 'pending',
        ]);

        $response = $this->get(route('rentals.documents.download', $document));

        $response->assertRedirect(route('login'));
    }
}
