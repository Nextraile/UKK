<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Kost\Actions;

use App\Domain\Kost\Actions\RejectKost;
use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RejectKostTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_pending_review_kost_with_reason(): void
    {
        $kost = Kost::factory()->pendingReview()->create();
        $action = new RejectKost;

        $result = $action->execute($kost, 'Alamat tidak lengkap, foto tidak jelas');

        $this->assertEquals('rejected', $result->status);
        $this->assertNotNull($result->rejected_at);
        $this->assertEquals('Alamat tidak lengkap, foto tidak jelas', $result->rejected_reason);
    }

    public function test_it_trims_rejection_reason(): void
    {
        $kost = Kost::factory()->pendingReview()->create();
        $action = new RejectKost;

        $result = $action->execute($kost, '  Whitespace padded reason  ');

        $this->assertEquals('Whitespace padded reason', $result->rejected_reason);
    }

    public function test_it_throws_exception_when_rejection_reason_empty(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason is required');

        (new RejectKost)->execute($kost, '');
    }

    public function test_it_throws_exception_when_rejection_reason_too_short(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 10 characters');

        (new RejectKost)->execute($kost, 'too short');
    }

    public function test_it_throws_exception_when_rejecting_draft(): void
    {
        $kost = Kost::factory()->draft()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage("current status is 'draft'");

        (new RejectKost)->execute($kost, 'Valid rejection reason here');
    }

    public function test_it_throws_exception_when_rejecting_approved(): void
    {
        $kost = Kost::factory()->approved()->create();

        $this->expectException(InvalidKostTransitionException::class);

        (new RejectKost)->execute($kost, 'Valid rejection reason here');
    }

    public function test_it_throws_exception_when_rejecting_active(): void
    {
        $kost = Kost::factory()->active()->create();

        $this->expectException(InvalidKostTransitionException::class);

        (new RejectKost)->execute($kost, 'Valid rejection reason here');
    }
}
