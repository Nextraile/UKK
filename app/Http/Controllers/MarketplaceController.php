<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Renders the public kost marketplace (PAGE-001).
 *
 * Displays active kosts with eager-loaded relationships and pagination.
 */
class MarketplaceController extends Controller
{
    /**
     * Display marketplace landing page active kosts.
     *
     * Implements FR-048 (marketplace browsing), FR-049 (display kost info), FR-051 (search),
     * FR-052 (price filter), FR-053 (category filter), FR-054 (rating filter), FR-055 (combined filters).
     * Query optimizations:
     * - Eager loads address, categories, thumbnail images (prevents N+1)
     * - Aggregates review ratings counts (COMP-008, gracefully handles if not yet implemented)
     * - Filters only active kosts, respects soft deletes
     * - Search by name, city, district, or full address (OR logic)
     * - Filters by price range, categories, and minimum rating (AND logic)
     * - Paginates 20 items per page
     *
     * @param  Request  $request  HTTP request with optional filter parameters
     * @return View marketplace page with paginated active kosts, filters, and all categories.
     */
    public function index(Request $request): View
    {
        // Validate inputs (FR-051, FR-052, FR-053, FR-054)
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price_min',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'rating_min' => 'nullable|numeric|min:1|max:5',
        ]);

        $search = $validated['search'] ?? null;
        $priceMin = $validated['price_min'] ?? null;
        $priceMax = $validated['price_max'] ?? null;
        $categories = $validated['categories'] ?? [];
        $ratingMin = $validated['rating_min'] ?? null;

        $kosts = Kost::query()
            ->where('status', 'active')
            ->whereNull('deleted_at')
            // Search filter (FR-051): name OR city OR district OR full_address
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('address', function ($subQuery) use ($search) {
                            $subQuery->where('city', 'like', "%{$search}%")
                                ->orWhere('district', 'like', "%{$search}%")
                                ->orWhere('full_address', 'like', "%{$search}%");
                        });
                });
            })
            // Price filter (FR-052): filter by active price schemes
            ->when($priceMin, function ($query, $priceMin) {
                $query->whereHas('roomTypes.priceSchemes', function ($q) use ($priceMin) {
                    $q->where('is_active', true)
                        ->where('price', '>=', $priceMin);
                });
            })
            ->when($priceMax, function ($query, $priceMax) {
                $query->whereHas('roomTypes.priceSchemes', function ($q) use ($priceMax) {
                    $q->where('is_active', true)
                        ->where('price', '<=', $priceMax);
                });
            })
            // Category filter (FR-053): filter by category IDs
            ->when(! empty($categories), function ($query) use ($categories) {
                $query->whereHas('categories', function ($q) use ($categories) {
                    $q->whereIn('categories.id', $categories);
                });
            })
            ->with([
                'address:id,kost_id,city,district',
                'categories:id,name,slug',
                'kostImages' => fn ($q) => $q->where('is_thumbnail', true)
                    ->select('id', 'kost_id', 'image_path', 'is_thumbnail'),
            ])
            ->withAvg('reviews', 'kost_rating') // Alias: reviews_avg_kost_rating
            ->withCount('reviews') // Alias: reviews_count
            // Rating filter (FR-054): filter by minimum average rating
            ->when($ratingMin, function ($query, $ratingMin) {
                $query->having('reviews_avg_kost_rating', '>=', $ratingMin);
            })
            ->orderByDesc('reviews_avg_kost_rating') // Highest rated first
            ->orderByDesc('published_at') // Then newest
            ->paginate(20);

        // Get all categories for filter sidebar
        $allCategories = Category::orderBy('name')->get();

        return view('marketplace.index', compact('kosts', 'search', 'allCategories'));
    }
}
