<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Kost\Actions;

use App\Domain\Identity\Models\User;
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
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->pendingReview()->create();
        $action = new RejectKost;

        $result = $action->execute($kost, $superAdmin, 'Alamat tidak lengkap, foto tidak jelas');

        $this->assertEquals('rejected', $result->status);
        $this->assertNotNull($result->rejected_at);
        $this->assertEquals($superAdmin->id, $result->rejected_by);
        $this->assertEquals('Alamat tidak lengkap, foto tidak jelas', $result->rejected_reason);
    }

    public function test_it_trims_rejection_reason(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->pendingReview()->create();
        $action = new RejectKost;

        $result = $action->execute($kost, $superAdmin, '  Whitespace padded reason  ');

        $this->assertEquals('Whitespace padded reason', $result->rejected_reason);
    }

    public function test_it_throws_exception_when_rejection_reason_empty(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->pendingReview()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason is required');

        (new RejectKost)->execute($kost, $superAdmin, '');
    }

    public function test_it_throws_exception_when_rejection_reason_too_short(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->pendingReview()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 10 characters');

        (new RejectKost)->execute($kost, $superAdmin, 'too short');
    }

    public function test_it_throws_exception_when_rejecting_draft(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->draft()->create();

        $this->expectException(InvalidKostTransitionException::class);

        (new RejectKost)->execute($kost, $superAdmin, 'Valid rejection reason here');
    }

    public function test_it_throws_exception_when_rejecting_approved(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->approved()->create();

        $this->expectException(InvalidKostTransitionException::class);

        (new RejectKost)->execute($kost, $superAdmin, 'Valid rejection reason here');
    }

    public function test_it_throws_exception_when_rejecting_active(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->active()->create();

        $this->expectException(InvalidKostTransitionException::class);

        (new RejectKost)->execute($kost, $superAdmin, 'Valid rejection reason here');
    }
}
