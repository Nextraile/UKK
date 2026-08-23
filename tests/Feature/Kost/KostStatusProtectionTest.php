<?php

declare(strict_types=1);

namespace Tests\Feature\Kost;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Actions\SubmitKostForReview;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for status field protection (FR-023).
 *
 * Verifies that status can only be changed via Action classes,
 * not through direct form updates or mass assignment.
 */
class KostStatusProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_status_field_not_mass_assignable(): void
    {
        $kost = Kost::factory()->draft()->create();

        // Attempt mass assignment should be ignored
        $kost->update(['status' => 'active']);

        // Status should remain unchanged
        $this->assertEquals('draft', $kost->fresh()->status);
    }

    /**
     * @test
     */
    public function test_create_kost_form_rejects_status_field(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.kosts.store'), [
            'name' => 'Test Kost',
            'contact_number' => '08123456789',
            'status' => 'active', // Attempt to set status directly
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseMissing('kosts', ['name' => 'Test Kost']);
    }

    /**
     * @test
     */
    public function test_update_kost_form_rejects_status_field(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->for($admin, 'owner')->create();

        $response = $this->actingAs($admin)->put(route('admin.kosts.update', $kost), [
            'name' => $kost->name,
            'contact_number' => $kost->contact_number,
            'status' => 'active', // Attempt to change status
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertEquals('draft', $kost->fresh()->status);
    }

    /**
     * @test
     */
    public function test_status_can_be_changed_via_action_classes(): void
    {
        $kost = Kost::factory()->draft()->create([
            'name' => 'Complete Kost',
        ]);

        // Add required relationships for submission
        $kost->address()->create([
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Kebayoran Baru',
            'full_address' => 'Jl. Test No. 123',
            'postal_code' => '12345',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        // Create category first
        $category = Category::factory()->create();
        $kost->categories()->attach($category->id);

        RoomType::factory()->for($kost)->create();

        // Use Action class - should work
        app(SubmitKostForReview::class)->execute($kost);

        $this->assertEquals('pending_review', $kost->fresh()->status);
    }

    /**
     * @test
     */
    public function test_create_form_does_not_expose_status_field(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.kosts.create'));

        $response->assertStatus(200);
        $response->assertDontSee('name="status"', false);
        $response->assertDontSee('<select name="status"', false);
    }

    /**
     * @test
     */
    public function test_edit_form_does_not_expose_status_field(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->for($admin, 'owner')->create();

        $response = $this->actingAs($admin)->get(route('admin.kosts.edit', $kost));

        $response->assertStatus(200);
        $response->assertDontSee('name="status"', false);
        $response->assertDontSee('<select name="status"', false);
    }

    /**
     * @test
     */
    public function test_edit_form_shows_status_as_readonly_badge(): void
    {
        $admin = User::factory()->admin()->create();
        $kost = Kost::factory()->draft()->for($admin, 'owner')->create();

        $response = $this->actingAs($admin)->get(route('admin.kosts.show', $kost));

        $response->assertStatus(200);
        $response->assertSee('Draft', false);
        $response->assertSee('bg-gray-100'); // Badge styling
    }
}
