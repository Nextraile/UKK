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

class RoomTypeCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_room_types_index(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        RoomType::factory()->count(3)->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.room-types.index', $kost));

        $response->assertStatus(200);
        $response->assertViewIs('admin.room-types.index');
        $response->assertViewHas('roomTypes');
    }

    public function test_admin_can_view_create_form(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.room-types.create', $kost));

        $response->assertStatus(200);
        $response->assertViewIs('admin.room-types.create');
    }

    public function test_admin_can_create_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Kamar Type A',
                'description' => 'Kamar luas dengan AC',
                'room_size' => '3x4 m',
                'max_occupants' => 2,
                'security_deposit' => 500000,
                'facilities' => ['WiFi', 'AC', 'Lemari'],
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));
        $this->assertDatabaseHas('room_types', [
            'kost_id' => $kost->id,
            'name' => 'Kamar Type A',
            'slug' => 'kamar-type-a',
            'max_occupants' => 2,
        ]);
    }

    public function test_admin_can_view_edit_form(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.room-types.edit', [$kost, $roomType]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.room-types.edit');
        $response->assertViewHas('roomType');
    }

    public function test_admin_can_update_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id, 'name' => 'Old Name']);

        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'room_size' => '4x5 m',
                'max_occupants' => 3,
                'security_deposit' => 750000,
                'facilities' => ['WiFi'],
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));
        $this->assertDatabaseHas('room_types', [
            'id' => $roomType->id,
            'name' => 'Updated Name',
            'slug' => 'updated-name',
            'max_occupants' => 3,
        ]);
    }

    public function test_admin_can_delete_room_type(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.room-types.destroy', [$kost, $roomType]));

        $response->assertRedirect(route('admin.room-types.index', $kost));
        $this->assertSoftDeleted('room_types', ['id' => $roomType->id]);
    }

    public function test_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => '',
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_name_must_be_unique_per_kost(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        RoomType::factory()->create(['kost_id' => $kost->id, 'name' => 'Existing Name']);

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Existing Name',
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_name_max_length_is_150(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => str_repeat('a', 151),
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_max_occupants_minimum_is_one(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'room_size' => '3x3 m',
                'max_occupants' => 0,
                'security_deposit' => 500000,
            ]);

        $response->assertSessionHasErrors('max_occupants');
    }

    public function test_unauthorized_admin_cannot_access(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);

        $response = $this->actingAs($admin)
            ->get(route('admin.room-types.index', $kost));

        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_room_types(): void
    {
        $tenant = User::factory()->tenant()->create();
        $kost = Kost::factory()->create();

        $response = $this->actingAs($tenant)
            ->get(route('admin.room-types.index', $kost));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $kost = Kost::factory()->create();

        $response = $this->get(route('admin.room-types.index', $kost));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_create_room_type_with_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Deluxe',
                'description' => 'Deluxe room',
                'room_size' => '4x5 m',
                'max_occupants' => 2,
                'security_deposit' => 1000000,
                'images' => [
                    UploadedFile::fake()->image('room1.jpg'),
                    UploadedFile::fake()->image('room2.jpg'),
                ],
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));
        $this->assertDatabaseCount('room_type_images', 2);

        $roomType = RoomType::where('name', 'Deluxe')->first();
        $this->assertTrue($roomType->roomTypeImages->first()->is_thumbnail); // First = thumbnail
    }

    public function test_admin_can_create_room_type_with_facilities_and_rules(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Standard',
                'description' => 'Standard room',
                'room_size' => '3x4 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'facilities' => ['AC', 'TV', 'Lemari'],
                'rules' => ['No smoking', 'Max jam malam 22:00'],
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));

        $roomType = RoomType::where('name', 'Standard')->first();
        $this->assertCount(3, $roomType->facilities);
        $this->assertContains('AC', $roomType->facilities);
        $this->assertCount(2, $roomType->rules);
        $this->assertContains('No smoking', $roomType->rules);
    }

    public function test_create_validates_max_10_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Try upload 11 images
        $images = [];
        for ($i = 0; $i < 11; $i++) {
            $images[] = UploadedFile::fake()->image("room{$i}.jpg");
        }

        $response = $this->actingAs($admin)
            ->post(route('admin.room-types.store', $kost), [
                'name' => 'Test Room',
                'description' => 'Test',
                'room_size' => '3x3 m',
                'max_occupants' => 1,
                'security_deposit' => 500000,
                'images' => $images,
            ]);

        $response->assertSessionHasErrors('images');
    }

    public function test_admin_can_edit_append_new_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Create room type with 2 images
        $roomType = RoomType::factory()->for($kost)->create();
        RoomTypeImage::factory()->for($roomType)->create(['sort_order' => 1]);
        RoomTypeImage::factory()->for($roomType)->create(['sort_order' => 2]);

        // Append 1 new image
        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'images' => [
                    UploadedFile::fake()->image('new.jpg'),
                ],
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));
        $this->assertDatabaseCount('room_type_images', 3);
    }

    public function test_edit_allows_up_to_10_new_images_per_request(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Create room type with NO existing images
        $roomType = RoomType::factory()->for($kost)->create();

        // Upload 10 images in one request (max allowed per request)
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

    public function test_first_uploaded_image_becomes_thumbnail(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Create room type without images
        $roomType = RoomType::factory()->for($kost)->create();

        // Upload first image
        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'images' => [
                    UploadedFile::fake()->image('new.jpg'),
                ],
            ]);

        $response->assertRedirect(route('admin.room-types.index', $kost));

        $image = RoomTypeImage::where('room_type_id', $roomType->id)->first();
        $this->assertTrue($image->is_thumbnail);
    }

    public function test_update_rejects_upload_exceeding_10_total(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Create room type with 8 existing images
        $roomType = RoomType::factory()->for($kost)->create();
        RoomTypeImage::factory()->count(8)->for($roomType)->create();

        // Try to upload 3 more images (8 + 3 = 11, exceeds limit)
        $response = $this->actingAs($admin)
            ->put(route('admin.room-types.update', [$kost, $roomType]), [
                'name' => $roomType->name,
                'description' => $roomType->description,
                'room_size' => $roomType->room_size,
                'max_occupants' => $roomType->max_occupants,
                'security_deposit' => $roomType->security_deposit,
                'images' => [
                    UploadedFile::fake()->image('new1.jpg'),
                    UploadedFile::fake()->image('new2.jpg'),
                    UploadedFile::fake()->image('new3.jpg'),
                ],
            ]);

        $response->assertSessionHasErrors('images');
        $this->assertDatabaseCount('room_type_images', 8); // Still 8, no upload
    }
}
