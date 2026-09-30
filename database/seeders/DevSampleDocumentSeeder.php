<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Seeding\ImageUrlGenerator;
use Illuminate\Database\Seeder;

/**
 * DEPRECATED: This seeder previously downloaded 60 document files via HTTP.
 *
 * Now replaced by ImageUrlGenerator which generates external URLs instantly.
 * Kept for backward compatibility but does nothing when executed.
 *
 * Migration guide:
 * - Use ImageUrlGenerator::document($id) for document URLs
 * - Use ImageUrlGenerator::documentBatch($count) for batch generation
 * - No HTTP downloads required - URLs point to external services
 */
class DevSampleDocumentSeeder extends Seeder
{
    /**
     * Generate dummy document URLs (no downloads).
     *
     * Previously downloaded:
     * - 20 KTP documents (600x400 portrait)
     * - 20 Selfie with KTP documents (400x600 portrait)
     * - 20 Payment proofs (800x600 landscape)
     *
     * Now: Generates external URLs instantly using ImageUrlGenerator.
     * Total: 60 URLs generated (~instant, no HTTP calls).
     */
    public function run(): void
    {
        $this->command->info('🔗 Generating document URLs (no downloads)...');
        $this->command->newLine();

        // Generate document URLs instantly (no HTTP downloads)
        $this->command->info('📄 Generating document URLs (60 URLs)...');

        // Generate 20 KTP document URLs
        $ktpUrls = ImageUrlGenerator::documentBatch(20, startId: 1);
        $this->command->info('   ✓ Generated 20 KTP document URLs');

        // Generate 20 selfie document URLs
        $selfieUrls = ImageUrlGenerator::documentBatch(20, startId: 21);
        $this->command->info('   ✓ Generated 20 Selfie document URLs');

        // Generate 20 payment proof URLs
        $paymentUrls = ImageUrlGenerator::documentBatch(20, startId: 41);
        $this->command->info('   ✓ Generated 20 Payment proof URLs');

        // Store URLs in cache for RentalSeeder to consume (optional)
        cache()->put('seeder:ktp_document_urls', $ktpUrls, now()->addHour());
        cache()->put('seeder:selfie_document_urls', $selfieUrls, now()->addHour());
        cache()->put('seeder:payment_proof_urls', $paymentUrls, now()->addHour());

        $this->command->newLine();
        $this->command->info('✅ Document URLs generated successfully!');
        $this->command->info('   - 20 KTP document URLs cached');
        $this->command->info('   - 20 Selfie document URLs cached');
        $this->command->info('   - 20 Payment proof URLs cached');
        $this->command->newLine();
        $this->command->warn('⚠️  MIGRATION NOTE: This seeder no longer downloads files.');
        $this->command->info('   Use ImageUrlGenerator::document($id) in your seeders instead.');
    }
}
