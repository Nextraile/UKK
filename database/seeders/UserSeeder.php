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
     * - 13 Tenants (11 verified, 1 unverified, 1 soft deleted)
     *
     * Stores LoremFlickr avatar URLs (no downloads).
     */
    public function run(): void
    {
        $this->command->info('👤 Seeding users...');

        // SuperAdmin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@sewakost.com'],
            [
                'first_name' => 'Ahmad',
                'last_name' => 'Superadmin',
                'password' => 'password', // Will be hashed by factory/model cast
                'role' => 'superadmin',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=1',
            ]
        );

        // Admins (Kost Owners)
        $admin1 = User::firstOrCreate(
            ['email' => 'budi.admin@sewakost.com'],
            [
                'first_name' => 'Budi',
                'last_name' => 'Santoso',
                'password' => 'password',
                'phone' => '081234567890',
                'role' => 'admin',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=2',
            ]
        );

        $admin2 = User::firstOrCreate(
            ['email' => 'siti.admin@sewakost.com'],
            [
                'first_name' => 'Siti',
                'last_name' => 'Rahayu',
                'password' => 'password',
                'phone' => '081234567891',
                'role' => 'admin',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=3',
            ]
        );

        $admin3 = User::firstOrCreate(
            ['email' => 'andi.admin@sewakost.com'],
            [
                'first_name' => 'Andi',
                'last_name' => 'Wijaya',
                'password' => 'password',
                'phone' => '081234567892',
                'role' => 'admin',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=4',
            ]
        );

        // Tenants
        $tenant1 = User::firstOrCreate(
            ['email' => 'rina.tenant@example.com'],
            [
                'first_name' => 'Rina',
                'last_name' => 'Kusuma',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=5',
            ]
        );

        $tenant2 = User::firstOrCreate(
            ['email' => 'doni.tenant@example.com'],
            [
                'first_name' => 'Doni',
                'last_name' => 'Pratama',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=6',
            ]
        );

        $tenant3 = User::firstOrCreate(
            ['email' => 'maya.tenant@example.com'],
            [
                'first_name' => 'Maya',
                'last_name' => 'Lestari',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=7',
            ]
        );

        $tenant4 = User::firstOrCreate(
            ['email' => 'riko.tenant@example.com'],
            [
                'first_name' => 'Riko',
                'last_name' => 'Saputra',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=8',
            ]
        );

        // Additional tenants for rental seed data
        $tenant5 = User::firstOrCreate(
            ['email' => 'budi.tenant@example.com'],
            [
                'first_name' => 'Budi',
                'last_name' => 'Hartono',
                'password' => 'password',
                'phone' => '081234567897',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=9',
            ]
        );

        $tenant6 = User::firstOrCreate(
            ['email' => 'ani.tenant@example.com'],
            [
                'first_name' => 'Ani',
                'last_name' => 'Susanti',
                'password' => 'password',
                'phone' => '081234567898',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=10',
            ]
        );

        $tenant7 = User::firstOrCreate(
            ['email' => 'eko.tenant@example.com'],
            [
                'first_name' => 'Eko',
                'last_name' => 'Prasetyo',
                'password' => 'password',
                'phone' => '081234567899',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=11',
            ]
        );

        $tenant8 = User::firstOrCreate(
            ['email' => 'sari.tenant@example.com'],
            [
                'first_name' => 'Sari',
                'last_name' => 'Melati',
                'password' => 'password',
                'phone' => '081234567900',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=12',
            ]
        );

        $tenant9 = User::firstOrCreate(
            ['email' => 'dedi.tenant@example.com'],
            [
                'first_name' => 'Dedi',
                'last_name' => 'Santoso',
                'password' => 'password',
                'phone' => '081234567901',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=13',
            ]
        );

        $tenant10 = User::firstOrCreate(
            ['email' => 'lina.tenant@example.com'],
            [
                'first_name' => 'Lina',
                'last_name' => 'Wijaya',
                'password' => 'password',
                'phone' => '081234567902',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=14',
            ]
        );

        $tenant11 = User::firstOrCreate(
            ['email' => 'tono.tenant@example.com'],
            [
                'first_name' => 'Tono',
                'last_name' => 'Suryadi',
                'password' => 'password',
                'phone' => '081234567903',
                'role' => 'user',
                'email_verified_at' => now(),
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=15',
            ]
        );

        // Unverified tenant
        $unverified = User::firstOrCreate(
            ['email' => 'unverified@example.com'],
            [
                'first_name' => 'Dewi',
                'last_name' => 'Anggraini',
                'password' => 'password',
                'role' => 'user',
                'email_verified_at' => null, // NOT verified
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=16',
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
                'avatar_path' => 'https://loremflickr.com/400/400/portrait,face?random=17',
            ]
        );

        if (! $deletedUser->trashed()) {
            $deletedUser->delete();
        }

        $this->command->info('✅ Users seeded: 17 total (1 SuperAdmin, 3 Admins, 13 Tenants)');
        $this->command->info('   🔗 Avatar URLs generated (LoremFlickr)');
    }
}
