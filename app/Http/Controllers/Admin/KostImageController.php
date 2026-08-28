<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Admin Kost Image management controller.
 *
 * Handles image upload, deletion, thumbnail selection, and sort order management.
 * Images stored with pattern: kost-{id}-img-{Ymd-His}-{seq}.{ext}
 */
class KostImageController extends Controller
{
    /**
     * Display the image management page for the specified kost.
     *
     * Shows all uploaded images ordered by sort_order for reordering,
     * thumbnail selection, and deletion.
     *
     * @param  Kost  $kost  The kost to manage images for
     */
    public function index(Kost $kost): View
    {
        $this->authorize('update', $kost);

        $kost->load(['kostImages' => fn ($q) => $q->orderBy('sort_order')]);

        return view('admin.kosts.config.images', compact('kost'));
    }

    /**
     * Store a newly uploaded image for the kost.
     *
     * @param  Request  $request  The HTTP request with image file
     * @param  Kost  $kost  The kost to add image to
     *
     * @throws ValidationException If validation fails
     */
    public function store(Request $request, Kost $kost): RedirectResponse
    {
        $this->authorize('update', $kost);

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5MB
        ], [
            'image.required' => 'Gambar wajib diunggah.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus: JPEG, JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        $file = $request->file('image');

        // Generate UUID filename for security (prevent enumeration attacks)
        $filename = Str::uuid().'.'.$file->guessExtension();

        // Store in storage/app/public/kost-images/
        $path = $file->storeAs('kost-images', $filename, 'public');

        // Get next sort_order
        $sequence = $kost->kostImages()->count() + 1;

        // Create database record
        $image = KostImage::create([
            'kost_id' => $kost->id,
            'image_path' => $path,
            'is_thumbnail' => false,
            'sort_order' => $sequence,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Gambar berhasil diunggah.');
    }

    /**
     * Delete the specified image.
     *
     * @param  Kost  $kost  The kost owning the image
     * @param  KostImage  $image  The image to delete
     */
    public function destroy(Kost $kost, KostImage $image): RedirectResponse
    {
        $this->authorize('update', $kost);

        // Verify image belongs to kost (route model binding doesn't check relationship)
        if ($image->kost_id !== $kost->id) {
            abort(404);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        // Delete database record
        $image->delete();

        return redirect()
            ->back()
            ->with('success', 'Gambar berhasil dihapus.');
    }

    /**
     * Set the specified image as thumbnail.
     *
     * Unsets existing thumbnail (unique constraint: only 1 per kost).
     *
     * @param  Kost  $kost  The kost owning the image
     * @param  KostImage  $image  The image to set as thumbnail
     */
    public function setThumbnail(Kost $kost, KostImage $image): RedirectResponse
    {
        $this->authorize('update', $kost);

        // Verify image belongs to kost
        if ($image->kost_id !== $kost->id) {
            abort(404);
        }

        DB::transaction(function () use ($kost, $image) {
            // Unset all thumbnails for this kost (raw query to avoid constraint issues)
            DB::table('kost_images')
                ->where('kost_id', $kost->id)
                ->where('is_thumbnail', true)
                ->update(['is_thumbnail' => false]);

            // Set new thumbnail
            $image->update(['is_thumbnail' => true]);
        });

        return redirect()
            ->back()
            ->with('success', 'Thumbnail berhasil diatur.');
    }

    /**
     * Update sort order for multiple images.
     *
     * Expects request data: ['image_ids' => [3, 1, 2]] where array index = sort_order
     *
     * @param  Request  $request  The HTTP request with sort order array
     * @param  Kost  $kost  The kost owning the images
     *
     * @throws ValidationException If validation fails
     */
    public function updateSortOrder(Request $request, Kost $kost): RedirectResponse
    {
        $this->authorize('update', $kost);

        $validated = $request->validate([
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['required', 'integer', 'exists:kost_images,id'],
        ], [
            'image_ids.required' => 'Data urutan gambar tidak ditemukan.',
            'image_ids.array' => 'Data urutan gambar harus berupa array.',
            'image_ids.*.exists' => 'Gambar tidak ditemukan.',
        ]);

        DB::transaction(function () use ($kost, $validated) {
            foreach ($validated['image_ids'] as $index => $imageId) {
                $image = KostImage::find($imageId);

                // Verify image belongs to this kost
                if ($image && $image->kost_id === $kost->id) {
                    $image->update(['sort_order' => $index + 1]);
                }
            }
        });

        return redirect()
            ->back()
            ->with('success', 'Urutan gambar berhasil diperbarui.');
    }
}
