<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for Document Requirements CRUD operations.
 *
 * Tests FR-031: Document Requirements Configuration
 * - Admin can add/edit/delete document requirements for their kosts
 * - Unique constraint: 1 document_type per kost
 * - Authorization: only kost owner can manage requirements
 * - Status constraint: only in draft/rejected status
 */
class KostDocumentRequirementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $otherAdmin;

    private Kost $draftKost;

    private Kost $pendingKost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->otherAdmin = User::factory()->admin()->create();

        $this->draftKost = Kost::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'draft',
        ]);

        $this->pendingKost = Kost::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'pending_review',
        ]);
    }

    public function test_admin_can_view_document_requirements_for_their_kost(): void
    {
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.kosts.document-requirements.index', $this->draftKost));

        $response->assertOk();
        $response->assertSee('Persyaratan Dokumen');
        $response->assertSee($requirement->document_type_label);
    }

    public function test_admin_cannot_view_other_admin_kost_requirements(): void
    {
        $response = $this->actingAs($this->otherAdmin)
            ->get(route('admin.kosts.document-requirements.index', $this->draftKost));

        $response->assertForbidden();
    }

    public function test_admin_can_add_document_requirement_to_draft_kost(): void
    {
        $data = [
            'document_type' => 'ktp',
            'is_required' => true,
            'reason' => 'Verifikasi identitas',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kosts.document-requirements.store', $this->draftKost), $data);

        $response->assertRedirect(route('admin.kosts.document-requirements.index', $this->draftKost));
        $response->assertSessionHas('success', 'Persyaratan dokumen berhasil ditambahkan.');

        $this->assertDatabaseHas('kost_document_requirements', [
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
            'is_required' => true,
            'reason' => 'Verifikasi identitas',
        ]);
    }

    public function test_admin_cannot_add_duplicate_document_type_to_same_kost(): void
    {
        KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
        ]);

        $data = [
            'document_type' => 'ktp',
            'is_required' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kosts.document-requirements.store', $this->draftKost), $data);

        $response->assertSessionHasErrors('document_type');
        $this->assertEquals(1, $this->draftKost->documentRequirements()->count());
    }

    public function test_admin_can_add_same_document_type_to_different_kosts(): void
    {
        $anotherKost = Kost::factory()->create([
            'user_id' => $this->admin->id,
            'status' => 'draft',
        ]);

        KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
        ]);

        $data = [
            'document_type' => 'ktp',
            'is_required' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kosts.document-requirements.store', $anotherKost), $data);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('kost_document_requirements', [
            'kost_id' => $anotherKost->id,
            'document_type' => 'ktp',
        ]);
    }

    public function test_admin_cannot_add_requirement_to_pending_review_kost(): void
    {
        $data = [
            'document_type' => 'ktp',
            'is_required' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kosts.document-requirements.store', $this->pendingKost), $data);

        $response->assertForbidden();
    }

    public function test_admin_cannot_add_requirement_to_other_admin_kost(): void
    {
        $data = [
            'document_type' => 'ktp',
            'is_required' => true,
        ];

        $response = $this->actingAs($this->otherAdmin)
            ->post(route('admin.kosts.document-requirements.store', $this->draftKost), $data);

        $response->assertForbidden();
    }

    public function test_validation_requires_document_type(): void
    {
        $data = [
            'is_required' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kosts.document-requirements.store', $this->draftKost), $data);

        $response->assertSessionHasErrors('document_type');
    }

    public function test_validation_requires_valid_document_type(): void
    {
        $data = [
            'document_type' => 'invalid_type',
            'is_required' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kosts.document-requirements.store', $this->draftKost), $data);

        $response->assertSessionHasErrors('document_type');
    }

    public function test_validation_requires_is_required_field(): void
    {
        $data = [
            'document_type' => 'ktp',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kosts.document-requirements.store', $this->draftKost), $data);

        $response->assertSessionHasErrors('is_required');
    }

    public function test_validation_allows_optional_reason(): void
    {
        $data = [
            'document_type' => 'ktp',
            'is_required' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kosts.document-requirements.store', $this->draftKost), $data);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function validation_limits_reason_to_500_characters(): void
    {
        $data = [
            'document_type' => 'ktp',
            'is_required' => true,
            'reason' => str_repeat('a', 501),
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kosts.document-requirements.store', $this->draftKost), $data);

        $response->assertSessionHasErrors('reason');
    }

    public function test_admin_can_update_requirement_for_draft_kost(): void
    {
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
            'is_required' => true,
            'reason' => 'Old reason',
        ]);

        $data = [
            'document_type' => 'selfie',
            'is_required' => false,
            'reason' => 'New reason',
        ];

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.kosts.document-requirements.update', [$this->draftKost, $requirement]), $data);

        $response->assertRedirect(route('admin.kosts.document-requirements.index', $this->draftKost));
        $response->assertSessionHas('success', 'Persyaratan dokumen berhasil diperbarui.');

        $this->assertDatabaseHas('kost_document_requirements', [
            'id' => $requirement->id,
            'document_type' => 'selfie',
            'is_required' => false,
            'reason' => 'New reason',
        ]);
    }

    public function test_admin_cannot_update_requirement_to_duplicate_document_type(): void
    {
        $requirement1 = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
        ]);

        $requirement2 = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'selfie',
        ]);

        $data = [
            'document_type' => 'ktp', // Already exists
            'is_required' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.kosts.document-requirements.update', [$this->draftKost, $requirement2]), $data);

        $response->assertSessionHasErrors('document_type');
    }

    public function test_admin_can_update_requirement_keeping_same_document_type(): void
    {
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
            'is_required' => true,
        ]);

        $data = [
            'document_type' => 'ktp', // Same type
            'is_required' => false, // Only change status
        ];

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.kosts.document-requirements.update', [$this->draftKost, $requirement]), $data);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('kost_document_requirements', [
            'id' => $requirement->id,
            'document_type' => 'ktp',
            'is_required' => false,
        ]);
    }

    public function test_admin_cannot_update_requirement_for_pending_review_kost(): void
    {
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->pendingKost->id,
            'document_type' => 'ktp',
        ]);

        $data = [
            'document_type' => 'selfie',
            'is_required' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.kosts.document-requirements.update', [$this->pendingKost, $requirement]), $data);

        $response->assertForbidden();
    }

    public function test_admin_cannot_update_requirement_for_other_admin_kost(): void
    {
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
        ]);

        $data = [
            'document_type' => 'selfie',
            'is_required' => true,
        ];

        $response = $this->actingAs($this->otherAdmin)
            ->patch(route('admin.kosts.document-requirements.update', [$this->draftKost, $requirement]), $data);

        $response->assertForbidden();
    }

    public function test_admin_can_delete_requirement_from_draft_kost(): void
    {
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.kosts.document-requirements.destroy', [$this->draftKost, $requirement]));

        $response->assertRedirect(route('admin.kosts.document-requirements.index', $this->draftKost));
        $response->assertSessionHas('success', 'Persyaratan dokumen berhasil dihapus.');

        $this->assertDatabaseMissing('kost_document_requirements', [
            'id' => $requirement->id,
        ]);
    }

    public function test_admin_cannot_delete_requirement_from_pending_review_kost(): void
    {
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->pendingKost->id,
            'document_type' => 'ktp',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.kosts.document-requirements.destroy', [$this->pendingKost, $requirement]));

        $response->assertForbidden();
        $this->assertDatabaseHas('kost_document_requirements', ['id' => $requirement->id]);
    }

    public function test_admin_cannot_delete_requirement_from_other_admin_kost(): void
    {
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
            'document_type' => 'ktp',
        ]);

        $response = $this->actingAs($this->otherAdmin)
            ->delete(route('admin.kosts.document-requirements.destroy', [$this->draftKost, $requirement]));

        $response->assertForbidden();
        $this->assertDatabaseHas('kost_document_requirements', ['id' => $requirement->id]);
    }

    public function test_guest_cannot_access_document_requirements(): void
    {
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
        ]);

        $this->get(route('admin.kosts.document-requirements.index', $this->draftKost))
            ->assertRedirect(route('login'));

        $this->post(route('admin.kosts.document-requirements.store', $this->draftKost), [])
            ->assertRedirect(route('login'));

        $this->patch(route('admin.kosts.document-requirements.update', [$this->draftKost, $requirement]), [])
            ->assertRedirect(route('login'));

        $this->delete(route('admin.kosts.document-requirements.destroy', [$this->draftKost, $requirement]))
            ->assertRedirect(route('login'));
    }

    public function test_tenant_cannot_access_document_requirements(): void
    {
        $tenant = User::factory()->tenant()->create();
        $requirement = KostDocumentRequirement::factory()->create([
            'kost_id' => $this->draftKost->id,
        ]);

        $this->actingAs($tenant)
            ->get(route('admin.kosts.document-requirements.index', $this->draftKost))
            ->assertForbidden();

        $this->actingAs($tenant)
            ->post(route('admin.kosts.document-requirements.store', $this->draftKost), [])
            ->assertForbidden();

        $this->actingAs($tenant)
            ->patch(route('admin.kosts.document-requirements.update', [$this->draftKost, $requirement]), [])
            ->assertForbidden();

        $this->actingAs($tenant)
            ->delete(route('admin.kosts.document-requirements.destroy', [$this->draftKost, $requirement]))
            ->assertForbidden();
    }
}
