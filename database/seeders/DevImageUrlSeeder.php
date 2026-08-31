<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class DevImageUrlSeeder extends Seeder
{
    /**
     * Generate and cache image URLs (no downloads).
     *
     * Generates external URLs for:
     * - 100 kost images (1200x800) - supports 25 kosts with 4 images each
     * - 80 room type images (800x600) - supports 40 room types with 2 images each
     *
     * Total: 180 URLs generated (no file downloads)
     *
     * URLs stored in cache for KostSeeder to consume.
     * Uses Pixabay API URLs if PIXABAY_API_KEY is set, otherwise LoremFlickr URLs.
     */
    public function run(): void
    {
        $pixabayKey = config('services.pixabay.api_key');
        $usePixabay = ! empty($pixabayKey);

        if ($usePixabay) {
            $this->command->info('🔗 Generating Pixabay image URLs (no downloads)...');
        } else {
            $this->command->warn('⚠️  PIXABAY_API_KEY not set - using LoremFlickr URLs');
            $this->command->info('🔗 Generating LoremFlickr URLs (no downloads)...');
        }

        $this->command->newLine();

        // Generate kost image URLs
        $kostUrls = $this->generateKostImageUrls($usePixabay, $pixabayKey);

        // Generate room type image URLs
        $roomUrls = $this->generateRoomImageUrls($usePixabay, $pixabayKey);

        // Store URLs in cache for KostSeeder to consume
        cache()->put('seeder:kost_image_urls', $kostUrls, now()->addHour());
        cache()->put('seeder:room_image_urls', $roomUrls, now()->addHour());

        $this->command->newLine();
        $this->command->info('✅ Image URLs generated successfully!');
        $this->command->info('   - 100 kost image URLs cached');
        $this->command->info('   - 80 room type image URLs cached');
    }

    /**
     * Generate kost image URLs with contextual queries.
     *
     * @param  bool  $usePixabay  Whether to use Pixabay API
     * @param  string|null  $pixabayKey  Pixabay API key
     * @return array Array of image URLs
     */
    private function generateKostImageUrls(bool $usePixabay, ?string $pixabayKey): array
    {
        $this->command->info('🔗 Generating kost image URLs (100 URLs)...');
        $bar = $this->command->getOutput()->createProgressBar(100);
        $bar->start();

        // Rotate query terms for variety
        $queries = [
            'boarding house',
            'kost indonesia',
            'apartment exterior',
            'residential building',
            'house facade',
        ];

        $urls = [];

        for ($i = 1; $i <= 100; $i++) {
            try {
                if ($usePixabay) {
                    $query = $queries[($i - 1) % count($queries)];
                    $url = $this->getPixabayImageUrl($query, 'horizontal', $pixabayKey, $i);

                    // Fallback to LoremFlickr if Pixabay returns no results
                    if ($url === null) {
                        $url = "https://loremflickr.com/1200/800/apartment,house,building?random={$i}";
                    }
                } else {
                    $url = "https://loremflickr.com/1200/800/apartment,house,building?random={$i}";
                }

                $urls[] = $url;
            } catch (\Exception $e) {
                $this->command->warn("\n⚠️  Failed to generate kost URL {$i}: {$e->getMessage()}");
                $this->command->warn('   Using fallback LoremFlickr URL...');
                $urls[] = "https://loremflickr.com/1200/800/apartment,house,building?random={$i}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();

        return $urls;
    }

    /**
     * Generate room type image URLs with contextual queries.
     *
     * @param  bool  $usePixabay  Whether to use Pixabay API
     * @param  string|null  $pixabayKey  Pixabay API key
     * @return array Array of image URLs
     */
    private function generateRoomImageUrls(bool $usePixabay, ?string $pixabayKey): array
    {
        $this->command->info('🔗 Generating room type image URLs (80 URLs)...');
        $bar = $this->command->getOutput()->createProgressBar(80);
        $bar->start();

        // Rotate query terms for variety
        $queries = [
            'bedroom interior',
            'furnished room',
            'modern bedroom',
            'cozy bedroom',
            'minimalist bedroom',
        ];

        $urls = [];

        for ($i = 1; $i <= 80; $i++) {
            try {
                if ($usePixabay) {
                    $query = $queries[($i - 1) % count($queries)];
                    $url = $this->getPixabayImageUrl($query, 'horizontal', $pixabayKey, $i);

                    // Fallback to picsum if Pixabay returns no results
                    if ($url === null) {
                        $url = "https://loremflickr.com/800/600/bedroom,interior,room?random={$i}";
                    }
                } else {
                    $url = "https://loremflickr.com/800/600/bedroom,interior,room?random={$i}";
                }

                $urls[] = $url;
            } catch (\Exception $e) {
                $this->command->warn("\n⚠️  Failed to generate room URL {$i}: {$e->getMessage()}");
                $this->command->warn('   Using fallback LoremFlickr URL...');
                $urls[] = "https://loremflickr.com/800/600/bedroom,interior,room?random={$i}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();

        return $urls;
    }

    /**
     * Get Pixabay image URL without downloading.
     *
     * @param  string  $query  Search query
     * @param  string  $orientation  Image orientation (horizontal, vertical)
     * @param  string  $apiKey  Pixabay API key
     * @param  int  $seed  Seed for randomization
     * @return string|null Image URL, or null if no results
     *
     * @throws \Exception If API request fails
     */
    private function getPixabayImageUrl(string $query, string $orientation, string $apiKey, int $seed): ?string
    {
        // Get random image metadata from Pixabay (no download)
        $randomPage = ($seed % 50) + 1; // Deterministic page based on seed

        $response = Http::timeout(10)
            ->get('https://pixabay.com/api/', [
                'key' => $apiKey,
                'q' => $query,
                'image_type' => 'photo',
                'orientation' => $orientation,
                'per_page' => 3, // Minimum allowed by Pixabay API (3-200 range)
                'page' => $randomPage,
                'safesearch' => 'true',
            ])
            ->throw()
            ->json();

        // Check if we got results
        if (empty($response['hits'])) {
            return null;
        }

        // Pick image from results (deterministic based on seed)
        $index = $seed % count($response['hits']);

        return $response['hits'][$index]['webformatURL'];
    }
}
