<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SystemUserSeeder extends Seeder
{
    /**
     * Seed system user for automated operations.
     *
     * Used by scheduled jobs (MonitorRentalLifecycle) for `changed_by` field.
     * Uses 'superadmin' role since 'system' is not in users.role enum.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'system@sewakost.local'],
            [
                'first_name' => 'System',
                'last_name' => null,
                'role' => 'superadmin',
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]
        );
    }
}
