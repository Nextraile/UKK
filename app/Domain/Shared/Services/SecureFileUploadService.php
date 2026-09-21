<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use App\Domain\Shared\DTOs\ValidationResult;
use App\Domain\Shared\Exceptions\InvalidFileException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Secure file upload service with comprehensive validation.
 *
 * This service provides centralized file validation and upload handling
 * to prevent common security vulnerabilities including:
 * - MIME type spoofing (validates magic bytes)
 * - Empty/corrupted files
 * - PHP code injection in uploads
 * - Path traversal attacks
 * - Insufficient file validation
 */
class SecureFileUploadService
{
    /**
     * Validate an uploaded file against security rules.
     *
     * Performs comprehensive validation including:
     * - File size limits (min/max)
     * - Extension whitelist
     * - MIME type verification via magic bytes
     * - Image integrity check (for image types)
     * - PDF structure validation (for PDF files)
     * - Dimension validation (for image types)
     * - PHP code detection
     * - Path traversal prevention
     *
     * @param  UploadedFile  $file  The uploaded file to validate
     * @param  string  $uploadType  The upload type key from config/secure-uploads.php
     * @return ValidationResult Validation result with errors if any
     *
     * @throws InvalidFileException If upload type is not configured
     */
    public function validate(UploadedFile $file, string $uploadType): ValidationResult
    {
        $config = config("secure-uploads.upload_types.{$uploadType}");

        if (! $config) {
            throw new InvalidFileException("Upload type '{$uploadType}' tidak dikonfigurasi.");
        }

        $errors = [];

        // 1. Validate file size - minimum (prevent empty files)
        if ($file->getSize() < $config['min_size']) {
            $errors[] = sprintf(
                'Ukuran file terlalu kecil. Minimum %s KB.',
                number_format($config['min_size'] / 1024, 0)
            );
        }

        // 2. Validate file size - maximum
        if ($file->getSize() > $config['max_size']) {
            $errors[] = sprintf(
                'Ukuran file terlalu besar. Maksimum %s MB.',
                number_format($config['max_size'] / 1048576, 1)
            );
        }

        // 3. Validate extension whitelist (case-insensitive)
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $config['extensions'], true)) {
            $errors[] = sprintf(
                'Ekstensi file tidak diizinkan. Hanya: %s',
                implode(', ', $config['extensions'])
            );
        }

        // 4. Validate MIME type via magic bytes (not just extension)
        $realPath = $file->getRealPath();
        if ($realPath === false) {
            $errors[] = 'File tidak dapat dibaca.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                $errors[] = 'Gagal membuka fileinfo untuk validasi MIME.';
            } else {
                $detectedMime = finfo_file($finfo, $realPath);
                finfo_close($finfo);

                if ($detectedMime === false || ! in_array($detectedMime, $config['mimes'], true)) {
                    $errors[] = sprintf(
                        'Tipe MIME tidak valid. Terdeteksi: %s. Diizinkan: %s',
                        $detectedMime ?: 'unknown',
                        implode(', ', $config['mimes'])
                    );
                }
            }
        }

        // 5. Content integrity check based on file type
        if ($realPath !== false) {
            // For images: verify actual image data using getimagesize()
            if ($config['validate_dimensions']) {
                $imageInfo = @getimagesize($realPath);

                if ($imageInfo === false) {
                    $errors[] = 'File bukan gambar yang valid atau corrupted.';
                } else {
                    // 6. Validate dimensions
                    [$width, $height] = $imageInfo;

                    if (isset($config['min_width']) && $width < $config['min_width']) {
                        $errors[] = sprintf(
                            'Lebar gambar terlalu kecil. Minimum %d px.',
                            $config['min_width']
                        );
                    }

                    if (isset($config['min_height']) && $height < $config['min_height']) {
                        $errors[] = sprintf(
                            'Tinggi gambar terlalu kecil. Minimum %d px.',
                            $config['min_height']
                        );
                    }
                }
            }

            // For PDFs: verify magic bytes (only if uploaded file claims to be PDF)
            $uploadedMime = $file->getMimeType();
            if ($uploadedMime === 'application/pdf' && in_array('application/pdf', $config['mimes'], true)) {
                $fileHandle = @fopen($realPath, 'rb');
                if ($fileHandle !== false) {
                    $header = fread($fileHandle, 5);
                    fclose($fileHandle);

                    if ($header !== false && $header !== '%PDF-') {
                        $errors[] = 'File bukan PDF yang valid atau corrupted.';
                    }
                } else {
                    $errors[] = 'Tidak dapat membaca file untuk validasi PDF.';
                }
            }
        }

        // 7. Detect PHP code in file content
        if ($realPath !== false) {
            $content = file_get_contents($realPath);
            if ($content !== false) {
                $phpPatterns = [
                    '/<\?php/i',
                    '/<\?=/i',
                    '/<script/i',
                ];

                foreach ($phpPatterns as $pattern) {
                    if (preg_match($pattern, $content) === 1) {
                        $errors[] = 'File mengandung kode berbahaya.';
                        break;
                    }
                }
            }
        }

        // 8. Validate filename doesn't contain path traversal
        $originalName = $file->getClientOriginalName();
        if (preg_match('/\.\.\/|\.\.\\\\/', $originalName) === 1) {
            $errors[] = 'Nama file mengandung karakter tidak diizinkan.';
        }

        return empty($errors)
            ? ValidationResult::success()
            : ValidationResult::failure($errors);
    }

    /**
     * Store an uploaded file with UUID filename.
     *
     * This method:
     * - Generates a UUID v4 filename
     * - Preserves the validated extension
     * - Stores in the specified disk and path
     * - Returns the relative path for database storage
     *
     * Note: This method does NOT perform validation. Call validate() first.
     *
     * @param  UploadedFile  $file  The file to store
     * @param  string  $path  The storage path (e.g., 'avatars', 'kost-images')
     * @param  string  $disk  The storage disk (default: 'public')
     * @return string The relative path of the stored file
     */
    public function store(UploadedFile $file, string $path, string $disk = 'public'): string
    {
        // Generate UUID v4 filename
        $uuid = Str::uuid()->toString();

        // Sanitize and preserve extension
        $extension = strtolower($file->getClientOriginalExtension());
        $safeExtension = preg_replace('/[^a-z0-9]/', '', $extension);

        $filename = "{$uuid}.{$safeExtension}";

        // Store the file
        $storedPath = $file->storeAs($path, $filename, $disk);

        if ($storedPath === false) {
            throw new InvalidFileException('Gagal menyimpan file.');
        }

        return $storedPath;
    }

    /**
     * Validate and store a file in one operation.
     *
     * This is a convenience method that combines validation and storage.
     * If validation fails, an exception is thrown.
     *
     * @param  UploadedFile  $file  The file to validate and store
     * @param  string  $uploadType  The upload type key from config
     * @param  string  $path  The storage path
     * @param  string  $disk  The storage disk (default: 'public')
     * @return string The relative path of the stored file
     *
     * @throws InvalidFileException If validation fails
     */
    public function validateAndStore(
        UploadedFile $file,
        string $uploadType,
        string $path,
        string $disk = 'public'
    ): string {
        $validation = $this->validate($file, $uploadType);

        if (! $validation->isValid()) {
            throw new InvalidFileException(
                'Validasi file gagal: '.implode(' ', $validation->getErrors())
            );
        }

        return $this->store($file, $path, $disk);
    }
}
