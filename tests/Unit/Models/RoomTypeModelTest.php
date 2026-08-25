<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Kost\Models\RoomTypeImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTypeModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function room_type_belongs_to_kost(): void
    {
        $kost = Kost::factory()->create();
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        $this->assertInstanceOf(Kost::class, $roomType->kost);
        $this->assertEquals($kost->id, $roomType->kost->id);
    }

    /** @test */
    public function room_type_has_many_room_type_images(): void
    {
        $roomType = RoomType::factory()->create();
        RoomTypeImage::factory()->count(3)->create(['room_type_id' => $roomType->id]);

        $this->assertCount(3, $roomType->roomTypeImages);
        $this->assertInstanceOf(RoomTypeImage::class, $roomType->roomTypeImages->first());
    }

    /** @test */
    public function room_type_has_one_thumbnail_image(): void
    {
        $roomType = RoomType::factory()->create();
        $thumbnail = RoomTypeImage::factory()->create([
            'room_type_id' => $roomType->id,
            'is_thumbnail' => true,
        ]);
        RoomTypeImage::factory()->count(2)->create([
            'room_type_id' => $roomType->id,
            'is_thumbnail' => false,
        ]);

        $this->assertInstanceOf(RoomTypeImage::class, $roomType->thumbnailImage);
        $this->assertEquals($thumbnail->id, $roomType->thumbnailImage->id);
    }

    /** @test */
    public function room_type_has_many_price_schemes(): void
    {
        $roomType = RoomType::factory()->create();
        PriceScheme::factory()->count(3)->create(['room_type_id' => $roomType->id]);

        $this->assertCount(3, $roomType->priceSchemes);
        $this->assertInstanceOf(PriceScheme::class, $roomType->priceSchemes->first());
    }

    /** @test */
    public function room_type_has_many_rooms(): void
    {
        $roomType = RoomType::factory()->create();
        Room::factory()->count(5)->create(['room_type_id' => $roomType->id]);

        $this->assertCount(5, $roomType->rooms);
        $this->assertInstanceOf(Room::class, $roomType->rooms->first());
    }

    /** @test */
    public function facilities_cast_to_array(): void
    {
        $roomType = RoomType::factory()->create([
            'facilities' => ['WiFi', 'AC', 'Parking'],
        ]);

        $this->assertIsArray($roomType->facilities);
        $this->assertCount(3, $roomType->facilities);
        $this->assertContains('WiFi', $roomType->facilities);
    }

    /** @test */
    public function rules_cast_to_array(): void
    {
        $roomType = RoomType::factory()->create([
            'rules' => ['No smoking', 'No pets'],
        ]);

        $this->assertIsArray($roomType->rules);
        $this->assertCount(2, $roomType->rules);
        $this->assertContains('No smoking', $roomType->rules);
    }

    /** @test */
    public function security_deposit_cast_to_decimal(): void
    {
        $roomType = RoomType::factory()->create([
            'security_deposit' => '500000.50',
        ]);

        $this->assertIsString($roomType->security_deposit);
        $this->assertEquals('500000.50', $roomType->security_deposit);
    }

    /** @test */
    public function room_type_uses_soft_deletes(): void
    {
        $roomType = RoomType::factory()->create();
        $id = $roomType->id;

        $roomType->delete();

        $this->assertSoftDeleted('room_types', ['id' => $id]);
        $this->assertNotNull($roomType->fresh()->deleted_at);
    }

    /** @test */
    public function facilities_can_be_null(): void
    {
        $roomType = RoomType::factory()->create(['facilities' => null]);

        $this->assertNull($roomType->facilities);
    }

    /** @test */
    public function rules_can_be_null(): void
    {
        $roomType = RoomType::factory()->create(['rules' => null]);

        $this->assertNull($roomType->rules);
    }
}
