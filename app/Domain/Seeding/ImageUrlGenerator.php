<?php

declare(strict_types=1);

namespace App\Domain\Seeding;

/**
 * Generate local storage paths for seeding images.
 *
 * Rotates through pre-downloaded sample images stored in storage/app/seed-samples/.
 * Uses modulo arithmetic to cycle through available images for variety without
 * external HTTP dependencies.
 *
 * All methods are static for easy use in seeders without instantiation.
 */
class ImageUrlGenerator
{
    /**
     * Generate avatar storage path (portrait/face images).
     *
     * Rotates through 3 avatar sample images using modulo arithmetic.
     * Returns path relative to storage/app disk.
     *
     * Example: avatar(1) → "seed-samples/avatars/avatar-1.jpg"
     *          avatar(4) → "seed-samples/avatars/avatar-1.jpg" (rotates back)
     *
     * @param  int  $counter  Sequential counter for rotation (1-based)
     * @return string Storage path relative to app disk
     */
    public static function avatar(int $counter): string
    {
        // Rotate through 3 avatar images: avatar-1.jpg, avatar-2.jpg, avatar-3.jpg
        $index = (($counter - 1) % 3) + 1;

        return "seed-samples/avatars/avatar-{$index}.jpg";
    }

    /**
     * Generate kost building/exterior image storage path.
     *
     * Rotates through 5 kost sample images using modulo arithmetic.
     * Returns path relative to storage/app disk.
     *
     * Example: kostImage(1) → "seed-samples/kost/kost-1.jpg"
     *          kostImage(6) → "seed-samples/kost/kost-1.jpg" (rotates back)
     *
     * @param  int  $counter  Sequential counter for rotation (1-based)
     * @return string Storage path relative to app disk
     */
    public static function kostImage(int $counter): string
    {
        // Rotate through 5 kost images: kost-1.jpg, kost-2.jpg, ..., kost-5.jpg
        $index = (($counter - 1) % 5) + 1;

        return "seed-samples/kost/kost-{$index}.jpg";
    }

    /**
     * Generate room interior image storage path.
     *
     * Rotates through 5 room sample images using modulo arithmetic.
     * Returns path relative to storage/app disk.
     *
     * Example: roomImage(1) → "seed-samples/rooms/rooms-1.jpg"
     *          roomImage(6) → "seed-samples/rooms/rooms-1.jpg" (rotates back)
     *
     * @param  int  $counter  Sequential counter for rotation (1-based)
     * @return string Storage path relative to app disk
     */
    public static function roomImage(int $counter): string
    {
        // Rotate through 5 room images: rooms-1.jpg, rooms-2.jpg, ..., rooms-5.jpg
        $index = (($counter - 1) % 5) + 1;

        return "seed-samples/rooms/rooms-{$index}.jpg";
    }

    /**
     * Generate document image storage path (KTP, selfie, etc.).
     *
     * Rotates through 2 document sample images using modulo arithmetic.
     * Returns path relative to storage/app disk.
     *
     * Example: document(1) → "seed-samples/documents/doc-1.jpg"
     *          document(3) → "seed-samples/documents/doc-1.jpg" (rotates back)
     *
     * @param  int  $counter  Sequential counter for rotation (1-based)
     * @return string Storage path relative to app disk
     */
    public static function document(int $counter): string
    {
        // Rotate through 2 document images: doc-1.jpg, doc-2.jpg
        $index = (($counter - 1) % 2) + 1;

        return "seed-samples/documents/doc-{$index}.jpg";
    }

    /**
     * Generate QRIS QR code image URL.
     *
     * Generates dynamic QRIS-format QR codes via QR Server API.
     * Unlike other methods, this generates external URLs since QRIS codes
     * must be unique per merchant/transaction.
     *
     * Size: 400x400 (square, optimized for payment scanning).
     *
     * @param  string  $merchantId  Merchant ID (16 digits)
     * @param  string  $merchantName  Merchant name (max 25 chars)
     * @param  string  $city  City name (max 15 chars)
     * @param  string  $postalCode  Postal code (5 digits)
     * @param  int  $seed  Random seed for unique transaction IDs
     * @return string External QR code image URL
     */
    public static function qrisCode(
        string $merchantId,
        string $merchantName,
        string $city,
        string $postalCode,
        int $seed
    ): string {
        // Generate deterministic random transaction ID based on seed
        $randomId = str_pad((string) ($seed % 999999999999), 12, '0', STR_PAD_LEFT);

        // Calculate CRC length (2 digits: length of checksum field)
        $crc = '07'; // Standard QRIS CRC length indicator

        // Generate simple checksum (last 4 digits of seed)
        $checksum = str_pad((string) ($seed % 9999), 4, '0', STR_PAD_LEFT);

        // Sanitize merchant name and city (remove special chars, max length)
        $safeMerchantName = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $merchantName), 0, 25);
        $safeCity = substr(preg_replace('/[^A-Za-z0-9 ]/', '', $city), 0, 15);

        // Build QRIS string
        $qrisString = "00020101021226{$merchantId}0102{$randomId}5204{$checksum}5303360540155802ID5916{$safeMerchantName}6009{$safeCity}610560{$postalCode}6304{$crc}{$checksum}";

        // Generate QR code using QR Server API
        return 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data='.urlencode($qrisString);
    }

    /**
     * Generate batch of avatar storage paths.
     *
     * Uses rotation to cycle through available avatar samples.
     *
     * @param  int  $count  Number of paths to generate
     * @param  int  $startCounter  Starting counter (default: 1)
     * @return array<int, string> Array of avatar storage paths
     */
    public static function avatarBatch(int $count, int $startCounter = 1): array
    {
        $paths = [];

        for ($i = 0; $i < $count; $i++) {
            $paths[] = self::avatar($startCounter + $i);
        }

        return $paths;
    }

    /**
     * Generate batch of kost image storage paths.
     *
     * Uses rotation to cycle through available kost samples.
     *
     * @param  int  $count  Number of paths to generate
     * @param  int  $startCounter  Starting counter (default: 1)
     * @return array<int, string> Array of kost image storage paths
     */
    public static function kostImageBatch(int $count, int $startCounter = 1): array
    {
        $paths = [];

        for ($i = 0; $i < $count; $i++) {
            $paths[] = self::kostImage($startCounter + $i);
        }

        return $paths;
    }

    /**
     * Generate batch of room image storage paths.
     *
     * Uses rotation to cycle through available room samples.
     *
     * @param  int  $count  Number of paths to generate
     * @param  int  $startCounter  Starting counter (default: 1)
     * @return array<int, string> Array of room image storage paths
     */
    public static function roomImageBatch(int $count, int $startCounter = 1): array
    {
        $paths = [];

        for ($i = 0; $i < $count; $i++) {
            $paths[] = self::roomImage($startCounter + $i);
        }

        return $paths;
    }

    /**
     * Generate batch of document storage paths.
     *
     * Uses rotation to cycle through available document samples.
     *
     * @param  int  $count  Number of paths to generate
     * @param  int  $startCounter  Starting counter (default: 1)
     * @return array<int, string> Array of document storage paths
     */
    public static function documentBatch(int $count, int $startCounter = 1): array
    {
        $paths = [];

        for ($i = 0; $i < $count; $i++) {
            $paths[] = self::document($startCounter + $i);
        }

        return $paths;
    }
}
