<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Kost\Models\RoomTypeImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoomTypeImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_delete_image(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $image = RoomTypeImage::factory()->create(['room_type_id' => $roomType->id]);

        // Create actual file in fake storage
        Storage::disk('public')->put($image->image_path, 'fake content');

        $response = $this->actingAs($admin)
            ->delete(route('admin.room-type-images.destroy', $image));

        $response->assertRedirect();
        $this->assertDatabaseMissing('room_type_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->image_path);
    }

    public function test_image_validation_mimes(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test',
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'images' => [$file],
            ]);

        $response->assertSessionHasErrors('images.0');
    }

    public function test_image_validation_max_size_5mb(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->image('large.jpg')->size(6000); // 6MB

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test',
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'images' => [$file],
            ]);

        $response->assertSessionHasErrors('images.0');
    }

    public function test_edit_allows_up_to_10_new_images_per_request(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Room type with NO existing images
        $roomType = RoomType::factory()->for($kost)->create();

        // Upload 10 new images (max per request and max total)
        $images = [];
        for ($i = 0; $i < 10; $i++) {
            $images[] = UploadedFile::fake()->image("new{$i}.jpg");
        }

        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'images' => $images,
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));
        $this->assertDatabaseCount('room_type_images', 10); // 0 existing + 10 new = max limit
    }

    public function test_unauthorized_admin_cannot_delete_image(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $image = RoomTypeImage::factory()->create(['room_type_id' => $roomType->id]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.room-type-images.destroy', $image));

        $response->assertStatus(403);
    }

    public function test_file_stored_in_public_disk(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->image('test.jpg');

        $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test',
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'images' => [$file],
            ]);

        $image = RoomTypeImage::first();
        Storage::disk('public')->assertExists($image->image_path);
    }

    public function test_filename_pattern_correct(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $file = UploadedFile::fake()->image('test.jpg');

        $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test',
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'images' => [$file],
            ]);

        $image = RoomTypeImage::first();
        $this->assertMatchesRegularExpression(
            '/^room-type-images\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.\w+$/',
            $image->image_path
        );
    }

    public function test_image_deleted_from_storage_on_destroy(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $image = RoomTypeImage::factory()->create(['room_type_id' => $roomType->id]);

        Storage::disk('public')->put($image->image_path, 'fake content');

        $this->actingAs($admin)
            ->delete(route('admin.room-type-images.destroy', $image));

        Storage::disk('public')->assertMissing($image->image_path);
    }

    public function test_first_image_automatically_set_as_thumbnail(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test',
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'images' => [
                    UploadedFile::fake()->image('first.jpg'),
                    UploadedFile::fake()->image('second.jpg'),
                ],
            ]);

        $roomType = RoomType::where('name', 'Test Room')->first();
        $images = $roomType->roomTypeImages()->orderBy('sort_order')->get();

        $this->assertTrue($images->first()->is_thumbnail);
        $this->assertFalse($images->last()->is_thumbnail);
    }

    public function test_images_sorted_by_sort_order(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test',
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'images' => [
                    UploadedFile::fake()->image('img1.jpg'),
                    UploadedFile::fake()->image('img2.jpg'),
                    UploadedFile::fake()->image('img3.jpg'),
                ],
            ]);

        $roomType = RoomType::where('name', 'Test Room')->first();
        $images = $roomType->roomTypeImages()->orderBy('sort_order')->get();

        $this->assertEquals(1, $images[0]->sort_order);
        $this->assertEquals(2, $images[1]->sort_order);
        $this->assertEquals(3, $images[2]->sort_order);
    }
}
