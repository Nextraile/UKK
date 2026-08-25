<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\RoomType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePriceSchemeRequest;
use App\Http\Requests\Admin\UpdatePriceSchemeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin Price Scheme management controller.
 *
 * Handles CRUD operations for price schemes of room types owned by authenticated Admin.
 */
class PriceSchemeController extends Controller
{
    /**
     * Display price schemes for a room type (inline CRUD).
     *
     * @param  RoomType  $roomType  The room type to list price schemes for
     */
    public function index(RoomType $roomType): View
    {
        $this->authorize('viewAny', [PriceScheme::class, $roomType]);

        $roomType->load(['priceSchemes' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        return view('admin.price-schemes.index', compact('roomType'));
    }

    /**
     * Store a new price scheme.
     *
     * @param  RoomType  $roomType  The room type to add price scheme to
     */
    public function store(StorePriceSchemeRequest $request, RoomType $roomType): RedirectResponse
    {
        $this->authorize('create', [PriceScheme::class, $roomType]);

        $roomType->priceSchemes()->create($request->validated());

        return redirect()
            ->route('admin.price-schemes.index', $roomType)
            ->with('success', 'Paket harga berhasil ditambahkan.');
    }

    /**
     * Update a price scheme.
     *
     * @param  RoomType  $roomType  The room type owning the price scheme
     * @param  PriceScheme  $priceScheme  The price scheme to update
     */
    public function update(UpdatePriceSchemeRequest $request, RoomType $roomType, PriceScheme $priceScheme): RedirectResponse
    {
        $this->authorize('update', $priceScheme);

        $priceScheme->update($request->validated());

        return redirect()
            ->route('admin.price-schemes.index', $roomType)
            ->with('success', 'Paket harga berhasil diperbarui.');
    }

    /**
     * Delete a price scheme.
     *
     * @param  RoomType  $roomType  The room type owning the price scheme
     * @param  PriceScheme  $priceScheme  The price scheme to delete
     */
    public function destroy(RoomType $roomType, PriceScheme $priceScheme): RedirectResponse
    {
        $this->authorize('delete', $priceScheme);

        $priceScheme->delete();

        return redirect()
            ->route('admin.price-schemes.index', $roomType)
            ->with('success', 'Paket harga berhasil dihapus.');
    }

    /**
     * Toggle active status of a price scheme.
     *
     * @param  RoomType  $roomType  The room type owning the price scheme
     * @param  PriceScheme  $priceScheme  The price scheme to toggle
     */
    public function toggleActive(RoomType $roomType, PriceScheme $priceScheme): RedirectResponse
    {
        $this->authorize('update', $priceScheme);

        $priceScheme->update(['is_active' => ! $priceScheme->is_active]);

        return redirect()
            ->route('admin.price-schemes.index', $roomType)
            ->with('success', 'Status paket harga berhasil diubah.');
    }
}
