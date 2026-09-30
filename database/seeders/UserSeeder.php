<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use App\Domain\Seeding\ImageUrlGenerator;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed users for comprehensive demo data.
     *
     * Creates:
     * - 1 System SuperAdmin (preserved test data)
     * - 1 SuperAdmin (preserved test data)
     * - 30 Admins (3 preserved test data + 27 generated)
     * - 50 Tenants (48 verified + 1 unverified + 1 soft deleted)
     *
     * Total: ~82 users
     *
     * Stores LoremFlickr avatar URLs (no downloads).
     */
    public function run(): void
    {
        $this->command->info('👤 Seeding users...');

        $avatarCounter = 1;

        // ============================================================
        // PRESERVED TEST USERS (config/seeding.php)
        // ============================================================

        // System User (SuperAdmin)
        User::firstOrCreate(
            ['email' => 'system@sewakost.local'],
            [
                'first_name' => 'System',
                'last_name' => 'User',
                'password' => 'password',
                'role' => 'superadmin',
                'email_verified_at' => now(),
                'avatar_path' => ImageUrlGenerator::avatar($avatarCounter),
            ]
        );
        $avatarCounter++;

        // Super Administrator
        User::firstOrCreate(
            ['email' => 'superadmin@sewakost.local'],
            [
                'first_name' => 'Super',
                'last_name' => 'Administrator',
                'password' => 'password',
                'role' => 'superadmin',
                'email_verified_at' => now(),
                'avatar_path' => ImageUrlGenerator::avatar($avatarCounter),
            ]
        );
        $avatarCounter++;

        // Admin 1 (Preserved)
        User::firstOrCreate(
            ['email' => 'admin1@sewakost.local'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Pertama',
                'password' => 'password',
                'phone' => '081234567801',
                'role' => 'admin',
                'email_verified_at' => now(),
                'avatar_path' => ImageUrlGenerator::avatar($avatarCounter),
            ]
        );
        $avatarCounter++;

        // Admin 2 (Preserved)
        User::firstOrCreate(
            ['email' => 'admin2@sewakost.local'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Kedua',
                'password' => 'password',
                'phone' => '081234567802',
                'role' => 'admin',
                'email_verified_at' => now(),
                'avatar_path' => ImageUrlGenerator::avatar($avatarCounter),
            ]
        );
        $avatarCounter++;

        // Admin 3 (Preserved)
        User::firstOrCreate(
            ['email' => 'admin3@sewakost.local'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Ketiga',
                'password' => 'password',
                'phone' => '081234567803',
                'role' => 'admin',
                'email_verified_at' => now(),
                'avatar_path' => ImageUrlGenerator::avatar($avatarCounter),
            ]
        );
        $avatarCounter++;

        $this->command->info('   ✓ Preserved test users created (2 superadmins, 3 admins)');

        // ============================================================
        // ADDITIONAL ADMINS (27 more to reach 30 total)
        // ============================================================

        $this->command->info('   🔄 Generating 27 additional admins...');
        $output = $this->command->getOutput();
        $output->progressStart(27);

        for ($i = 0; $i < 27; $i++) {
            User::factory()
                ->admin()
                ->create([
                    'avatar_path' => ImageUrlGenerator::avatar($avatarCounter),
                ]);
            $avatarCounter++;
            $output->progressAdvance();
        }

        $output->progressFinish();
        $this->command->info('   ✓ 27 additional admins created');

        // ============================================================
        // TENANTS (50 total: 48 verified + 1 unverified + 1 deleted)
        // ============================================================

        $this->command->info('   🔄 Generating 48 verified tenants...');
        $output->progressStart(48);

        for ($i = 0; $i < 48; $i++) {
            User::factory()
                ->tenant()
                ->create([
                    'avatar_path' => ImageUrlGenerator::avatar($avatarCounter),
                ]);
            $avatarCounter++;
            $output->progressAdvance();
        }

        $output->progressFinish();
        $this->command->info('   ✓ 48 verified tenants created');

        // Unverified tenant
        User::factory()
            ->tenant()
            ->unverified()
            ->create([
                'avatar_path' => ImageUrlGenerator::avatar($avatarCounter),
            ]);
        $avatarCounter++;
        $this->command->info('   ✓ 1 unverified tenant created');

        // Soft deleted tenant
        $deletedTenant = User::factory()
            ->tenant()
            ->create([
                'avatar_path' => ImageUrlGenerator::avatar($avatarCounter),
            ]);
        $deletedTenant->delete();
        $avatarCounter++;
        $this->command->info('   ✓ 1 soft deleted tenant created');

        // ============================================================
        // SUMMARY
        // ============================================================

        $totalUsers = User::withTrashed()->count();
        $activeUsers = User::count();
        $superAdmins = User::where('role', 'superadmin')->count();
        $admins = User::where('role', 'admin')->count();
        $tenants = User::where('role', 'user')->count();
        $deletedUsers = User::onlyTrashed()->count();

        $this->command->newLine();
        $this->command->info("✅ Users seeded: {$totalUsers} total ({$activeUsers} active, {$deletedUsers} deleted)");
        $this->command->info("   • {$superAdmins} SuperAdmins");
        $this->command->info("   • {$admins} Admins");
        $this->command->info("   • {$tenants} Tenants (excluding deleted)");
        $this->command->info("   🔗 Avatar URLs generated (LoremFlickr, {$avatarCounter} total)");
    }
}
