<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Kost\Models\Kost;
use App\Domain\Review\Models\Review;
use Illuminate\View\View;

/**
 * Handles public kost detail page (PAGE-003).
 *
 * Display complete kost information for Active kosts only.
 */
class KostDetailController extends Controller
{
    /**
     * Display kost detail page.
     *
     * @param  Kost  $kost  Route model binding by slug
     */
    public function show(Kost $kost): View
    {
        // Only show active kosts (FR-057)
        abort_if($kost->status !== 'active', 404);

        // Eager load all required relationships to prevent N+1 queries
        $kost->load([
            'address',
            'categories',
            'kostImages' => fn ($q) => $q->orderBy('sort_order'),
            'documentRequirements',
            'roomTypes.priceSchemes' => fn ($q) => $q->where('is_active', true),
            'roomTypes.roomTypeImages',
        ]);

        // Get reviews with pagination (COMP-008)
        $reviews = Review::whereHas('rental.room', function ($query) use ($kost) {
            $query->where('kost_id', $kost->id);
        })
            ->with(['rental.user'])
            ->latest()
            ->paginate(10);

        // Calculate review metrics
        $avgKostRating = Review::whereHas('rental.room', function ($query) use ($kost) {
            $query->where('kost_id', $kost->id);
        })
            ->whereNotNull('kost_rating')
            ->avg('kost_rating');

        $avgRoomRating = Review::whereHas('rental.room', function ($query) use ($kost) {
            $query->where('kost_id', $kost->id);
        })
            ->whereNotNull('room_rating')
            ->avg('room_rating');

        $reviewCount = $reviews->total();

        return view('marketplace.show', compact('kost', 'reviews', 'avgKostRating', 'avgRoomRating', 'reviewCount'));
    }
}
