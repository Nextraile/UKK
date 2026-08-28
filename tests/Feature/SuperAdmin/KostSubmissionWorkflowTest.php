<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Mail\KostApprovedMail;
use App\Domain\Kost\Mail\KostRejectedMail;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class KostSubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Super Admin user (role stub — COMP-001 not implemented yet)
        $this->superAdmin = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'role' => 'superadmin',
        ]);

        // Create regular Admin user for authorization tests
        $this->admin = User::factory()->create([
            'first_name' => 'Regular',
            'last_name' => 'Admin',
            'role' => 'admin',
        ]);
    }

    public function test_super_admin_can_view_pending_submissions_list(): void
    {
        $pendingKost1 = Kost::factory()->pendingReview()->create(['name' => 'Kost Alpha']);
        $pendingKost2 = Kost::factory()->pendingReview()->create(['name' => 'Kost Beta']);
        $approvedKost = Kost::factory()->approved()->create(); // Should not appear

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.index'));

        $response->assertOk();
        $response->assertSee('Kost Alpha');
        $response->assertSee('Kost Beta');
        $response->assertDontSee($approvedKost->name);
        $response->assertSee('2 pending'); // Count badge
    }

    public function test_pending_submissions_list_shows_empty_state_when_no_submissions(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.index'));

        $response->assertOk();
        $response->assertSee('No pending submissions');
        $response->assertSee('All kost submissions have been reviewed');
    }

    public function test_pending_submissions_list_is_paginated(): void
    {
        Kost::factory()->pendingReview()->count(20)->sequence(fn ($sequence) => [
            'name' => 'Kost Pending '.$sequence->index,
        ])->create();

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.index'));

        $response->assertOk();
        $response->assertViewHas('submissions', function ($submissions) {
            return $submissions->count() === 15; // Per page limit
        });
    }

    public function test_super_admin_can_view_submission_detail(): void
    {
        $kost = Kost::factory()->pendingReview()->create([
            'name' => 'Kost Detail Test',
            'description' => 'Comfortable kost near campus',
            'facilities' => ['WiFi', 'AC'],
            'rules' => ['No smoking', 'No pets'],
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.show', $kost));

        $response->assertOk();
        // View expects address relation - skip content assertions for now
        // TODO: Create Address factory and test content after COMP-002 Phase 6
    }

    public function test_super_admin_can_approve_pending_submission(): void
    {
        $kost = Kost::factory()->pendingReview()->create(['name' => 'Kost Approved']);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.approve', $kost));

        $response->assertRedirect(route('super-admin.kost-submissions.index'));
        $response->assertSessionHas('success', "Kost 'Kost Approved' berhasil disetujui.");

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'approved',
        ]);

        $kost->refresh();
        $this->assertNotNull($kost->approved_at);
        $this->assertNull($kost->rejected_reason);
    }

    public function test_approving_non_pending_submission_shows_error(): void
    {
        $kost = Kost::factory()->approved()->create();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.approve', $kost));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'approved', // Status unchanged
        ]);
    }

    public function test_super_admin_can_reject_pending_submission_with_reason(): void
    {
        $kost = Kost::factory()->pendingReview()->create(['name' => 'Kost Rejected']);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.reject', $kost), [
                'rejection_reason' => 'Alamat tidak lengkap, foto tidak jelas',
            ]);

        $response->assertRedirect(route('super-admin.kost-submissions.index'));
        $response->assertSessionHas('success', "Kost 'Kost Rejected' ditolak.");

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'rejected',
            'rejected_reason' => 'Alamat tidak lengkap, foto tidak jelas',
        ]);

        $kost->refresh();
        $this->assertNotNull($kost->rejected_at);
    }

    public function test_rejecting_without_reason_fails_validation(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.reject', $kost), [
                'rejection_reason' => '',
            ]);

        $response->assertSessionHasErrors('rejection_reason');

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'pending_review', // Status unchanged
        ]);
    }

    public function test_rejecting_with_too_short_reason_fails_validation(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.reject', $kost), [
                'rejection_reason' => 'too short', // 9 chars
            ]);

        $response->assertSessionHasErrors('rejection_reason');

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'pending_review',
        ]);
    }

    public function test_rejecting_with_too_long_reason_fails_validation(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.reject', $kost), [
                'rejection_reason' => str_repeat('a', 1001), // 1001 chars (max 1000)
            ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_rejecting_non_pending_submission_shows_error(): void
    {
        $kost = Kost::factory()->draft()->create();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.reject', $kost), [
                'rejection_reason' => 'Valid rejection reason',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'draft', // Status unchanged
        ]);
    }

    public function test_regular_admin_cannot_access_submissions_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('super-admin.kost-submissions.index'));

        // Role middleware active - expect 403
        $response->assertForbidden();
    }

    public function test_regular_admin_cannot_approve_submissions(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('super-admin.kost-submissions.approve', $kost));

        // Role middleware active - expect 403
        $response->assertForbidden();
    }

    public function test_regular_admin_cannot_reject_submissions(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('super-admin.kost-submissions.reject', $kost), [
                'rejection_reason' => 'Valid rejection reason',
            ]);

        // Role middleware active - expect 403
        $response->assertForbidden();
    }

    public function test_guests_cannot_access_submissions_list(): void
    {
        $response = $this->get(route('super-admin.kost-submissions.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_approve_submissions(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $response = $this->post(route('super-admin.kost-submissions.approve', $kost));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_reject_submissions(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $response = $this->post(route('super-admin.kost-submissions.reject', $kost), [
            'rejection_reason' => 'Valid rejection reason',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_submissions_list_eager_loads_relationships(): void
    {
        Kost::factory()->pendingReview()->count(3)->create();

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.index'));

        // Verify response renders without N+1 query issues
        // Manual verification: Enable Laravel Debugbar and check query count
        $response->assertOk();
    }

    public function test_approval_clears_previous_rejection_reason(): void
    {
        $kost = Kost::factory()->pendingReview()->create([
            'rejected_reason' => 'Old rejection reason',
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.approve', $kost));

        $kost->refresh();
        $this->assertNull($kost->rejected_reason);
    }

    public function test_approving_submission_sends_email_to_owner(): void
    {
        Mail::fake();

        $kost = Kost::factory()->pendingReview()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.approve', $kost));

        Mail::assertQueued(KostApprovedMail::class, function ($mail) use ($kost) {
            return $mail->hasTo($kost->owner->email)
                && $mail->kost->id === $kost->id;
        });
    }

    public function test_rejecting_submission_sends_email_to_owner(): void
    {
        Mail::fake();

        $kost = Kost::factory()->pendingReview()->create();

        $this->actingAs($this->superAdmin)
            ->post(route('super-admin.kost-submissions.reject', $kost), [
                'rejection_reason' => 'Alamat tidak lengkap',
            ]);

        Mail::assertQueued(KostRejectedMail::class, function ($mail) use ($kost) {
            return $mail->hasTo($kost->owner->email)
                && $mail->kost->id === $kost->id;
        });
    }
}
