<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Seeding\ImageUrlGenerator;
use Illuminate\Database\Seeder;

class DevImageUrlSeeder extends Seeder
{
    /**
     * Generate and cache image URLs (no downloads).
     *
     * Generates external URLs for:
     * - 100 kost images (1200x800) - supports 25 kosts with 4 images each
     * - 80 room type images (800x600) - supports 40 room types with 2 images each
     *
     * Total: 180 URLs generated instantly (no file downloads, no HTTP calls).
     *
     * URLs stored in cache for KostSeeder to consume.
     * Uses ImageUrlGenerator helper with LoremFlickr templates.
     */
    public function run(): void
    {
        $this->command->info('🔗 Generating LoremFlickr URLs (no downloads)...');
        $this->command->newLine();

        // Generate kost image URLs (instant generation, no HTTP calls)
        $this->command->info('🔗 Generating kost image URLs (100 URLs)...');
        $kostUrls = ImageUrlGenerator::kostImageBatch(100);
        $this->command->info('   ✓ Generated 100 kost image URLs');

        // Generate room type image URLs (instant generation, no HTTP calls)
        $this->command->info('🔗 Generating room type image URLs (80 URLs)...');
        $roomUrls = ImageUrlGenerator::roomImageBatch(80);
        $this->command->info('   ✓ Generated 80 room type image URLs');

        // Store URLs in cache for KostSeeder to consume
        cache()->put('seeder:kost_image_urls', $kostUrls, now()->addHour());
        cache()->put('seeder:room_image_urls', $roomUrls, now()->addHour());

        $this->command->newLine();
        $this->command->info('✅ Image URLs generated successfully!');
        $this->command->info('   - 100 kost image URLs cached');
        $this->command->info('   - 80 room type image URLs cached');
    }
}
