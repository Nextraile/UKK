<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Identity\Models\User;
use App\Domain\Rental\Actions\RejectPayment;
use App\Domain\Rental\Actions\VerifyDocument;
use App\Domain\Rental\Actions\VerifyPayment;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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

    /**
     * Approve payment (AJAX endpoint for Phase 12).
     *
     * FR-072: Admin approve payment
     */
    public function approvePayment(Rental $rental): JsonResponse
    {
        // Authorization: admin must own the kost
        $this->authorize('viewAsAdmin', $rental);

        try {
            /** @var User $admin */
            $admin = auth()->user();

            app(VerifyPayment::class)->execute($rental->payment, $admin);

            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject payment (AJAX endpoint for Phase 12).
     *
     * FR-073: Admin reject payment with reason
     */
    public function rejectPayment(Request $request, Rental $rental): JsonResponse
    {
        // Authorization: admin must own the kost
        $this->authorize('viewAsAdmin', $rental);

        // Validate rejection reason
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        try {
            /** @var User $admin */
            $admin = auth()->user();

            app(RejectPayment::class)->execute(
                $rental->payment,
                $validated['rejection_reason'],
                $admin
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment rejected, tenant notified',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve document (AJAX endpoint for Phase 12).
     *
     * FR-088: Admin verifies document
     */
    public function approveDocument(RentalDocument $document): JsonResponse
    {
        // Authorization: admin must own the kost
        $this->authorize('viewAsAdmin', $document->rental);

        try {
            app(VerifyDocument::class)->execute($document, true);

            return response()->json([
                'success' => true,
                'message' => 'Document approved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject document (AJAX endpoint for Phase 12).
     *
     * FR-089: Admin rejects document with reason
     */
    public function rejectDocument(Request $request, RentalDocument $document): JsonResponse
    {
        // Authorization: admin must own the kost
        $this->authorize('viewAsAdmin', $document->rental);

        // Validate rejection reason
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        try {
            app(VerifyDocument::class)->execute(
                $document,
                false,
                $validated['rejection_reason']
            );

            return response()->json([
                'success' => true,
                'message' => 'Document rejected, tenant notified',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve all pending documents in bulk (AJAX endpoint for Phase 12).
     *
     * FR-088: Admin verifies document (bulk action)
     */
    public function approveAllDocuments(Rental $rental): JsonResponse
    {
        // Authorization: admin must own the kost
        $this->authorize('viewAsAdmin', $rental);

        try {
            // Get all pending documents (properly typed)
            $pendingDocuments = $rental->rentalDocuments()
                ->where('verification_status', 'pending')
                ->get();

            if ($pendingDocuments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending documents to approve',
                ], 400);
            }

            // Approve each document
            /** @var RentalDocument $document */
            foreach ($pendingDocuments as $document) {
                app(VerifyDocument::class)->execute($document, true);
            }

            return response()->json([
                'success' => true,
                'message' => "All {$pendingDocuments->count()} documents approved successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
