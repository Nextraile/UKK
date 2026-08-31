<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DevSampleImageSeeder extends Seeder
{
    /**
     * Download sample images from Pixabay API with contextual queries.
     *
     * Downloads:
     * - 100 kost images (1200x800) - supports 25 kosts with 4 images each
     * - 80 room type images (800x600) - supports 40 room types with 2 images each
     *
     * Total: 180 images
     *
     * Falls back to picsum.photos if PIXABAY_API_KEY is not set.
     */
    public function run(): void
    {
        $pixabayKey = config('services.pixabay.api_key');
        $usePixabay = ! empty($pixabayKey);

        if ($usePixabay) {
            $this->command->info('🖼️  Downloading contextual images from Pixabay API...');
        } else {
            $this->command->warn('⚠️  PIXABAY_API_KEY not set - falling back to picsum.photos');
            $this->command->info('🖼️  Downloading sample images from picsum.photos...');
        }

        $this->command->newLine();

        // Create directories if they don't exist
        Storage::disk('public')->makeDirectory('kost-images');
        Storage::disk('public')->makeDirectory('room-type-images');

        // Download kost images (1200x800)
        $this->downloadKostImages($usePixabay, $pixabayKey);

        // Download room type images (800x600)
        $this->downloadRoomImages($usePixabay, $pixabayKey);

        $this->command->newLine();
        $this->command->info('✅ Images downloaded successfully!');
        $this->command->info('   - 100 kost images in storage/app/public/kost-images/');
        $this->command->info('   - 80 room type images in storage/app/public/room-type-images/');
    }

    /**
     * Download kost images with contextual queries.
     *
     * @param  bool  $usePixabay  Whether to use Pixabay API
     * @param  string|null  $pixabayKey  Pixabay API key
     */
    private function downloadKostImages(bool $usePixabay, ?string $pixabayKey): void
    {
        $this->command->info('📥 Downloading kost images (100 images, 1200x800)...');
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

        for ($i = 1; $i <= 100; $i++) {
            try {
                if ($usePixabay) {
                    $query = $queries[($i - 1) % count($queries)];
                    $content = $this->downloadFromPixabay($query, 'horizontal', $pixabayKey);

                    // Fallback to picsum if Pixabay returns no results
                    if ($content === null) {
                        $content = Http::timeout(10)
                            ->get('https://picsum.photos/1200/800')
                            ->throw()
                            ->body();
                    }
                } else {
                    $content = Http::timeout(10)
                        ->get('https://picsum.photos/1200/800')
                        ->throw()
                        ->body();
                }

                Storage::disk('public')->put("kost-images/seed-kost-{$i}.jpg", $content);
            } catch (\Exception $e) {
                $this->command->warn("\n⚠️  Failed to download kost image {$i}: {$e->getMessage()}");
                $this->command->warn('   Skipping and continuing...');
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
    }

    /**
     * Download room type images with contextual queries.
     *
     * @param  bool  $usePixabay  Whether to use Pixabay API
     * @param  string|null  $pixabayKey  Pixabay API key
     */
    private function downloadRoomImages(bool $usePixabay, ?string $pixabayKey): void
    {
        $this->command->info('📥 Downloading room type images (80 images, 800x600)...');
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

        for ($i = 1; $i <= 80; $i++) {
            try {
                if ($usePixabay) {
                    $query = $queries[($i - 1) % count($queries)];
                    $content = $this->downloadFromPixabay($query, 'horizontal', $pixabayKey);

                    // Fallback to picsum if Pixabay returns no results
                    if ($content === null) {
                        $content = Http::timeout(10)
                            ->get('https://picsum.photos/800/600')
                            ->throw()
                            ->body();
                    }
                } else {
                    $content = Http::timeout(10)
                        ->get('https://picsum.photos/800/600')
                        ->throw()
                        ->body();
                }

                Storage::disk('public')->put("room-type-images/seed-room-{$i}.jpg", $content);
            } catch (\Exception $e) {
                $this->command->warn("\n⚠️  Failed to download room image {$i}: {$e->getMessage()}");
                $this->command->warn('   Skipping and continuing...');
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
    }

    /**
     * Download image from Pixabay API.
     *
     * @param  string  $query  Search query
     * @param  string  $orientation  Image orientation (horizontal, vertical)
     * @param  string  $apiKey  Pixabay API key
     * @return string|null Image binary content, or null if no results
     *
     * @throws \Exception If download fails
     */
    private function downloadFromPixabay(string $query, string $orientation, string $apiKey): ?string
    {
        // Add delay to avoid rate limiting (Pixabay API has strict rate limits)
        sleep(1); // 1 second delay = 60 requests/minute (conservative approach)

        // Get random image metadata from Pixabay
        $randomPage = rand(1, 50); // Random page for variety

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

        // Pick random image from results (up to 3)
        $randomIndex = rand(0, count($response['hits']) - 1);
        $imageUrl = $response['hits'][$randomIndex]['webformatURL'];

        return Http::timeout(15)
            ->get($imageUrl)
            ->throw()
            ->body();
    }
}
