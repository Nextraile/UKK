<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for SuperAdmin category management.
 *
 * Tests CRUD operations, authorization, slug auto-generation, and soft delete.
 */
class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test SuperAdmin can view categories list.
     */
    public function test_superadmin_can_view_categories_list(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('super-admin.categories.index'));

        $response->assertOk();
        $response->assertViewIs('super-admin.categories.index');
        $response->assertViewHas('categories');
    }

    /**
     * Test SuperAdmin can view create category form.
     */
    public function test_superadmin_can_view_create_category_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('super-admin.categories.create'));

        $response->assertOk();
        $response->assertViewIs('super-admin.categories.create');
    }

    /**
     * Test SuperAdmin can create category with auto-generated slug.
     */
    public function test_superadmin_can_create_category_with_auto_generated_slug(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.categories.store'), [
                'name' => 'Kategori Baru',
                'description' => 'Deskripsi kategori baru',
            ]);

        $response->assertRedirect(route('super-admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Kategori Baru',
            'slug' => 'kategori-baru',
            'description' => 'Deskripsi kategori baru',
        ]);
    }

    /**
     * Test SuperAdmin can create category with custom slug.
     */
    public function test_superadmin_can_create_category_with_custom_slug(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.categories.store'), [
                'name' => 'Kategori Custom',
                'slug' => 'custom-slug-unique',
                'description' => 'Deskripsi custom',
            ]);

        $response->assertRedirect(route('super-admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Kategori Custom',
            'slug' => 'custom-slug-unique',
        ]);
    }

    /**
     * Test SuperAdmin cannot create category with duplicate slug.
     */
    public function test_superadmin_cannot_create_duplicate_slug(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Category::factory()->create(['slug' => 'existing-slug']);

        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.categories.store'), [
                'name' => 'Another Category',
                'slug' => 'existing-slug',
            ]);

        $response->assertSessionHasErrors('slug');
    }

    /**
     * Test SuperAdmin can view category detail.
     */
    public function test_superadmin_can_view_category_detail(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('super-admin.categories.show', $category));

        $response->assertOk();
        $response->assertViewIs('super-admin.categories.show');
        $response->assertViewHas('category');
    }

    /**
     * Test SuperAdmin can view edit category form.
     */
    public function test_superadmin_can_view_edit_category_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('super-admin.categories.edit', $category));

        $response->assertOk();
        $response->assertViewIs('super-admin.categories.edit');
        $response->assertViewHas('category');
    }

    /**
     * Test SuperAdmin can update category.
     */
    public function test_superadmin_can_update_category(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $category = Category::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-slug',
        ]);

        $response = $this->actingAs($superAdmin)
            ->put(route('super-admin.categories.update', $category), [
                'name' => 'Updated Name',
                'slug' => 'updated-slug',
                'description' => 'Updated description',
            ]);

        $response->assertRedirect(route('super-admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
            'description' => 'Updated description',
        ]);
    }

    /**
     * Test SuperAdmin cannot update category with duplicate slug.
     */
    public function test_superadmin_cannot_update_to_duplicate_slug(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $category1 = Category::factory()->create(['slug' => 'existing-slug']);
        $category2 = Category::factory()->create(['slug' => 'another-slug']);

        $response = $this->actingAs($superAdmin)
            ->put(route('super-admin.categories.update', $category2), [
                'name' => 'Updated Name',
                'slug' => 'existing-slug',
            ]);

        $response->assertSessionHasErrors('slug');
    }

    /**
     * Test SuperAdmin can soft delete category.
     */
    public function test_superadmin_can_soft_delete_category(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $category = Category::factory()->create(['name' => 'To Be Deleted']);

        $response = $this->actingAs($superAdmin)
            ->delete(route('super-admin.categories.destroy', $category));

        $response->assertRedirect(route('super-admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test Admin cannot access category management routes (403).
     */
    public function test_admin_cannot_access_category_management(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        // Index
        $response = $this->actingAs($admin)
            ->get(route('super-admin.categories.index'));
        $response->assertForbidden();

        // Create
        $response = $this->actingAs($admin)
            ->get(route('super-admin.categories.create'));
        $response->assertForbidden();

        // Store
        $response = $this->actingAs($admin)
            ->post(route('super-admin.categories.store'), [
                'name' => 'Test Category',
            ]);
        $response->assertForbidden();

        // Show
        $response = $this->actingAs($admin)
            ->get(route('super-admin.categories.show', $category));
        $response->assertForbidden();

        // Edit
        $response = $this->actingAs($admin)
            ->get(route('super-admin.categories.edit', $category));
        $response->assertForbidden();

        // Update
        $response = $this->actingAs($admin)
            ->put(route('super-admin.categories.update', $category), [
                'name' => 'Updated',
            ]);
        $response->assertForbidden();

        // Destroy
        $response = $this->actingAs($admin)
            ->delete(route('super-admin.categories.destroy', $category));
        $response->assertForbidden();
    }

    /**
     * Test deleted category not shown in active categories query.
     */
    public function test_deleted_category_not_shown_in_active_query(): void
    {
        $activeCategory = Category::factory()->create(['name' => 'Active']);
        $deletedCategory = Category::factory()->create(['name' => 'Deleted']);
        $deletedCategory->delete();

        $activeCategories = Category::whereNull('deleted_at')->get();

        $this->assertTrue($activeCategories->contains($activeCategory));
        $this->assertFalse($activeCategories->contains('id', $deletedCategory->id));
    }

    /**
     * Test category name is required.
     */
    public function test_category_name_is_required(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.categories.store'), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test category name max length.
     */
    public function test_category_name_max_length(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.categories.store'), [
                'name' => str_repeat('a', 101),
            ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test category description max length.
     */
    public function test_category_description_max_length(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('super-admin.categories.store'), [
                'name' => 'Valid Name',
                'description' => str_repeat('a', 501),
            ]);

        $response->assertSessionHasErrors('description');
    }
}
