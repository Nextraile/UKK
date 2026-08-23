<?php

declare(strict_types=1);

namespace Tests\Feature\Kost;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Address;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KostBasicTest extends TestCase
{
    use RefreshDatabase;

    public function test_kost_can_be_created_via_factory(): void
    {
        $kost = Kost::factory()->create();

        $this->assertDatabaseHas('kosts', [
            'id' => $kost->id,
            'name' => $kost->name,
        ]);
    }

    public function test_kost_slug_is_auto_generated_from_name(): void
    {
        $kost = Kost::factory()->create(['name' => 'Kost Mawar Indah']);

        $this->assertEquals('kost-mawar-indah', $kost->slug);
    }

    public function test_kost_belongs_to_admin_owner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $this->assertInstanceOf(User::class, $kost->owner);
        $this->assertEquals($admin->id, $kost->owner->id);
    }

    public function test_kost_belongs_to_superadmin_approver(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $kost = Kost::factory()->approved()->create([
            'approved_by' => $superadmin->id,
            'approved_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $kost->approver);
        $this->assertEquals($superadmin->id, $kost->approver->id);
    }

    public function test_kost_has_one_address(): void
    {
        $kost = Kost::factory()->create();
        $address = Address::factory()->create(['kost_id' => $kost->id]);

        $this->assertInstanceOf(Address::class, $kost->address);
        $this->assertEquals($address->id, $kost->address->id);
    }

    public function test_kost_has_many_categories(): void
    {
        $kost = Kost::factory()->create();
        $category1 = Category::factory()->create(['name' => 'Kost Putra', 'slug' => 'kost-putra']);
        $category2 = Category::factory()->create(['name' => 'Kost Putri', 'slug' => 'kost-putri']);

        $kost->categories()->attach([$category1->id, $category2->id]);

        $this->assertCount(2, $kost->categories);
        $this->assertContains($category1->id, $kost->categories->pluck('id'));
        $this->assertContains($category2->id, $kost->categories->pluck('id'));
    }

    public function test_kost_has_many_room_types(): void
    {
        $kost = Kost::factory()->create();
        RoomType::factory()->count(3)->create(['kost_id' => $kost->id]);

        $this->assertCount(3, $kost->roomTypes);
    }

    public function test_kost_facilities_and_rules_are_cast_to_array(): void
    {
        $kost = Kost::factory()->create([
            'facilities' => ['WiFi', 'AC', 'Kasur'],
            'rules' => ['No smoking', 'No pets'],
        ]);

        $this->assertIsArray($kost->facilities);
        $this->assertIsArray($kost->rules);
        $this->assertCount(3, $kost->facilities);
        $this->assertCount(2, $kost->rules);
    }

    public function test_is_draft_returns_true_for_draft_status(): void
    {
        $kost = Kost::factory()->create(['status' => 'draft']);

        $this->assertTrue($kost->isDraft());
        $this->assertFalse($kost->isPendingReview());
        $this->assertFalse($kost->isApproved());
        $this->assertFalse($kost->isActive());
        $this->assertFalse($kost->isRejected());
    }

    public function test_is_pending_review_returns_true_for_pending_status(): void
    {
        $kost = Kost::factory()->pendingReview()->create();

        $this->assertTrue($kost->isPendingReview());
        $this->assertFalse($kost->isDraft());
        $this->assertFalse($kost->isApproved());
    }

    public function test_is_approved_returns_true_for_approved_status(): void
    {
        $kost = Kost::factory()->approved()->create();

        $this->assertTrue($kost->isApproved());
        $this->assertFalse($kost->isDraft());
    }

    public function test_is_active_returns_true_for_active_status(): void
    {
        $kost = Kost::factory()->active()->create();

        $this->assertTrue($kost->isActive());
        $this->assertFalse($kost->isDraft());
    }

    public function test_is_rejected_returns_true_for_rejected_status(): void
    {
        $kost = Kost::factory()->rejected()->create();

        $this->assertTrue($kost->isRejected());
        $this->assertFalse($kost->isDraft());
    }

    public function test_kost_can_be_soft_deleted(): void
    {
        $kost = Kost::factory()->create();
        $kost->delete();

        $this->assertSoftDeleted('kosts', ['id' => $kost->id]);
        $this->assertNotNull($kost->fresh()->deleted_at);
    }
}
