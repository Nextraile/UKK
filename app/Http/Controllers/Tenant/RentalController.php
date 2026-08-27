<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Rental\Actions\CancelRental;
use App\Domain\Rental\Actions\CreateRental;
use App\Domain\Rental\Actions\UploadDocument;
use App\Domain\Rental\Exceptions\InvalidRentalStatusException;
use App\Domain\Rental\Exceptions\RoomFullException;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CancelRentalRequest;
use App\Http\Requests\Tenant\CreateRentalRequest;
use App\Http\Requests\Tenant\UploadDocumentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RentalController extends Controller
{
    /**
     * Display list of tenant's rentals (dashboard).
     *
     * FR-096: View own rentals
     * PAGE-007: Dashboard with stat cards + filters
     */
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        // Load rentals with relationships (eager loading to avoid N+1)
        $rentals = $user->rentals()
            ->with([
                'room.roomType.kost.owner',
                'payment',
                'statusHistories',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate stats from collection (no separate queries)
        $stats = [
            'active' => $rentals->where('status', 'active')->count(),
            'pending_actions' => $rentals->whereIn('status', ['pending', 'paid', 'confirmed'])->count(),
            'completed' => $rentals->where('status', 'completed')->count(),
        ];

        return view('tenant.rentals.index', compact('rentals', 'stats'));
    }

    /**
     * Show rental creation form.
     *
     * FR-063: Display available rooms & price schemes
     *
     * Query params: kost_id (optional), room_type_id (optional)
     */
    public function create(): View
    {
        $kostId = request('kost_id');
        $roomTypeId = request('room_type_id');

        // Load available rooms with relationships
        $rooms = Room::with(['roomType.kost', 'roomType.priceSchemes'])
            ->where('status', 'available')
            ->when($kostId, fn ($q) => $q->where('kost_id', $kostId))
            ->when($roomTypeId, fn ($q) => $q->where('room_type_id', $roomTypeId))
            ->get();

        // Build room_id => price_schemes mapping for Alpine.js filtering
        $roomSchemes = $rooms->mapWithKeys(function (Room $room) {
            return [$room->id => $room->roomType->priceSchemes->map(function ($scheme) use ($room) {
                /** @var PriceScheme $scheme */
                return [
                    'id' => $scheme->id,
                    'name' => "{$scheme->duration_value} ".__($scheme->duration_unit).' - Rp '.number_format((float) $scheme->price, 0, ',', '.'),
                    'price' => (float) $scheme->price,
                    'deposit' => (float) $room->roomType->security_deposit,
                ];
            })];
        });

        return view('tenant.rentals.create', compact('rooms', 'roomSchemes'));
    }

    /**
     * Store rental creation.
     *
     * FR-067: Create rental with status pending
     * FR-068: Payment deadline 48 hours
     */
    public function store(CreateRentalRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $rental = (new CreateRental)->execute($validated);

            /** @var Payment $payment */
            $payment = $rental->payment;

            return redirect()
                ->route('rentals.show', $rental)
                ->with('success', 'Booking berhasil dibuat. Selesaikan pembayaran sebelum '.$payment->expired_at->format('d M Y H:i'));
        } catch (RoomFullException $e) {
            return back()
                ->withInput()
                ->withErrors(['room_id' => $e->getMessage()]);
        }
    }

    /**
     * Display rental detail.
     *
     * FR-097: View rental detail
     * FR-103: Display status history timeline
     * PAGE-008: Rental detail with timeline, payment, documents
     */
    public function show(Rental $rental): View
    {
        // Authorization check: tenant must own this rental
        $this->authorize('view', $rental);

        // Eager load relationships
        $rental->load([
            'room.roomType.kost.owner',
            'room.roomType.kost.documentRequirements',
            'payment',
            'rentalDocuments.verifier',
            'statusHistories.user',
        ]);

        return view('tenant.rentals.show', compact('rental'));
    }

    /**
     * Upload document for rental.
     *
     * FR-086, FR-087: Upload required documents
     */
    public function uploadDocument(UploadDocumentRequest $request, Rental $rental): RedirectResponse
    {
        $action = new UploadDocument;
        $document = $action->execute(
            $rental,
            $request->input('document_type'),
            $request->file('file')
        );

        return redirect()
            ->route('rentals.show', $rental)
            ->with('success', "Dokumen {$document->document_type} berhasil diunggah. Menunggu verifikasi admin.");
    }

    /**
     * Show cancellation confirmation form.
     *
     * FR-094: Manual rental cancellation by tenant
     */
    public function cancelForm(Rental $rental): View
    {
        $this->authorize('cancel', $rental);

        // Eager load relationships for display
        $rental->load([
            'room.roomType.kost.owner',
            'payment',
        ]);

        return view('tenant.rentals.cancel', compact('rental'));
    }

    /**
     * Process rental cancellation.
     *
     * FR-094: Manual cancellation with optional reason
     * FR-095: Cancellation side effects (status, emails, history)
     */
    public function cancel(CancelRentalRequest $request, Rental $rental): RedirectResponse
    {
        try {
            $action = new CancelRental;
            $action->execute(
                $rental,
                auth()->id(),
                $request->input('cancellation_reason')
            );

            return redirect()
                ->route('rentals.show', $rental)
                ->with('success', 'Rental berhasil dibatalkan.');
        } catch (InvalidRentalStatusException $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
