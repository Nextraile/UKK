<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Edge case tests for QRIS upload failures and corrupted files.
 *
 * FR-030: QRIS image upload with validation
 */
class QrisUploadEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test corrupted PNG/JPG file is rejected.
     *
     * Scenario: Admin uploads corrupted QRIS image file.
     */
    public function test_corrupted_qris_image_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Create corrupted QRIS image file
        $corruptedFile = UploadedFile::fake()->create('qris.png', 100, 'image/png');
        // Overwrite content with non-image data
        file_put_contents($corruptedFile->getRealPath(), 'Not a PNG at all');

        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.payment.update', $kost), [
                'qris_image' => $corruptedFile,
                'bank_name' => 'Bank Test',
                'account_number' => '1234567890',
                'account_holder_name' => 'John Doe',
            ]);

        // QRIS upload validation should reject corrupted images
        $response->assertSessionHasErrors('qris_image');
        Storage::disk('public')->assertMissing('qris/');
    }

    /**
     * Test file with no image data (1x1 pixel) is accepted.
     *
     * Scenario: Admin uploads tiny but valid QRIS image.
     */
    public function test_extremely_small_qris_image_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $tinyFile = UploadedFile::fake()->image('qris.jpg', 1, 1);

        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.payment.update', $kost), [
                'qris_image' => $tinyFile,
                'bank_name' => 'Bank Test',
                'account_number' => '1234567890',
                'account_holder_name' => 'John Doe',
            ]);

        $response->assertSessionHasErrors('qris_image');
        $kost->refresh();

        $this->assertNull($kost->qris_image_path);
    }

    /**
     * Test malformed filename with special characters.
     *
     * Scenario: Admin uploads QRIS image with special characters in filename.
     */
    public function test_malformed_filename_with_special_characters(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $specialFile = UploadedFile::fake()->image('qris_<>:"|?*.jpg', 300, 300)->size(500);

        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.payment.update', $kost), [
                'qris_image' => $specialFile,
                'bank_name' => 'Bank Test',
                'account_number' => '1234567890',
                'account_holder_name' => 'John Doe',
            ]);

        // Filename should be sanitized or rejected
        $response->assertRedirect();
        $kost->refresh();

        if ($kost->qris_image_path) {
            // Filename was sanitized (should follow pattern: qris/{uuid}.jpg)
            $this->assertMatchesRegularExpression('/^qris\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(jpg|jpeg|png)$/', $kost->qris_image_path);
        }
    }

    /**
     * Test upload while kost is under review fails.
     *
     * Scenario: Admin tries to upload QRIS while kost is pending review.
     */
    public function test_upload_while_kost_under_review_fails(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create([
            'user_id' => $admin->id,
            'status' => 'pending_review', // Under review
        ]);

        $validFile = UploadedFile::fake()->image('qris.jpg', 300, 300)->size(500);

        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.payment.update', $kost), [
                'qris_image' => $validFile,
                'bank_name' => 'Bank Test',
                'account_number' => '1234567890',
                'account_holder_name' => 'John Doe',
            ]);

        $response->assertForbidden();
        Storage::disk('public')->assertMissing('qris/');
    }

    /**
     * Test duplicate simultaneous uploads only last succeeds.
     *
     * Scenario: Admin uploads QRIS image multiple times quickly.
     */
    public function test_duplicate_simultaneous_uploads_only_last_succeeds(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $file1 = UploadedFile::fake()->image('qris1.jpg', 300, 300)->size(500);
        $file2 = UploadedFile::fake()->image('qris2.jpg', 300, 300)->size(500);

        // Simulate concurrent uploads
        $response1 = $this->actingAs($admin)
            ->patch(route('admin.kosts.payment.update', $kost), [
                'qris_image' => $file1,
                'bank_name' => 'Bank Test',
                'account_number' => '1234567890',
                'account_holder_name' => 'John Doe',
            ]);

        $response2 = $this->actingAs($admin)
            ->patch(route('admin.kosts.payment.update', $kost), [
                'qris_image' => $file2,
                'bank_name' => 'Bank Test',
                'account_number' => '1234567890',
                'account_holder_name' => 'Jane Doe',
            ]);

        // Both should succeed (last one wins)
        $response1->assertRedirect();
        $response2->assertRedirect();

        $kost->refresh();
        $this->assertNotNull($kost->qris_image_path);

        // Only one file should be stored (latest)
        $files = Storage::disk('public')->files('qris');
        $this->assertCount(1, $files);

        // Bank info should be from last update
        $this->assertEquals('Jane Doe', $kost->account_holder_name);
    }

    /**
     * Test non-image file disguised as image is rejected.
     *
     * Scenario: Admin uploads text file with .jpg extension.
     */
    public function test_non_image_file_disguised_as_image_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $textFile = UploadedFile::fake()->create('qris.jpg', 100, 'text/plain');

        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.payment.update', $kost), [
                'qris_image' => $textFile,
                'bank_name' => 'Bank Test',
                'account_number' => '1234567890',
                'account_holder_name' => 'John Doe',
            ]);

        $response->assertSessionHasErrors('qris_image');
        Storage::disk('public')->assertMissing('qris/');
    }

    /**
     * Test file exceeding max size is rejected.
     *
     * Scenario: Admin uploads QRIS image larger than 2MB limit.
     */
    public function test_qris_image_exceeding_max_size_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Create file larger than 2MB (2MB = 2048KB)
        $largeFile = UploadedFile::fake()->image('qris.jpg')->size(2500000); // 2.5MB

        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.payment.update', $kost), [
                'qris_image' => $largeFile,
                'bank_name' => 'Bank Test',
                'account_number' => '1234567890',
                'account_holder_name' => 'John Doe',
            ]);

        $response->assertSessionHasErrors('qris_image');
        Storage::disk('public')->assertMissing('qris/'.basename($largeFile->getPathname()));
    }
}
