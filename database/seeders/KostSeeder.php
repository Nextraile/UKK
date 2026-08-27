<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Address;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\KostDocumentRequirement;
use App\Domain\Kost\Models\KostImage;
use App\Domain\Kost\Models\PriceScheme;
use App\Domain\Kost\Models\Room;
use App\Domain\Kost\Models\RoomType;
use App\Domain\Kost\Models\RoomTypeImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KostSeeder extends Seeder
{
    /**
     * Image counter for sequential image assignment.
     */
    protected int $kostImageCounter = 1;

    protected int $roomImageCounter = 1;

    /**
     * Realistic Bandung kost locations with full address data.
     */
    protected array $bandungLocations = [
        ['name' => 'Kost Ganesha Residence', 'address' => 'Jl. Ganesha No. 15', 'district' => 'Coblong', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40132', 'lat' => -6.8915, 'lng' => 107.6107],
        ['name' => 'Kost Dago Heights', 'address' => 'Jl. Ir. H. Juanda (Dago) No. 234', 'district' => 'Coblong', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40135', 'lat' => -6.8698, 'lng' => 107.6165],
        ['name' => 'Kost Cihampelas Walk', 'address' => 'Jl. Cihampelas No. 123', 'district' => 'Sukajadi', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40131', 'lat' => -6.8943, 'lng' => 107.6037],
        ['name' => 'Kost Pasteur Medika', 'address' => 'Jl. Dr. Djunjunan No. 45', 'district' => 'Pasteur', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40161', 'lat' => -6.9034, 'lng' => 107.5789],
        ['name' => 'Kost Buah Batu Syariah', 'address' => 'Jl. Buah Batu No. 88', 'district' => 'Buah Batu', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40286', 'lat' => -6.9431, 'lng' => 107.6334],
        ['name' => 'Kost Kopo Permai', 'address' => 'Jl. Kopo No. 199', 'district' => 'Bojongloa Kaler', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40233', 'lat' => -6.9341, 'lng' => 107.5734],
        ['name' => 'Kost Cicaheum Transit', 'address' => 'Jl. A.H. Nasution No. 67', 'district' => 'Cicaheum', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40293', 'lat' => -6.9125, 'lng' => 107.6564],
        ['name' => 'Kost Antapani Residence', 'address' => 'Jl. Terusan Jakarta No. 112', 'district' => 'Antapani', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40291', 'lat' => -6.9167, 'lng' => 107.6478],
        ['name' => 'Kost Setiabudi Regency', 'address' => 'Jl. Dr. Setiabudi No. 289', 'district' => 'Sukasari', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40154', 'lat' => -6.8734, 'lng' => 107.5923],
        ['name' => 'Kost Surya Sumantri Elite', 'address' => 'Jl. Surya Sumantri No. 156', 'district' => 'Sukajadi', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40163', 'lat' => -6.8912, 'lng' => 107.5889],
        ['name' => 'Kost Tamansari Heritage', 'address' => 'Jl. Tamansari No. 77', 'district' => 'Bandung Wetan', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40131', 'lat' => -6.8856, 'lng' => 107.6045],
        ['name' => 'Kost Braga City Center', 'address' => 'Jl. Suniaraja No. 34', 'district' => 'Sumur Bandung', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40111', 'lat' => -6.9217, 'lng' => 107.6189],
        ['name' => 'Kost Margahayu Raya', 'address' => 'Jl. Margahayu Raya No. 245', 'district' => 'Margahayu', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40286', 'lat' => -6.9612, 'lng' => 107.6456],
        ['name' => 'Kost Ciumbuleuit View', 'address' => 'Jl. Ciumbuleuit No. 178', 'district' => 'Cidadap', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40142', 'lat' => -6.8534, 'lng' => 107.6078],
        ['name' => 'Kost Rancasari Budget', 'address' => 'Jl. A.H. Nasution No. 345', 'district' => 'Rancasari', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'postal_code' => '40292', 'lat' => -6.9567, 'lng' => 107.6723],
    ];

    /**
     * Seed comprehensive kost data with full configuration.
     *
     * Creates:
     * - 15 kosts (6 Active, 3 Draft, 3 Pending, 2 Approved, 1 Rejected)
     * - Distributed across 3 admin owners
     * - Full configuration for active kosts (images, documents, rooms, prices)
     */
    public function run(): void
    {
        $this->command->info('🏠 Seeding kosts with full configuration...');
        $this->command->newLine();

        // Get users
        $admins = [
            User::where('email', 'budi.admin@sewakost.com')->first(),
            User::where('email', 'siti.admin@sewakost.com')->first(),
            User::where('email', 'andi.admin@sewakost.com')->first(),
        ];

        $superAdmin = User::where('email', 'superadmin@sewakost.com')->first();

        if (! $superAdmin) {
            $this->command->error('❌ SuperAdmin not found. Run UserSeeder first.');

            return;
        }

        // Kost distribution: 5 kosts per admin
        $kostDistribution = [
            // Budi: 5 kosts (indices 0-4)
            ['admin_index' => 0, 'location_index' => 0, 'status' => 'active'],
            ['admin_index' => 0, 'location_index' => 1, 'status' => 'active'],
            ['admin_index' => 0, 'location_index' => 2, 'status' => 'draft'],
            ['admin_index' => 0, 'location_index' => 3, 'status' => 'pending_review'],
            ['admin_index' => 0, 'location_index' => 4, 'status' => 'rejected'],

            // Siti: 5 kosts (indices 5-9)
            ['admin_index' => 1, 'location_index' => 5, 'status' => 'draft'],
            ['admin_index' => 1, 'location_index' => 6, 'status' => 'active'],
            ['admin_index' => 1, 'location_index' => 7, 'status' => 'active'],
            ['admin_index' => 1, 'location_index' => 8, 'status' => 'approved'],
            ['admin_index' => 1, 'location_index' => 9, 'status' => 'rejected'],

            // Andi: 5 kosts (indices 10-14)
            ['admin_index' => 2, 'location_index' => 10, 'status' => 'draft'],
            ['admin_index' => 2, 'location_index' => 11, 'status' => 'active'],
            ['admin_index' => 2, 'location_index' => 12, 'status' => 'pending_review'],
            ['admin_index' => 2, 'location_index' => 13, 'status' => 'approved'],
            ['admin_index' => 2, 'location_index' => 14, 'status' => 'active'],
        ];

        foreach ($kostDistribution as $index => $config) {
            $admin = $admins[$config['admin_index']];
            $location = $this->bandungLocations[$config['location_index']];
            $status = $config['status'];

            $kostNumber = $index + 1;
            $this->command->info("Creating kost {$kostNumber}/15: {$location['name']} ({$status})");

            if ($status === 'active') {
                $this->createActiveKost($admin, $superAdmin, $location);
            } elseif ($status === 'draft') {
                $this->createDraftKost($admin, $location);
            } elseif ($status === 'pending_review') {
                $this->createPendingKost($admin, $location);
            } elseif ($status === 'approved') {
                $this->createApprovedKost($admin, $superAdmin, $location);
            } elseif ($status === 'rejected') {
                $this->createRejectedKost($admin, $location);
            }
        }

        $this->command->newLine();
        $this->command->info('✅ Kosts seeded: 15 total');
        $this->command->info('   - 6 Active (marketplace ready)');
        $this->command->info('   - 3 Draft');
        $this->command->info('   - 2 Pending Review');
        $this->command->info('   - 2 Approved');
        $this->command->info('   - 2 Rejected');
    }

    /**
     * Create a fully configured active kost.
     */
    protected function createActiveKost(User $admin, User $superAdmin, array $location): void
    {
        // Create kost with manual slug generation (boot event not working with mass assignment)
        $slug = Str::slug($location['name']);
        $kost = Kost::create([
            'user_id' => $admin->id,
            'name' => $location['name'],
            'slug' => $slug,
            'description' => $this->generateDescription($location['name']),
            'contact_number' => $admin->phone ?? '081234567890',
            'facilities' => $this->generateFacilities(),
            'rules' => $this->generateRules(),
            'bank_name' => $this->randomBankName(),
            'account_number' => $this->generateAccountNumber(),
            'account_holder_name' => $admin->first_name.' '.$admin->last_name,
            'qris_image_path' => 'kost-images/qris-seed-placeholder.jpg',
            'status' => 'active',
            'submitted_at' => now()->subDays(rand(10, 15)),
            'approved_at' => now()->subDays(rand(5, 10)),
            'approved_by' => $superAdmin->id,
            'published_at' => now()->subDays(rand(1, 5)),
        ]);

        // Create address
        Address::create([
            'kost_id' => $kost->id,
            'full_address' => $location['address'],
            'district' => $location['district'],
            'city' => $location['city'],
            'province' => $location['province'],
            'postal_code' => $location['postal_code'],
            'country' => 'Indonesia',
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
        ]);

        // Attach random categories (1-2)
        $categories = Category::inRandomOrder()->limit(rand(1, 2))->get();
        $kost->categories()->attach($categories->pluck('id'));

        // Create 4 kost images
        for ($i = 0; $i < 4; $i++) {
            KostImage::create([
                'kost_id' => $kost->id,
                'image_path' => "kost-images/seed-kost-{$this->kostImageCounter}.jpg",
                'is_thumbnail' => $i === 0,
                'sort_order' => $i + 1,
            ]);
            $this->kostImageCounter++;
        }

        // Create 3 document requirements
        KostDocumentRequirement::create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
            'reason' => 'Verifikasi identitas penyewa',
        ]);

        KostDocumentRequirement::create([
            'kost_id' => $kost->id,
            'document_type' => 'selfie_with_ktp',
            'is_required' => true,
            'reason' => 'Verifikasi kesesuaian identitas',
        ]);

        KostDocumentRequirement::create([
            'kost_id' => $kost->id,
            'document_type' => 'student_card',
            'is_required' => false,
            'reason' => 'Opsional untuk mahasiswa',
        ]);

        // Create 2 room types per kost
        $this->createRoomType($kost, 'Kamar Single', 1, '3x3 m', 500000);
        $this->createRoomType($kost, 'Kamar Double', 2, '4x4 m', 750000);
    }

    /**
     * Create a draft kost (minimal configuration).
     */
    protected function createDraftKost(User $admin, array $location): void
    {
        $slug = Str::slug($location['name']);
        $kost = Kost::create([
            'user_id' => $admin->id,
            'name' => $location['name'],
            'slug' => $slug,
            'description' => $this->generateDescription($location['name']).' (Masih dalam tahap penyusunan)',
            'contact_number' => $admin->phone ?? '081234567890',
            'facilities' => $this->generateFacilities(6),
            'rules' => $this->generateRules(2),
            'status' => 'draft',
        ]);

        Address::create([
            'kost_id' => $kost->id,
            'full_address' => $location['address'],
            'district' => $location['district'],
            'city' => $location['city'],
            'province' => $location['province'],
            'postal_code' => $location['postal_code'],
            'country' => 'Indonesia',
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
        ]);

        // 1 category
        $category = Category::inRandomOrder()->first();
        $kost->categories()->attach($category->id);

        // 2 images
        for ($i = 0; $i < 2; $i++) {
            KostImage::create([
                'kost_id' => $kost->id,
                'image_path' => "kost-images/seed-kost-{$this->kostImageCounter}.jpg",
                'is_thumbnail' => $i === 0,
                'sort_order' => $i + 1,
            ]);
            $this->kostImageCounter++;
        }

        // 2 document requirements
        KostDocumentRequirement::create([
            'kost_id' => $kost->id,
            'document_type' => 'ktp',
            'is_required' => true,
        ]);

        KostDocumentRequirement::create([
            'kost_id' => $kost->id,
            'document_type' => 'selfie_with_ktp',
            'is_required' => true,
        ]);
    }

    /**
     * Create a pending review kost.
     */
    protected function createPendingKost(User $admin, array $location): void
    {
        $slug = Str::slug($location['name']);
        $kost = Kost::create([
            'user_id' => $admin->id,
            'name' => $location['name'],
            'slug' => $slug,
            'description' => $this->generateDescription($location['name']),
            'contact_number' => $admin->phone ?? '081234567890',
            'facilities' => $this->generateFacilities(),
            'rules' => $this->generateRules(),
            'bank_name' => $this->randomBankName(),
            'account_number' => $this->generateAccountNumber(),
            'account_holder_name' => $admin->first_name.' '.$admin->last_name,
            'qris_image_path' => 'kost-images/qris-seed-placeholder.jpg',
            'status' => 'pending_review',
            'submitted_at' => now()->subDays(rand(1, 3)),
        ]);

        Address::create([
            'kost_id' => $kost->id,
            'full_address' => $location['address'],
            'district' => $location['district'],
            'city' => $location['city'],
            'province' => $location['province'],
            'postal_code' => $location['postal_code'],
            'country' => 'Indonesia',
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
        ]);

        $categories = Category::inRandomOrder()->limit(rand(1, 2))->get();
        $kost->categories()->attach($categories->pluck('id'));

        for ($i = 0; $i < 3; $i++) {
            KostImage::create([
                'kost_id' => $kost->id,
                'image_path' => "kost-images/seed-kost-{$this->kostImageCounter}.jpg",
                'is_thumbnail' => $i === 0,
                'sort_order' => $i + 1,
            ]);
            $this->kostImageCounter++;
        }

        KostDocumentRequirement::create(['kost_id' => $kost->id, 'document_type' => 'ktp', 'is_required' => true]);
        KostDocumentRequirement::create(['kost_id' => $kost->id, 'document_type' => 'selfie_with_ktp', 'is_required' => true]);
    }

    /**
     * Create an approved kost.
     */
    protected function createApprovedKost(User $admin, User $superAdmin, array $location): void
    {
        $slug = Str::slug($location['name']);
        $kost = Kost::create([
            'user_id' => $admin->id,
            'name' => $location['name'],
            'slug' => $slug,
            'description' => $this->generateDescription($location['name']),
            'contact_number' => $admin->phone ?? '081234567890',
            'facilities' => $this->generateFacilities(),
            'rules' => $this->generateRules(),
            'bank_name' => $this->randomBankName(),
            'account_number' => $this->generateAccountNumber(),
            'account_holder_name' => $admin->first_name.' '.$admin->last_name,
            'qris_image_path' => 'kost-images/qris-seed-placeholder.jpg',
            'status' => 'approved',
            'submitted_at' => now()->subDays(rand(5, 10)),
            'approved_at' => now()->subDays(rand(1, 3)),
            'approved_by' => $superAdmin->id,
        ]);

        Address::create([
            'kost_id' => $kost->id,
            'full_address' => $location['address'],
            'district' => $location['district'],
            'city' => $location['city'],
            'province' => $location['province'],
            'postal_code' => $location['postal_code'],
            'country' => 'Indonesia',
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
        ]);

        $categories = Category::inRandomOrder()->limit(rand(1, 2))->get();
        $kost->categories()->attach($categories->pluck('id'));

        for ($i = 0; $i < 4; $i++) {
            KostImage::create([
                'kost_id' => $kost->id,
                'image_path' => "kost-images/seed-kost-{$this->kostImageCounter}.jpg",
                'is_thumbnail' => $i === 0,
                'sort_order' => $i + 1,
            ]);
            $this->kostImageCounter++;
        }

        KostDocumentRequirement::create(['kost_id' => $kost->id, 'document_type' => 'ktp', 'is_required' => true]);
        KostDocumentRequirement::create(['kost_id' => $kost->id, 'document_type' => 'selfie_with_ktp', 'is_required' => true]);
        KostDocumentRequirement::create(['kost_id' => $kost->id, 'document_type' => 'student_card', 'is_required' => false]);
    }

    /**
     * Create a rejected kost.
     */
    protected function createRejectedKost(User $admin, array $location): void
    {
        $slug = Str::slug($location['name']);
        $kost = Kost::create([
            'user_id' => $admin->id,
            'name' => $location['name'],
            'slug' => $slug,
            'description' => $this->generateDescription($location['name']),
            'contact_number' => $admin->phone ?? '081234567890',
            'facilities' => $this->generateFacilities(5),
            'rules' => $this->generateRules(3),
            'status' => 'rejected',
            'submitted_at' => now()->subDays(rand(5, 10)),
            'rejected_at' => now()->subDays(rand(1, 3)),
            'rejected_reason' => 'Data tidak lengkap. Mohon lengkapi informasi alamat, foto kost, dan dokumen persyaratan.',
        ]);

        Address::create([
            'kost_id' => $kost->id,
            'full_address' => $location['address'],
            'district' => $location['district'],
            'city' => $location['city'],
            'province' => $location['province'],
            'postal_code' => $location['postal_code'],
            'country' => 'Indonesia',
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
        ]);

        $category = Category::inRandomOrder()->first();
        $kost->categories()->attach($category->id);

        for ($i = 0; $i < 2; $i++) {
            KostImage::create([
                'kost_id' => $kost->id,
                'image_path' => "kost-images/seed-kost-{$this->kostImageCounter}.jpg",
                'is_thumbnail' => $i === 0,
                'sort_order' => $i + 1,
            ]);
            $this->kostImageCounter++;
        }
    }

    /**
     * Create a room type with images, price schemes, and rooms.
     */
    protected function createRoomType(Kost $kost, string $name, int $maxOccupants, string $roomSize, int $securityDeposit): void
    {
        $slug = Str::slug($name);
        $roomType = RoomType::create([
            'kost_id' => $kost->id,
            'name' => $name,
            'slug' => $slug,
            'description' => "Tipe kamar {$name} dengan ukuran {$roomSize}",
            'room_size' => $roomSize,
            'max_occupants' => $maxOccupants,
            'security_deposit' => $securityDeposit,
            'facilities' => $this->generateRoomFacilities(),
            'rules' => $this->generateRoomRules(),
        ]);

        // Create 2 room type images
        for ($i = 0; $i < 2; $i++) {
            RoomTypeImage::create([
                'room_type_id' => $roomType->id,
                'image_path' => "room-type-images/seed-room-{$this->roomImageCounter}.jpg",
                'is_thumbnail' => $i === 0,
                'sort_order' => $i + 1,
            ]);
            $this->roomImageCounter++;
        }

        // Create 3 price schemes
        $monthlyPrice = rand(1200000, 2500000);

        PriceScheme::create([
            'room_type_id' => $roomType->id,
            'name' => 'Bulanan',
            'description' => 'Sewa per bulan',
            'price' => $monthlyPrice,
            'duration_value' => 1,
            'duration_unit' => 'month',
            'is_active' => true,
        ]);

        PriceScheme::create([
            'room_type_id' => $roomType->id,
            'name' => '3 Bulan',
            'description' => 'Sewa 3 bulan (diskon 5%)',
            'price' => $monthlyPrice * 3 * 0.95,
            'duration_value' => 3,
            'duration_unit' => 'month',
            'is_active' => true,
        ]);

        PriceScheme::create([
            'room_type_id' => $roomType->id,
            'name' => '6 Bulan',
            'description' => 'Sewa 6 bulan (diskon 10%)',
            'price' => $monthlyPrice * 6 * 0.90,
            'duration_value' => 6,
            'duration_unit' => 'month',
            'is_active' => true,
        ]);

        // Create 5 rooms per room type (unique codes using counter)
        static $roomCodeCounter = 1;

        for ($i = 0; $i < 5; $i++) {
            Room::create([
                'kost_id' => $kost->id,
                'room_type_id' => $roomType->id,
                'code' => 'R-'.str_pad((string) $roomCodeCounter, 3, '0', STR_PAD_LEFT),
                'status' => $i < 4 ? 'available' : 'unavailable', // 80% available, 20% unavailable
            ]);
            $roomCodeCounter++;
        }
    }

    /**
     * Generate realistic kost description.
     */
    protected function generateDescription(string $name): string
    {
        $descriptions = [
            'Kost nyaman dan strategis dekat dengan kampus dan pusat kota. Lingkungan aman dengan akses mudah ke transportasi umum.',
            'Hunian kost eksklusif dengan fasilitas lengkap dan modern. Cocok untuk mahasiswa dan pekerja profesional.',
            'Kost bersih dan terawat dengan suasana kekeluargaan. Dekat dengan minimarket, warung makan, dan fasilitas umum.',
            'Tempat tinggal ideal untuk Anda yang mencari kenyamanan dan keamanan. Area strategis dengan akses 24 jam.',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Generate kost facilities (7 items default).
     */
    protected function generateFacilities(int $count = 7): array
    {
        $allFacilities = [
            'WiFi gratis',
            'Parkir motor',
            'Parkir mobil',
            'Air PDAM',
            'Listrik token',
            'Kamar mandi dalam',
            'Kamar mandi luar',
            'Dapur bersama',
            'Ruang tamu',
            'Mesin cuci',
            'Jemuran',
            'CCTV',
            'Security 24 jam',
            'Akses kartu',
        ];

        shuffle($allFacilities);

        return array_slice($allFacilities, 0, $count);
    }

    /**
     * Generate kost rules (4 items default).
     */
    protected function generateRules(int $count = 4): array
    {
        $allRules = [
            'Dilarang merokok di dalam kamar',
            'Dilarang membawa hewan peliharaan',
            'Dilarang membawa tamu menginap tanpa izin',
            'Jam berkunjung maksimal pukul 21.00 WIB',
            'Wajib menjaga kebersihan kamar',
            'Dilarang membuat keributan',
            'Wajib lapor jika ada tamu',
            'Dilarang memasak di dalam kamar',
        ];

        shuffle($allRules);

        return array_slice($allRules, 0, $count);
    }

    /**
     * Generate room type facilities (5 items).
     */
    protected function generateRoomFacilities(): array
    {
        $facilities = [
            'Kasur',
            'Lemari pakaian',
            'Meja belajar',
            'Kursi',
            'AC',
            'Kipas angin',
            'Cermin',
            'Gantungan baju',
        ];

        shuffle($facilities);

        return array_slice($facilities, 0, 5);
    }

    /**
     * Generate room type rules (3 items).
     */
    protected function generateRoomRules(): array
    {
        $rules = [
            'Jaga kebersihan kamar',
            'Matikan AC/lampu saat keluar',
            'Dilarang memaku dinding',
            'Buang sampah pada tempatnya',
        ];

        shuffle($rules);

        return array_slice($rules, 0, 3);
    }

    /**
     * Get random bank name.
     */
    protected function randomBankName(): string
    {
        $banks = ['BCA', 'Mandiri', 'BNI', 'BRI'];

        return $banks[array_rand($banks)];
    }

    /**
     * Generate random 10-digit account number.
     */
    protected function generateAccountNumber(): string
    {
        return (string) rand(1000000000, 9999999999);
    }
}
