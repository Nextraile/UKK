<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Address;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\RoomType;
use Illuminate\Database\Seeder;

class KostSeeder extends Seeder
{
    /**
     * Seed kost data for testing.
     */
    public function run(): void
    {
        // Create master categories
        $categoryPutra = Category::firstOrCreate(
            ['slug' => 'putra'],
            ['name' => 'Putra', 'description' => 'Kost khusus pria']
        );

        $categoryPutri = Category::firstOrCreate(
            ['slug' => 'putri'],
            ['name' => 'Putri', 'description' => 'Kost khusus wanita']
        );

        $categoryCampur = Category::firstOrCreate(
            ['slug' => 'campur'],
            ['name' => 'Campur', 'description' => 'Kost untuk pria dan wanita']
        );

        // Create test users
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Test',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => bcrypt('password'),
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );

        // Scenario 1: Draft kost
        $kostDraft = Kost::factory()
            ->for($admin, 'owner')
            ->create([
                'name' => 'Kost Mawar Draft',
                'description' => 'Kost nyaman dekat kampus ITB, masih dalam tahap penyusunan data.',
            ]);

        Address::factory()->create([
            'kost_id' => $kostDraft->id,
            'full_address' => 'Jl. Ganesha No. 10',
            'district' => 'Coblong',
        ]);

        $kostDraft->categories()->attach($categoryPutra->id);

        RoomType::factory()->create([
            'kost_id' => $kostDraft->id,
            'name' => 'Single Bed',
            'description' => 'Kamar single dengan fasilitas lengkap',
        ]);

        // Scenario 2: Pending review kost
        $kostPending = Kost::factory()
            ->pendingReview()
            ->for($admin, 'owner')
            ->create([
                'name' => 'Kost Melati Pending',
                'description' => 'Kost strategis dekat stasiun, menunggu persetujuan admin.',
            ]);

        Address::factory()->create([
            'kost_id' => $kostPending->id,
            'full_address' => 'Jl. Merdeka No. 45',
            'district' => 'Sumur Bandung',
        ]);

        $kostPending->categories()->attach($categoryPutri->id);

        RoomType::factory()->create([
            'kost_id' => $kostPending->id,
            'name' => 'Double Bed',
            'description' => 'Kamar double untuk 2 orang',
        ]);

        // Scenario 3: Approved kost
        $kostApproved = Kost::factory()
            ->approved()
            ->for($admin, 'owner')
            ->create([
                'name' => 'Kost Anggrek Approved',
                'description' => 'Kost bersih dan aman, sudah disetujui oleh super admin.',
                'approved_by' => $superAdmin->id,
            ]);

        Address::factory()->create([
            'kost_id' => $kostApproved->id,
            'full_address' => 'Jl. Dago No. 78',
            'district' => 'Coblong',
        ]);

        $kostApproved->categories()->attach($categoryCampur->id);

        RoomType::factory()->create([
            'kost_id' => $kostApproved->id,
            'name' => 'Suite',
            'description' => 'Kamar suite dengan kamar mandi dalam',
        ]);

        // Scenario 4: Active kost
        $kostActive = Kost::factory()
            ->active()
            ->for($admin, 'owner')
            ->create([
                'name' => 'Kost Dahlia Active',
                'description' => 'Kost premium dengan fasilitas lengkap, sudah aktif dan bisa dipesan.',
                'approved_by' => $superAdmin->id,
            ]);

        Address::factory()->create([
            'kost_id' => $kostActive->id,
            'full_address' => 'Jl. Cihampelas No. 123',
            'district' => 'Bandung Wetan',
        ]);

        $kostActive->categories()->attach($categoryPutra->id);

        RoomType::factory()->count(2)->create([
            'kost_id' => $kostActive->id,
        ]);

        // Scenario 5: Rejected kost
        $kostRejected = Kost::factory()
            ->rejected()
            ->for($admin, 'owner')
            ->create([
                'name' => 'Kost Kenanga Rejected',
                'description' => 'Kost murah meriah, ditolak karena data tidak lengkap.',
            ]);

        Address::factory()->create([
            'kost_id' => $kostRejected->id,
            'full_address' => 'Jl. Buah Batu No. 99',
            'district' => 'Cibeunying',
        ]);

        $kostRejected->categories()->attach($categoryPutri->id);

        RoomType::factory()->create([
            'kost_id' => $kostRejected->id,
            'name' => 'Standard',
            'description' => 'Kamar standard sederhana',
        ]);
    }
}
