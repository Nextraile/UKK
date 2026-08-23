<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Kost\Actions\CancelKostSubmission;
use App\Domain\Kost\Actions\PublishKost;
use App\Domain\Kost\Actions\SubmitKostForReview;
use App\Domain\Kost\Exceptions\InvalidKostSubmissionException;
use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Models\Kost;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKostRequest;
use App\Http\Requests\Admin\UpdateKostRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin Kost management controller.
 *
 * Handles CRUD operations for kosts owned by authenticated Admin.
 * Admin can only manage kosts in draft or rejected status.
 */
class KostController extends Controller
{
    /**
     * Display a listing of kosts owned by authenticated Admin.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Kost::class);

        $kosts = Kost::with('owner')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.kosts.index', compact('kosts'));
    }

    /**
     * Show the form for creating a new kost.
     */
    public function create(): View
    {
        $this->authorize('create', Kost::class);

        return view('admin.kosts.create');
    }

    /**
     * Store a newly created kost in storage.
     */
    public function store(StoreKostRequest $request): RedirectResponse
    {
        $this->authorize('create', Kost::class);

        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['status'] = 'draft';

        $kost = Kost::create($data);

        return redirect()
            ->route('admin.kosts.show', $kost)
            ->with('success', 'Kost berhasil dibuat sebagai draft.');
    }

    /**
     * Display the specified kost.
     */
    public function show(Kost $kost): View
    {
        $this->authorize('view', $kost);

        $kost->load('owner');

        return view('admin.kosts.show', compact('kost'));
    }

    /**
     * Show the form for editing the specified kost.
     */
    public function edit(Kost $kost): View
    {
        $this->authorize('update', $kost);

        return view('admin.kosts.edit', compact('kost'));
    }

    /**
     * Update the specified kost in storage.
     */
    public function update(UpdateKostRequest $request, Kost $kost): RedirectResponse
    {
        $this->authorize('update', $kost);

        $data = $request->validated();

        // FR-020: Auto-revert rejected → draft on update
        $wasRejected = $kost->isRejected();

        DB::transaction(function () use ($kost, $data, $wasRejected) {
            // Update fillable fields
            $kost->fill($data);

            // If was rejected, revert to draft (TASK-016: direct assignment)
            if ($wasRejected) {
                $kost->status = 'draft';
                $kost->rejected_reason = null;
            }

            $kost->save();
        });

        return redirect()
            ->route('admin.kosts.show', $kost)
            ->with('success', 'Kost berhasil diperbarui.');
    }

    /**
     * Remove the specified kost from storage (soft delete).
     */
    public function destroy(Kost $kost): RedirectResponse
    {
        $this->authorize('delete', $kost);

        $kost->delete();

        return redirect()
            ->route('admin.kosts.index')
            ->with('success', 'Kost berhasil dihapus.');
    }

    /**
     * Submit kost for Super Admin review.
     *
     * Validates data completeness before transitioning draft → pending_review.
     * FR-016, FR-017: Nama, alamat, kategori, room type must be complete.
     */
    public function submit(Kost $kost): RedirectResponse
    {
        $this->authorize('submit', $kost);

        try {
            $submitAction = new SubmitKostForReview;
            $submitAction->execute($kost);

            return redirect()
                ->route('admin.kosts.show', $kost)
                ->with('success', 'Kost berhasil disubmit untuk review. Menunggu persetujuan Super Admin.');
        } catch (InvalidKostSubmissionException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Publish approved kost (approved → active).
     *
     * Makes kost visible to tenants in marketplace.
     * FR-021: Admin can publish approved kost.
     */
    public function publish(Kost $kost): RedirectResponse
    {
        $this->authorize('publish', $kost);

        try {
            $publishAction = new PublishKost;
            $publishAction->execute($kost);

            return redirect()
                ->route('admin.kosts.show', $kost)
                ->with('success', "Kost '{$kost->name}' berhasil dipublikasikan.");
        } catch (InvalidKostTransitionException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel kost submission and revert to draft.
     *
     * Allows admin to withdraw pending_review submission for editing.
     * FR-016, FR-023: Admin can cancel submission before approval.
     */
    public function cancel(Kost $kost): RedirectResponse
    {
        $this->authorize('cancel', $kost);

        try {
            $action = new CancelKostSubmission;
            $action->execute($kost);

            return redirect()
                ->route('admin.kosts.show', $kost)
                ->with('success', 'Pengajuan berhasil dibatalkan. Anda dapat mengedit kembali kost ini.');
        } catch (InvalidKostTransitionException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
