<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Renders the public kost marketplace (PAGE-001).
 *
 * NOTE: This is an interim STUB — kost publishing does not exist yet.
 * The full marketplace (search, filters, paginated kost listings) is built
 * in TASK-036/COMP-005. Until then, `$kosts` is an empty collection so the
 * view renders its empty-state design.
 */
class MarketplaceController extends Controller
{
    /**
     * Display the marketplace landing page.
     *
     * @return View The marketplace page with an (empty) list of kosts.
     */
    public function index(): View
    {
        return view('marketplace.index', ['kosts' => collect()]);
    }
}
