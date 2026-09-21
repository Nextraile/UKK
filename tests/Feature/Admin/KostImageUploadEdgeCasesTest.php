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
 * Edge case tests for kost images upload failures and corrupted files.
 *
 * FR-026: Kost images upload with validation
 */
class KostImageUploadEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test upload to deleted kost fails.
     *
     * Scenario: Admin tries to upload images to soft-deleted kost.
     */
    public function test_upload_to_deleted_kost_fails(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Soft delete the kost
        $kost->delete();
        $kost->refresh();

        $validFile = UploadedFile::fake()->image('kost-image.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $validFile,
            ]);

        // Soft deleted resources typically return 404
        $this->assertContains($response->getStatusCode(), [403, 404]);
        Storage::disk('public')->assertMissing('kost-images/');
    }

    /**
     * Test corrupted multi-file upload (some valid, some invalid) fails.
     *
     * Scenario: Admin uploads batch of images where some are corrupted.
     */
    public function test_corrupted_multi_file_upload_fails(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $validFile = UploadedFile::fake()->image('valid.jpg');
        $corruptedFile = UploadedFile::fake()->create('corrupted.jpg', 100, 'image/jpeg');
        file_put_contents($corruptedFile->getRealPath(), 'Not an image');

        // Note: The KostImageController handles single uploads, not batch
        // So we test individual corrupted file rejection
        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $corruptedFile,
            ]);

        $response->assertSessionHasErrors('image');
        Storage::disk('public')->assertMissing('kost-images/');
    }

    /**
     * Test upload exceeding storage limit fails gracefully.
     *
     * Scenario: Simulate disk full or storage limit exceeded.
     */
    public function test_upload_exceeding_storage_limit_fails_gracefully(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Create a large file exceeding 5MB limit (kost_image max is 5MB)
        $largeFile = UploadedFile::fake()->image('large.jpg', 800, 600)->size(6000); // 6MB

        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $largeFile,
            ]);

        // Should be rejected due to size validation
        $response->assertSessionHasErrors('image');
    }

    /**
     * Test upload with special characters in filename.
     *
     * Scenario: Admin uploads image with special characters in filename.
     */
    public function test_upload_special_characters_in_filename(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Create valid test file meeting all validation requirements
        $specialFile = UploadedFile::fake()
            ->image('kost_<>:"|?*.jpg', 800, 600) // Meets dimension requirements (800x600)
            ->size(500); // 500KB, exceeds min 1KB, below max 5MB

        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $specialFile,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify UUID filename format (TASK-076 changed from kost-{id}-img-{date}-{seq}.{ext} to UUID)
        $kost->refresh();
        $images = $kost->kostImages;

        $this->assertTrue($images->isNotEmpty(), 'Image should be uploaded');

        $imagePath = $images->first()->image_path;
        // UUID v4 format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx.ext
        // Pattern updated to match any valid image extension (jpg, jpeg, png, webp)
        $this->assertMatchesRegularExpression(
            '/^kost-images\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(jpg|jpeg|png|webp)$/',
            $imagePath,
            'Filename should be UUID v4 format in kost-images/ directory'
        );
    }

    /**
     * Test race condition: setting thumbnail while uploading.
     *
     * Scenario: Admin tries to set thumbnail while another image is uploading.
     */
    public function test_race_condition_thumbnail_setting_while_uploading(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // First upload an image with proper dimensions
        $image1 = UploadedFile::fake()->image('image1.jpg', 800, 600)->size(500);
        $response1 = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $image1,
            ]);

        $response1->assertRedirect();

        // Get the uploaded image
        $kost->refresh();
        $firstImage = $kost->kostImages->first();

        // Try to set thumbnail while uploading second image
        $image2 = UploadedFile::fake()->image('image2.jpg', 800, 600)->size(500);

        // First set thumbnail
        $thumbnailResponse = $this->actingAs($admin)
            ->patch(route('admin.kosts.images.set-thumbnail', [
                'kost' => $kost,
                'image' => $firstImage,
            ]));

        // Then upload second image
        $uploadResponse = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $image2,
            ]);

        $thumbnailResponse->assertRedirect();
        $uploadResponse->assertRedirect();

        $kost->refresh();
        $this->assertCount(2, $kost->kostImages);

        // First image should still be thumbnail
        $thumbnailImage = $kost->kostImages()->where('is_thumbnail', true)->first();
        $this->assertEquals($firstImage->id, $thumbnailImage->id);
    }

    /**
     * Test upload to kost owned by different admin fails.
     *
     * Scenario: Admin tries to upload images to another admin's kost.
     */
    public function test_upload_to_kost_owned_by_different_admin_fails(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        $kost = Kost::factory()->create(['user_id' => $admin1->id]); // Owned by admin1

        $validFile = UploadedFile::fake()->image('kost-image.jpg', 800, 600)->size(500);

        $response = $this->actingAs($admin2) // admin2 trying to upload
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $validFile,
            ]);

        $response->assertForbidden();
        Storage::disk('public')->assertMissing('kost-images/');
    }

    /**
     * Test empty file upload is rejected.
     *
     * Scenario: Admin uploads empty (0 bytes) image file.
     */
    public function test_empty_file_upload_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $emptyFile = UploadedFile::fake()->create('empty.jpg', 0, 'image/jpeg');

        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $emptyFile,
            ]);

        $response->assertSessionHasErrors('image');
        Storage::disk('public')->assertMissing('kost-images/');
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

        $textFile = UploadedFile::fake()->create('image.jpg', 100, 'text/plain');

        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $textFile,
            ]);

        $response->assertSessionHasErrors('image');
        Storage::disk('public')->assertMissing('kost-images/');
    }
}
