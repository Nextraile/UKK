<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Rental\Models\Rental;
use App\Domain\RoomInventory\Models\Room;
use App\Domain\RoomInventory\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPaymentProofAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_payment_proof_for_own_kost_rental(): void
    {
        Storage::fake('private');

        // Create admin kost with rental
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        // Create fake payment proof file
        $file = UploadedFile::fake()->image('payment-proof.jpg');
        $path = $file->storeAs('payment-proofs', 'test-proof-admin.jpg', 'private');

        $rental->payment->update([
            'proof_of_payment_path' => $path,
        ]);

        // Verify file exists in storage
        Storage::disk('private')->assertExists($path);

        // Reload rental with relations for policy check
        $rental->load('room.roomType.kost');

        // Debug: verify ownership
        $this->assertEquals($admin->id, $rental->room->roomType->kost->user_id, 'Admin should own the kost');

        // Admin should be able to view payment proof
        $response = $this->actingAs($admin)->get(route('rentals.payment.proof', $rental));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_admin_cannot_view_payment_proof_for_other_kost_rental(): void
    {
        Storage::fake('private');

        // Create admin1 kost
        $admin1 = User::factory()->create(['role' => 'admin']);
        $kost1 = Kost::factory()->create(['user_id' => $admin1->id, 'status' => 'active']);
        $roomType1 = RoomType::factory()->create(['kost_id' => $kost1->id]);
        $room1 = Room::factory()->create(['room_type_id' => $roomType1->id]);

        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room1->id,
            'status' => 'pending',
        ]);

        // Create fake payment proof file
        $file = UploadedFile::fake()->image('payment-proof.jpg');
        $path = $file->store('payment-proofs', 'private');

        $rental->payment->update([
            'proof_of_payment_path' => $path,
        ]);

        // Admin2 (different kost owner) should NOT be able to view
        $admin2 = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin2)->get(route('rentals.payment.proof', $rental));

        $response->assertForbidden();
    }

    public function test_tenant_can_view_own_payment_proof(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        // Create fake payment proof file
        $file = UploadedFile::fake()->image('payment-proof.jpg');
        $path = $file->storeAs('payment-proofs', 'test-proof.jpg', 'private');

        $rental->payment->update([
            'proof_of_payment_path' => $path,
        ]);

        // Verify file exists in fake storage
        Storage::disk('private')->assertExists($path);

        // Tenant should be able to view own payment proof
        $response = $this->actingAs($tenant)->get(route('rentals.payment.proof', $rental));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_guest_cannot_view_payment_proof(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        // Create fake payment proof file
        $file = UploadedFile::fake()->image('payment-proof.jpg');
        $path = $file->store('payment-proofs', 'private');

        $rental->payment->update([
            'proof_of_payment_path' => $path,
        ]);

        // Guest should be redirected to login
        $response = $this->get(route('rentals.payment.proof', $rental));

        $response->assertRedirect(route('login'));
    }
}
