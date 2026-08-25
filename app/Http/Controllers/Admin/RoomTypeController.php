<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Kost\Models\RoomTypeImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomTypeRequest;
use App\Http\Requests\Admin\UpdateRoomTypeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Admin Room Type management controller.
 *
 * Handles CRUD operations for room types within a kost.
 * Admin can only manage room types for kosts they own.
 */
class RoomTypeController extends Controller
{
    /**
     * Display a listing of room types for a kost.
     */
    public function index(Kost $kost): View
    {
        $this->authorize('viewAny', [RoomType::class, $kost]);

        $roomTypes = $kost->roomTypes()
            ->withCount('rooms')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.room-types.index', compact('kost', 'roomTypes'));
    }

    /**
     * Show the form for creating a new room type.
     */
    public function create(Kost $kost): View
    {
        $this->authorize('create', [RoomType::class, $kost]);

        return view('admin.room-types.create', compact('kost'));
    }

    /**
     * Store a newly created room type in storage.
     *
     * Creates room type with basic fields, then batch uploads images if present.
     * First uploaded image is automatically set as thumbnail.
     *
     * @throws \Exception
     */
    public function store(StoreRoomTypeRequest $request, Kost $kost): RedirectResponse
    {
        $this->authorize('create', [RoomType::class, $kost]);

        $roomType = null;

        DB::transaction(function () use ($request, $kost, &$roomType) {
            // 1. Create RoomType record
            $validated = $request->validated();
            $validated['kost_id'] = $kost->id;
            $validated['slug'] = Str::slug($validated['name']);

            $roomType = RoomType::create($validated);

            // 2. Handle image uploads if present
            if ($request->hasFile('images')) {
                $files = $request->file('images');

                foreach ($files as $index => $file) {
                    $sequence = $index + 1;

                    // Generate filename: room-type-{id}-img-{Ymd-His}-{seq}.{ext}
                    $filename = sprintf(
                        'room-type-%d-img-%s-%d.%s',
                        $roomType->id,
                        now()->format('Ymd-His'),
                        $sequence,
                        $file->guessExtension()
                    );

                    // Store in storage/app/public/room-type-images/
                    $path = $file->storeAs('room-type-images', $filename, 'public');

                    // Create database record
                    RoomTypeImage::create([
                        'room_type_id' => $roomType->id,
                        'image_path' => $path,
                        'is_thumbnail' => $sequence === 1, // First image = thumbnail
                        'sort_order' => $sequence,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.room-types.index', $kost)
            ->with('success', 'Tipe kamar berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified room type.
     */
    public function edit(Kost $kost, RoomType $roomType): View
    {
        $this->authorize('update', $roomType);

        $roomType->load(['roomTypeImages', 'priceSchemes']);

        return view('admin.room-types.edit', compact('kost', 'roomType'));
    }

    /**
     * Update the specified room type in storage.
     *
     * Updates basic fields, handles image deletions, and appends new uploads.
     * If all images are deleted and new images uploaded, first new image becomes thumbnail.
     *
     * @throws \Exception
     */
    public function update(UpdateRoomTypeRequest $request, Kost $kost, RoomType $roomType): RedirectResponse
    {
        $this->authorize('update', $roomType);

        DB::transaction(function () use ($request, $roomType) {
            // 1. Update basic fields
            $validated = $request->validated();
            $validated['slug'] = Str::slug($validated['name']);

            $roomType->update($validated);

            // 2. Handle image deletions if present
            if ($request->has('delete_images')) {
                $imageIds = $request->input('delete_images');
                $imagesToDelete = RoomTypeImage::whereIn('id', $imageIds)
                    ->where('room_type_id', $roomType->id)
                    ->get();

                foreach ($imagesToDelete as $image) {
                    // Delete from storage
                    if (Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }

                    // Delete database record
                    $image->delete();
                }
            }

            // 3. Handle new image uploads if present
            if ($request->hasFile('images')) {
                $existingCount = $roomType->roomTypeImages()->count();
                $newCount = count($request->file('images'));

                // Validate total images don't exceed limit
                if ($existingCount + $newCount > 10) {
                    return back()->withErrors([
                        'images' => sprintf(
                            'Total gambar tidak boleh melebihi 10. Anda memiliki %d gambar, hanya bisa upload %d lagi.',
                            $existingCount,
                            10 - $existingCount
                        ),
                    ])->withInput();
                }

                // Get max sort_order for continuation
                $maxSortOrder = $roomType->roomTypeImages()->max('sort_order') ?? 0;

                $files = $request->file('images');

                foreach ($files as $index => $file) {
                    $sequence = $maxSortOrder + $index + 1;

                    $filename = sprintf(
                        'room-type-%d-img-%s-%d.%s',
                        $roomType->id,
                        now()->format('Ymd-His'),
                        $sequence,
                        $file->guessExtension()
                    );

                    $path = $file->storeAs('room-type-images', $filename, 'public');

                    // If all images were deleted and this is first new upload, set as thumbnail
                    $isThumbnail = ($existingCount === 0 && $index === 0);

                    RoomTypeImage::create([
                        'room_type_id' => $roomType->id,
                        'image_path' => $path,
                        'is_thumbnail' => $isThumbnail,
                        'sort_order' => $sequence,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.room-types.index', $kost)
            ->with('success', 'Tipe kamar berhasil diperbarui.');
    }

    /**
     * Remove the specified room type from storage.
     */
    public function destroy(Kost $kost, RoomType $roomType): RedirectResponse
    {
        $this->authorize('delete', $roomType);

        $roomType->delete();

        return redirect()
            ->route('admin.room-types.index', $kost)
            ->with('success', 'Tipe kamar berhasil dihapus.');
    }
}
