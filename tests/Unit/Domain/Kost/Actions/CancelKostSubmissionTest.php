<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Kost\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Actions\CancelKostSubmission;
use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelKostSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_it_can_cancel_pending_review_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->create([
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $action = new CancelKostSubmission;
        $result = $action->execute($kost);

        $this->assertEquals('draft', $result->status);
        $this->assertNull($result->submitted_at);
        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'draft',
            'submitted_at' => null,
        ]);
    }

    /** @test */
    public function test_it_cannot_cancel_draft_kost(): void
    {
        $kost = Kost::factory()->create(['status' => 'draft']);

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage('Cannot cancel kost submission. Current status: draft. Only pending_review submissions can be cancelled.');

        $action = new CancelKostSubmission;
        $action->execute($kost);
    }

    /** @test */
    public function test_it_cannot_cancel_approved_kost(): void
    {
        $kost = Kost::factory()->approved()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage('Cannot cancel kost submission. Current status: approved. Only pending_review submissions can be cancelled.');

        $action = new CancelKostSubmission;
        $action->execute($kost);
    }

    /** @test */
    public function test_it_cannot_cancel_rejected_kost(): void
    {
        $kost = Kost::factory()->rejected()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage('Cannot cancel kost submission. Current status: rejected. Only pending_review submissions can be cancelled.');

        $action = new CancelKostSubmission;
        $action->execute($kost);
    }

    /** @test */
    public function test_it_cannot_cancel_active_kost(): void
    {
        $kost = Kost::factory()->active()->create();

        $this->expectException(InvalidKostTransitionException::class);
        $this->expectExceptionMessage('Cannot cancel kost submission. Current status: active. Only pending_review submissions can be cancelled.');

        $action = new CancelKostSubmission;
        $action->execute($kost);
    }
}
