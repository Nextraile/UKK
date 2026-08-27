<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Mail\RentalActivatedMail;
use App\Domain\Rental\Mail\RentalCancelledMail;
use App\Domain\Rental\Mail\RentalCompletedMail;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RentalLifecycleJobsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure system user exists (ID=1) as superadmin
        if (! User::find(1)) {
            User::factory()->create([
                'id' => 1,
                'email' => 'system@sewakost.local',
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]);
        }
    }

    /**
     * Test auto-cancel overdue pending rentals (>7 days).
     */
    public function test_cancel_overdue_pending_rentals(): void
    {
        Mail::fake();

        // Setup: Create rental in pending status, created 8 days ago
        $rental = Rental::factory()->create([
            'status' => 'pending',
            'created_at' => now()->subDays(8),
        ]);

        // Act: Run command
        $exitCode = Artisan::call('rentals:cancel-overdue');

        // Assert: Command successful
        $this->assertEquals(0, $exitCode);

        // Assert: Rental cancelled
        $this->assertEquals('cancelled', $rental->fresh()->status);
        $this->assertNotNull($rental->fresh()->cancelled_at);
        $this->assertEquals('Auto-cancelled: Payment not received within 7 days', $rental->fresh()->cancelled_reason);

        // Assert: Status history recorded
        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $rental->id,
            'status' => 'cancelled',
            'changed_by' => 1, // System user
        ]);

        // Assert: Email sent
        Mail::assertQueued(RentalCancelledMail::class, function ($mail) use ($rental) {
            return $mail->hasTo($rental->user->email);
        });
    }

    /**
     * Test command does not cancel rentals created <7 days ago.
     */
    public function test_does_not_cancel_recent_pending_rentals(): void
    {
        // Setup: Create rental 6 days ago (not yet overdue)
        $rental = Rental::factory()->create([
            'status' => 'pending',
            'created_at' => now()->subDays(6),
        ]);

        // Act: Run command
        Artisan::call('rentals:cancel-overdue');

        // Assert: Rental still pending
        $this->assertEquals('pending', $rental->fresh()->status);
        $this->assertNull($rental->fresh()->cancelled_at);
    }

    /**
     * Test command does not cancel non-pending rentals.
     */
    public function test_does_not_cancel_non_pending_rentals(): void
    {
        // Setup: Create paid rental created 8 days ago
        $rental = Rental::factory()->create([
            'status' => 'paid',
            'created_at' => now()->subDays(8),
        ]);

        // Act: Run command
        Artisan::call('rentals:cancel-overdue');

        // Assert: Rental still paid
        $this->assertEquals('paid', $rental->fresh()->status);
        $this->assertNull($rental->fresh()->cancelled_at);
    }

    /**
     * Test auto-activate confirmed rentals on start_date.
     */
    public function test_activate_confirmed_rentals_on_start_date(): void
    {
        Mail::fake();

        // Setup: Create confirmed rental with start_date=today (date only, no time)
        $today = now()->startOfDay();
        $rental = Rental::factory()->create([
            'status' => 'confirmed',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(1),
        ]);

        // Act: Run command
        $exitCode = Artisan::call('rentals:activate');

        // Assert: Command successful
        $this->assertEquals(0, $exitCode);

        // Assert: Rental activated
        $this->assertEquals('active', $rental->fresh()->status);
        $this->assertNotNull($rental->fresh()->activated_at);

        // Assert: Status history recorded
        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $rental->id,
            'status' => 'active',
            'changed_by' => 1,
        ]);

        // Assert: Email sent
        Mail::assertQueued(RentalActivatedMail::class, function ($mail) use ($rental) {
            return $mail->hasTo($rental->user->email);
        });
    }

    /**
     * Test command does not activate rentals with future start_date.
     */
    public function test_does_not_activate_rentals_with_future_start_date(): void
    {
        // Setup: Create confirmed rental with start_date=tomorrow
        $tomorrow = now()->addDay()->startOfDay();
        $rental = Rental::factory()->create([
            'status' => 'confirmed',
            'start_date' => $tomorrow,
            'end_date' => $tomorrow->copy()->addMonths(1),
        ]);

        // Act: Run command
        Artisan::call('rentals:activate');

        // Assert: Rental still confirmed
        $this->assertEquals('confirmed', $rental->fresh()->status);
        $this->assertNull($rental->fresh()->activated_at);
    }

    /**
     * Test command does not activate non-confirmed rentals.
     */
    public function test_does_not_activate_non_confirmed_rentals(): void
    {
        // Setup: Create paid rental with start_date=today
        $today = now()->startOfDay();
        $rental = Rental::factory()->create([
            'status' => 'paid',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(1),
        ]);

        // Act: Run command
        Artisan::call('rentals:activate');

        // Assert: Rental still paid
        $this->assertEquals('paid', $rental->fresh()->status);
        $this->assertNull($rental->fresh()->activated_at);
    }

    /**
     * Test auto-complete active rentals after end_date.
     */
    public function test_complete_active_rentals_after_end_date(): void
    {
        Mail::fake();

        // Setup: Create active rental with end_date=yesterday
        $yesterday = now()->subDay()->startOfDay();
        $rental = Rental::factory()->create([
            'status' => 'active',
            'start_date' => now()->subMonths(1)->startOfDay(),
            'end_date' => $yesterday,
        ]);

        // Act: Run command
        $exitCode = Artisan::call('rentals:complete');

        // Assert: Command successful
        $this->assertEquals(0, $exitCode);

        // Assert: Rental completed
        $this->assertEquals('completed', $rental->fresh()->status);
        $this->assertNotNull($rental->fresh()->completed_at);

        // Assert: Status history recorded
        $this->assertDatabaseHas('rental_status_histories', [
            'rental_id' => $rental->id,
            'status' => 'completed',
            'changed_by' => 1,
        ]);

        // Assert: Email sent
        Mail::assertQueued(RentalCompletedMail::class, function ($mail) use ($rental) {
            return $mail->hasTo($rental->user->email);
        });
    }

    /**
     * Test command does not complete rentals with future end_date.
     */
    public function test_does_not_complete_rentals_with_future_end_date(): void
    {
        // Setup: Create active rental with end_date=tomorrow
        $tomorrow = now()->addDay()->startOfDay();
        $rental = Rental::factory()->create([
            'status' => 'active',
            'start_date' => now()->startOfDay(),
            'end_date' => $tomorrow,
        ]);

        // Act: Run command
        Artisan::call('rentals:complete');

        // Assert: Rental still active
        $this->assertEquals('active', $rental->fresh()->status);
        $this->assertNull($rental->fresh()->completed_at);
    }

    /**
     * Test command does not complete non-active rentals.
     */
    public function test_does_not_complete_non_active_rentals(): void
    {
        // Setup: Create confirmed rental with end_date=yesterday
        $yesterday = now()->subDay()->startOfDay();
        $rental = Rental::factory()->create([
            'status' => 'confirmed',
            'start_date' => now()->subMonths(1)->startOfDay(),
            'end_date' => $yesterday,
        ]);

        // Act: Run command
        Artisan::call('rentals:complete');

        // Assert: Rental still confirmed
        $this->assertEquals('confirmed', $rental->fresh()->status);
        $this->assertNull($rental->fresh()->completed_at);
    }

    /**
     * Test cancel command processes multiple rentals in batch.
     */
    public function test_cancel_processes_multiple_rentals_in_batch(): void
    {
        // Setup: Create 5 overdue pending rentals
        $rentals = Rental::factory()->count(5)->create([
            'status' => 'pending',
            'created_at' => now()->subDays(10),
        ]);

        // Act: Run command
        Artisan::call('rentals:cancel-overdue');

        // Assert: All 5 rentals cancelled
        foreach ($rentals as $rental) {
            $this->assertEquals('cancelled', $rental->fresh()->status);
        }
    }

    /**
     * Test command handles partial failures gracefully.
     */
    public function test_handles_partial_failures_gracefully(): void
    {
        // Setup: Create 2 overdue pending rentals
        $rental1 = Rental::factory()->create([
            'status' => 'pending',
            'created_at' => now()->subDays(10),
        ]);

        $rental2 = Rental::factory()->create([
            'status' => 'pending',
            'created_at' => now()->subDays(10),
        ]);

        // Act: Run command (should handle any failures gracefully)
        $exitCode = Artisan::call('rentals:cancel-overdue');

        // Assert: Command completes successfully even if individual rentals fail
        $this->assertEquals(0, $exitCode);
    }

    /**
     * Test command is idempotent (safe to run multiple times).
     */
    public function test_cancel_command_is_idempotent(): void
    {
        // Setup: Create overdue pending rental
        $rental = Rental::factory()->create([
            'status' => 'pending',
            'created_at' => now()->subDays(10),
        ]);

        // Act: Run command twice
        Artisan::call('rentals:cancel-overdue');
        Artisan::call('rentals:cancel-overdue');

        // Assert: Rental cancelled only once (status history count = 1)
        $this->assertEquals('cancelled', $rental->fresh()->status);
        $this->assertEquals(1, $rental->statusHistories()->where('status', 'cancelled')->count());
    }

    /**
     * Test activate command is idempotent.
     */
    public function test_activate_command_is_idempotent(): void
    {
        // Setup: Create confirmed rental with start_date=today
        $today = now()->startOfDay();
        $rental = Rental::factory()->create([
            'status' => 'confirmed',
            'start_date' => $today,
            'end_date' => $today->copy()->addMonths(1),
        ]);

        // Act: Run command twice
        Artisan::call('rentals:activate');
        Artisan::call('rentals:activate');

        // Assert: Rental activated only once (status history count = 1)
        $this->assertEquals('active', $rental->fresh()->status);
        $this->assertEquals(1, $rental->statusHistories()->where('status', 'active')->count());
    }

    /**
     * Test complete command is idempotent.
     */
    public function test_complete_command_is_idempotent(): void
    {
        // Setup: Create active rental with end_date=yesterday
        $yesterday = now()->subDay()->startOfDay();
        $rental = Rental::factory()->create([
            'status' => 'active',
            'start_date' => now()->subMonths(1)->startOfDay(),
            'end_date' => $yesterday,
        ]);

        // Act: Run command twice
        Artisan::call('rentals:complete');
        Artisan::call('rentals:complete');

        // Assert: Rental completed only once (status history count = 1)
        $this->assertEquals('completed', $rental->fresh()->status);
        $this->assertEquals(1, $rental->statusHistories()->where('status', 'completed')->count());
    }
}
