<?php

declare(strict_types=1);

namespace Tests\Feature\Kost;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminKostCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_admin_can_view_own_kosts_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ownKost = Kost::factory()->for($admin, 'owner')->create();
        $otherKost = Kost::factory()->create(); // Other admin's kost

        $response = $this->actingAs($admin)->get(route('admin.kosts.index'));

        $response->assertOk();
        $response->assertSee($ownKost->name);
        $response->assertDontSee($otherKost->name);
    }

    /** @test */
    public function test_tenant_cannot_access_admin_kosts_index(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($tenant)->get(route('admin.kosts.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function test_guest_cannot_access_admin_kosts_index(): void
    {
        $response = $this->get(route('admin.kosts.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_admin_can_view_create_kost_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.kosts.create'));

        $response->assertOk();
        $response->assertSee('Buat Kost Baru');
    }

    /** @test */
    public function test_admin_can_create_new_kost_as_draft(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.kosts.store'), [
            'name' => 'Kost Mawar Indah',
            'contact_number' => '081234567890',
            'description' => 'Kost nyaman dekat kampus',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kosts', [
            'name' => 'Kost Mawar Indah',
            'user_id' => $admin->id,
            'status' => 'draft',
            'contact_number' => '081234567890',
        ]);
    }

    /** @test */
    public function test_create_kost_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.kosts.store'), [
            'name' => '', // Required
            'contact_number' => '', // Required
        ]);

        $response->assertSessionHasErrors(['name', 'contact_number']);
    }

    /** @test */
    public function test_admin_can_view_own_kost_detail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->create();

        $response = $this->actingAs($admin)->get(route('admin.kosts.show', $kost));

        $response->assertOk();
        $response->assertSee($kost->name);
        $response->assertSee($kost->contact_number);
    }

    /** @test */
    public function test_admin_cannot_view_other_admin_kost(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin2, 'owner')->create();

        $response = $this->actingAs($admin1)->get(route('admin.kosts.show', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_admin_can_view_edit_form_for_draft_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'draft']);

        $response = $this->actingAs($admin)->get(route('admin.kosts.edit', $kost));

        $response->assertOk();
        $response->assertSee($kost->name);
    }

    /** @test */
    public function test_admin_cannot_edit_pending_review_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->pendingReview()->create();

        $response = $this->actingAs($admin)->get(route('admin.kosts.edit', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_admin_can_update_own_draft_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'draft']);

        $response = $this->actingAs($admin)->patch(route('admin.kosts.update', $kost), [
            'name' => 'Updated Kost Name',
            'contact_number' => '089999999999',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect(route('admin.kosts.show', $kost));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'name' => 'Updated Kost Name',
            'contact_number' => '089999999999',
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function test_rejected_kost_auto_reverts_to_draft_on_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->rejected()->create([
            'rejected_reason' => 'Data tidak lengkap',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.kosts.update', $kost), [
            'name' => 'Revised Kost Name',
            'contact_number' => '081111111111',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $kost->refresh();
        $this->assertEquals('draft', $kost->status);
        $this->assertNull($kost->rejected_reason);
    }

    /** @test */
    public function test_admin_cannot_update_other_admin_kost(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin2, 'owner')->create(['status' => 'draft']);

        $response = $this->actingAs($admin1)->patch(route('admin.kosts.update', $kost), [
            'name' => 'Hacked Name',
            'contact_number' => '081234567890',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function test_admin_can_delete_own_draft_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'draft']);

        $response = $this->actingAs($admin)->delete(route('admin.kosts.destroy', $kost));

        $response->assertRedirect(route('admin.kosts.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('kosts', ['id' => $kost->id]);
    }

    /** @test */
    public function test_admin_cannot_delete_active_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->active()->create();

        $response = $this->actingAs($admin)->delete(route('admin.kosts.destroy', $kost));

        $response->assertForbidden();
        $this->assertDatabaseHas('kosts', ['id' => $kost->id, 'deleted_at' => null]);
    }

    /** @test */
    public function test_admin_cannot_delete_other_admin_kost(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin2, 'owner')->create(['status' => 'draft']);

        $response = $this->actingAs($admin1)->delete(route('admin.kosts.destroy', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_superadmin_cannot_access_admin_crud_routes(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $kost = Kost::factory()->create();

        $createResponse = $this->actingAs($superadmin)->get(route('admin.kosts.create'));
        $storeResponse = $this->actingAs($superadmin)->post(route('admin.kosts.store'), [
            'name' => 'Test',
            'contact_number' => '081234567890',
        ]);

        $createResponse->assertForbidden();
        $storeResponse->assertForbidden();
    }

    /** @test */
    public function test_admin_can_publish_own_approved_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->approved()->for($admin, 'owner')->create(['name' => 'Kost Published']);

        $response = $this->actingAs($admin)->post(route('admin.kosts.publish', $kost));

        $response->assertRedirect(route('admin.kosts.show', $kost));
        $response->assertSessionHas('success', "Kost 'Kost Published' berhasil dipublikasikan.");

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'active',
        ]);

        $kost->refresh();
        $this->assertNotNull($kost->published_at);
    }

    /** @test */
    public function test_admin_cannot_publish_draft_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->draft()->for($admin, 'owner')->create();

        $response = $this->actingAs($admin)->post(route('admin.kosts.publish', $kost));

        // Policy blocks this (status != approved), returns 403 Forbidden
        $response->assertForbidden();

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'status' => 'draft', // Status unchanged
        ]);
    }

    /** @test */
    public function test_admin_cannot_publish_other_admin_kost(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->approved()->for($admin2, 'owner')->create();

        $response = $this->actingAs($admin1)->post(route('admin.kosts.publish', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_superadmin_cannot_publish_kost(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $kost = Kost::factory()->approved()->create();

        $response = $this->actingAs($superadmin)->post(route('admin.kosts.publish', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_tenant_cannot_publish_kost(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $kost = Kost::factory()->approved()->create();

        $response = $this->actingAs($tenant)->post(route('admin.kosts.publish', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_guest_cannot_publish_kost(): void
    {
        $kost = Kost::factory()->approved()->create();

        $response = $this->post(route('admin.kosts.publish', $kost));

        $response->assertRedirect(route('login'));
    }
}
