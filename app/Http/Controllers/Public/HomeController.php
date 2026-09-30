<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Kost\Models\Kost;
use App\Domain\Rental\Models\Rental;
use App\Domain\Review\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Landing page controller.
 *
 * Displays homepage with featured kosts and testimonials.
 * No authentication required (public access).
 */
class HomeController extends Controller
{
    /**
     * Display landing page with featured kosts.
     *
     * Featured kosts: 6 kosts with highest ratings (Kost Terpopuler).
     * Falls back to newest kosts if no ratings available.
     * Static testimonials for social proof.
     */
    public function index(): View
    {
        // Note: Reviews are related through Rental->Room, not directly to Kost,
        // so we use inRandomOrder() instead of sorting by rating for simplicity, at least for now.
        $featuredKosts = Kost::query()
            ->where('status', 'active')
            ->with([
                'address',
                'kostImages' => fn ($q) => $q->where('is_thumbnail', true),
            ])
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
                'completed_rentals_count' => Rental::selectRaw('COUNT(*)')
                    ->join('rooms', 'rentals.room_id', '=', 'rooms.id')
                    ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                    ->whereColumn('room_types.kost_id', 'kosts.id')
                    ->where('rentals.status', 'completed'),
            ])
            ->orderByRaw('COALESCE(average_kost_rating, 0) DESC')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Static testimonials
        $testimonials = [
            [
                'quote' => 'Proses booking sangat mudah dan transparan. Pembayaran via QRIS juga cepat. Sangat recommended!',
                'name' => 'Rina Marlina',
                'location' => 'Kost Mawar Indah — Jakarta',
                'avatar' => null,
            ],
            [
                'quote' => 'Sistem verifikasi dokumen yang ketat membuat saya merasa aman. Pemilik kost juga responsif.',
                'name' => 'Budi Santoso',
                'location' => 'Kost Melati — Bandung',
                'avatar' => null,
            ],
            [
                'quote' => 'Harga transparan, tidak ada biaya tersembunyi. Kamarnya sesuai dengan foto yang ditampilkan.',
                'name' => 'Siti Nurhaliza',
                'location' => 'Kost Anggrek — Surabaya',
                'avatar' => null,
            ],
        ];

        return view('welcome', compact('featuredKosts', 'testimonials'));
    }
}
