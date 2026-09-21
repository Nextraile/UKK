<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Domain\Kost\Actions\ApproveKost;
use App\Domain\Kost\Actions\RejectKost;
use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Models\Kost;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super Admin controller for reviewing kost submissions.
 *
 * Routes: /super-admin/kost-submissions
 *
 * FR-018: Super Admin review submitted kosts (list pending_review, view details, approve/reject)
 * FR-019: Approval transitions Pending Review → Approved (auto-notify Owner)
 * FR-023: Rejection reason (min 10 chars) transitions Pending Review → Rejected
 */
class KostSubmissionController extends Controller
{
    /**
     * List all pending kost submissions.
     */
    public function index(): View
    {
        $submissions = Kost::query()
            ->where('status', 'pending_review')
            ->with(['owner', 'categories', 'roomTypes'])
            ->latest('updated_at')
            ->paginate(15);

        return view('super-admin.kost-submissions.index', [
            'submissions' => $submissions,
        ]);
    }

    /**
     * Show detailed submission for review.
     */
    public function show(Kost $submission): View
    {
        $submission->load([
            'owner',
            'categories',
            'address',
            'kostImages' => fn ($q) => $q->orderBy('sort_order'),
            'roomTypes.roomTypeImages',
            'roomTypes.priceSchemes' => fn ($q) => $q->where('is_active', true)->orderBy('duration_value'),
            'documentRequirements',
            // TODO: Add 'bankAccounts' when BankAccount model is created (COMP-007)
        ]);

        return view('super-admin.kost-submissions.show', [
            'submission' => $submission,
        ]);
    }

    /**
     * Approve a kost submission.
     */
    public function approve(Kost $submission, ApproveKost $action): RedirectResponse
    {
        try {
            $action->execute($submission, auth()->user());

            return redirect()
                ->route('super-admin.kost-submissions.index')
                ->with('success', "Kost '{$submission->name}' berhasil disetujui.");
        } catch (InvalidKostTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a kost submission with reason.
     */
    public function reject(Request $request, Kost $submission, RejectKost $action): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        try {
            $action->execute($submission, auth()->user(), $validated['rejection_reason']);

            return redirect()
                ->route('super-admin.kost-submissions.index')
                ->with('success', "Kost '{$submission->name}' ditolak.");
        } catch (InvalidKostTransitionException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
