<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Rental\Actions\CancelRental;
use App\Domain\Rental\Actions\CreateRental;
use App\Domain\Rental\Exceptions\InvalidRentalStatusException;
use App\Domain\Rental\Exceptions\RoomFullException;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CancelRentalRequest;
use App\Http\Requests\Tenant\CreateRentalRequest;
use App\Http\Requests\Tenant\UploadPaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            // Pending actions: only pending (awaiting payment) and paid (awaiting doc upload)
            // Confirmed status excluded because admin verifies documents (no tenant action needed)
            'pending_actions' => $rentals->whereIn('status', ['pending', 'paid'])->count(),
            'completed' => $rentals->where('status', 'completed')->count(),
            'cancelled' => $rentals->where('status', 'cancelled')->count(),
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

        // Eager load relationships for single-page view
        $rental->load([
            'room.roomType.kost.owner',
            'room.roomType.kost.address',
            'room.roomType.kost.documentRequirements',
            'payment',
            'rentalDocuments.documentRequirement',
            'rentalDocuments.verifier',
            'statusHistories.user',
            'review', // For completed status
        ]);

        // Calculate progress for progress tracker
        $currentStep = $rental->getCurrentStep();
        $totalSteps = 4;

        // Document upload progress
        $requiredDocs = $rental->room->roomType->kost->documentRequirements;
        $uploadedDocs = $rental->rentalDocuments->filter(fn ($d) => ! empty($d->document_path));
        $verifiedDocs = $uploadedDocs->filter(fn ($d) => $d->verified_at !== null);

        $docProgress = [
            'total' => $requiredDocs->count(),
            'uploaded' => $uploadedDocs->count(),
            'verified' => $verifiedDocs->count(),
        ];

        // Section states for Alpine.js initialization
        $paymentState = $rental->getPaymentSectionState();
        $documentsState = $rental->getDocumentsSectionState();

        // Build steps array for progress stepper component
        $steps = [
            [
                'label' => 'Payment',
                'status' => $rental->status === 'pending' ? 'active' : 'completed',
                'timestamp' => $rental->payment->verified_at?->format('M d, H:i'),
            ],
            [
                'label' => 'Upload Documents',
                'status' => match ($rental->status) {
                    'pending' => 'locked',
                    'paid', 'documents_pending' => 'active',
                    default => 'completed',
                },
                'progress' => in_array($rental->status, ['paid', 'documents_pending'])
                    ? "{$docProgress['verified']}/{$docProgress['total']} verified"
                    : null,
                'message' => $rental->status === 'pending' ? 'Upload payment proof first' : null,
            ],
            [
                'label' => 'Verification',
                'status' => match ($rental->status) {
                    'pending', 'paid', 'documents_pending' => 'locked',
                    'confirmed', 'active', 'completed' => 'completed',
                    default => 'locked',
                },
                'timestamp' => $rental->confirmed_at?->format('M d, H:i'),
                'message' => in_array($rental->status, ['pending', 'paid', 'documents_pending'])
                    ? 'Available after documents verified'
                    : null,
            ],
            [
                'label' => 'Active Rental',
                'status' => match ($rental->status) {
                    'active' => 'active',
                    'completed' => 'completed',
                    default => 'locked',
                },
                'timestamp' => $rental->status === 'completed'
                    ? $rental->completed_at?->format('M d, H:i')
                    : null,
                'message' => ! in_array($rental->status, ['active', 'completed'])
                    ? 'Starts on '.$rental->start_date->format('M d, Y')
                    : null,
            ],
        ];

        return view('tenant.rentals.show', compact(
            'rental',
            'currentStep',
            'totalSteps',
            'docProgress',
            'paymentState',
            'documentsState',
            'steps'
        ));
    }

    /**
     * Upload payment proof (AJAX endpoint).
     *
     * FR-069: Upload payment proof with validation
     * FR-070: Update rental status to 'paid' after upload
     * PAGE-008: Payment upload modal with AJAX submission
     *
     * @param  UploadPaymentRequest  $request  Validated payment upload request
     * @param  Rental  $rental  The rental to upload payment for
     * @return JsonResponse JSON response with success/error status
     */
    public function uploadPayment(UploadPaymentRequest $request, Rental $rental): JsonResponse
    {
        // Authorization check: tenant must own this rental and rental must be pending
        $this->authorize('uploadPayment', $rental);

        try {
            // Store file in public disk (accessible via Storage::url())
            $path = $request->file('payment_proof')->store('payment-proofs', 'public');

            // Update payment record (clear rejection_reason on re-upload)
            $rental->payment->update([
                'proof_of_payment_path' => $path,
                'paid_at' => now(),
                'rejection_reason' => null, // Clear rejection reason on re-upload
            ]);

            // NOTE: Status remains 'pending' until admin verifies payment
            // Status will change to 'paid' only after admin approval via VerifyPayment action

            // Create status history entry (system-generated note)
            $rental->statusHistories()->create([
                'status' => 'pending',
                'changed_by' => auth()->id(),
                'internal_notes' => 'Payment proof uploaded by tenant, awaiting admin verification',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload bukti pembayaran',
            ], 500);
        }
    }

    /**
     * Cancel payment upload (tenant wants to re-upload).
     *
     * Business rule: Only allowed when status is 'paid' and payment not yet verified by admin.
     * This resets rental back to 'pending' status so tenant can upload correct proof.
     *
     * @param  Rental  $rental  The rental to cancel payment for
     * @return JsonResponse JSON response with success/error status
     */
    public function cancelPaymentUpload(Rental $rental): JsonResponse
    {
        // Authorization check: tenant must own this rental
        $this->authorize('update', $rental);

        // Business rule: Can only cancel if status is 'paid' and not yet verified
        if ($rental->status !== 'paid' || $rental->payment->verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membatalkan upload. Pembayaran sudah diverifikasi atau status rental tidak sesuai.',
            ], 403);
        }

        try {
            // Delete uploaded payment proof file
            if ($rental->payment->proof_of_payment_path) {
                \Storage::disk('public')->delete($rental->payment->proof_of_payment_path);
            }

            // Reset payment record
            $rental->payment->update([
                'proof_of_payment_path' => null,
                'paid_at' => null,
                'rejection_reason' => null,
            ]);

            // Reset rental status back to 'pending'
            $rental->update(['status' => 'pending']);

            // Create status history entry
            $rental->statusHistories()->create([
                'status' => 'pending',
                'changed_by' => auth()->id(),
                'internal_notes' => 'Payment upload cancelled by tenant for re-upload',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Upload dibatalkan. Silakan upload ulang bukti pembayaran yang benar.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan upload',
            ], 500);
        }
    }

    /**
     * Upload document for rental (AJAX endpoint for Phase 11 per-card upload).
     *
     * FR-086, FR-087: Upload required documents
     * DESIGN.md §3.41: Per-document upload flow
     *
     * @param  Request  $request  Contains 'document' file and 'type' string
     * @param  Rental  $rental  The rental to upload document for
     * @return JsonResponse JSON response with success/error status
     */
    public function uploadDocument(Request $request, Rental $rental): JsonResponse
    {
        // Authorization check
        $this->authorize('uploadDocument', $rental);

        // Validate input
        $validated = $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
            'type' => 'required|string',
        ]);

        try {
            // Verify document type exists in kost requirements
            $requirement = $rental->room->roomType->kost->documentRequirements()
                ->where('document_type', $validated['type'])
                ->first();

            if (! $requirement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document type not required for this kost',
                    'errors' => ['type' => ['The selected document type is not required.']],
                ], 422);
            }

            // Store file in public disk
            $path = $request->file('document')->store('rental-documents', 'public');

            // Create or update rental document
            $rentalDocument = $rental->rentalDocuments()->updateOrCreate(
                ['document_type' => $validated['type']],
                [
                    'document_path' => $path,
                    'uploaded_at' => now(),
                    'verification_status' => 'pending',
                    'verified_at' => null,
                    'verified_by' => null,
                    'rejection_reason' => null,
                ]
            );

            // Calculate document progress
            $totalRequired = $rental->room->roomType->kost->documentRequirements()->count();
            $uploadedCount = $rental->rentalDocuments()->whereNotNull('document_path')->count();

            // Update rental status if all documents uploaded
            if ($uploadedCount >= $totalRequired && $rental->status === 'paid') {
                $rental->update(['status' => 'documents_pending']);

                // Create status history
                $rental->statusHistories()->create([
                    'status' => 'documents_pending',
                    'changed_by' => auth()->id(),
                    'internal_notes' => 'All documents uploaded, pending verification',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'uploaded_count' => $uploadedCount,
                'total_required' => $totalRequired,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload dokumen',
            ], 500);
        }
    }

    /**
     * Bulk upload multiple documents at once.
     *
     * New feature: Upload all required documents in one submission
     * Allows partial upload - tenant can upload documents incrementally
     * Supports delete operation via delete[] array in request
     *
     * @param  Request  $request  Contains multiple files indexed by document type and optional delete[] array
     * @param  Rental  $rental  The rental to upload documents for
     * @return JsonResponse JSON response with success/error status
     */
    public function bulkUploadDocuments(Request $request, Rental $rental): JsonResponse
    {
        // Authorization check
        $this->authorize('uploadDocument', $rental);

        // Get all document types (required + optional) for this kost
        $allRequirements = $rental->room->roomType->kost->documentRequirements()
            ->get();

        $requiredTypes = $allRequirements->where('is_required', true)->pluck('document_type')->toArray();
        $allTypes = $allRequirements->pluck('document_type')->toArray();

        // Build dynamic validation rules - nullable to allow partial upload
        $rules = [];
        foreach ($allTypes as $type) {
            $rules['documents.'.$type] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'; // 5MB
        }
        $rules['delete'] = 'nullable|array';
        $rules['delete.*'] = 'string';

        // Validate input
        $validated = $request->validate($rules);

        try {
            $uploadedCount = 0;
            $deletedCount = 0;

            // Process deletions first
            if ($request->has('delete')) {
                foreach ($request->input('delete', []) as $docType) {
                    /** @var RentalDocument|null $document */
                    $document = $rental->rentalDocuments()->where('document_type', $docType)->first();

                    if ($document instanceof RentalDocument) {
                        // Prevent deletion if verified
                        if ($document->verified_at) {
                            return response()->json([
                                'success' => false,
                                'message' => "Dokumen '{$docType}' sudah diverifikasi dan tidak dapat dihapus",
                            ], 403);
                        }

                        // Delete file from storage
                        if ($document->document_path && Storage::disk('public')->exists($document->document_path)) {
                            Storage::disk('public')->delete($document->document_path);
                        }

                        // Delete database record
                        $document->delete();
                        $deletedCount++;
                    }
                }
            }

            // Process uploads/replacements for all document types (required + optional)
            foreach ($allTypes as $type) {
                if ($request->hasFile('documents.'.$type)) {
                    $file = $request->file('documents.'.$type);

                    // Store file in public disk
                    $path = $file->store('rental-documents', 'public');

                    // Get existing document if any
                    /** @var RentalDocument|null $existingDoc */
                    $existingDoc = $rental->rentalDocuments()->where('document_type', $type)->first();

                    // Delete old file if replacing
                    if ($existingDoc instanceof RentalDocument && $existingDoc->document_path && Storage::disk('public')->exists($existingDoc->document_path)) {
                        Storage::disk('public')->delete($existingDoc->document_path);
                    }

                    // Create or update rental document
                    $rental->rentalDocuments()->updateOrCreate(
                        ['document_type' => $type],
                        [
                            'document_path' => $path,
                            'uploaded_at' => now(),
                            'verification_status' => 'pending',
                            'verified_at' => null,
                            'verified_by' => null,
                            'rejection_reason' => null,
                        ]
                    );

                    $uploadedCount++;
                }
            }

            // Check if all required documents are now uploaded
            $currentDocCount = $rental->rentalDocuments()->whereIn('document_type', $requiredTypes)->count();

            // Update rental status based on document state
            if ($currentDocCount === count($requiredTypes) && $rental->status === 'paid') {
                // All documents uploaded
                $rental->update(['status' => 'documents_pending']);

                $rental->statusHistories()->create([
                    'status' => 'documents_pending',
                    'changed_by' => auth()->id(),
                    'internal_notes' => "All {$currentDocCount} documents uploaded, pending verification",
                ]);
            } elseif ($currentDocCount < count($requiredTypes) && $rental->status === 'documents_pending') {
                // Some documents deleted, revert to paid
                $rental->update(['status' => 'paid']);

                $rental->statusHistories()->create([
                    'status' => 'paid',
                    'changed_by' => auth()->id(),
                    'internal_notes' => "Document(s) deleted, reverted from documents_pending. Now {$currentDocCount}/".count($requiredTypes).' documents',
                ]);
            }

            $message = [];
            if ($uploadedCount > 0) {
                $message[] = "Berhasil mengupload {$uploadedCount} dokumen";
            }
            if ($deletedCount > 0) {
                $message[] = "Berhasil menghapus {$deletedCount} dokumen";
            }

            return response()->json([
                'success' => true,
                'message' => implode(', ', $message) ?: 'Tidak ada perubahan',
                'uploaded_count' => $uploadedCount,
                'deleted_count' => $deletedCount,
                'total_required' => count($requiredTypes),
                'current_uploaded' => $currentDocCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses dokumen',
            ], 500);
        }
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
            /** @var int $userId */
            $userId = auth()->id();

            $action = new CancelRental;
            $action->execute(
                $rental,
                $userId,
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

    /**
     * Download rental document with authorization.
     *
     * Serves requirement document (KTP, Passport, etc) from private storage
     * after verifying tenant owns the rental. Returns 404 if file not found.
     *
     * @param  RentalDocument  $document  The document to download
     *
     * @throws HttpException 404 if file not found
     */
    public function downloadDocument(RentalDocument $document): BinaryFileResponse
    {
        $this->authorize('view', $document->rental);

        $path = storage_path('app/private/'.$document->document_path);

        if (! file_exists($path)) {
            abort(404, 'Document not found');
        }

        return response()->download($path);
    }
}
