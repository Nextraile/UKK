<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Payment\Models\Payment;
use App\Domain\Rental\Actions\CancelRental;
use App\Domain\Rental\Actions\CreateRental;
use App\Domain\Rental\Exceptions\InvalidRentalStatusException;
use App\Domain\Rental\Exceptions\RoomFullException;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use App\Domain\RoomInventory\Models\Room;
use App\Domain\Shared\Exceptions\InvalidFileException;
use App\Domain\Shared\Services\SecureFileUploadService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CancelRentalRequest;
use App\Http\Requests\Tenant\CreateRentalRequest;
use App\Http\Requests\Tenant\UploadDocumentRequest;
use App\Http\Requests\Tenant\UploadPaymentRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
            // Pending actions: only payment_pending (awaiting payment) and paid (awaiting doc upload)
            // Confirmed status excluded because admin verifies documents (no tenant action needed)
            'pending_actions' => $rentals->whereIn('status', ['payment_pending', 'paid'])->count(),
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
     * Query params: kost_id (required)
     */
    public function create(Request $request): View
    {
        // Validate kost_id
        $request->validate([
            'kost_id' => 'required|exists:kosts,id',
        ]);

        $kost = Kost::with([
            'address',
            'roomTypes.rooms',
            'roomTypes.priceSchemes' => fn ($q) => $q->where('is_active', true),
        ])->findOrFail($request->kost_id);

        // Calculate availability for ALL rooms with ALL price schemes
        $availabilityMatrix = $this->buildAvailabilityMatrix($kost->roomTypes);

        return view('tenant.rentals.create', compact('kost', 'availabilityMatrix'));
    }

    /**
     * Build availability matrix for all room + price scheme combinations.
     *
     * Structure:
     * [
     *   room_id => [
     *     'room_code' => string,
     *     'max_occupants' => int,
     *     'schemes' => [
     *       price_scheme_id => [
     *         'duration_unit' => string,
     *         'price' => float,
     *         'free_slots' => int,
     *         'estimated_start' => Y-m-d,
     *         'estimated_end' => Y-m-d,
     *         'available' => bool,
     *       ]
     *     ]
     *   ]
     * ]
     */
    private function buildAvailabilityMatrix($roomTypes): array
    {
        $matrix = [];
        $today = now();
        $minStartDate = $today->copy()->addDays(4); // ADR-016: Min 4 days advance

        foreach ($roomTypes as $roomType) {
            foreach ($roomType->rooms as $room) {
                if ($room->status !== 'available') {
                    continue; // Skip unavailable rooms
                }

                $matrix[$room->id] = [
                    'room_code' => $room->code,
                    'max_occupants' => $roomType->max_occupants,
                    'schemes' => [],
                ];

                foreach ($roomType->priceSchemes as $priceScheme) {
                    // Calculate estimated period for this price scheme
                    $startDate = $minStartDate->copy();
                    $endDate = $this->calculateEndDate(
                        $startDate,
                        1, // 1 unit of duration (for estimation)
                        $priceScheme->duration_unit
                    );

                    // Check REAL availability for this specific period
                    $freeSlots = $room->getFreeSlotsForPeriod($startDate, $endDate);

                    $matrix[$room->id]['schemes'][$priceScheme->id] = [
                        'duration_unit' => $priceScheme->duration_unit,
                        'price' => (float) $priceScheme->price,
                        'deposit' => (float) $roomType->security_deposit,
                        'free_slots' => $freeSlots,
                        'estimated_start' => $startDate->format('Y-m-d'),
                        'estimated_end' => $endDate->format('Y-m-d'),
                        'available' => $freeSlots > 0,
                    ];
                }
            }
        }

        return $matrix;
    }

    /**
     * Calculate end date based on duration and unit.
     * (Same logic as CreateRental::calculateEndDate)
     */
    private function calculateEndDate(Carbon $startDate, int $durationValue, string $durationUnit): Carbon
    {
        return match ($durationUnit) {
            'day' => $startDate->copy()->addDays($durationValue),
            'week' => $startDate->copy()->addWeeks($durationValue),
            'month' => $startDate->copy()->addMonths($durationValue),
            default => $startDate->copy(),
        };
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
        $totalSteps = config('rental.document_upload.total_steps');

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
                'status' => $rental->status === 'payment_pending' ? 'active' : 'completed',
                'timestamp' => $rental->payment->verified_at?->format('M d, H:i'),
            ],
            [
                'label' => 'Upload Documents',
                'status' => match ($rental->status) {
                    'payment_pending' => 'locked',
                    'paid', 'documents_pending' => 'active',
                    default => 'completed',
                },
                'progress' => in_array($rental->status, ['paid', 'documents_pending'])
                    ? "{$docProgress['verified']}/{$docProgress['total']} verified"
                    : null,
                'message' => $rental->status === 'payment_pending' ? 'Upload payment proof first' : null,
            ],
            [
                'label' => 'Verification',
                'status' => match ($rental->status) {
                    'payment_pending', 'paid', 'documents_pending' => 'locked',
                    'confirmed', 'active', 'completed' => 'completed',
                    default => 'locked',
                },
                'timestamp' => $rental->confirmed_at?->format('M d, H:i'),
                'message' => in_array($rental->status, ['payment_pending', 'paid', 'documents_pending'])
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
            $service = app(SecureFileUploadService::class);

            DB::transaction(function () use ($request, $rental, $service) {
                $payment = $rental->payment;

                // Lock payment row to prevent concurrent uploads
                $payment = $payment->lockForUpdate()->findOrFail($payment->id);

                // ✅ VULN-108 FIX: Prevent upload during verification
                if ($payment->verified_at !== null) {
                    throw new InvalidFileException(
                        'Tidak dapat mengunggah bukti pembayaran setelah diverifikasi.'
                    );
                }

                // Delete old proof if exists
                if ($payment->proof_of_payment_path && Storage::disk('private')->exists($payment->proof_of_payment_path)) {
                    Storage::disk('private')->delete($payment->proof_of_payment_path);
                }

                // Store file in private disk with UUID filename
                $path = $service->store($request->file('payment_proof'), 'payment-proofs', 'private');

                // Update payment record (clear rejection_reason on re-upload)
                $payment->update([
                    'proof_of_payment_path' => $path,
                    'paid_at' => now(),
                    'rejection_reason' => null, // Clear rejection reason on re-upload
                ]);
            });

            // NOTE: Status remains 'payment_pending' until admin verifies payment
            // Status will change to 'paid' only after admin approval via VerifyPayment action

            // Create status history entry (system-generated note)
            $rental->statusHistories()->create([
                'status' => 'payment_pending',
                'changed_by' => auth()->id(),
                'internal_notes' => 'Payment proof uploaded by tenant, awaiting admin verification',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload',
            ]);
        } catch (InvalidFileException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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
        $this->authorize('cancelPaymentUpload', $rental);

        // Business rule: Can only cancel if status is 'payment_pending' and payment not yet verified
        if ($rental->status !== 'payment_pending' || $rental->payment->verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membatalkan upload. Pembayaran sudah diverifikasi atau status rental tidak sesuai.',
            ], 403);
        }

        try {
            // Delete uploaded payment proof file
            if ($rental->payment->proof_of_payment_path) {
                Storage::disk('private')->delete($rental->payment->proof_of_payment_path);
            }

            // Reset payment record
            $rental->payment->update([
                'proof_of_payment_path' => null,
                'paid_at' => null,
                'rejection_reason' => null,
            ]);

            // Reset rental status back to 'payment_pending'
            $rental->update(['status' => 'payment_pending']);

            // Create status history entry
            $rental->statusHistories()->create([
                'status' => 'payment_pending',
                'changed_by' => auth()->id(),
                'internal_notes' => 'Payment upload cancelled by tenant',
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
     * Upload rental document (KTP, KK, etc).
     *
     * FR-086, FR-087: Upload required documents
     * DESIGN.md §3.41: Per-document upload flow
     *
     * @param  UploadDocumentRequest  $request  Contains 'document' file and 'type' string
     * @param  Rental  $rental  The rental to upload document for
     * @return JsonResponse JSON response with success/error status
     */
    public function uploadDocument(UploadDocumentRequest $request, Rental $rental): JsonResponse
    {
        // Authorization handled in FormRequest
        $validated = $request->validated();

        try {
            $service = app(SecureFileUploadService::class);

            DB::transaction(function () use ($validated, $rental, $service, $request) {
                // Lock rental row
                $rental = Rental::lockForUpdate()->findOrFail($rental->id);

                // ✅ VULN-108 FIX: Only allow upload in valid statuses
                $allowedStatuses = ['pending', 'paid', 'documents_pending'];
                if (! in_array($rental->status, $allowedStatuses)) {
                    throw new InvalidFileException(
                        'Tidak dapat mengunggah dokumen pada status rental saat ini.'
                    );
                }

                // Verify document type exists in kost requirements (already validated in FormRequest)
                $requirement = $rental->room->roomType->kost->documentRequirements()
                    ->where('document_type', $validated['type'])
                    ->first();

                if (! $requirement) {
                    throw new InvalidFileException('Document type not required for this kost');
                }

                // Store file in private disk with UUID filename
                $path = $service->store($request->file('document'), 'rental-documents', 'private');

                // Create or update rental document
                $rental->rentalDocuments()->updateOrCreate(
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
            });

            // Re-fetch for fresh counts
            $rental->refresh();
            $totalRequired = $rental->room->roomType->kost->documentRequirements()->count();
            $uploadedCount = $rental->rentalDocuments()->whereNotNull('document_path')->count();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'uploaded_count' => $uploadedCount,
                'total_required' => $totalRequired,
            ]);

        } catch (InvalidFileException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => ['type' => [$e->getMessage()]],
            ], 422);
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
            $service = app(SecureFileUploadService::class);
            $uploadedCount = 0;
            $deletedCount = 0;

            DB::transaction(function () use ($request, $rental, $service, $allTypes, $requiredTypes, &$uploadedCount, &$deletedCount) {
                // Lock rental row
                $rental = Rental::lockForUpdate()->findOrFail($rental->id);

                // ✅ VULN-108 FIX: Only allow upload in valid statuses
                $allowedStatuses = ['pending', 'paid', 'documents_pending'];
                if (! in_array($rental->status, $allowedStatuses)) {
                    throw new InvalidFileException(
                        'Tidak dapat mengunggah dokumen pada status rental saat ini.'
                    );
                }

                // Process deletions first
                if ($request->has('delete')) {
                    foreach ($request->input('delete', []) as $docType) {
                        /** @var RentalDocument|null $document */
                        $document = $rental->rentalDocuments()->where('document_type', $docType)->first();

                        if ($document instanceof RentalDocument) {
                            // Prevent deletion if verified
                            if ($document->verified_at) {
                                throw new InvalidFileException(
                                    "Dokumen '{$docType}' sudah diverifikasi dan tidak dapat dihapus"
                                );
                            }

                            // Delete file from storage
                            if ($document->document_path && Storage::disk('private')->exists($document->document_path)) {
                                Storage::disk('private')->delete($document->document_path);
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

                        // Store file in private disk with UUID filename
                        $path = $service->store($file, 'rental-documents', 'private');

                        // Get existing document if any
                        /** @var RentalDocument|null $existingDoc */
                        $existingDoc = $rental->rentalDocuments()->where('document_type', $type)->first();

                        // Delete old file if replacing
                        if ($existingDoc instanceof RentalDocument && $existingDoc->document_path && Storage::disk('private')->exists($existingDoc->document_path)) {
                            Storage::disk('private')->delete($existingDoc->document_path);
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
            });

            // Re-fetch for fresh counts
            $rental->refresh();
            $currentDocCount = $rental->rentalDocuments()->whereIn('document_type', $requiredTypes)->count();

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
        } catch (InvalidFileException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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
    /**
     * Download/view rental document with authorization.
     *
     * Serves rental document file from private storage after verifying
     * tenant owns the rental OR admin owns the kost.
     * Returns inline response (not download) so images can be displayed.
     *
     * @param  RentalDocument  $document  The document to view/download
     *
     * @throws HttpException 404 if document not found
     */
    public function downloadDocument(RentalDocument $document): StreamedResponse
    {
        // Authorize using policy (same pattern as payment proof)
        $this->authorize('viewDocument', $document->rental);

        if (! $document->document_path) {
            abort(404, 'Document not uploaded yet');
        }

        if (! Storage::disk('private')->exists($document->document_path)) {
            abort(404, 'Document file not found');
        }

        // Use response() instead of download() to serve inline (displays in browser)
        // This allows images to be shown in <img> tags, similar to QRIS display
        return Storage::disk('private')->response($document->document_path);
    }
}
