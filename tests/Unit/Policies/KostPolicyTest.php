<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Policies\KostPolicy;
use Tests\TestCase;

class KostPolicyTest extends TestCase
{
    private KostPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new KostPolicy;
    }

    public function test_admin_can_view_any_kosts(): void
    {
        $admin = User::factory()->make(['role' => 'admin']);

        $this->assertTrue($this->policy->viewAny($admin));
    }

    public function test_superadmin_can_view_any_kosts(): void
    {
        $superadmin = User::factory()->make(['role' => 'superadmin']);

        $this->assertTrue($this->policy->viewAny($superadmin));
    }

    public function test_tenant_cannot_view_any_kosts(): void
    {
        $tenant = User::factory()->make(['role' => 'user']);

        $this->assertFalse($this->policy->viewAny($tenant));
    }

    public function test_admin_can_view_own_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1]);

        $this->assertTrue($this->policy->view($admin, $kost));
    }

    public function test_admin_cannot_view_other_admin_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 2]);

        $this->assertFalse($this->policy->view($admin, $kost));
    }

    public function test_superadmin_can_view_any_kost(): void
    {
        $superadmin = User::factory()->make(['role' => 'superadmin']);
        $kost = Kost::factory()->make(['user_id' => 999]);

        $this->assertTrue($this->policy->view($superadmin, $kost));
    }

    public function test_only_admin_can_create_kost(): void
    {
        $admin = User::factory()->make(['role' => 'admin']);
        $superadmin = User::factory()->make(['role' => 'superadmin']);
        $tenant = User::factory()->make(['role' => 'user']);

        $this->assertTrue($this->policy->create($admin));
        $this->assertFalse($this->policy->create($superadmin));
        $this->assertFalse($this->policy->create($tenant));
    }

    public function test_admin_can_update_own_draft_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'draft']);

        $this->assertTrue($this->policy->update($admin, $kost));
    }

    public function test_admin_can_update_own_rejected_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'rejected']);

        $this->assertTrue($this->policy->update($admin, $kost));
    }

    public function test_admin_cannot_update_own_pending_review_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'pending_review']);

        $this->assertFalse($this->policy->update($admin, $kost));
    }

    public function test_admin_cannot_update_own_approved_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'approved']);

        $this->assertFalse($this->policy->update($admin, $kost));
    }

    public function test_admin_cannot_update_own_active_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'active']);

        $this->assertFalse($this->policy->update($admin, $kost));
    }

    public function test_admin_can_submit_own_draft_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'draft']);

        $this->assertTrue($this->policy->submit($admin, $kost));
    }

    public function test_admin_cannot_submit_non_draft_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'pending_review']);

        $this->assertFalse($this->policy->submit($admin, $kost));
    }

    public function test_superadmin_can_approve_pending_review_kost(): void
    {
        $superadmin = User::factory()->make(['role' => 'superadmin']);
        $kost = Kost::factory()->make(['status' => 'pending_review']);

        $this->assertTrue($this->policy->approve($superadmin, $kost));
    }

    public function test_superadmin_cannot_approve_non_pending_kost(): void
    {
        $superadmin = User::factory()->make(['role' => 'superadmin']);
        $kost = Kost::factory()->make(['status' => 'draft']);

        $this->assertFalse($this->policy->approve($superadmin, $kost));
    }

    public function test_admin_cannot_approve_kost(): void
    {
        $admin = User::factory()->make(['role' => 'admin']);
        $kost = Kost::factory()->make(['status' => 'pending_review']);

        $this->assertFalse($this->policy->approve($admin, $kost));
    }

    public function test_superadmin_can_reject_pending_review_kost(): void
    {
        $superadmin = User::factory()->make(['role' => 'superadmin']);
        $kost = Kost::factory()->make(['status' => 'pending_review']);

        $this->assertTrue($this->policy->reject($superadmin, $kost));
    }

    public function test_admin_can_publish_own_approved_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'approved']);

        $this->assertTrue($this->policy->publish($admin, $kost));
    }

    public function test_admin_cannot_publish_non_approved_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'draft']);

        $this->assertFalse($this->policy->publish($admin, $kost));
    }

    public function test_admin_cannot_publish_other_admin_kost(): void
    {
        $admin1 = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $admin2 = User::factory()->make(['id' => 2, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 2, 'status' => 'approved']);

        $this->assertFalse($this->policy->publish($admin1, $kost));
    }

    public function test_superadmin_cannot_publish_kost(): void
    {
        $superadmin = User::factory()->make(['role' => 'superadmin']);
        $kost = Kost::factory()->make(['status' => 'approved']);

        $this->assertFalse($this->policy->publish($superadmin, $kost));
    }

    public function test_admin_can_delete_own_draft_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'draft']);

        $this->assertTrue($this->policy->delete($admin, $kost));
    }

    public function test_admin_cannot_delete_active_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'active']);

        $this->assertFalse($this->policy->delete($admin, $kost));
    }

    public function test_admin_can_restore_own_deleted_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1]);

        $this->assertTrue($this->policy->restore($admin, $kost));
    }

    public function test_admin_owner_can_cancel_pending_review_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'pending_review']);

        $this->assertTrue($this->policy->cancel($admin, $kost));
    }

    public function test_admin_cannot_cancel_other_admin_kost(): void
    {
        $admin1 = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 2, 'status' => 'pending_review']);

        $this->assertFalse($this->policy->cancel($admin1, $kost));
    }

    public function test_super_admin_cannot_cancel_kost(): void
    {
        $superAdmin = User::factory()->make(['role' => 'superadmin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'pending_review']);

        $this->assertFalse($this->policy->cancel($superAdmin, $kost));
    }

    public function test_admin_cannot_cancel_draft_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'draft']);

        $this->assertFalse($this->policy->cancel($admin, $kost));
    }

    public function test_admin_cannot_cancel_approved_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'approved']);

        $this->assertFalse($this->policy->cancel($admin, $kost));
    }

    public function test_admin_cannot_cancel_active_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'active']);

        $this->assertFalse($this->policy->cancel($admin, $kost));
    }

    public function test_admin_cannot_cancel_rejected_kost(): void
    {
        $admin = User::factory()->make(['id' => 1, 'role' => 'admin']);
        $kost = Kost::factory()->make(['user_id' => 1, 'status' => 'rejected']);

        $this->assertFalse($this->policy->cancel($admin, $kost));
    }

    public function test_no_one_can_force_delete_kost(): void
    {
        $admin = User::factory()->make(['role' => 'admin']);
        $superadmin = User::factory()->make(['role' => 'superadmin']);
        $kost = Kost::factory()->make();

        $this->assertFalse($this->policy->forceDelete($admin, $kost));
        $this->assertFalse($this->policy->forceDelete($superadmin, $kost));
    }
}
