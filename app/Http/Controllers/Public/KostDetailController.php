<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Kost\Models\Kost;
use App\Domain\Review\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Handles public kost detail page.
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
        abort_if($kost->status !== 'active', 404);

        $kost->load([
            'address',
            'categories',
            'kostImages' => fn ($q) => $q->orderBy('sort_order'),
            'documentRequirements',
            'roomTypes.priceSchemes' => fn ($q) => $q->where('is_active', true),
            'roomTypes.roomTypeImages',
            'roomTypes.rooms.rentals' => fn ($q) => $q->whereIn('status', ['payment_pending', 'paid', 'documents_pending', 'confirmed', 'active']),
        ]);

        $reviews = Review::whereHas('rental.room', function ($query) use ($kost) {
            $query->where('kost_id', $kost->id);
        })
            ->with(['rental.user'])
            ->latest()
            ->paginate(10);

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
