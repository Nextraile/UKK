<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Review\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\MarketplaceFilterRequest;
use Illuminate\Support\Facades\DB;
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
     * @param  MarketplaceFilterRequest  $request  HTTP request with optional filter parameters
     * @return View marketplace page with paginated active kosts, filters, and all categories.
     */
    public function index(MarketplaceFilterRequest $request): View
    {
        $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $priceMin = $validated['price_min'] ?? null;
        $priceMax = $validated['price_max'] ?? null;
        $categories = $validated['categories'] ?? [];
        $ratingMin = $validated['rating_min'] ?? null;

        $kosts = Kost::query()
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('address', function ($subQuery) use ($search) {
                            // Use FULLTEXT in production, LIKE in testing (FULLTEXT needs proper dataset)
                            if (app()->environment('testing')) {
                                $subQuery->where('city', 'like', "%{$search}%")
                                    ->orWhere('district', 'like', "%{$search}%")
                                    ->orWhere('full_address', 'like', "%{$search}%");
                            } else {
                                // FULLTEXT search with BOOLEAN MODE (production)
                                $subQuery->whereRaw(
                                    'MATCH(full_address, district, city) AGAINST(? IN BOOLEAN MODE)',
                                    [$search]
                                );
                            }
                        });
                });
            })
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
            ->when(! empty($categories), function ($query) use ($categories) {
                $query->whereHas('categories', function ($q) use ($categories) {
                    $q->whereIn('categories.id', $categories);
                });
            })
            ->when($ratingMin, function ($query, $ratingMin) {
                $kostIdsWithRating = DB::table('reviews')
                    ->join('rentals', 'reviews.rental_id', '=', 'rentals.id')
                    ->join('rooms', 'rentals.room_id', '=', 'rooms.id')
                    ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                    ->whereNotNull('reviews.kost_rating')
                    ->groupBy('room_types.kost_id')
                    ->havingRaw('AVG(reviews.kost_rating) >= ?', [$ratingMin])
                    ->pluck('room_types.kost_id');

                $query->whereIn('id', $kostIdsWithRating);
            })
            ->addSelect([
                'kosts.*',
                'average_kost_rating' => Review::selectRaw('ROUND(AVG(kost_rating), 1)')
                    ->join('rentals', 'reviews.rental_id', '=', 'rentals.id')
                    ->join('rooms', 'rentals.room_id', '=', 'rooms.id')
                    ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                    ->whereColumn('room_types.kost_id', 'kosts.id')
                    ->whereNotNull('kost_rating'),
                'review_count' => Review::selectRaw('COUNT(*)')
                    ->join('rentals', 'reviews.rental_id', '=', 'rentals.id')
                    ->join('rooms', 'rentals.room_id', '=', 'rooms.id')
                    ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                    ->whereColumn('room_types.kost_id', 'kosts.id')
                    ->whereNotNull('kost_rating'),
            ])
            ->with([
                'address:id,kost_id,full_address,district,city,province,postal_code',
                'categories:id,name,slug',
                'kostImages' => fn ($q) => $q->where('is_thumbnail', true)
                    ->select('id', 'kost_id', 'image_path', 'is_thumbnail'),
            ])
            ->orderByDesc('published_at')
            ->paginate(20);

        $allCategories = Category::orderBy('name')->get();

        return view('marketplace.index', compact('kosts', 'search', 'allCategories'));
    }
}
