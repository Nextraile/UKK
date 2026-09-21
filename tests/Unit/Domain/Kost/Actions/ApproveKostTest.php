<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Kost\Actions;

use App\Domain\Identity\Models\User;
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
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->pendingReview()->create();
        $action = new ApproveKost;

        $result = $action->execute($kost, $superAdmin);

        $this->assertEquals('approved', $result->status);
        $this->assertNotNull($result->approved_at);
        $this->assertEquals($superAdmin->id, $result->approved_by);
        $this->assertNull($result->rejected_reason);
    }

    public function test_it_clears_previous_rejected_reason(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->pendingReview()->create([
            'rejected_reason' => 'Old rejection reason',
        ]);
        $action = new ApproveKost;

        $result = $action->execute($kost, $superAdmin);

        $this->assertNull($result->rejected_reason);
    }

    public function test_it_throws_exception_when_approving_draft(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->draft()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage("current status is 'draft'");

        (new ApproveKost)->execute($kost, $superAdmin);
    }

    public function test_it_throws_exception_when_approving_already_approved(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->approved()->create();

        $this->expectException(InvalidKostTransitionException::class);

        (new ApproveKost)->execute($kost, $superAdmin);
    }

    public function test_it_throws_exception_when_approving_active(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $kost = Kost::factory()->active()->create();

        $this->expectException(InvalidKostTransitionException::class);

        (new ApproveKost)->execute($kost, $superAdmin);
    }
}
