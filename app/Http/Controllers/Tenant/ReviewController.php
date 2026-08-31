<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Domain\Rental\Models\Rental;
use App\Domain\Review\Actions\DeleteReviewAction;
use App\Domain\Review\Actions\SubmitReviewAction;
use App\Domain\Review\Actions\UpdateReviewAction;
use App\Domain\Review\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ReviewRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    /**
     * Show the form for creating a new review.
     *
     * @return View
     */
    public function create(Rental $rental)
    {
        $this->authorize('create', [Review::class, $rental]);

        return view('tenant.reviews.create', [
            'rental' => $rental->load('room.kost'),
        ]);
    }

    /**
     * Store a newly created review.
     */
    public function store(ReviewRequest $request, Rental $rental): RedirectResponse
    {
        $this->authorize('create', [Review::class, $rental]);

        try {
            $review = app(SubmitReviewAction::class)->execute(
                $rental,
                $request->validated()
            );

            return redirect()
                ->route('rentals.show', $rental)
                ->with('success', 'Review berhasil dikirim.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the review.
     *
     * @return View
     */
    public function edit(Rental $rental)
    {
        $review = $rental->review;
        $this->authorize('update', $review);

        return view('tenant.reviews.edit', [
            'rental' => $rental->load('room.kost'),
            'review' => $review,
        ]);
    }

    /**
     * Update the specified review.
     */
    public function update(ReviewRequest $request, Rental $rental): RedirectResponse
    {
        $review = $rental->review;
        $this->authorize('update', $review);

        try {
            app(UpdateReviewAction::class)->execute(
                $review,
                $request->validated()
            );

            return redirect()
                ->route('rentals.show', $rental)
                ->with('success', 'Review berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified review.
     */
    public function destroy(Rental $rental): RedirectResponse
    {
        $review = $rental->review;
        $this->authorize('delete', $review);

        try {
            app(DeleteReviewAction::class)->execute($review);

            return redirect()
                ->route('rentals.show', $rental)
                ->with('success', 'Review berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
