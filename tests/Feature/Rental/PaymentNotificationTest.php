<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Mail\PaymentRejectedMail;
use App\Domain\Rental\Mail\PaymentVerifiedMail;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that email is sent when admin approves payment.
     *
     * Covers: FR-082 (Notifikasi Verifikasi Bayar)
     */
    public function test_email_sent_when_payment_approved(): void
    {
        Mail::fake();
        Storage::fake('private');

        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        // Upload payment proof
        $rental->payment->update([
            'proof_of_payment_path' => UploadedFile::fake()->image('proof.jpg')->store('payment-proofs', 'private'),
        ]);

        // Admin approves payment
        $response = $this->actingAs($admin)->post(
            route('admin.payments.approve', $rental->payment)
        );

        $response->assertRedirect();

        // Assert email was queued for tenant
        Mail::assertQueued(PaymentVerifiedMail::class, function ($mail) use ($tenant, $rental) {
            return $mail->hasTo($tenant->email) &&
                   $mail->rental->id === $rental->id;
        });
    }

    /**
     * Test that email is sent when admin rejects payment.
     *
     * Covers: FR-082 (Notifikasi Verifikasi Bayar)
     */
    public function test_email_sent_when_payment_rejected(): void
    {
        Mail::fake();
        Storage::fake('private');

        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $kost = Kost::factory()->create(['user_id' => $admin->id, 'status' => 'active']);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'room_id' => $room->id,
            'status' => 'pending',
        ]);

        // Upload payment proof
        $rental->payment->update([
            'proof_of_payment_path' => UploadedFile::fake()->image('proof.jpg')->store('payment-proofs', 'private'),
        ]);

        // Admin rejects payment
        $response = $this->actingAs($admin)->post(
            route('admin.payments.reject', $rental->payment),
            ['rejection_reason' => 'Bukti transfer tidak jelas']
        );

        $response->assertRedirect();

        // Assert email was queued for tenant
        Mail::assertQueued(PaymentRejectedMail::class, function ($mail) use ($tenant, $rental) {
            return $mail->hasTo($tenant->email) &&
                   $mail->rental->id === $rental->id;
        });
    }
}
