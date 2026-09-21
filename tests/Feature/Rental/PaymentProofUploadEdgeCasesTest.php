<?php

declare(strict_types=1);

namespace Tests\Feature\Rental;

use App\Domain\Identity\Models\User;
use App\Domain\Payment\Models\Payment;
use App\Domain\Rental\Models\Rental;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Edge case tests for payment proof upload failures and corrupted files.
 *
 * FR-070: Tenant upload proof
 * FR-075: Re-upload clears rejection_reason
 */
class PaymentProofUploadEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');

        // Force JSON responses for all tests (simulates AJAX requests)
        $this->withHeaders(['Accept' => 'application/json']);
    }

    /**
     * Test corrupted image file is rejected.
     *
     * Scenario: User uploads a file with valid image extension
     * but corrupted/invalid image data.
     */
    public function test_corrupted_image_file_rejected(): void
    {
        $user = User::factory()->create();
        $rental = Rental::factory()->create(['user_id' => $user->id]);

        // Create corrupted file (invalid image header)
        $corruptedFile = UploadedFile::fake()->create('payment.jpg', 100, 'image/jpeg');
        // Overwrite content with non-image data
        file_put_contents($corruptedFile->getRealPath(), 'Not an image at all');

        $response = $this->actingAs($user)
            ->postJson(route('tenant.rentals.payment.upload', $rental), [
                'payment_proof' => $corruptedFile,
            ]);

        // Validation should reject corrupted files
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_proof']);

        // Verify file was not stored
        $rental->refresh();
        $this->assertNull($rental->payment->proof_of_payment_path ?? null);
    }

    /**
     * Test empty file (0 bytes) is rejected.
     *
     * Scenario: User uploads an empty file.
     */
    public function test_empty_file_rejected(): void
    {
        $user = User::factory()->create();
        $rental = Rental::factory()->create(['user_id' => $user->id]);

        $emptyFile = UploadedFile::fake()->create('payment.jpg', 0, 'image/jpeg');

        $response = $this->actingAs($user)
            ->postJson(route('tenant.rentals.payment.upload', $rental), [
                'payment_proof' => $emptyFile,
            ]);

        // Empty files should be rejected by min:1 rule
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_proof']);

        // Verify file was not stored
        $rental->refresh();
        $this->assertNull($rental->payment->proof_of_payment_path ?? null);
    }

    /**
     * Test file with wrong extension (PHP disguised as JPG) is rejected.
     *
     * Scenario: User tries to upload a PHP file disguised as JPG.
     */
    public function test_php_disguised_as_image_rejected(): void
    {
        $user = User::factory()->create();
        $rental = Rental::factory()->create(['user_id' => $user->id]);

        $phpFile = UploadedFile::fake()->create('malicious.php.jpg', 100, 'image/jpeg');
        file_put_contents($phpFile->getRealPath(), '<?php echo "malicious"; ?>');

        $response = $this->actingAs($user)
            ->postJson(route('tenant.rentals.payment.upload', $rental), [
                'payment_proof' => $phpFile,
            ]);

        // PHP files disguised as images should be rejected
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_proof']);

        // Verify file was not stored
        $rental->refresh();
        $this->assertNull($rental->payment->proof_of_payment_path ?? null);
    }

    /**
     * Test file exceeding max size is rejected.
     *
     * Scenario: User uploads file larger than 5MB limit.
     */
    public function test_file_exceeding_max_size_rejected(): void
    {
        $user = User::factory()->create();
        $rental = Rental::factory()->create(['user_id' => $user->id]);

        // Create file larger than 5MB (5MB = 5242880 bytes)
        $largeFile = UploadedFile::fake()->image('payment.jpg')->size(5500000); // 5.5MB

        $response = $this->actingAs($user)
            ->postJson(route('tenant.rentals.payment.upload', $rental), [
                'payment_proof' => $largeFile,
            ]);

        // Files exceeding max size should be rejected
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_proof']);

        // Verify file was not stored
        $rental->refresh();
        $this->assertNull($rental->payment->proof_of_payment_path ?? null);
    }

    /**
     * Test malformed filename with special characters.
     *
     * Scenario: User uploads file with special characters in filename.
     */
    public function test_file_with_special_characters_filename_rejected(): void
    {
        $user = User::factory()->create();
        $rental = Rental::factory()->create(['user_id' => $user->id]);

        // Create file with special characters
        $specialFile = UploadedFile::fake()->image('payment_<>:"|?*.jpg');

        $response = $this->actingAs($user)
            ->postJson(route('tenant.rentals.payment.upload', $rental), [
                'payment_proof' => $specialFile,
            ]);

        // Files with special characters should be rejected or sanitized
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_proof']);

        // Verify file was not stored
        $rental->refresh();
        $this->assertNull($rental->payment->proof_of_payment_path ?? null);
    }

    /**
     * Test extremely small image (1x1 pixel) is rejected.
     *
     * Scenario: User uploads valid but tiny image.
     */
    public function test_extremely_small_image_rejected(): void
    {
        $user = User::factory()->create();
        $rental = Rental::factory()->create(['user_id' => $user->id]);

        $tinyFile = UploadedFile::fake()->image('tiny.jpg', 1, 1);

        $response = $this->actingAs($user)
            ->postJson(route('tenant.rentals.payment.upload', $rental), [
                'payment_proof' => $tinyFile,
            ]);

        // Extremely small images should be rejected
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_proof']);

        // Verify file was not stored
        $rental->refresh();
        $this->assertNull($rental->payment->proof_of_payment_path ?? null);
    }

    /**
     * Test upload while rental is not in pending status.
     *
     * Scenario: User tries to upload payment proof for non-pending rental.
     */
    public function test_upload_non_pending_rental_rejected(): void
    {
        $user = User::factory()->create();
        $rental = Rental::factory()->create([
            'user_id' => $user->id,
            'status' => 'active', // Not pending
        ]);

        // Use a valid JPEG file to pass validation (authorization check happens in controller)
        $validFile = UploadedFile::fake()->image('payment.jpg', 800, 600);

        $response = $this->actingAs($user)
            ->postJson(route('tenant.rentals.payment.upload', $rental), [
                'payment_proof' => $validFile,
            ]);

        // Authorization check happens after validation, so it depends on file validity
        // If file passes validation, should get 403 (forbidden)
        // If file fails validation, should get 422 (validation error)
        $this->assertContains($response->status(), [403, 422]);

        // Verify file was not stored regardless
        $rental->refresh();
        $this->assertNull($rental->payment->proof_of_payment_path ?? null);
    }
}
