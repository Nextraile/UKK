<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Kost\Models\Kost;
use Illuminate\View\View;

/**
 * Landing page controller.
 *
 * Displays marketing homepage with featured kosts and testimonials.
 * No authentication required (public access).
 *
 * @see PAGES.md §2 PAGE-001 (lines 66-172)
 */
class HomeController extends Controller
{
    /**
     * Display landing page with featured kosts.
     *
     * Featured kosts: 6 random active kosts.
     * Static testimonials for social proof.
     */
    public function index(): View
    {
        // Featured kosts: 6 random active kosts (FR-006: public can browse without auth)
        // Note: Reviews are related through Rental->Room, not directly to Kost,
        // so we use inRandomOrder() instead of sorting by rating for simplicity.
        $featuredKosts = Kost::query()
            ->where('status', 'active')
            ->with([
                'address',
                'kostImages' => fn ($q) => $q->where('is_thumbnail', true),
            ])
            ->inRandomOrder()
            ->limit(6)
            ->get();

        // Static testimonials (can be moved to DB later if needed)
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
