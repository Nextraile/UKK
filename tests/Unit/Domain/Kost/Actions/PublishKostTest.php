<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Kost\Actions;

use App\Domain\Kost\Actions\PublishKost;
use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishKostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_publishes_approved_kost(): void
    {
        $kost = Kost::factory()->approved()->create();

        $action = new PublishKost;
        $result = $action->execute($kost);

        $this->assertEquals('active', $result->status);
        $this->assertNotNull($result->published_at);
        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_throws_exception_when_publishing_draft(): void
    {
        $kost = Kost::factory()->draft()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage("Cannot publish kost #{$kost->id}: current status is 'draft', expected 'approved'");

        $action = new PublishKost;
        $action->execute($kost);
    }

    /** @test */
    public function it_throws_exception_when_publishing_pending_review(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage("Cannot publish kost #{$kost->id}: current status is 'pending_review', expected 'approved'");

        $action = new PublishKost;
        $action->execute($kost);
    }

    /** @test */
    public function it_throws_exception_when_publishing_rejected(): void
    {
        $kost = Kost::factory()->rejected()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage("Cannot publish kost #{$kost->id}: current status is 'rejected', expected 'approved'");

        $action = new PublishKost;
        $action->execute($kost);
    }

    /** @test */
    public function it_throws_exception_when_publishing_already_active(): void
    {
        $kost = Kost::factory()->active()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage("Cannot publish kost #{$kost->id}: current status is 'active', expected 'approved'");

        $action = new PublishKost;
        $action->execute($kost);
    }
}
