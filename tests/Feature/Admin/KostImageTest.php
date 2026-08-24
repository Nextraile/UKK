<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test suite for Kost Image management (COMP-003).
 *
 * Covers: upload, delete, thumbnail selection, sort order, authorization.
 */
class KostImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can upload image to their kost.
     */
    public function test_admin_can_upload_image_to_own_kost(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600)->size(2048); // 2MB

        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), [
                'image' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Gambar berhasil diunggah.');

        // Verify database record
        $this->assertDatabaseHas('kost_images', [
            'kost_id' => $kost->id,
            'is_thumbnail' => false,
            'sort_order' => 1,
        ]);

        // Verify file stored with correct pattern
        $image = KostImage::where('kost_id', $kost->id)->first();
        $this->assertStringStartsWith('kost-images/kost-'.$kost->id.'-img-', $image->image_path);
        Storage::disk('public')->assertExists($image->image_path);
    }

    /**
     * Test upload validates image file requirements.
     */
    public function test_upload_validates_image_requirements(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        // Missing image
        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), []);
        $response->assertSessionHasErrors(['image']);

        // Invalid file type
        $file = UploadedFile::fake()->create('document.pdf', 1024);
        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);
        $response->assertSessionHasErrors(['image']);

        // File too large (> 5MB)
        $file = UploadedFile::fake()->image('large.jpg')->size(6000);
        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);
        $response->assertSessionHasErrors(['image']);
    }

    /**
     * Test filename generation follows pattern.
     */
    public function test_filename_follows_pattern(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->image('test.jpg');

        $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);

        $image = KostImage::where('kost_id', $kost->id)->first();

        // Pattern: kost-{id}-img-{Ymd-His}-{seq}.{ext}
        $pattern = '/^kost-images\/kost-\d+-img-\d{8}-\d{6}-\d+\.(jpg|jpeg|png|webp)$/';
        $this->assertMatchesRegularExpression($pattern, $image->image_path);
    }

    /**
     * Test sequence increments correctly for multiple uploads.
     */
    public function test_sequence_increments_for_multiple_uploads(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        // Upload 3 images
        for ($i = 1; $i <= 3; $i++) {
            $file = UploadedFile::fake()->image("test-{$i}.jpg");
            $this->actingAs($admin)
                ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);
        }

        $images = KostImage::where('kost_id', $kost->id)->orderBy('sort_order')->get();

        $this->assertCount(3, $images);
        $this->assertEquals(1, $images[0]->sort_order);
        $this->assertEquals(2, $images[1]->sort_order);
        $this->assertEquals(3, $images[2]->sort_order);
    }

    /**
     * Test admin can delete image from their kost.
     */
    public function test_admin_can_delete_image_from_own_kost(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        // Upload actual file through controller to ensure proper storage
        $file = UploadedFile::fake()->image('test.jpg');
        $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);

        $image = KostImage::where('kost_id', $kost->id)->first();

        $response = $this->actingAs($admin)
            ->delete(route('admin.kosts.images.destroy', [$kost, $image]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Gambar berhasil dihapus.');

        // Verify database record deleted
        $this->assertDatabaseMissing('kost_images', ['id' => $image->id]);

        // Verify file deleted from storage
        Storage::disk('public')->assertMissing($image->image_path);
    }

    /**
     * Test admin can set thumbnail.
     */
    public function test_admin_can_set_thumbnail(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        // Create first image and set as thumbnail
        $image1 = KostImage::factory()->create([
            'kost_id' => $kost->id,
            'is_thumbnail' => true,
            'sort_order' => 1,
        ]);

        // Create second image (not thumbnail)
        $image2 = KostImage::factory()->create([
            'kost_id' => $kost->id,
            'is_thumbnail' => false,
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.images.set-thumbnail', [$kost, $image2]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Thumbnail berhasil diatur.');

        // Verify only one thumbnail
        $this->assertDatabaseHas('kost_images', [
            'id' => $image1->id,
            'is_thumbnail' => false,
        ]);
        $this->assertDatabaseHas('kost_images', [
            'id' => $image2->id,
            'is_thumbnail' => true,
        ]);

        // Verify only one thumbnail exists for kost
        $thumbnailCount = KostImage::where('kost_id', $kost->id)
            ->where('is_thumbnail', true)
            ->count();
        $this->assertEquals(1, $thumbnailCount);
    }

    /**
     * Test update sort order updates multiple images.
     */
    public function test_update_sort_order_updates_multiple_images(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        $image1 = KostImage::factory()->create([
            'kost_id' => $kost->id,
            'sort_order' => 1,
            'is_thumbnail' => false,
        ]);
        $image2 = KostImage::factory()->create([
            'kost_id' => $kost->id,
            'sort_order' => 2,
            'is_thumbnail' => false,
        ]);
        $image3 = KostImage::factory()->create([
            'kost_id' => $kost->id,
            'sort_order' => 3,
            'is_thumbnail' => false,
        ]);

        // Reverse order: 3, 2, 1
        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.images.sort-order', $kost), [
                'image_ids' => [$image3->id, $image2->id, $image1->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Urutan gambar berhasil diperbarui.');

        // Verify sort order updated
        $this->assertEquals(1, $image3->fresh()->sort_order);
        $this->assertEquals(2, $image2->fresh()->sort_order);
        $this->assertEquals(3, $image1->fresh()->sort_order);
    }

    /**
     * Test update sort order validates image IDs.
     */
    public function test_update_sort_order_validates_image_ids(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        // Missing image_ids
        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.images.sort-order', $kost), []);
        $response->assertSessionHasErrors(['image_ids']);

        // Non-existent image ID
        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.images.sort-order', $kost), [
                'image_ids' => [999999],
            ]);
        $response->assertSessionHasErrors(['image_ids.0']);
    }

    /**
     * Test admin cannot upload to kost they don't own.
     */
    public function test_admin_cannot_upload_to_others_kost(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin2->id]);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($admin1)
            ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);

        $response->assertForbidden();
    }

    /**
     * Test admin cannot delete image from kost they don't own.
     */
    public function test_admin_cannot_delete_image_from_others_kost(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin2->id]);
        $image = KostImage::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin1)
            ->delete(route('admin.kosts.images.destroy', [$kost, $image]));

        $response->assertForbidden();
    }

    /**
     * Test admin cannot manage images for kost in pending_review status.
     */
    public function test_admin_cannot_manage_images_for_pending_review_kost(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create([
            'user_id' => $admin->id,
            'status' => 'pending_review',
        ]);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);

        $response->assertForbidden();
    }

    /**
     * Test admin cannot manage images for active kost.
     */
    public function test_admin_cannot_manage_images_for_active_kost(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create([
            'user_id' => $admin->id,
            'status' => 'active',
        ]);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);

        $response->assertForbidden();
    }

    /**
     * Test admin can manage images for rejected kost.
     */
    public function test_admin_can_manage_images_for_rejected_kost(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create([
            'user_id' => $admin->id,
            'status' => 'rejected',
        ]);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test tenant cannot upload kost images.
     */
    public function test_tenant_cannot_upload_kost_images(): void
    {
        $tenant = User::factory()->tenant()->create();
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($tenant)
            ->post(route('admin.kosts.images.store', $kost), ['image' => $file]);

        $response->assertForbidden();
    }

    /**
     * Test image deletion handles missing file gracefully.
     */
    public function test_image_deletion_handles_missing_file_gracefully(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        $image = KostImage::factory()->create([
            'kost_id' => $kost->id,
            'image_path' => 'kost-images/missing.jpg',
        ]);

        // File doesn't exist, but deletion should succeed
        $response = $this->actingAs($admin)
            ->delete(route('admin.kosts.images.destroy', [$kost, $image]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Database record still deleted
        $this->assertDatabaseMissing('kost_images', ['id' => $image->id]);
    }

    /**
     * Test cannot set thumbnail for image from different kost.
     */
    public function test_cannot_set_thumbnail_for_image_from_different_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $kost1 = Kost::factory()->draft()->create(['user_id' => $admin->id]);
        $kost2 = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        $image = KostImage::factory()->create(['kost_id' => $kost2->id]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.kosts.images.set-thumbnail', [$kost1, $image]));

        $response->assertNotFound();
    }

    /**
     * Test cannot delete image from different kost.
     */
    public function test_cannot_delete_image_from_different_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $kost1 = Kost::factory()->draft()->create(['user_id' => $admin->id]);
        $kost2 = Kost::factory()->draft()->create(['user_id' => $admin->id]);

        $image = KostImage::factory()->create(['kost_id' => $kost2->id]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.kosts.images.destroy', [$kost1, $image]));

        $response->assertNotFound();
    }
}
