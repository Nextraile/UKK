<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Actions\CancelRental;
use App\Domain\Rental\Exceptions\InvalidRentalStatusException;
use App\Domain\Rental\Mail\RentalCancelledAdminNotificationMail;
use App\Domain\Rental\Mail\RentalCancelledMail;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalStatusHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RentalCancellationTest extends TestCase
{
    use RefreshDatabase;

    private User $tenant;

    private User $admin;

    private Kost $kost;

    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // Create admin with kost
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $category = Category::factory()->create();

        $this->kost = Kost::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'active',
        ]);
        $this->kost->categories()->attach($category->id);

        $roomType = RoomType::factory()->create([
            'kost_id' => $this->kost->id,
        ]);

        $this->room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'status' => 'available',
        ]);
    }

    /**
     * Test tenant can cancel rental in pending status.
     */
    public function test_tenant_can_cancel_pending_rental(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'pending',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $action = new CancelRental;
        $result = $action->execute($rental, $this->tenant->id, 'Changed my mind');

        $this->assertEquals('cancelled', $result->status);
        $this->assertDatabaseHas('rentals', [
            'id' => $rental->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Test tenant can cancel rental in paid status.
     */
    public function test_tenant_can_cancel_paid_rental(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'paid',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $action = new CancelRental;
        $result = $action->execute($rental, $this->tenant->id);

        $this->assertEquals('cancelled', $result->status);
    }

    /**
     * Test tenant can cancel rental in documents_pending status.
     */
    public function test_tenant_can_cancel_documents_pending_rental(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'documents_pending',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $action = new CancelRental;
        $result = $action->execute($rental, $this->tenant->id);

        $this->assertEquals('cancelled', $result->status);
    }

    /**
     * Test tenant can cancel rental in confirmed status.
     */
    public function test_tenant_can_cancel_confirmed_rental(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $action = new CancelRental;
        $result = $action->execute($rental, $this->tenant->id);

        $this->assertEquals('cancelled', $result->status);
    }

    /**
     * Test tenant cannot cancel active rental.
     */
    public function test_tenant_cannot_cancel_active_rental(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'active',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(1),
        ]);

        $this->expectException(InvalidRentalStatusException::class);

        $action = new CancelRental;
        $action->execute($rental, $this->tenant->id);
    }

    /**
     * Test tenant cannot cancel completed rental.
     */
    public function test_tenant_cannot_cancel_completed_rental(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'completed',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonths(1),
        ]);

        $this->expectException(InvalidRentalStatusException::class);

        $action = new CancelRental;
        $action->execute($rental, $this->tenant->id);
    }

    /**
     * Test tenant cannot cancel already cancelled rental.
     */
    public function test_tenant_cannot_cancel_already_cancelled_rental(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'cancelled',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $this->expectException(InvalidRentalStatusException::class);

        $action = new CancelRental;
        $action->execute($rental, $this->tenant->id);
    }

    /**
     * Test tenant cannot cancel after start_date has passed.
     */
    public function test_tenant_cannot_cancel_after_start_date_passed(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addMonths(1),
        ]);

        $this->expectException(InvalidRentalStatusException::class);
        $this->expectExceptionMessage('tanggal mulai sudah terlewat');

        $action = new CancelRental;
        $action->execute($rental, $this->tenant->id);
    }

    /**
     * Test cancellation records status history.
     */
    public function test_cancellation_records_status_history(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $action = new CancelRental;
        $action->execute($rental, $this->tenant->id, 'Personal emergency');

        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $rental->id,
            'status' => 'cancelled',
            'changed_by' => $this->tenant->id,
        ]);

        $history = RentalStatusHistory::where('rental_id', $rental->id)
            ->where('status', 'cancelled')
            ->first();

        $this->assertStringContainsString('Personal emergency', $history->internal_notes);
    }

    /**
     * Test cancellation sends tenant notification email.
     */
    public function test_cancellation_sends_tenant_email(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $action = new CancelRental;
        $action->execute($rental, $this->tenant->id);

        Mail::assertQueued(RentalCancelledMail::class, function ($mail) use ($rental) {
            return $mail->hasTo($this->tenant->email) &&
                   $mail->rental->id === $rental->id;
        });
    }

    /**
     * Test cancellation sends admin notification email.
     */
    public function test_cancellation_sends_admin_notification_email(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $action = new CancelRental;
        $action->execute($rental, $this->tenant->id);

        Mail::assertQueued(RentalCancelledAdminNotificationMail::class, function ($mail) use ($rental) {
            return $mail->hasTo($this->admin->email) &&
                   $mail->rental->id === $rental->id;
        });
    }

    /**
     * Test cancellation with reason includes reason in notes.
     */
    public function test_cancellation_with_reason_records_notes(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $reason = 'Found a better place nearby';
        $action = new CancelRental;
        $action->execute($rental, $this->tenant->id, $reason);

        $history = RentalStatusHistory::where('rental_id', $rental->id)
            ->where('status', 'cancelled')
            ->first();

        $this->assertStringContainsString($reason, $history->internal_notes);
        $this->assertStringContainsString('Dibatalkan oleh tenant', $history->internal_notes);
    }

    /**
     * Test cancellation without reason still works.
     */
    public function test_cancellation_without_reason_works(): void
    {
        Mail::fake();

        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $action = new CancelRental;
        $result = $action->execute($rental, $this->tenant->id, null);

        $this->assertEquals('cancelled', $result->status);

        $history = RentalStatusHistory::where('rental_id', $rental->id)
            ->where('status', 'cancelled')
            ->first();

        $this->assertEquals('Dibatalkan oleh tenant.', $history->internal_notes);
    }

    /**
     * Test tenant cannot cancel other user's rental.
     */
    public function test_tenant_cannot_cancel_other_users_rental(): void
    {
        Mail::fake();

        $otherTenant = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $rental = Rental::factory()->create([
            'user_id' => $otherTenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not belong to you');

        $action = new CancelRental;
        $action->execute($rental, $this->tenant->id);
    }

    /**
     * Test guest cannot cancel rental.
     */
    public function test_guest_cannot_cancel_rental(): void
    {
        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $response = $this->post(route('rentals.cancel', $rental));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test cancel button visible for cancellable rentals.
     */
    public function test_cancel_button_visible_for_cancellable_rentals(): void
    {
        $rental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.show', $rental));

        $response->assertStatus(200);
        $response->assertSee('Batalkan Rental');
        $response->assertSee(route('rentals.cancel.form', $rental));
    }

    /**
     * Test cancel button hidden for non-cancellable rentals.
     */
    public function test_cancel_button_hidden_for_non_cancellable_rentals(): void
    {
        // Test active rental
        $activeRental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'active',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addMonths(1),
        ]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.show', $activeRental));
        $response->assertStatus(200);
        $response->assertDontSee(route('rentals.cancel.form', $activeRental));

        // Test completed rental
        $completedRental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'completed',
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonths(1),
        ]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.show', $completedRental));
        $response->assertStatus(200);
        $response->assertDontSee(route('rentals.cancel.form', $completedRental));

        // Test cancelled rental
        $cancelledRental = Rental::factory()->create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => 'cancelled',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addMonths(1)->addDays(10),
        ]);

        $response = $this->actingAs($this->tenant)->get(route('rentals.show', $cancelledRental));
        $response->assertStatus(200);
        $response->assertDontSee(route('rentals.cancel.form', $cancelledRental));
    }
}
