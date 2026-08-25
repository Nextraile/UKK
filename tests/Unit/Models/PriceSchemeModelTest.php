<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceSchemeModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function price_scheme_belongs_to_room_type(): void
    {
        $roomType = RoomType::factory()->create();
        $priceScheme = PriceScheme::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertInstanceOf(RoomType::class, $priceScheme->roomType);
        $this->assertEquals($roomType->id, $priceScheme->roomType->id);
    }

    /** @test */
    public function price_cast_to_decimal_with_two_places(): void
    {
        $priceScheme = PriceScheme::factory()->create(['price' => '1500000.99']);

        $this->assertIsString($priceScheme->price);
        $this->assertEquals('1500000.99', $priceScheme->price);
    }

    /** @test */
    public function duration_value_cast_to_integer(): void
    {
        $priceScheme = PriceScheme::factory()->create(['duration_value' => 3]);

        $this->assertIsInt($priceScheme->duration_value);
        $this->assertEquals(3, $priceScheme->duration_value);
    }

    /** @test */
    public function duration_unit_accepts_day(): void
    {
        $priceScheme = PriceScheme::factory()->create(['duration_unit' => 'day']);

        $this->assertEquals('day', $priceScheme->duration_unit);
    }

    /** @test */
    public function duration_unit_accepts_week(): void
    {
        $priceScheme = PriceScheme::factory()->create(['duration_unit' => 'week']);

        $this->assertEquals('week', $priceScheme->duration_unit);
    }

    /** @test */
    public function duration_unit_accepts_month(): void
    {
        $priceScheme = PriceScheme::factory()->create(['duration_unit' => 'month']);

        $this->assertEquals('month', $priceScheme->duration_unit);
    }

    /** @test */
    public function is_active_cast_to_boolean(): void
    {
        $priceScheme = PriceScheme::factory()->create(['is_active' => true]);

        $this->assertIsBool($priceScheme->is_active);
        $this->assertTrue($priceScheme->is_active);
    }

    /** @test */
    public function is_active_defaults_to_true(): void
    {
        $priceScheme = PriceScheme::factory()->create();

        $this->assertTrue($priceScheme->is_active);
    }

    /** @test */
    public function price_scheme_uses_soft_deletes(): void
    {
        $priceScheme = PriceScheme::factory()->create();
        $id = $priceScheme->id;

        $priceScheme->delete();

        $this->assertSoftDeleted('price_schemes', ['id' => $id]);
        $this->assertNotNull($priceScheme->fresh()->deleted_at);
    }

    /** @test */
    public function description_can_be_null(): void
    {
        $priceScheme = PriceScheme::factory()->create(['description' => null]);

        $this->assertNull($priceScheme->description);
    }
}
