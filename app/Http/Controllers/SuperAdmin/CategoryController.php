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
     */
    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::withCount('kosts')
            ->orderBy('name')
            ->paginate(15);

        return view('super-admin.categories.index', compact('categories'));
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
     * Display the specified category.
     */
    public function show(Category $category): View
    {
        $this->authorize('view', $category);

        $category->loadCount('kosts');

        return view('super-admin.categories.show', compact('category'));
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
}
