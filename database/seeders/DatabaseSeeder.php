<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use App\Domain\Review\Models\Review;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with comprehensive demo data.
     *
     * Execution order respects FK constraints:
     * 1. SystemUserSeeder - System user (id=1) for automated operations
     * 2. CategorySeeder - Independent master data (8 categories)
     * 3. UserSeeder - Independent identity data (17 users, avatar URLs)
     * 4. DevImageUrlSeeder - Generate image URLs (no downloads)
     * 5. DevSampleDocumentSeeder - Download document files (60 files)
     * 6. KostSeeder - Depends on users, categories, images (25 kosts, 4 cities)
     * 7. RentalSeeder - Depends on kosts, users, documents (20 rentals)
     * 8. ReviewSeeder - Depends on rentals (reviews for completed rentals)
     */
    public function run(): void
    {
        // Environment check
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('⚠️  Comprehensive seeding only runs in local/testing environments');
            $this->command->info('Current environment: '.app()->environment());

            return;
        }

        $this->command->info('🌱 Starting comprehensive seeding for SewaKost...');
        $this->command->newLine();

        // Execution order (respect FK constraints)
        $this->call([
            SystemUserSeeder::class,          // System user (id=1)
            SuperAdminSeeder::class,          // Super Admin account (COMP-009)
            CategorySeeder::class,            // Independent (master data) - 8 categories
            UserSeeder::class,                // Independent (identity) - 17 users, avatar URLs
            DevImageUrlSeeder::class,         // Generate image URLs (no downloads)
            DevSampleDocumentSeeder::class,   // Download documents (60 files) BEFORE rentals
            KostSeeder::class,                // Depends: users, categories, images - 25 kosts
            RentalSeeder::class,              // Depends: kosts, users, documents - 20 rentals
            ReviewSeeder::class,              // Depends: rentals - reviews for completed
        ]);

        $this->command->newLine();
        $this->command->info('✅ Seeding complete!');
        $this->command->newLine();

        // Summary table with all seeded entities
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Users', User::count()],
                ['Categories', Category::count()],
                ['Kosts', Kost::count()],
                ['Active Kosts (Marketplace)', Kost::where('status', 'active')->count()],
                ['Room Types', RoomType::count()],
                ['Rooms', Room::count()],
                ['Price Schemes', PriceScheme::count()],
                ['Rentals', Rental::count()],
                ['Active Rentals', Rental::where('status', 'active')->count()],
                ['Payments', Payment::count()],
                ['Rental Documents', RentalDocument::count()],
                ['Reviews', Review::count()],
            ]
        );
    }
}
