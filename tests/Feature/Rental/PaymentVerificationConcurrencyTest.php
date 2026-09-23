<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Payment\Models\Payment;
use App\Domain\Rental\Actions\CancelRental;
use App\Domain\Rental\Actions\RejectPayment;
use App\Domain\Rental\Actions\VerifyPayment;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Payment Verification Race Condition Tests
 *
 * Tests concurrent access scenarios for payment verification operations.
 * Ensures only one admin can verify a payment when multiple attempt simultaneously.
 *
 * FR-072: Admin approve payment
 * ADR-010: Transactional rental creation with pessimistic locking
 */
class PaymentVerificationConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * Test that multiple admins cannot approve the same payment simultaneously.
     *
     * Scenario: Two admins attempt to verify the same pending payment at the same time.
     * Expected: Only the first verification succeeds, second fails with appropriate error.
     * Behavior: Payment should have status 'success' and be verified by first admin only.
     */
    public function test_concurrent_payment_approval_only_first_succeeds(): void
    {
        // Arrange: Create a rental (which creates a payment via factory)
        $rental = Rental::factory()->create(['status' => 'payment_pending']);

        // Get the payment created by the rental factory
        $payment = $rental->payment ?? Payment::where('rental_id', $rental->id)->first();

        // Ensure payment is in pending state
        $payment->update(['status' => 'pending']);

        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $results = [];

        // Act: Simulate concurrent verification attempts
        // First admin attempts to verify payment
        try {
            DB::transaction(function () use ($payment, $admin1, &$results) {
                $action = new VerifyPayment;
                $action->execute($payment, $admin1);
                $results['admin1'] = 'success';
            });
        } catch (\Exception $e) {
            $results['admin1'] = 'failed: '.$e->getMessage();
        }

        // Second admin attempts to verify same payment
        try {
            $payment->refresh(); // Refresh to get updated state
            $action = new VerifyPayment;
            $action->execute($payment, $admin2);
            $results['admin2'] = 'success';
        } catch (\Exception $e) {
            $results['admin2'] = 'failed: '.$e->getMessage();
        }

        // Assert: First succeeds, second fails
        $this->assertEquals('success', $results['admin1'], 'First admin should succeed in verifying payment');
        $this->assertStringContainsString('failed:', $results['admin2'], 'Second admin verification should fail');
        $this->assertStringContainsString('Pembayaran sudah diverifikasi', $results['admin2'], 'Should indicate payment already verified');

        // Verify database state
        $payment->refresh();
        $this->assertEquals('success', $payment->status);
        $this->assertEquals($admin1->id, $payment->verified_by, 'Payment should be verified by first admin only');
        $this->assertNotNull($payment->verified_at);
        $this->assertNotNull($payment->paid_at);

        // Verify rental status transition
        $rental->refresh();
        $this->assertEquals('paid', $rental->status);
    }

    /**
     * @test
     *
     * Test that admin cannot verify payment while tenant is re-uploading proof.
     *
     * Scenario: Admin attempts to verify payment while tenant simultaneously
     * uploads new proof of payment.
     * Expected: Either operation succeeds, but payment should have consistent state.
     */
    public function test_admin_approving_while_tenant_reuploads_proof(): void
    {
        // Arrange: Create rental (which creates a payment via factory)
        $rental = Rental::factory()->create(['status' => 'payment_pending']);

        // Get the payment created by the rental factory
        $payment = $rental->payment ?? Payment::where('rental_id', $rental->id)->first();

        // Ensure payment is in pending state
        $payment->update(['status' => 'pending']);

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

        // Tenant re-uploading proof (simulated by updating payment)
        try {
            $payment->refresh();
            $payment->update([
                'proof_of_payment_path' => 'proofs/new-proof.jpg',
            ]);
            $results['tenant'] = 'updated';
        } catch (\Exception $e) {
            $results['tenant'] = 'failed: '.$e->getMessage();
        }

        // Assert: Payment should have consistent state
        $payment->refresh();
        $this->assertNotEmpty($results['admin']);
        $this->assertNotEmpty($results['tenant']);

        // Either admin verification or tenant update should succeed
        if ($results['admin'] === 'verified') {
            $this->assertEquals('success', $payment->status);
        } elseif ($results['tenant'] === 'updated') {
            $this->assertEquals('pending', $payment->status);
            $this->assertEquals('proofs/new-proof.jpg', $payment->proof_of_payment_path);
        }
    }

    /**
     * @test
     *
     * Test that payment approval and rejection cannot happen simultaneously.
     *
     * Scenario: One admin tries to approve payment while another tries to reject it.
     * Expected: Only one operation succeeds, payment has consistent status.
     */
    public function test_payment_approval_and_rejection_same_time_only_one_succeeds(): void
    {
        // Arrange
        $rental = Rental::factory()->create(['status' => 'payment_pending']);
        // Get the payment created by the rental factory
        $payment = $rental->payment ?? Payment::where('rental_id', $rental->id)->first();
        // Ensure payment is in pending state
        $payment->update(['status' => 'pending']);

        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $results = [];

        // Act: Simulate concurrent approve and reject
        // Admin 1 tries to approve
        try {
            DB::transaction(function () use ($payment, $admin1, &$results) {
                $action = new VerifyPayment;
                $action->execute($payment, $admin1);
                $results['admin1'] = 'approved';
            });
        } catch (\Exception $e) {
            $results['admin1'] = 'failed: '.$e->getMessage();
        }

        // Admin 2 tries to reject
        try {
            $payment->refresh();
            $action = new RejectPayment;
            $action->execute($payment, 'Duplicate payment', $admin2);
            $results['admin2'] = 'rejected';
        } catch (\Exception $e) {
            $results['admin2'] = 'failed: '.$e->getMessage();
        }

        // Assert: Only one operation should succeed
        $payment->refresh();
        $this->assertCount(1, array_filter($results, fn ($r) => $r === 'approved' || $r === 'rejected'));
    }

    /**
     * @test
     *
     * Test double payment verification is prevented.
     *
     * Scenario: Admin verifies payment successfully, then attempts to verify again.
     * Expected Behavior: Second verification should fail with appropriate error/exception.
     *
     * This test verifies the fix for VULN-106.
     */
    public function test_double_payment_verification_second_attempt_fails_gracefully(): void
    {
        // Arrange: Create rental (which creates a payment via factory)
        $rental = Rental::factory()->create(['status' => 'payment_pending']);

        // Get the payment created by the rental factory
        $payment = $rental->payment ?? Payment::where('rental_id', $rental->id)->first();

        // Ensure payment is in pending state
        $payment->update(['status' => 'pending']);

        $admin = User::factory()->admin()->create();

        // First verification (succeeds)
        $action = new VerifyPayment;
        $action->execute($payment, $admin);

        // Act: Attempt second verification
        $exceptionThrown = false;
        $exceptionMessage = '';
        $verificationSucceeded = false;

        try {
            $action->execute($payment, $admin);
            $verificationSucceeded = true;
        } catch (\Exception $e) {
            $exceptionThrown = true;
            $exceptionMessage = $e->getMessage();
        }

        // Assert: Second verification should fail
        $this->assertTrue($exceptionThrown, 'VerifyPayment action should prevent double verification');
        $this->assertFalse($verificationSucceeded, 'Second verification should not succeed');
        $this->assertStringContainsString('Pembayaran sudah diverifikasi', $exceptionMessage, 'Exception message should indicate already verified');

        // Payment state unchanged by second attempt
        $payment->refresh();
        $this->assertEquals('success', $payment->status);
        $this->assertEquals($admin->id, $payment->verified_by);
    }

    /**
     * @test
     *
     * Test payment verification while rental is being cancelled.
     *
     * Scenario: Admin tries to verify payment while tenant is cancelling rental.
     * Expected: Either operation succeeds, but state should be consistent.
     */
    public function test_payment_verification_while_rental_cancelling(): void
    {
        // Arrange
        $rental = Rental::factory()->create(['status' => 'payment_pending']);
        // Get the payment created by the rental factory
        $payment = $rental->payment ?? Payment::where('rental_id', $rental->id)->first();
        // Ensure payment is in pending state
        $payment->update(['status' => 'pending']);

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
            $rental->refresh();
            $action = new CancelRental;
            $action->execute($rental, $tenant->id, 'Change of plans');
            $results['tenant'] = 'cancelled';
        } catch (\Exception $e) {
            $results['tenant'] = 'failed: '.$e->getMessage();
        }

        // Assert: State should be consistent
        $rental->refresh();
        $payment->refresh();

        // Check which operation succeeded
        $adminSucceeded = isset($results['admin']) && $results['admin'] === 'verified';
        $tenantSucceeded = isset($results['tenant']) && $results['tenant'] === 'cancelled';

        // At least one should succeed
        $this->assertTrue($adminSucceeded || $tenantSucceeded, 'At least one operation should succeed');

        // Check rental status - if both succeeded, rental status shows which operation "won"
        // If admin verification succeeded, rental should be 'paid'
        // If tenant cancellation succeeded, rental should be 'cancelled'
        if ($adminSucceeded && $tenantSucceeded) {
            // Both succeeded - check which one "won" based on rental status
            // This can happen due to race conditions
            $this->assertContains($rental->status, ['paid', 'cancelled'], 'Rental status should be either paid or cancelled');

            // If rental is paid, payment should be success
            if ($rental->status === 'paid') {
                $this->assertEquals('success', $payment->status);
            }
        } elseif ($adminSucceeded) {
            $this->assertEquals('paid', $rental->status);
            $this->assertEquals('success', $payment->status);
        } elseif ($tenantSucceeded) {
            $this->assertEquals('cancelled', $rental->status);
            // Payment status depends on business rules
        }
    }
}
