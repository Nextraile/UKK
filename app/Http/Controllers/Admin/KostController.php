<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Kost\Actions\CancelKostSubmission;
use App\Domain\Kost\Actions\PublishKost;
use App\Domain\Kost\Actions\SubmitKostForReview;
use App\Domain\Kost\Exceptions\InvalidKostSubmissionException;
use App\Domain\Kost\Exceptions\InvalidKostTransitionException;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKostRequest;
use App\Http\Requests\Admin\UpdateKostRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $kost->load(['owner', 'address', 'categories', 'kostImages', 'documentRequirements']);

        return view('admin.kosts.show', compact('kost'));
    }

    /**
     * Show the form for editing the specified kost.
     */
    public function edit(Kost $kost): View
    {
        $this->authorize('update', $kost);

        $kost->load('address');

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

        // Handle fallback for JS-disabled clients
        $data = $this->parseFacilitiesAndRules($request, $data);

        DB::transaction(function () use ($kost, $data, $wasRejected, $request) {
            // Update fillable fields
            $kost->fill($data);

            // If was rejected, revert to draft (TASK-016: direct assignment)
            if ($wasRejected) {
                $kost->status = 'draft';
                $kost->rejected_reason = null;
            }

            $kost->save();

            // Update or create address if full_address is provided
            if ($request->filled('full_address')) {
                $kost->address()->updateOrCreate(
                    ['kost_id' => $kost->id],
                    $request->only([
                        'full_address',
                        'district',
                        'city',
                        'province',
                        'postal_code',
                        'country',
                        'latitude',
                        'longitude',
                    ])
                );
            }
        });

        return redirect()
            ->route('admin.kosts.show', $kost)
            ->with('success', 'Kost berhasil diperbarui.');
    }

    /**
     * Parse facilities and rules from text input for JS-disabled clients.
     *
     * Converts newline-separated text into arrays for facilities and rules.
     *
     * @param  UpdateKostRequest  $request  The form request
     * @param  array<string, mixed>  $validated  Validated data array
     * @return array<string, mixed> Modified validated data with parsed facilities/rules
     */
    private function parseFacilitiesAndRules(UpdateKostRequest $request, array $validated): array
    {
        // If facilities_text exists but facilities doesn't, parse line-by-line
        if ($request->has('facilities_text') && ! $request->has('facilities')) {
            $validated['facilities'] = array_values(array_filter(
                array_map('trim', explode("\n", $request->input('facilities_text', ''))),
                fn ($line) => ! empty($line)
            ));
        }

        // If rules_text exists but rules doesn't, parse line-by-line
        if ($request->has('rules_text') && ! $request->has('rules')) {
            $validated['rules'] = array_values(array_filter(
                array_map('trim', explode("\n", $request->input('rules_text', ''))),
                fn ($line) => ! empty($line)
            ));
        }

        return $validated;
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

    /**
     * Show the form for editing kost categories.
     *
     * Displays category selection form with current assignments.
     *
     * @param  Kost  $kost  The kost to edit categories for
     */
    public function editCategories(Kost $kost): View
    {
        $this->authorize('update', $kost);

        $categories = Category::orderBy('name')->get();
        $kost->load('categories');

        return view('admin.kosts.config.categories', compact('kost', 'categories'));
    }

    /**
     * Update categories assigned to kost via junction table.
     *
     * Sync categories using category_kost junction table.
     * Minimum 1 category required for submission.
     */
    public function updateCategories(Request $request, Kost $kost): RedirectResponse
    {
        $this->authorize('update', $kost);

        $validated = $request->validate([
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['exists:categories,id'],
        ]);

        $kost->categories()->sync($validated['category_ids']);

        return redirect()
            ->back()
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Show the form for editing payment information.
     *
     * Displays QRIS image upload and bank account fields.
     *
     * @param  Kost  $kost  The kost to edit payment info for
     */
    public function editPayment(Kost $kost): View
    {
        $this->authorize('update', $kost);

        return view('admin.kosts.config.payment', compact('kost'));
    }

    /**
     * Update payment information (QRIS image and bank account).
     *
     * Handles QRIS image upload with auto-generated filename pattern:
     * qris-kost-{id}-{Ymd-His}.{ext}
     * Storage: storage/app/public/qris/
     * Bank info displayed to tenants during payment.
     */
    public function updatePayment(Request $request, Kost $kost): RedirectResponse
    {
        $this->authorize('update', $kost);

        $validated = $request->validate([
            'qris_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'bank_name' => ['required_with:account_number', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['required_with:account_number', 'string', 'max:150'],
        ]);

        // Upload QRIS image
        if ($request->hasFile('qris_image')) {
            // Delete old QRIS image if exists
            if ($kost->qris_image_path) {
                Storage::disk('public')->delete($kost->qris_image_path);
            }

            $filename = sprintf(
                'qris-kost-%d-%s.%s',
                $kost->id,
                now()->format('Ymd-His'),
                $request->file('qris_image')->guessExtension()
            );

            $path = $request->file('qris_image')->storeAs('qris', $filename, 'public');
            $validated['qris_image_path'] = $path;
        }

        $kost->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Informasi pembayaran berhasil diperbarui.');
    }
}
