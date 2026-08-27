<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Display rooms grouped by room type.
     */
    public function index(Kost $kost): View
    {
        $this->authorize('viewAny', [Room::class, $kost]);

        $roomTypes = $kost->roomTypes()
            ->with(['rooms' => function ($query) {
                $query->orderBy('code');
            }])
            ->orderBy('name')
            ->get();

        return view('admin.rooms.index', compact('kost', 'roomTypes'));
    }

    /**
     * Store a new room.
     */
    public function store(StoreRoomRequest $request, Kost $kost): RedirectResponse
    {
        $this->authorize('create', [Room::class, $kost]);

        Room::create($request->validated());

        return redirect()
            ->route('admin.rooms.index', $kost)
            ->with('success', 'Room created successfully.');
    }

    /**
     * Update a room.
     */
    public function update(UpdateRoomRequest $request, Kost $kost, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $room->update($request->validated());

        return redirect()
            ->route('admin.rooms.index', $kost)
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Delete a room.
     */
    public function destroy(Kost $kost, Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        $room->delete();

        return redirect()
            ->route('admin.rooms.index', $kost)
            ->with('success', 'Room deleted successfully.');
    }

    /**
     * Set room status (available/unavailable).
     *
     * FR-046: Room can only be set unavailable if no active rentals.
     * ADR-009: Authorization enforced via RoomPolicy::setUnavailable().
     */
    public function setStatus(Request $request, Kost $kost, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,unavailable'],
        ]);

        // Policy enforcement: setUnavailable checks used_slots === 0
        if ($validated['status'] === 'unavailable') {
            $this->authorize('setUnavailable', $room);
        } else {
            $this->authorize('update', $room);
        }

        // Double-check validation (redundant with policy, but defensive)
        if ($validated['status'] === 'unavailable' && $room->used_slots > 0) {
            return redirect()
                ->route('admin.rooms.index', $kost)
                ->with('error', 'Cannot set room unavailable - active rentals exist.');
        }

        $room->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.rooms.index', $kost)
            ->with('success', 'Room status updated successfully.');
    }
}
