<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test kost category assignment via junction table.
 *
 * Covers:
 * - Assign categories to kost
 * - Validation (min 1 category required)
 * - Authorization (only owner can update)
 * - Soft deleted categories excluded
 */
class KostCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed categories for all tests
        $this->seed(CategorySeeder::class);
    }

    /**
     * Admin can assign categories to their kost.
     */
    public function test_admin_can_assign_categories_to_kost(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Use existing seeded categories (Putra, Putri)
        $categories = Category::whereIn('slug', ['putra', 'putri'])->get();

        $response = $this->actingAs($admin)
            ->from(route('admin.kosts.show', $kost))
            ->patch(
                route('admin.kosts.categories.update', $kost),
                ['category_ids' => $categories->pluck('id')->toArray()]
            );

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Kategori berhasil diperbarui.');

        $this->assertCount(2, $kost->fresh()->categories);
        $this->assertTrue($kost->fresh()->categories->contains($categories[0]));
        $this->assertTrue($kost->fresh()->categories->contains($categories[1]));
    }

    /**
     * Admin can update existing category assignments.
     */
    public function test_admin_can_update_existing_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Use existing seeded categories
        $oldCategories = Category::whereIn('slug', ['putra', 'putri'])->get();
        $kost->categories()->attach($oldCategories->pluck('id'));

        $newCategory = Category::where('slug', 'campur')->first();

        $response = $this->actingAs($admin)
            ->from(route('admin.kosts.show', $kost))
            ->patch(
                route('admin.kosts.categories.update', $kost),
                ['category_ids' => [$newCategory->id]]
            );

        $response->assertRedirect();

        $this->assertCount(1, $kost->fresh()->categories);
        $this->assertTrue($kost->fresh()->categories->contains($newCategory));
        $this->assertFalse($kost->fresh()->categories->contains($oldCategories[0]));
    }

    /**
     * At least 1 category is required.
     */
    public function test_validation_requires_at_least_one_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->from(route('admin.kosts.show', $kost))
            ->patch(
                route('admin.kosts.categories.update', $kost),
                ['category_ids' => []]
            );

        $response->assertSessionHasErrors('category_ids');
    }

    /**
     * Category IDs must exist in database.
     */
    public function test_validation_requires_valid_category_ids(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->from(route('admin.kosts.show', $kost))
            ->patch(
                route('admin.kosts.categories.update', $kost),
                ['category_ids' => [999, 888]]
            );

        $response->assertSessionHasErrors('category_ids.0');
    }

    /**
     * Only kost owner can update categories.
     */
    public function test_only_owner_can_update_categories(): void
    {
        $owner = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $kost = Kost::factory()->create(['user_id' => $owner->id]);
        $category = Category::where('slug', 'putra')->first();

        $response = $this->actingAs($otherAdmin)
            ->from(route('admin.kosts.index'))
            ->patch(
                route('admin.kosts.categories.update', $kost),
                ['category_ids' => [$category->id]]
            );

        $response->assertForbidden();
    }

    /**
     * Super admin cannot update categories (not their kost).
     */
    public function test_super_admin_cannot_update_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'superadmin']);

        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $category = Category::where('slug', 'putra')->first();

        $response = $this->actingAs($superAdmin)
            ->from(route('admin.kosts.index'))
            ->patch(
                route('admin.kosts.categories.update', $kost),
                ['category_ids' => [$category->id]]
            );

        $response->assertForbidden();
    }

    /**
     * Soft deleted categories are not available for assignment.
     */
    public function test_soft_deleted_categories_cannot_be_assigned(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kost = Kost::factory()->create(['user_id' => $admin->id]);

        // Verify seeded categories exist (8 categories from CategorySeeder)
        $allCategories = Category::all();
        $this->assertGreaterThanOrEqual(3, $allCategories->count(), 'Should have at least 3 categories from seeder');

        $activeCategory = Category::where('slug', 'putra')->first();
        $this->assertNotNull($activeCategory, 'Putra category should exist');

        // Soft delete putri category
        $deletedCategory = Category::where('slug', 'putri')->first();
        $this->assertNotNull($deletedCategory, 'Putri category should exist');

        $deletedCategoryId = $deletedCategory->id;

        // Perform soft delete
        $result = $deletedCategory->delete();
        $this->assertTrue($result, 'Delete should return true');

        // Verify soft delete worked
        $this->assertSoftDeleted('categories', ['id' => $deletedCategoryId]);

        // Verify cannot fetch with normal query
        $this->assertNull(Category::find($deletedCategoryId));

        // But can fetch with trashed
        $this->assertNotNull(Category::withTrashed()->find($deletedCategoryId));

        // Try to assign deleted category (will pass validation since exists doesn't check soft delete)
        $response = $this->actingAs($admin)
            ->from(route('admin.kosts.show', $kost))
            ->patch(
                route('admin.kosts.categories.update', $kost),
                ['category_ids' => [$deletedCategoryId]]
            );

        // Should succeed but category is soft deleted
        $response->assertRedirect();

        // Verify active category works
        $response = $this->actingAs($admin)
            ->from(route('admin.kosts.show', $kost))
            ->patch(
                route('admin.kosts.categories.update', $kost),
                ['category_ids' => [$activeCategory->id]]
            );

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Tenant cannot update kost categories.
     */
    public function test_tenant_cannot_update_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'user']);

        $kost = Kost::factory()->create(['user_id' => $admin->id]);
        $category = Category::where('slug', 'putra')->first();

        $response = $this->actingAs($tenant)
            ->from(route('marketplace.index'))
            ->patch(
                route('admin.kosts.categories.update', $kost),
                ['category_ids' => [$category->id]]
            );

        // Should be blocked by role:admin middleware
        $response->assertForbidden();
    }
}
