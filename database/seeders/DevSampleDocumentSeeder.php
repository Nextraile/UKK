<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DevSampleDocumentSeeder extends Seeder
{
    /**
     * Download dummy documents from LoremFlickr for testing.
     *
     * Downloads:
     * - 20 KTP documents (600x400 portrait)
     * - 20 Selfie with KTP documents (400x600 portrait)
     * - 20 Payment proofs (800x600 landscape)
     *
     * Total: 60 files (~30 seconds download time)
     *
     * These files are referenced by RentalSeeder for document verification testing.
     */
    public function run(): void
    {
        $this->command->info('📄 Downloading dummy documents for testing...');
        $this->command->newLine();

        // Create directories if they don't exist
        Storage::disk('public')->makeDirectory('rental-documents');
        Storage::disk('public')->makeDirectory('payment-proofs');

        // Download 20 KTP documents (600x400 portrait)
        $this->downloadDocuments('rental-documents', 'seed-ktp', 20, 600, 400);

        // Download 20 selfie with KTP documents (400x600 portrait)
        $this->downloadDocuments('rental-documents', 'seed-selfie', 20, 400, 600);

        // Download 20 payment proofs (800x600 landscape)
        $this->downloadDocuments('payment-proofs', 'seed-payment', 20, 800, 600);

        $this->command->newLine();
        $this->command->info('✅ Documents downloaded successfully!');
        $this->command->info('   - 20 KTP documents in storage/app/public/rental-documents/');
        $this->command->info('   - 20 Selfie documents in storage/app/public/rental-documents/');
        $this->command->info('   - 20 Payment proofs in storage/app/public/payment-proofs/');
    }

    /**
     * Download documents from LoremFlickr.
     *
     * @param  string  $dir  Directory to store files (relative to public disk)
     * @param  string  $prefix  Filename prefix
     * @param  int  $count  Number of files to download
     * @param  int  $width  Image width
     * @param  int  $height  Image height
     */
    private function downloadDocuments(string $dir, string $prefix, int $count, int $width, int $height): void
    {
        $this->command->info("📥 Downloading {$count} {$prefix} files ({$width}x{$height})...");
        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        for ($i = 1; $i <= $count; $i++) {
            try {
                // Use LoremFlickr with contextual tags for better variety
                $tags = match ($prefix) {
                    'seed-ktp' => 'document,card,id',
                    'seed-selfie' => 'portrait,person,face',
                    'seed-payment' => 'receipt,document,paper',
                    default => 'document',
                };

                $url = "https://loremflickr.com/{$width}/{$height}/{$tags}?random={$i}";
                $content = Http::timeout(20)->get($url)->throw()->body();

                Storage::disk('public')->put("{$dir}/{$prefix}-{$i}.jpg", $content);
            } catch (\Exception $e) {
                $this->command->warn("\n⚠️  Failed to download {$prefix}-{$i}: {$e->getMessage()}");
                $this->command->warn('   Skipping and continuing...');
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
    }
}
