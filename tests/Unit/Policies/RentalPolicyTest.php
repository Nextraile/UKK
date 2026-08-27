<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Rental;
use App\Policies\RentalPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalPolicyTest extends TestCase
{
    use RefreshDatabase;

    private RentalPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RentalPolicy;
    }

    // viewAny tests

    public function test_tenant_can_view_any_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);

        $this->assertTrue($this->policy->viewAny($tenant));
    }

    public function test_admin_cannot_view_any_rental_as_tenant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertFalse($this->policy->viewAny($admin));
    }

    public function test_superadmin_cannot_view_any_rental_as_tenant(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->assertFalse($this->policy->viewAny($superadmin));
    }

    // view tests

    public function test_tenant_can_view_own_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create(['user_id' => $tenant->id]);

        $this->assertTrue($this->policy->view($tenant, $rental));
    }

    public function test_tenant_cannot_view_other_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $otherTenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create(['user_id' => $otherTenant->id]);

        $this->assertFalse($this->policy->view($tenant, $rental));
    }

    public function test_admin_cannot_view_rental_as_tenant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rental = Rental::factory()->create();

        $this->assertFalse($this->policy->view($admin, $rental));
    }

    // viewAnyAsAdmin tests

    public function test_admin_can_view_any_rental_as_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($this->policy->viewAnyAsAdmin($admin));
    }

    public function test_tenant_cannot_view_any_rental_as_admin(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);

        $this->assertFalse($this->policy->viewAnyAsAdmin($tenant));
    }

    public function test_superadmin_cannot_view_any_rental_as_admin(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $this->assertFalse($this->policy->viewAnyAsAdmin($superadmin));
    }

    // viewAsAdmin tests

    public function test_admin_can_view_rental_for_own_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create(['room_id' => $room->id]);

        $this->assertTrue($this->policy->viewAsAdmin($admin, $rental));
    }

    public function test_admin_cannot_view_rental_for_other_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create(['room_id' => $room->id]);

        $this->assertFalse($this->policy->viewAsAdmin($admin, $rental));
    }

    public function test_tenant_cannot_view_rental_as_admin(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create();

        $this->assertFalse($this->policy->viewAsAdmin($tenant, $rental));
    }

    public function test_superadmin_cannot_view_rental_as_admin(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $rental = Rental::factory()->create();

        $this->assertFalse($this->policy->viewAsAdmin($superadmin, $rental));
    }

    // uploadDocument tests

    public function test_tenant_can_upload_document_for_paid_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'paid',
        ]);

        $this->assertTrue($this->policy->uploadDocument($tenant, $rental));
    }

    public function test_tenant_can_upload_document_for_documents_pending_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'documents_pending',
        ]);

        $this->assertTrue($this->policy->uploadDocument($tenant, $rental));
    }

    public function test_tenant_cannot_upload_document_for_pending_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'pending',
        ]);

        $this->assertFalse($this->policy->uploadDocument($tenant, $rental));
    }

    public function test_tenant_cannot_upload_document_for_confirmed_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'confirmed',
        ]);

        $this->assertFalse($this->policy->uploadDocument($tenant, $rental));
    }

    public function test_tenant_cannot_upload_document_for_other_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $otherTenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $otherTenant->id,
            'status' => 'paid',
        ]);

        $this->assertFalse($this->policy->uploadDocument($tenant, $rental));
    }

    public function test_admin_cannot_upload_document(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rental = Rental::factory()->create(['status' => 'paid']);

        $this->assertFalse($this->policy->uploadDocument($admin, $rental));
    }

    // verifyDocument tests

    public function test_admin_can_verify_document_for_own_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create(['room_id' => $room->id]);

        $this->assertTrue($this->policy->verifyDocument($admin, $rental));
    }

    public function test_admin_cannot_verify_document_for_other_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $otherAdmin->id]);
        $roomType = RoomType::factory()->create(['kost_id' => $kost->id]);
        $room = Room::factory()->create(['room_type_id' => $roomType->id]);
        $rental = Rental::factory()->create(['room_id' => $room->id]);

        $this->assertFalse($this->policy->verifyDocument($admin, $rental));
    }

    public function test_tenant_cannot_verify_document(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create(['user_id' => $tenant->id]);

        $this->assertFalse($this->policy->verifyDocument($tenant, $rental));
    }

    public function test_superadmin_cannot_verify_document(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $rental = Rental::factory()->create();

        $this->assertFalse($this->policy->verifyDocument($superadmin, $rental));
    }

    // cancel tests

    public function test_tenant_can_cancel_pending_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'pending',
            'start_date' => now()->addDays(10),
        ]);

        $this->assertTrue($this->policy->cancel($tenant, $rental));
    }

    public function test_tenant_can_cancel_paid_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'paid',
            'start_date' => now()->addDays(10),
        ]);

        $this->assertTrue($this->policy->cancel($tenant, $rental));
    }

    public function test_tenant_can_cancel_documents_pending_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'documents_pending',
            'start_date' => now()->addDays(10),
        ]);

        $this->assertTrue($this->policy->cancel($tenant, $rental));
    }

    public function test_tenant_can_cancel_confirmed_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'confirmed',
            'start_date' => now()->addDays(10),
        ]);

        $this->assertTrue($this->policy->cancel($tenant, $rental));
    }

    public function test_tenant_cannot_cancel_active_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'active',
            'start_date' => now()->subDays(5),
        ]);

        $this->assertFalse($this->policy->cancel($tenant, $rental));
    }

    public function test_tenant_cannot_cancel_completed_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'completed',
            'start_date' => now()->subMonths(4),
        ]);

        $this->assertFalse($this->policy->cancel($tenant, $rental));
    }

    public function test_tenant_cannot_cancel_already_cancelled_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'cancelled',
            'start_date' => now()->addDays(10),
        ]);

        $this->assertFalse($this->policy->cancel($tenant, $rental));
    }

    public function test_tenant_cannot_cancel_after_start_date(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $tenant->id,
            'status' => 'confirmed',
            'start_date' => now()->subDays(1),
        ]);

        $this->assertFalse($this->policy->cancel($tenant, $rental));
    }

    public function test_tenant_cannot_cancel_other_rental(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $otherTenant = User::factory()->create(['role' => 'user']);
        $rental = Rental::factory()->create([
            'user_id' => $otherTenant->id,
            'status' => 'pending',
            'start_date' => now()->addDays(10),
        ]);

        $this->assertFalse($this->policy->cancel($tenant, $rental));
    }

    public function test_admin_cannot_cancel_rental(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $rental = Rental::factory()->create([
            'status' => 'pending',
            'start_date' => now()->addDays(10),
        ]);

        $this->assertFalse($this->policy->cancel($admin, $rental));
    }
}
