<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Rental\Actions\VerifyDocument;
use App\Domain\Rental\Exceptions\DocumentAlreadyVerifiedException;
use App\Domain\Rental\Models\RentalDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerifyDocumentRequest;
use Illuminate\Http\RedirectResponse;

class DocumentVerificationController extends Controller
{
    /**
     * Approve document.
     *
     * FR-088: Admin verifies document
     */
    public function approve(VerifyDocumentRequest $request, RentalDocument $document): RedirectResponse
    {
        try {
            $action = new VerifyDocument;
            $action->execute($document, true);

            return redirect()
                ->route('admin.rentals.show', $document->rental_id)
                ->with('success', "Dokumen {$document->document_type} telah disetujui.");

        } catch (DocumentAlreadyVerifiedException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject document with reason.
     *
     * FR-089: Admin rejects document with reason
     */
    public function reject(VerifyDocumentRequest $request, RentalDocument $document): RedirectResponse
    {
        try {
            $action = new VerifyDocument;
            $action->execute(
                $document,
                false,
                $request->input('rejection_reason')
            );

            return redirect()
                ->route('admin.rentals.show', $document->rental_id)
                ->with('success', "Dokumen {$document->document_type} telah ditolak. Tenant dapat upload ulang.");

        } catch (DocumentAlreadyVerifiedException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
