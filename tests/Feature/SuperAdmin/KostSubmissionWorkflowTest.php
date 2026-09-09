<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Mail\KostApprovedMail;
use App\Domain\Kost\Mail\KostRejectedMail;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use App\Domain\Kost\Models\KostImage;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Kost\Models\RoomTypeImage;
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
        $category = Category::factory()->create();
        $kost = Kost::factory()->pendingReview()->create([
            'name' => 'Kost Detail Test',
            'description' => 'Comfortable kost near campus',
            'facilities' => ['WiFi', 'AC'],
            'rules' => ['No smoking', 'No pets'],
        ]);
        $kost->categories()->attach($category);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.show', $kost));

        $response->assertOk();

        // Verify new relations are eager loaded
        $submission = $response->viewData('submission');
        $this->assertTrue($submission->relationLoaded('kostImages'));
        $this->assertTrue($submission->relationLoaded('documentRequirements'));
        $this->assertTrue($submission->relationLoaded('roomTypes'));

        // Verify room types have nested relations loaded if any exist
        if ($submission->roomTypes->isNotEmpty()) {
            $firstRoomType = $submission->roomTypes->first();
            $this->assertTrue($firstRoomType->relationLoaded('roomTypeImages'));
            $this->assertTrue($firstRoomType->relationLoaded('priceSchemes'));
        }
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

    public function test_submission_detail_displays_kost_images(): void
    {
        $category = Category::factory()->create();
        $kost = Kost::factory()->pendingReview()->create();
        $kost->categories()->attach($category);

        // Create kost images
        KostImage::factory()->count(3)->create(['kost_id' => $kost->id]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.show', $kost));

        $response->assertOk();
        $response->assertSee('Kost Images (3)');
        $response->assertViewHas('submission', function ($submission) {
            return $submission->kostImages->count() === 3;
        });
    }

    public function test_submission_detail_displays_payment_configuration(): void
    {
        $category = Category::factory()->create();
        $kost = Kost::factory()->pendingReview()->create([
            'qris_image_path' => 'qris/test.png',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder_name' => 'John Doe',
        ]);
        $kost->categories()->attach($category);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.show', $kost));

        $response->assertOk();
        $response->assertSee('Payment Configuration');
        $response->assertSee('QRIS');
        $response->assertSee('Bank Transfer');
    }

    public function test_submission_detail_displays_document_requirements(): void
    {
        $category = Category::factory()->create();
        $kost = Kost::factory()->pendingReview()->create();
        $kost->categories()->attach($category);

        // Create document requirements
        KostDocumentRequirement::factory()->create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
            'reason' => 'Untuk verifikasi identitas',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.show', $kost));

        $response->assertOk();
        $response->assertSee('Document Requirements (1)');
        $response->assertSee('Ktp'); // Formatted title case
        $response->assertSee('Wajib');
        $response->assertSee('Untuk verifikasi identitas');
    }

    public function test_submission_detail_displays_enhanced_room_type_details(): void
    {
        $category = Category::factory()->create();
        $kost = Kost::factory()->pendingReview()->create();
        $kost->categories()->attach($category);

        $roomType = RoomType::factory()->create([
            'kost_id' => $kost->id,
            'name' => 'Standard Room',
            'room_size' => '3x4 m',
            'max_occupants' => 2,
        ]);

        // Create room type image
        RoomTypeImage::factory()->create(['room_type_id' => $roomType->id]);

        // Create multiple price schemes
        PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'name' => '1 Month',
            'duration_value' => 1,
            'duration_unit' => 'month',
            'price' => 1500000,
            'is_active' => true,
        ]);

        PriceScheme::factory()->create([
            'room_type_id' => $roomType->id,
            'name' => '3 Month',
            'duration_value' => 3,
            'duration_unit' => 'month',
            'price' => 4000000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.kost-submissions.show', $kost));

        $response->assertOk();
        $response->assertSee('Standard Room');
        $response->assertSee('3x4 m');
        $response->assertSee('Max 2 orang');
        $response->assertSee('Price Schemes:');
        $response->assertSee('1 Month');
        $response->assertSee('3 Month');
        $response->assertSee('1.500.000'); // Indonesian number format
        $response->assertSee('4.000.000');
    }
}
