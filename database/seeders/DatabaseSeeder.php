<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
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
     * 2. CategorySeeder - Independent master data
     * 3. UserSeeder - Independent identity data
     * 4. DevSampleImageSeeder - Download images from picsum.photos
     * 5. KostSeeder - Depends on users, categories, images
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
            SystemUserSeeder::class,       // System user (id=1)
            SuperAdminSeeder::class,       // Super Admin account (COMP-009)
            CategorySeeder::class,        // Independent (master data)
            UserSeeder::class,             // Independent (identity)
            DevSampleImageSeeder::class,   // Download images first
            KostSeeder::class,             // Depends: users, categories, images
        ]);

        $this->command->newLine();
        $this->command->info('✅ Seeding complete!');
        $this->command->newLine();

        // Summary table
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
            ]
        );
    }
}
