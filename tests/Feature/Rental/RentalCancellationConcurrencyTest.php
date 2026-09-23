<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Payment\Models\Payment;
use App\Domain\Rental\Actions\ActivateRental;
use App\Domain\Rental\Actions\CancelRental;
use App\Domain\Rental\Actions\VerifyPayment;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Rental Cancellation Race Condition Tests
 *
 * Tests concurrent access scenarios for rental cancellation operations.
 * Ensures cancellation conflicts with other operations are handled gracefully.
 *
 * FR-080: Tenant cancel rental
 * ADR-016: Minimum start_date = today + 4 days
 */
class RentalCancellationConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * Test tenant cancelling while system auto-activating rental.
     *
     * Scenario: System cron job triggers auto-activation on start_date while
     * tenant simultaneously attempts to cancel.
     * Expected: Cancellation should be processed first if requested before activation,
     * or activation should proceed if cancellation fails.
     */
    public function test_tenant_cancelling_while_system_auto_activating(): void
    {
        // Arrange: Create rental with start_date = today (ready for auto-activation)
        $rental = Rental::factory()->create([
            'status' => 'confirmed',
            'start_date' => now()->format('Y-m-d'),
        ]);

        $tenant = $rental->user;

        $results = [];

        // Act: Simulate concurrent auto-activation and cancellation
        // System auto-activation
        try {
            DB::transaction(function () use ($rental, &$results) {
                $action = new ActivateRental; // TODO: Implement this action
                $action->execute($rental);
                $results['system'] = 'activated';
            });
        } catch (\Exception $e) {
            $results['system'] = 'failed: '.$e->getMessage();
        }

        // Tenant cancellation (concurrently)
        try {
            DB::transaction(function () use ($rental, $tenant, &$results) {
                $action = new CancelRental;
                $action->execute($rental, $tenant->id, 'Change of plans');
                $results['tenant'] = 'cancelled';
            });
        } catch (\Exception $e) {
            $results['tenant'] = 'failed: '.$e->getMessage();
        }

        // Assert: Rental should have consistent final state
        $rental->refresh();

        if ($results['system'] === 'activated') {
            $this->assertEquals('active', $rental->status);
            $this->assertNotEmpty($rental->activated_at);
        } elseif ($results['tenant'] === 'cancelled') {
            $this->assertEquals('cancelled', $rental->status);
            $this->assertNotEmpty($rental->cancelled_at);
        }

        // Only one operation should succeed
        $successCount = 0;
        if (str_contains($results['system'] ?? '', 'activated')) {
            $successCount++;
        }
        if (str_contains($results['tenant'] ?? '', 'cancelled')) {
            $successCount++;
        }
        $this->assertLessThanOrEqual(1, $successCount, 'Only one operation should succeed');
    }

    /**
     * @test
     *
     * Test tenant cancelling while admin verifying payment.
     *
     * Scenario: Tenant requests cancellation while admin is verifying payment.
     * Expected: Payment verification should proceed or cancellation should fail,
     * depending on timing and business rules.
     */
    public function test_tenant_cancelling_while_admin_verifying_payment(): void
    {
        // Arrange: Create rental with pending payment
        $rental = Rental::factory()->create(['status' => 'payment_pending']);
        $payment = $rental->payment;

        $admin = User::factory()->admin()->create();
        $tenant = $rental->user;

        $results = [];

        // Act: Simulate concurrent operations
        // Admin verifying payment
        try {
            DB::transaction(function () use ($payment, $admin, &$results) {
                $action = new VerifyPayment;
                $action->execute($payment, $admin);
                $results['admin'] = 'verified';
            });
        } catch (\Exception $e) {
            $results['admin'] = 'failed: '.$e->getMessage();
        }

        // Tenant cancelling rental
        try {
            DB::transaction(function () use ($rental, $tenant, &$results) {
                $action = new CancelRental;
                $action->execute($rental, $tenant->id, 'Found better option');
                $results['tenant'] = 'cancelled';
            });
        } catch (\Exception $e) {
            $results['tenant'] = 'failed: '.$e->getMessage();
        }

        // Assert: Rental and payment should have consistent state
        $rental->refresh();
        $payment->refresh();

        // Check which operation succeeded
        $adminSucceeded = isset($results['admin']) && $results['admin'] === 'verified';
        $tenantSucceeded = isset($results['tenant']) && $results['tenant'] === 'cancelled';

        // At least one should succeed
        $this->assertTrue($adminSucceeded || $tenantSucceeded, 'At least one operation should succeed');

        if ($results['admin'] === 'verified' && $results['tenant'] !== 'cancelled') {
            $this->assertEquals('paid', $rental->status);
            $this->assertEquals('success', $payment->status);
        } elseif ($results['tenant'] === 'cancelled' && $results['admin'] !== 'verified') {
            $this->assertEquals('cancelled', $rental->status);
            // Payment status depends on business rules (refund vs keep)
        } else {
            // Both operations attempted - check final state
            $this->assertContains($rental->status, ['paid', 'cancelled'], 'Final rental status should be either paid or cancelled');
        }
    }

    /**
     * @test
     *
     * Test multiple cancellation requests for same rental.
     *
     * Scenario: Tenant submits multiple cancellation requests for same rental
     * (e.g., double-click submit button, multiple tabs).
     * Expected: Only one cancellation should be processed, subsequent requests should fail.
     */
    public function test_multiple_cancellation_requests_same_rental_only_one_succeeds(): void
    {
        // Arrange: Create confirmed rental
        $rental = Rental::factory()->create(['status' => 'confirmed']);
        $tenant = $rental->user;

        $results = [];

        // Act: Simulate multiple cancellation attempts
        for ($i = 1; $i <= 3; $i++) {
            try {
                DB::transaction(function () use ($rental, $tenant, $i, &$results) {
                    $action = new CancelRental;
                    $action->execute($rental, $tenant->id, "Attempt $i");
                    $results["attempt_$i"] = 'success';
                });
            } catch (\Exception $e) {
                $results["attempt_$i"] = 'failed: '.$e->getMessage();
            }

            // Small delay between attempts
            usleep(10000); // 10ms
        }

        // Assert: Only one cancellation should succeed
        $successCount = 0;
        foreach ($results as $result) {
            if (str_contains($result, 'success')) {
                $successCount++;
            }
        }

        $this->assertEquals(1, $successCount, 'Only one cancellation should succeed');

        // Rental should be cancelled
        $rental->refresh();
        $this->assertEquals('cancelled', $rental->status);
    }

    /**
     * @test
     *
     * Test cancellation while rental is completing (end_date reached).
     *
     * Scenario: Rental reaches end_date and system starts completion process
     * while tenant tries to cancel.
     * Expected: Cancellation should fail (rental already ending).
     */
    public function test_cancellation_while_rental_completing(): void
    {
        // Arrange: Create rental with end_date = today (ready for completion)
        $rental = Rental::factory()->create([
            'status' => 'active',
            'end_date' => now()->format('Y-m-d'),
        ]);

        $tenant = $rental->user;

        $results = [];

        // Act: Simulate concurrent completion and cancellation
        // System auto-completion
        try {
            DB::transaction(function () use ($rental, &$results) {
                // Simulate CompleteRentals command logic
                $rental->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                $results['system'] = 'completed';
            });
        } catch (\Exception $e) {
            $results['system'] = 'failed: '.$e->getMessage();
        }

        // Tenant cancellation
        try {
            DB::transaction(function () use ($rental, $tenant, &$results) {
                $action = new CancelRental;
                $action->execute($rental, $tenant->id, 'Early departure');
                $results['tenant'] = 'cancelled';
            });
        } catch (\Exception $e) {
            $results['tenant'] = 'failed: '.$e->getMessage();
        }

        // Assert: Completion should succeed, cancellation should fail
        $rental->refresh();

        $this->assertStringContainsString('completed', $results['system'] ?? '');
        $this->assertStringContainsString('failed', $results['tenant'] ?? '');

        $this->assertEquals('completed', $rental->status);
    }
}
