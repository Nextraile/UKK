<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Kost\Actions;

use App\Domain\Kost\Actions\ApproveKost;
use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApproveKostTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_approves_pending_review_kost(): void
    {
        $kost = Kost::factory()->pendingReview()->create();
        $action = new ApproveKost;

        $result = $action->execute($kost);

        $this->assertEquals('approved', $result->status);
        $this->assertNotNull($result->approved_at);
        $this->assertNull($result->rejected_reason);
    }

    public function test_it_clears_previous_rejected_reason(): void
    {
        $kost = Kost::factory()->pendingReview()->create([
            'rejected_reason' => 'Old rejection reason',
        ]);
        $action = new ApproveKost;

        $result = $action->execute($kost);

        $this->assertNull($result->rejected_reason);
    }

    public function test_it_throws_exception_when_approving_draft(): void
    {
        $kost = Kost::factory()->draft()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage("current status is 'draft'");

        (new ApproveKost)->execute($kost);
    }

    public function test_it_throws_exception_when_approving_already_approved(): void
    {
        $kost = Kost::factory()->approved()->create();

        $this->expectException(InvalidKostTransitionException::class);

        (new ApproveKost)->execute($kost);
    }

    public function test_it_throws_exception_when_approving_active(): void
    {
        $kost = Kost::factory()->active()->create();

        $this->expectException(InvalidKostTransitionException::class);

        (new ApproveKost)->execute($kost);
    }
}
