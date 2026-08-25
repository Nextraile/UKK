<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Kost\Models\Kost;
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
            'reviews' => fn ($q) => $q->latest()->limit(10),
            'reviews.tenant',
        ]);

        // Calculate review metrics (COMP-008 placeholder - will work when Review model exists)
        $avgRating = $kost->reviews()->avg('kost_rating');
        $reviewCount = $kost->reviews()->count();

        return view('marketplace.show', compact('kost', 'avgRating', 'reviewCount'));
    }
}
