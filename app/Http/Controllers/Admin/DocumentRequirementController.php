<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
     * @param  Kost  $kost  The kost to add requirement to
     */
    public function store(Request $request, Kost $kost): RedirectResponse
    {
        $this->authorize('update', $kost);

        $validated = $request->validate([
            'document_type' => [
                'required',
                'string',
                Rule::in(array_keys(config('kost.document_types'))),
                Rule::unique('kost_document_requirements')->where('kost_id', $kost->id),
            ],
            'is_required' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'document_type.required' => 'Jenis dokumen harus dipilih.',
            'document_type.in' => 'Jenis dokumen tidak valid.',
            'document_type.unique' => 'Jenis dokumen ini sudah ditambahkan untuk kost ini.',
            'is_required.required' => 'Status wajib harus dipilih.',
            'is_required.boolean' => 'Status wajib tidak valid.',
            'reason.max' => 'Alasan maksimal 500 karakter.',
        ]);

        $kost->documentRequirements()->create($validated);

        return redirect()
            ->route('admin.kosts.document-requirements.index', $kost)
            ->with('success', 'Persyaratan dokumen berhasil ditambahkan.');
    }

    /**
     * Update the specified document requirement.
     *
     * @param  Kost  $kost  The kost owning the requirement
     * @param  KostDocumentRequirement  $requirement  The requirement to update
     */
    public function update(Request $request, Kost $kost, KostDocumentRequirement $requirement): RedirectResponse
    {
        $this->authorize('update', $kost);
        $this->authorize('update', $requirement);

        $validated = $request->validate([
            'document_type' => [
                'required',
                'string',
                Rule::in(array_keys(config('kost.document_types'))),
                Rule::unique('kost_document_requirements')
                    ->where('kost_id', $kost->id)
                    ->ignore($requirement->id),
            ],
            'is_required' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'document_type.required' => 'Jenis dokumen harus dipilih.',
            'document_type.in' => 'Jenis dokumen tidak valid.',
            'document_type.unique' => 'Jenis dokumen ini sudah ditambahkan untuk kost ini.',
            'is_required.required' => 'Status wajib harus dipilih.',
            'is_required.boolean' => 'Status wajib tidak valid.',
            'reason.max' => 'Alasan maksimal 500 karakter.',
        ]);

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
