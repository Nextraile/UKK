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

class KostSubmitWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_admin_can_submit_complete_draft_kost_for_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'draft']);
        Address::factory()->for($kost)->create();
        RoomType::factory()->for($kost)->create();
        $kost->categories()->attach($category->id);

        $response = $this->actingAs($admin)->post(route('admin.kosts.submit', $kost));

        $response->assertRedirect(route('admin.kosts.show', $kost));
        $response->assertSessionHas('success');

        $kost->refresh();
        $this->assertEquals('pending_review', $kost->status);
    }

    /** @test */
    public function test_submit_fails_if_nama_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $kost = Kost::factory()->for($admin, 'owner')->create([
            'status' => 'draft',
            'name' => '', // Missing name
        ]);
        Address::factory()->for($kost)->create();
        RoomType::factory()->for($kost)->create();
        $kost->categories()->attach($category->id);

        $response = $this->actingAs($admin)->post(route('admin.kosts.submit', $kost));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Nama kost', session('error'));

        $kost->refresh();
        $this->assertEquals('draft', $kost->status);
    }

    /** @test */
    public function test_submit_fails_if_address_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'draft']);
        // NO Address created
        RoomType::factory()->for($kost)->create();
        $kost->categories()->attach($category->id);

        $response = $this->actingAs($admin)->post(route('admin.kosts.submit', $kost));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Alamat kost', session('error'));

        $kost->refresh();
        $this->assertEquals('draft', $kost->status);
    }

    /** @test */
    public function test_submit_fails_if_category_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'draft']);
        Address::factory()->for($kost)->create();
        RoomType::factory()->for($kost)->create();
        // NO Category attached

        $response = $this->actingAs($admin)->post(route('admin.kosts.submit', $kost));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Kategori kost', session('error'));

        $kost->refresh();
        $this->assertEquals('draft', $kost->status);
    }

    /** @test */
    public function test_submit_fails_if_room_type_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $kost = Kost::factory()->for($admin, 'owner')->create(['status' => 'draft']);
        Address::factory()->for($kost)->create();
        // NO RoomType created
        $kost->categories()->attach($category->id);

        $response = $this->actingAs($admin)->post(route('admin.kosts.submit', $kost));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Tipe kamar', session('error'));

        $kost->refresh();
        $this->assertEquals('draft', $kost->status);
    }

    /** @test */
    public function test_admin_cannot_submit_other_admin_kost(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $kost = Kost::factory()->for($admin2, 'owner')->create(['status' => 'draft']);
        Address::factory()->for($kost)->create();
        RoomType::factory()->for($kost)->create();
        $kost->categories()->attach($category->id);

        $response = $this->actingAs($admin1)->post(route('admin.kosts.submit', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_cannot_submit_pending_review_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();
        $kost = Kost::factory()->for($admin, 'owner')->pendingReview()->create();
        Address::factory()->for($kost)->create();
        RoomType::factory()->for($kost)->create();
        $kost->categories()->attach($category->id);

        $response = $this->actingAs($admin)->post(route('admin.kosts.submit', $kost));

        $response->assertForbidden(); // Policy blocks non-draft submissions

        $kost->refresh();
        $this->assertEquals('pending_review', $kost->status); // Unchanged
    }

    /** @test */
    public function test_cannot_submit_approved_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->approved()->create();

        $response = $this->actingAs($admin)->post(route('admin.kosts.submit', $kost));

        $response->assertForbidden(); // Policy blocks non-draft submissions
    }

    /** @test */
    public function test_cannot_submit_active_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->for($admin, 'owner')->active()->create();

        $response = $this->actingAs($admin)->post(route('admin.kosts.submit', $kost));

        $response->assertForbidden(); // Policy blocks non-draft submissions
    }

    /** @test */
    public function test_tenant_cannot_submit_kost(): void
    {
        $tenant = User::factory()->create(['role' => 'user']);
        $kost = Kost::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($tenant)->post(route('admin.kosts.submit', $kost));

        $response->assertForbidden();
    }

    /** @test */
    public function test_guest_cannot_submit_kost(): void
    {
        $kost = Kost::factory()->create(['status' => 'draft']);

        $response = $this->post(route('admin.kosts.submit', $kost));

        $response->assertRedirect(route('login'));
    }
}
