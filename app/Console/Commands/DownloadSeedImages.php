<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadSeedImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:download-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download sample images for database seeding';

    /**
     * Image sets configuration.
     *
     * @var array<string, array{directory: string, count: int, width: int, height: int, start: int}>
     */
    private array $imageSets = [
        'kost' => [
            'directory' => 'seed-samples/kost',
            'count' => 5,
            'width' => 800,
            'height' => 600,
            'start' => 1,
        ],
        'rooms' => [
            'directory' => 'seed-samples/rooms',
            'count' => 5,
            'width' => 800,
            'height' => 600,
            'start' => 10,
        ],
        'avatars' => [
            'directory' => 'seed-samples/avatars',
            'count' => 3,
            'width' => 400,
            'height' => 400,
            'start' => 20,
        ],
        'documents' => [
            'directory' => 'seed-samples/documents',
            'count' => 2,
            'width' => 800,
            'height' => 1200,
            'start' => 30,
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting seed image download...');

        $totalImages = array_sum(array_column($this->imageSets, 'count'));
        $this->info("Will download {$totalImages} images across ".count($this->imageSets).' categories.');

        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        foreach ($this->imageSets as $setName => $config) {
            $this->info("Downloading {$setName} images...");

            // Create directory if not exists
            $this->ensureDirectoryExists($config['directory']);

            $bar = $this->output->createProgressBar($config['count']);
            $bar->start();

            for ($i = 1; $i <= $config['count']; $i++) {
                $filename = "{$setName}-{$i}.jpg";
                $path = "{$config['directory']}/{$filename}";
                $randomSeed = $config['start'] + $i - 1;
                $url = "https://picsum.photos/{$config['width']}/{$config['height']}?random={$randomSeed}";

                $downloaded = $this->downloadImageWithRetry($url, $path);

                if ($downloaded) {
                    $successCount++;
                } else {
                    $errorCount++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
        }

        $this->newLine();
        $this->info("Download complete: {$successCount} successful, {$errorCount} errors.");

        if ($errorCount > 0) {
            $this->warn('Some images failed to download. Run the command again to retry.');

            return self::FAILURE;
        }

        $this->info('All images downloaded successfully!');

        return self::SUCCESS;
    }

    /**
     * Ensure storage directory exists.
     *
     * @param  string  $directory  Directory path relative to storage/app/public
     */
    private function ensureDirectoryExists(string $directory): void
    {
        $fullPath = Storage::disk('public')->path($directory);

        if (! File::isDirectory($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        // Create .gitkeep to preserve directory structure
        $gitkeepPath = "{$fullPath}/.gitkeep";
        if (! File::exists($gitkeepPath)) {
            File::put($gitkeepPath, '');
        }
    }

    /**
     * Download image with retry mechanism.
     *
     * @param  string  $url  Image URL
     * @param  string  $path  Storage path relative to storage/app/public
     * @param  int  $maxAttempts  Maximum retry attempts
     * @return bool True if download successful
     */
    private function downloadImageWithRetry(string $url, string $path, int $maxAttempts = 3): bool
    {
        $fullPath = Storage::disk('public')->path($path);

        // Skip if file already exists and is valid
        if (File::exists($fullPath) && File::size($fullPath) > 0 && $this->isValidImage($fullPath)) {
            return true;
        }

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    $content = $response->body();

                    // Validate content is not empty
                    if (empty($content) || strlen($content) < 100) {
                        throw new \RuntimeException('Downloaded content is empty or too small');
                    }

                    // Save to storage
                    File::put($fullPath, $content);

                    // Verify saved file
                    if (File::exists($fullPath) && File::size($fullPath) > 0 && $this->isValidImage($fullPath)) {
                        return true;
                    }

                    throw new \RuntimeException('Saved file verification failed');
                }

                throw new \RuntimeException("HTTP error: {$response->status()}");
            } catch (\Exception $e) {
                if ($attempt === $maxAttempts) {
                    $this->error("\nFailed to download {$path} after {$maxAttempts} attempts: {$e->getMessage()}");

                    return false;
                }

                // Wait before retry (exponential backoff)
                sleep($attempt);
            }
        }

        return false;
    }

    /**
     * Validate if file is a valid image.
     *
     * @param  string  $path  Full file path
     * @return bool True if valid image
     */
    private function isValidImage(string $path): bool
    {
        try {
            $imageSize = @getimagesize($path);

            return $imageSize !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
