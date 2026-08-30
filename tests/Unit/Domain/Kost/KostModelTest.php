<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Kost;

use App\Domain\Kost\Models\Address;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostImage;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for Kost model.
 */
class KostModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_status_not_in_fillable_array(): void
    {
        $kost = new Kost;

        $this->assertNotContains('status', $kost->getFillable());
    }

    /**
     * @test
     */
    public function test_lifecycle_timestamps_are_fillable(): void
    {
        $kost = new Kost;

        // These timestamps should be fillable (set by Action classes)
        $this->assertContains('approved_at', $kost->getFillable());
        $this->assertContains('rejected_at', $kost->getFillable());
        $this->assertContains('submitted_at', $kost->getFillable());

        // published_at NOT fillable - only set by PublishKost Action via direct assignment
        $this->assertNotContains('published_at', $kost->getFillable());
    }

    /**
     * @test
     */
    public function test_it_returns_thumbnail_url_from_first_thumbnail_image(): void
    {
        $kost = Kost::factory()->create();
        KostImage::factory()->create([
            'kost_id' => $kost->id,
            'image_path' => 'kost-images/test-thumbnail.jpg',
            'is_thumbnail' => true,
        ]);

        $this->assertNotNull($kost->fresh()->thumbnail_url);
        $this->assertStringContainsString('test-thumbnail.jpg', $kost->fresh()->thumbnail_url);
    }

    /**
     * @test
     */
    public function test_it_returns_null_thumbnail_url_when_no_thumbnail_exists(): void
    {
        $kost = Kost::factory()->create();

        $this->assertNull($kost->thumbnail_url);
    }

    /**
     * @test
     */
    public function test_it_returns_null_thumbnail_url_when_only_non_thumbnail_images_exist(): void
    {
        $kost = Kost::factory()->create();
        KostImage::factory()->create([
            'kost_id' => $kost->id,
            'image_path' => 'kost-images/regular-image.jpg',
            'is_thumbnail' => false,
        ]);

        $this->assertNull($kost->fresh()->thumbnail_url);
    }

    /**
     * @test
     */
    public function test_it_returns_city_from_address_relation(): void
    {
        $kost = Kost::factory()->create();
        Address::factory()->create([
            'kost_id' => $kost->id,
            'city' => 'Bandung',
        ]);

        $this->assertEquals('Bandung', $kost->fresh()->city);
    }

    /**
     * @test
     */
    public function test_it_returns_null_city_when_address_missing(): void
    {
        $kost = Kost::factory()->create();

        $this->assertNull($kost->city);
    }

    /**
     * @test
     */
    public function test_it_returns_province_from_address_relation(): void
    {
        $kost = Kost::factory()->create();
        Address::factory()->create([
            'kost_id' => $kost->id,
            'province' => 'Jawa Barat',
        ]);

        $this->assertEquals('Jawa Barat', $kost->fresh()->province);
    }

    /**
     * @test
     */
    public function test_it_returns_null_province_when_address_missing(): void
    {
        $kost = Kost::factory()->create();

        $this->assertNull($kost->province);
    }

    /**
     * @test
     */
    public function test_it_returns_minimum_price_from_active_price_schemes(): void
    {
        $kost = Kost::factory()->create();

        // Room type 1: min price = 1200000 (active)
        $roomType1 = RoomType::factory()->create(['kost_id' => $kost->id]);
        PriceScheme::factory()->create([
            'room_type_id' => $roomType1->id,
            'price' => 1500000,
            'is_active' => true,
        ]);
        PriceScheme::factory()->create([
            'room_type_id' => $roomType1->id,
            'price' => 2000000,
            'is_active' => true,
        ]);

        // Room type 2: min price = 900000 (active)
        $roomType2 = RoomType::factory()->create(['kost_id' => $kost->id]);
        PriceScheme::factory()->create([
            'room_type_id' => $roomType2->id,
            'price' => 1200000,
            'is_active' => true,
        ]);
        PriceScheme::factory()->create([
            'room_type_id' => $roomType2->id,
            'price' => 900000,
            'is_active' => true,
        ]);

        // Overall minimum should be 900000
        $this->assertEquals(900000, $kost->fresh()->min_price);
    }

    /**
     * @test
     */
    public function test_it_returns_null_min_price_when_no_active_price_schemes_exist(): void
    {
        $kost = Kost::factory()->create();
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        // Only inactive price schemes
        PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'price' => 1000000,
            'is_active' => false,
        ]);

        $this->assertNull($kost->fresh()->min_price);
    }

    /**
     * @test
     */
    public function test_it_returns_null_min_price_when_no_room_types_exist(): void
    {
        $kost = Kost::factory()->create();

        $this->assertNull($kost->min_price);
    }

    /**
     * @test
     */
    public function test_it_ignores_inactive_price_schemes_when_calculating_min_price(): void
    {
        $kost = Kost::factory()->create();
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);

        PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'price' => 500000, // Lower but inactive
            'is_active' => false,
        ]);
        PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'price' => 1200000, // Higher but active
            'is_active' => true,
        ]);

        // Should return active price, not inactive lower price
        $this->assertEquals(1200000, $kost->fresh()->min_price);
    }
}
