<?php

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_upload_proof_of_payment(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $rental = Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'pending']);

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->actingAs($tenant)->post(
            route('rentals.payment.upload', $rental),
            ['proof' => $file]
        );

        $response->assertRedirect(route('rentals.show', $rental));
        $this->assertDatabaseHas('payments', [
            'rental_id' => $rental->id,
        ]);

        $rental->payment->refresh();
        $this->assertNotNull($rental->payment->proof_of_payment_path);
        $this->assertTrue(Storage::disk('private')->exists($rental->payment->proof_of_payment_path));
    }

    public function test_admin_can_approve_payment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create(['room_id' => $room->id, 'status' => 'pending']);

        $rental->payment->update(['proof_of_payment_path' => 'proof.jpg']);

        $response = $this->actingAs($admin)->post(
            route('admin.payments.approve', $rental->payment)
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'id' => $rental->payment->id,
            'status' => 'success',
            'verified_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $rental->id,
            'status' => 'paid',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_reject_payment_with_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create(['room_id' => $room->id, 'status' => 'pending']);

        $response = $this->actingAs($admin)->post(
            route('admin.payments.reject', $rental->payment),
            ['rejection_reason' => 'Bukti transfer tidak jelas']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'id' => $rental->payment->id,
            'rejection_reason' => 'Bukti transfer tidak jelas',
        ]);
    }

    public function test_re_upload_clears_rejection_reason(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $rental = Rental::factory()->create(['user_id' => $tenant->id, 'status' => 'pending']);
        $rental->payment->update(['rejection_reason' => 'Old reason']);

        $file = UploadedFile::fake()->image('proof2.jpg');

        $this->actingAs($tenant)->post(
            route('rentals.payment.upload', $rental),
            ['proof' => $file]
        );

        $this->assertDatabaseHas('payments', [
            'rental_id' => $rental->id,
            'rejection_reason' => null,
        ]);
    }

    public function test_rejection_reason_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create(['room_id' => $room->id, 'status' => 'pending']);

        $response = $this->actingAs($admin)->post(
            route('admin.payments.reject', $rental->payment),
            ['rejection_reason' => ''] // Empty reason
        );

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_rejection_reason_must_be_at_least_10_characters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create(['room_id' => $room->id, 'status' => 'pending']);

        $response = $this->actingAs($admin)->post(
            route('admin.payments.reject', $rental->payment),
            ['rejection_reason' => 'Short'] // Less than 10 characters
        );

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_only_kost_owner_can_verify_payment(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $kost = Kost::factory()->create(['user_id' => $admin1->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create(['room_id' => $room->id, 'status' => 'pending']);

        $rental->payment->update(['proof_of_payment_path' => 'proof.jpg']);

        // Admin2 tries to approve admin1's kost payment
        $response = $this->actingAs($admin2)->post(
            route('admin.payments.approve', $rental->payment)
        );

        $response->assertForbidden();
    }

    /**
     * Test tenant can view payment page with QRIS and bank info.
     *
     * Covers: FR-069 (Display QRIS + Bank Info)
     */
    public function test_tenant_can_view_payment_page_with_qris_and_bank_info(): void
    {
        Storage::fake('private');

        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin']);

        // Create kost with payment config
        $kost = Kost::factory()->create([
            'user_id' => $admin->id,
            'status' => 'active',
            'qris_image_path' => UploadedFile::fake()->image('qris.png')->store('qris', 'private'),
            'bank_name' => 'Bank BCA',
            'account_number' => '1234567890',
            'account_holder_name' => 'John Doe',
        ]);

        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        // Act: Tenant views payment page
        $response = $this->actingAs($tenant)->get(route('rentals.payment.show', $rental));

        // Assert: Page displays payment info
        $response->assertOk()
            ->assertSee('Rp '.number_format($rental->grand_total, 0, ',', '.'))
            ->assertSee('QRIS')
            ->assertSee('Bank BCA')
            ->assertSee('1234567890')
            ->assertSee('John Doe');
    }

    /**
     * Test tenant can view payment rejection reason.
     *
     * Covers: FR-074 (Reject Bukti Pembayaran - tenant notification)
     */
    public function test_tenant_can_view_payment_rejection_reason(): void
    {
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin']);

        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);

        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        // Admin rejects payment with reason
        $rental->payment->update([
            'rejection_reason' => 'Bukti tidak jelas, mohon upload ulang dengan kualitas lebih baik',
        ]);

        // Act: Tenant views payment page
        $response = $this->actingAs($tenant)->get(route('rentals.payment.show', $rental));

        // Assert: Rejection reason displayed
        $response->assertOk()
            ->assertSee('Bukti tidak jelas, mohon upload ulang dengan kualitas lebih baik');
    }
}
