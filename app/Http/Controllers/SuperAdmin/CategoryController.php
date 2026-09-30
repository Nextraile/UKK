<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Domain\Kost\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreCategoryRequest;
use App\Http\Requests\SuperAdmin\UpdateCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Super Admin controller for managing categories.
 *
 * Routes: /super-admin/categories
 *
 * Only SuperAdmin can CRUD categories (Admin only assigns them to kosts).
 */
class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * Supports tab filtering between active and soft-deleted categories.
     * Query param: ?status=deleted to show only trashed categories.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        $status = request('status', 'active');

        $query = Category::withCount('kosts')->orderBy('name');

        if ($status === 'deleted') {
            $query->onlyTrashed();
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('super-admin.categories.index', compact('categories', 'status'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('super-admin.categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $category = Category::create($request->validated());

        return redirect()
            ->route('super-admin.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil dibuat.");
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('super-admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update($request->validated());

        return redirect()
            ->route('super-admin.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified category from storage (soft delete).
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()
            ->route('super-admin.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil dihapus.");
    }

    /**
     * Restore a soft-deleted category.
     *
     * Business rule: Category must be soft-deleted to be restored.
     *
     * @param  Category  $category  Category instance (with withTrashed binding)
     */
    public function restore(Category $category): RedirectResponse
    {
        $this->authorize('restore', $category);

        if (! $category->trashed()) {
            return redirect()
                ->back()
                ->with('error', 'Kategori tidak dalam status terhapus.');
        }

        $category->restore();

        return redirect()
            ->route('super-admin.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil dipulihkan.");
    }

    /**
     * Permanently delete a category.
     *
     * Business rules:
     * 1. Category must be soft-deleted first
     * 2. Category must not be used by any kost
     *
     * @param  Category  $category  Category instance (with withTrashed binding)
     */
    public function forceDelete(Category $category): RedirectResponse
    {
        $this->authorize('forceDelete', $category);

        // Rule 1: Must be soft-deleted first
        if (! $category->trashed()) {
            return redirect()
                ->back()
                ->with('error', 'Kategori harus di-soft delete terlebih dahulu.');
        }

        // Rule 2: Must not be used by any kost
        $kostsCount = $category->kosts()->count();
        if ($kostsCount > 0) {
            return redirect()
                ->back()
                ->with('error', "Kategori masih digunakan oleh {$kostsCount} kost. Hapus relasi terlebih dahulu.");
        }

        $name = $category->name;
        $category->forceDelete();

        return redirect()
            ->route('super-admin.categories.index', ['status' => 'deleted'])
            ->with('success', "Kategori \"{$name}\" berhasil dihapus permanen.");
    }
}
