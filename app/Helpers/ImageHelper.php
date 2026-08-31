<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

if (! function_exists('image_url')) {
    /**
     * Get image URL from path - supports both external URLs and local storage paths.
     *
     * If the path starts with http:// or https://, returns the path as-is (external URL).
     * Otherwise, returns the local storage URL using Storage::url().
     *
     * @param  string|null  $path  Image path or URL
     * @return string|null Image URL or null if path is empty
     */
    function image_url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::url($path);
    }
}
