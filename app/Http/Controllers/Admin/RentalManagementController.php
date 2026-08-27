<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RentalManagementController extends Controller
{
    /**
     * Display list of rentals for admin's kosts.
     *
     * FR-098: Admin view rentals for own kost
     */
    public function index(Request $request): View
    {
        $admin = auth()->user();

        // Get all kost IDs owned by this admin
        $kostIds = $admin->kosts()->pluck('id');

        // Query rentals for admin's kosts
        $query = Rental::query()
            ->whereHas('room.roomType.kost', function ($q) use ($admin) {
                $q->where('user_id', $admin->id);
            })
            ->with([
                'room.roomType.kost',
                'user',
                'payment',
            ])
            ->latest('created_at');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by kost if provided
        if ($request->filled('kost_id')) {
            $query->whereHas('room.roomType.kost', function ($q) use ($request) {
                $q->where('id', $request->kost_id);
            });
        }

        // Filter payment verification status (FR-071)
        if ($request->filled('payment_verification') && $request->payment_verification === 'pending') {
            $query->whereHas('payment', function ($q) {
                $q->whereNotNull('proof_of_payment_path');
            });
        }

        $rentals = $query->paginate(20);

        // Get admin's kosts for filter dropdown
        $kosts = $admin->kosts()->get(['id', 'name']);

        return view('admin.rentals.index', compact('rentals', 'kosts'));
    }

    /**
     * Display rental detail for admin review.
     *
     * FR-099: Admin view rental detail
     */
    public function show(Rental $rental): View
    {
        // Authorization: ensure admin owns the kost
        $this->authorize('viewAsAdmin', $rental);

        // Eager load relationships
        $rental->load([
            'room.roomType.kost',
            'user',
            'payment',
            'rentalDocuments.verifier',
            'statusHistories.user',
        ]);

        return view('admin.rentals.show', compact('rental'));
    }

    /**
     * Serve rental document file for admin review.
     *
     * FR-087: Admin view submitted documents before approval
     *
     * @param  RentalDocument  $document  The document to view
     *
     * @throws HttpException 404 if file not found
     */
    public function viewDocument(RentalDocument $document): BinaryFileResponse
    {
        // Authorization: admin must own the kost
        $this->authorize('viewAsAdmin', $document->rental);

        // Check if file exists on private disk
        if (! Storage::disk('private')->exists($document->document_path)) {
            abort(404, 'Document not found');
        }

        // Serve from private disk
        return response()->file(
            Storage::disk('private')->path($document->document_path)
        );
    }
}
