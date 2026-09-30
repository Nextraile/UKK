<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Payment\Models\Payment;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use App\Domain\Review\Models\Review;
use App\Domain\RoomInventory\Models\PriceScheme;
use App\Domain\RoomInventory\Models\Room;
use App\Domain\RoomInventory\Models\RoomType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with comprehensive demo data.
     *
     * Seeding strategy: High-volume realistic test data for SewaKost marketplace.
     * - 82 users (2 superadmins, 30 admins, 50 tenants) with avatar URLs
     * - 300 kosts across multiple cities, status distributions, room hierarchies
     * - 500 rentals with full payment/document/history chains
     * - ~200 reviews for completed rentals
     *
     * Execution order respects FK constraints and data dependencies:
     * 1. SystemUserSeeder - System user (id=1) for automated operations
     * 2. SuperAdminSeeder - Superadmin account (id=2) for platform management
     * 3. CategorySeeder - Master data (8 categories: Putra, Putri, Campur, etc.)
     * 4. UserSeeder - Identity data (82 users with roles, avatar URLs)
     * 5. KostSeeder - Kosts with room types, rooms, facilities, images (300 kosts)
     * 6. RentalSeeder - Rental lifecycle with payments, documents, histories (500 rentals)
     * 7. ReviewSeeder - Reviews for completed rentals (~200 reviews, ~90% coverage)
     *
     * Image/document handling: Uses external Picsum/Lorem.space URLs + ImageUrlGenerator.
     * No file downloads during seeding (performance optimization).
     */
    public function run(): void
    {
        // Environment check - only seed comprehensive data in local/testing
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('⚠️  Comprehensive seeding only runs in local/testing environments');
            $this->command->info('Current environment: '.app()->environment());

            return;
        }

        $this->command->info('🌱 Seeding database for '.app()->environment().' environment...');
        $this->command->newLine();

        $startTime = microtime(true);

        // Execute seeders in dependency order
        $this->call([
            SystemUserSeeder::class,   // System user for automated operations
            SuperAdminSeeder::class,   // Superadmin account
            CategorySeeder::class,     // 8 categories (master data)
            UserSeeder::class,         // 82 users (2 SA, 30 admins, 50 tenants)
            KostSeeder::class,         // 300 kosts with all children (room types, rooms, facilities, images)
            RentalSeeder::class,       // 500 rentals with payments, documents, histories
            ReviewSeeder::class,       // ~200 reviews (~90% of completed rentals)
        ]);

        $duration = round(microtime(true) - $startTime, 2);

        $this->command->newLine();
        $this->command->info('📊 Database Seeding Summary');
        $this->command->newLine();

        // Comprehensive summary table with counts and details
        $this->command->table(
            ['Entity', 'Count', 'Details'],
            [
                [
                    'Users',
                    User::count(),
                    sprintf(
                        '2 superadmins, %d admins, %d tenants',
                        User::where('role', 'admin')->count(),
                        User::where('role', 'tenant')->count()
                    ),
                ],
                [
                    'Categories',
                    Category::count(),
                    'Master data (Putra, Putri, Campur, etc.)',
                ],
                [
                    'Kosts',
                    Kost::count(),
                    sprintf(
                        '%d active, %d draft, %d pending, %d approved, %d rejected',
                        Kost::where('status', 'active')->count(),
                        Kost::where('status', 'draft')->count(),
                        Kost::where('status', 'pending_review')->count(),
                        Kost::where('status', 'approved')->count(),
                        Kost::where('status', 'rejected')->count()
                    ),
                ],
                [
                    'Room Types',
                    RoomType::count(),
                    '2 per active/approved kost',
                ],
                [
                    'Rooms',
                    Room::count(),
                    '5 per room type',
                ],
                [
                    'Price Schemes',
                    PriceScheme::count(),
                    '2-3 per room type (daily, monthly, yearly)',
                ],
                [
                    'Rentals',
                    Rental::count(),
                    sprintf(
                        '%d pending, %d paid, %d confirmed, %d active, %d completed, %d cancelled',
                        Rental::where('status', 'pending')->count(),
                        Rental::where('status', 'paid')->count(),
                        Rental::where('status', 'confirmed')->count(),
                        Rental::where('status', 'active')->count(),
                        Rental::where('status', 'completed')->count(),
                        Rental::where('status', 'cancelled')->count()
                    ),
                ],
                [
                    'Payments',
                    Payment::count(),
                    'QRIS transfers with verification timestamps',
                ],
                [
                    'Rental Documents',
                    RentalDocument::count(),
                    'KTP, Selfie, Family Card uploads',
                ],
                [
                    'Reviews',
                    Review::count(),
                    sprintf(
                        '~%.0f%% of completed rentals',
                        Rental::where('status', 'completed')->count() > 0
                            ? (Review::count() / Rental::where('status', 'completed')->count() * 100)
                            : 0
                    ),
                ],
            ]
        );

        $this->command->newLine();
        $this->command->info("✓ Database seeded successfully in {$duration}s");
    }
}
