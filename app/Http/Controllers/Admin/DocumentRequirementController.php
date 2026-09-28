<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentRequirementRequest;
use App\Http\Requests\Admin\UpdateDocumentRequirementRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin Document Requirement management controller.
 *
 * Handles CRUD operations for document requirements of kosts owned by authenticated Admin.
 * Document requirements define what documents tenants must provide when renting.
 */
class DocumentRequirementController extends Controller
{
    /**
     * Display a listing of document requirements for the specified kost.
     *
     * @param  Kost  $kost  The kost to list requirements for
     * @return View
     */
    public function index(Kost $kost)
    {
        $this->authorize('view', $kost);

        $kost->load(['documentRequirements' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        $documentTypes = config('kost.document_types');

        return view('admin.kosts.config.document-requirements', compact('kost', 'documentTypes'));
    }

    /**
     * Store a newly created document requirement for the kost.
     *
     * @param  StoreDocumentRequirementRequest  $request  The HTTP request with form data
     * @param  Kost  $kost  The kost to add requirement to
     */
    public function store(StoreDocumentRequirementRequest $request, Kost $kost): RedirectResponse
    {
        $this->authorize('update', $kost);

        $validated = $request->validated();

        $kost->documentRequirements()->create($validated);

        return redirect()
            ->route('admin.kosts.document-requirements.index', $kost)
            ->with('success', 'Persyaratan dokumen berhasil ditambahkan.');
    }

    /**
     * Update the specified document requirement.
     *
     * @param  UpdateDocumentRequirementRequest  $request  The HTTP request with form data
     * @param  Kost  $kost  The kost owning the requirement
     * @param  KostDocumentRequirement  $requirement  The requirement to update
     */
    public function update(UpdateDocumentRequirementRequest $request, Kost $kost, KostDocumentRequirement $requirement): RedirectResponse
    {
        $this->authorize('update', $kost);
        $this->authorize('update', $requirement);

        $validated = $request->validated();

        $requirement->update($validated);

        return redirect()
            ->route('admin.kosts.document-requirements.index', $kost)
            ->with('success', 'Persyaratan dokumen berhasil diperbarui.');
    }

    /**
     * Remove the specified document requirement from storage.
     *
     * @param  Kost  $kost  The kost owning the requirement
     * @param  KostDocumentRequirement  $requirement  The requirement to delete
     */
    public function destroy(Kost $kost, KostDocumentRequirement $requirement): RedirectResponse
    {
        $this->authorize('update', $kost);
        $this->authorize('delete', $requirement);

        $requirement->delete();

        return redirect()
            ->route('admin.kosts.document-requirements.index', $kost)
            ->with('success', 'Persyaratan dokumen berhasil dihapus.');
    }
}
