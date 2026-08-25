<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Kost\Models\RoomTypeImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Admin Room Type Image management controller.
 *
 * Handles manual image deletion only. Batch upload now handled in RoomTypeController.
 */
class RoomTypeImageController extends Controller
{
    /**
     * Delete a room type image.
     *
     * Used for manual cleanup of individual images.
     *
     * @param  RoomTypeImage  $roomTypeImage  The image to delete
     */
    public function destroy(RoomTypeImage $roomTypeImage): RedirectResponse
    {
        $this->authorize('delete', $roomTypeImage);

        // Delete file from storage
        if (Storage::disk('public')->exists($roomTypeImage->image_path)) {
            Storage::disk('public')->delete($roomTypeImage->image_path);
        }

        // Delete database record
        $roomTypeImage->delete();

        return redirect()
            ->back()
            ->with('success', 'Gambar berhasil dihapus.');
    }
}
