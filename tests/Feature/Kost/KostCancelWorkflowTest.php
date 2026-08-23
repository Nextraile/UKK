<?php

declare(strict_types=1);

namespace Tests\Feature\Kost;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KostCancelWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_admin_can_cancel_pending_review_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->create([
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.kosts.cancel', $kost));

        $response->assertRedirect(route('admin.kosts.show', $kost));
        $response->assertSessionHas('success', 'Pengajuan berhasil dibatalkan. Anda dapat mengedit kembali kost ini.');

        $kost->refresh();
        $this->assertEquals('draft', $kost->status);
        $this->assertNull($kost->submitted_at);
    }

    /** @test */
    public function test_other_admin_cannot_cancel_kost(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin1, 'owner')->create(['status' => 'pending_review']);

        $response = $this->actingAs($admin2)->delete(route('admin.kosts.cancel', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_super_admin_cannot_cancel_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'superadmin']);
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'pending_review']);

        $response = $this->actingAs($superAdmin)->delete(route('admin.kosts.cancel', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_cannot_cancel_draft_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'draft']);

        $response = $this->actingAs($admin)->delete(route('admin.kosts.cancel', $kost));

        $response->assertForbidden();

        $kost->refresh();
        $this->assertEquals('draft', $kost->status);
    }

    /** @test */
    public function test_cannot_cancel_approved_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->approved()->create();

        $response = $this->actingAs($admin)->delete(route('admin.kosts.cancel', $kost));

        $response->assertForbidden();

        $kost->refresh();
        $this->assertEquals('approved', $kost->status);
    }

    /** @test */
    public function test_cannot_cancel_rejected_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->rejected()->create();

        $response = $this->actingAs($admin)->delete(route('admin.kosts.cancel', $kost));

        $response->assertForbidden();

        $kost->refresh();
        $this->assertEquals('rejected', $kost->status);
    }

    /** @test */
    public function test_cannot_cancel_active_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->active()->create();

        $response = $this->actingAs($admin)->delete(route('admin.kosts.cancel', $kost));

        $response->assertForbidden();

        $kost->refresh();
        $this->assertEquals('active', $kost->status);
    }

    /** @test */
    public function test_tenant_cannot_cancel_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user']);
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'pending_review']);

        $response = $this->actingAs($tenant)->delete(route('admin.kosts.cancel', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_guest_cannot_cancel_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'pending_review']);

        $response = $this->delete(route('admin.kosts.cancel', $kost));

        $response->assertRedirect(route('login'));
    }
}
