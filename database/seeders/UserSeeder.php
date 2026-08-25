<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed users for comprehensive demo data.
     *
     * Creates:
     * - 1 SuperAdmin
     * - 3 Admins (kost owners)
     * - 6 Tenants (5 verified, 1 unverified, 1 soft deleted)
     */
    public function run(): void
    {
        $this->command->info('👤 Seeding users...');

        // SuperAdmin
        User::firstOrCreate(
            ['email' => 'superadmin@sewakost.com'],
            [
                'first_name' => 'Ahmad',
                'last_name' => 'Superadmin',
                'password' => 'password', // Will be hashed by factory/model cast
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );

        // Admins (Kost Owners)
        User::firstOrCreate(
            ['email' => 'budi.admin@sewakost.com'],
            [
                'first_name' => 'Budi',
                'last_name' => 'Santoso',
                'password' => 'password',
                'phone' => '081234567890',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'siti.admin@sewakost.com'],
            [
                'first_name' => 'Siti',
                'last_name' => 'Rahayu',
                'password' => 'password',
                'phone' => '081234567891',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'andi.admin@sewakost.com'],
            [
                'first_name' => 'Andi',
                'last_name' => 'Wijaya',
                'password' => 'password',
                'phone' => '081234567892',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Tenants
        User::firstOrCreate(
            ['email' => 'rina.tenant@example.com'],
            [
                'first_name' => 'Rina',
                'last_name' => 'Kusuma',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'doni.tenant@example.com'],
            [
                'first_name' => 'Doni',
                'last_name' => 'Pratama',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'maya.tenant@example.com'],
            [
                'first_name' => 'Maya',
                'last_name' => 'Lestari',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'riko.tenant@example.com'],
            [
                'first_name' => 'Riko',
                'last_name' => 'Saputra',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        // Unverified tenant
        User::firstOrCreate(
            ['email' => 'unverified@example.com'],
            [
                'first_name' => 'Dewi',
                'last_name' => 'Anggraini',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => null, // NOT verified
            ]
        );

        // Soft deleted tenant
        $deletedUser = User::withTrashed()->firstOrCreate(
            ['email' => 'deleted@example.com'],
            [
                'first_name' => 'Joko',
                'last_name' => 'Susanto',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );

        if (!$deletedUser->trashed()) {
            $deletedUser->delete();
        }

        $this->command->info('✅ Users seeded: 10 total (1 SuperAdmin, 3 Admins, 6 Tenants)');
    }
}
