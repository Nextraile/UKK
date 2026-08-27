<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentViewingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view document for own kost's rental.
     *
     * FR-087: Admin view submitted documents
     */
    public function test_admin_can_view_document_for_own_kost_rental(): void
    {
        Storage::fake('private');

        // Create admin with kost
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->for($admin, 'owner')->approved()->create();
        $roomType = RoomType::factory()->for($kost)->create();
        $priceScheme = PriceScheme::factory()->for($roomType)->create();
        $room = Room::factory()->for($roomType)->create();

        // Create tenant with rental
        $tenant = User::factory()->create();
        $rental = Rental::factory()
            ->for($room)
            ->for($tenant, 'user')
            ->for($priceScheme)
            ->create(['status' => 'paid']);

        // Payment created automatically by factory

        // Create document requirement
        KostDocumentRequirement::factory()
            ->for($kost)
            ->create(['document_type' => 'KTP']);

        // Upload document to fake disk
        $file = UploadedFile::fake()->image('ktp.jpg');
        $path = Storage::disk('private')->putFile('rental-documents', $file);

        $document = RentalDocument::factory()
            ->for($rental)
            ->create([
                'document_type' => 'KTP',
                'document_path' => $path,
                'verification_status' => 'pending',
            ]);

        // Verify file exists in fake storage
        Storage::disk('private')->assertExists($path);

        // Admin views document
        $response = $this->actingAs($admin)
            ->get(route('admin.rentals.documents.show', $document));

        $response->assertOk();
    }

    /**
     * Test admin cannot view document for other admin's kost.
     *
     * Authorization boundary test
     */
    public function test_admin_cannot_view_document_for_other_admin_kost(): void
    {
        Storage::fake('private');

        // Create two admins with separate kosts
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $kost1 = Kost::factory()->for($admin1, 'owner')->approved()->create();
        $roomType1 = RoomType::factory()->for($kost1)->create();
        $priceScheme1 = PriceScheme::factory()->for($roomType1)->create();
        $room1 = Room::factory()->for($roomType1)->create();

        // Create rental for admin1's kost
        $tenant = User::factory()->create();
        $rental = Rental::factory()
            ->for($room1)
            ->for($tenant, 'user')
            ->for($priceScheme1)
            ->create(['status' => 'paid']);

        // Payment created automatically by factory

        // Upload document
        $file = UploadedFile::fake()->image('ktp.jpg');
        $path = $file->store('rental-documents', 'private');

        $document = RentalDocument::factory()
            ->for($rental)
            ->create([
                'document_type' => 'KTP',
                'document_path' => $path,
                'verification_status' => 'pending',
            ]);

        // Admin2 tries to view admin1's document
        $response = $this->actingAs($admin2)
            ->get(route('admin.rentals.documents.show', $document));

        $response->assertForbidden();
    }

    /**
     * Test 404 when document file missing from storage.
     */
    public function test_returns_404_when_document_file_missing(): void
    {
        Storage::fake('private');

        // Create admin with kost
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->for($admin, 'owner')->approved()->create();
        $roomType = RoomType::factory()->for($kost)->create();
        $priceScheme = PriceScheme::factory()->for($roomType)->create();
        $room = Room::factory()->for($roomType)->create();

        // Create rental with document record but no actual file
        $tenant = User::factory()->create();
        $rental = Rental::factory()
            ->for($room)
            ->for($tenant, 'user')
            ->for($priceScheme)
            ->create(['status' => 'paid']);

        // Payment created automatically by factory

        $document = RentalDocument::factory()
            ->for($rental)
            ->create([
                'document_type' => 'KTP',
                'document_path' => 'rental-documents/nonexistent.jpg',
                'verification_status' => 'pending',
            ]);

        // Admin tries to view missing document
        $response = $this->actingAs($admin)
            ->get(route('admin.rentals.documents.show', $document));

        $response->assertNotFound();
    }

    /**
     * Test admin can view PDF documents.
     */
    public function test_admin_can_view_pdf_document(): void
    {
        Storage::fake('private');

        // Create admin with kost
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->for($admin, 'owner')->approved()->create();
        $roomType = RoomType::factory()->for($kost)->create();
        $priceScheme = PriceScheme::factory()->for($roomType)->create();
        $room = Room::factory()->for($roomType)->create();

        // Create rental
        $tenant = User::factory()->create();
        $rental = Rental::factory()
            ->for($room)
            ->for($tenant, 'user')
            ->for($priceScheme)
            ->create(['status' => 'paid']);

        // Payment created automatically by factory

        // Upload PDF document to fake disk
        $file = UploadedFile::fake()->create('passport.pdf', 1024, 'application/pdf');
        $path = Storage::disk('private')->putFile('rental-documents', $file);

        $document = RentalDocument::factory()
            ->for($rental)
            ->create([
                'document_type' => 'Passport',
                'document_path' => $path,
                'verification_status' => 'pending',
            ]);

        // Verify file exists
        Storage::disk('private')->assertExists($path);

        // Admin views PDF
        $response = $this->actingAs($admin)
            ->get(route('admin.rentals.documents.show', $document));

        $response->assertOk();
    }

    /**
     * Test authorization enforced for guest users.
     */
    public function test_guest_cannot_view_document(): void
    {
        Storage::fake('private');

        // Create document
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->for($admin, 'owner')->approved()->create();
        $roomType = RoomType::factory()->for($kost)->create();
        $priceScheme = PriceScheme::factory()->for($roomType)->create();
        $room = Room::factory()->for($roomType)->create();

        $tenant = User::factory()->create();
        $rental = Rental::factory()
            ->for($room)
            ->for($tenant, 'user')
            ->for($priceScheme)
            ->create(['status' => 'paid']);

        // Payment created automatically by factory

        $file = UploadedFile::fake()->image('ktp.jpg');
        $path = $file->store('rental-documents', 'private');

        $document = RentalDocument::factory()
            ->for($rental)
            ->create([
                'document_type' => 'KTP',
                'document_path' => $path,
            ]);

        // Guest tries to view document
        $response = $this->get(route('admin.rentals.documents.show', $document));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test tenant cannot view documents via admin route.
     */
    public function test_tenant_cannot_view_document_via_admin_route(): void
    {
        Storage::fake('private');

        // Create admin with kost
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->for($admin, 'owner')->approved()->create();
        $roomType = RoomType::factory()->for($kost)->create();
        $priceScheme = PriceScheme::factory()->for($roomType)->create();
        $room = Room::factory()->for($roomType)->create();

        // Create tenant with rental
        $tenant = User::factory()->create();
        $rental = Rental::factory()
            ->for($room)
            ->for($tenant, 'user')
            ->for($priceScheme)
            ->create(['status' => 'paid']);

        // Payment created automatically by factory

        $file = UploadedFile::fake()->image('ktp.jpg');
        $path = $file->store('rental-documents', 'private');

        $document = RentalDocument::factory()
            ->for($rental)
            ->create([
                'document_type' => 'KTP',
                'document_path' => $path,
            ]);

        // Tenant tries admin route (should be blocked by role:admin middleware)
        $response = $this->actingAs($tenant)
            ->get(route('admin.rentals.documents.show', $document));

        $response->assertForbidden();
    }
}
