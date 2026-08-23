<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Kost;

use App\Domain\Kost\Models\Kost;
use Tests\TestCase;

/**
 * Unit tests for Kost model.
 */
class KostModelTest extends TestCase
{
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
}
