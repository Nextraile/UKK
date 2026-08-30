<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DevSampleImageSeeder extends Seeder
{
    /**
     * Download sample images from picsum.photos for kost and room type galleries.
     *
     * Downloads:
     * - 100 kost images (1200x800) - supports 25 kosts with 4 images each
     * - 80 room type images (800x600) - supports 40 room types with 2 images each
     *
     * Total: 180 images
     */
    public function run(): void
    {
        $this->command->info('🖼️  Downloading sample images from picsum.photos...');
        $this->command->newLine();

        // Create directories if they don't exist
        Storage::disk('public')->makeDirectory('kost-images');
        Storage::disk('public')->makeDirectory('room-type-images');

        // Download kost images (1200x800)
        $this->command->info('📥 Downloading kost images (100 images, 1200x800)...');
        $bar = $this->command->getOutput()->createProgressBar(100);
        $bar->start();

        for ($i = 1; $i <= 100; $i++) {
            try {
                $content = Http::timeout(10)
                    ->get('https://picsum.photos/1200/800')
                    ->throw()
                    ->body();

                Storage::disk('public')->put("kost-images/seed-kost-{$i}.jpg", $content);
            } catch (\Exception $e) {
                $this->command->warn("\n⚠️  Failed to download kost image {$i}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();

        // Download room type images (800x600)
        $this->command->info('📥 Downloading room type images (80 images, 800x600)...');
        $bar = $this->command->getOutput()->createProgressBar(80);
        $bar->start();

        for ($i = 1; $i <= 80; $i++) {
            try {
                $content = Http::timeout(10)
                    ->get('https://picsum.photos/800/600')
                    ->throw()
                    ->body();

                Storage::disk('public')->put("room-type-images/seed-room-{$i}.jpg", $content);
            } catch (\Exception $e) {
                $this->command->warn("\n⚠️  Failed to download room image {$i}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->newLine();

        $this->command->info('✅ Images downloaded successfully!');
        $this->command->info('   - 100 kost images in storage/app/public/kost-images/');
        $this->command->info('   - 80 room type images in storage/app/public/room-type-images/');
    }
}
