<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Domain\RoomInventory\Models\PriceScheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceSchemeModelTest extends TestCase
{
    use RefreshDatabase;

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
