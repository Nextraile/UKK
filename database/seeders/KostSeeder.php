<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Address;
use App\Domain\Kost\Models\Category;
use App\Domain\Kost\Models\Kost;
use App\Domain\RoomInventory\Models\Room;
use App\Domain\RoomInventory\Models\RoomType;
use App\Domain\Seeding\ImageUrlGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seed 300 kosts with complete child records for marketplace testing.
 *
 * Performance-optimized seeder using bulk inserts for child records to achieve
 * <20 second execution time. Preserves 5 test kosts with known slugs for
 * integration testing, then generates remaining kosts distributed by city
 * and status according to config/seeding.php.
 *
 * Distribution:
 * - 150 active (including 4 preserved)
 * - 80 draft (including 1 preserved)
 * - 40 pending_review
 * - 20 approved
 * - 10 rejected
 *
 * Child records per kost:
 * - 1 address (1:1 relation)
 * - 4 kost images
 * - 3 document requirements
 * - 2 room types (active/approved only)
 *   - 3 price schemes per room type
 *   - 2 room type images per room type
 *   - 5 rooms per room type
 */
class KostSeeder extends Seeder
{
    /**
     * Counter for generating unique image URLs.
     *
     * Incremented for each image to ensure unique external URLs from LoremFlickr.
     * Prevents caching issues where same ID returns same image.
     */
    private int $imageCounter = 1;

    /**
     * Counter for generating unique room codes.
     *
     * Ensures unique room codes across all kosts (format: R-001, R-002, etc.).
     */
    private int $roomCodeCounter = 1;

    /**
     * Admin users pool for kost ownership assignment.
     *
     * Cached to avoid repeated queries when assigning kost owners.
     *
     * @var Collection<int, User>
     */
    private $adminUsers;

    /**
     * Super admin users pool for approval assignment.
     *
     * Cached to avoid repeated queries when assigning approvers.
     *
     * @var Collection<int, User>
     */
    private $superAdminUsers;

    /**
     * Categories pool keyed by slug for efficient lookup.
     *
     * Cached to avoid repeated queries when attaching categories to kosts.
     *
     * @var Collection<string, Category>
     */
    private Collection $categories;

    /**
     * City distribution configuration from config/seeding.php.
     *
     * Format: ['City Name' => percentage, ...]
     *
     * @var array<string, int>
     */
    private array $cities;

    /**
     * Chunk size for bulk inserts to prevent memory exhaustion.
     */
    private int $chunkSize;

    /**
     * Run the database seeds.
     *
     * Orchestrates seeding of 300 kosts with all child records in sequence:
     * 1. Preserve 5 test kosts (known slugs)
     * 2. Generate 146 active kosts (150 - 4 preserved)
     * 3. Generate 79 draft kosts (80 - 1 preserved)
     * 4. Generate 40 pending_review kosts
     * 5. Generate 20 approved kosts
     * 6. Generate 10 rejected kosts
     *
     * Each kost gets address, images, documents, and room types with pricing.
     */
    public function run(): void
    {
        $this->command->info('🏠 Seeding kosts...');

        // Cache admin users to avoid repeated queries
        $this->adminUsers = User::where('role', 'admin')->get();
        $this->superAdminUsers = User::where('role', 'superadmin')->get();

        if ($this->adminUsers->isEmpty()) {
            $this->command->error('❌ No admin users found. Run UserSeeder first.');

            return;
        }

        if ($this->superAdminUsers->isEmpty()) {
            $this->command->error('❌ No super admin users found. Run SuperAdminSeeder first.');

            return;
        }

        // Cache categories for efficient lookups
        $this->categories = Category::all()->keyBy('slug');

        if ($this->categories->isEmpty()) {
            $this->command->error('❌ No categories found. Run CategorySeeder first.');

            return;
        }

        // Load configuration
        $this->cities = config('seeding.cities');
        $this->chunkSize = config('seeding.performance.chunk_size', 100);

        // Seed in sequence by status
        $this->seedPreservedKosts();
        $this->seedActiveKosts(146); // 150 - 4 preserved
        $this->seedDraftKosts(79); // 80 - 1 preserved
        $this->seedPendingReviewKosts(40);
        $this->seedApprovedKosts(20);
        $this->seedRejectedKosts(10);

        $this->command->info('✓ Kosts seeded successfully (300 total)');
    }

    /**
     * Seed 5 preserved test kosts with known slugs and cities.
     *
     * These kosts are used in integration tests and must have consistent
     * slugs, names, and cities. Creates complete child records for each.
     *
     * Preserved kosts:
     * - Kost Mawar Indah (active, Bandung)
     * - Kost Melati Residence (active, Jakarta)
     * - Kost Anggrek Premium (active, Yogyakarta)
     * - Kost Dahlia Budget (active, Surabaya)
     * - Kost Tulip Syariah (draft, Bandung)
     */
    protected function seedPreservedKosts(): void
    {
        $preservedData = config('seeding.preserve_test_data.kosts');

        foreach ($preservedData as $data) {
            $kost = $this->createKost(
                name: $data['name'],
                slug: $data['slug'],
                status: $data['status'],
                city: $data['city']
            );

            $this->createChildRecords($kost);

            $this->command->info("  ✓ {$data['name']} ({$data['status']})");
        }
    }

    /**
     * Seed active kosts distributed by city percentage.
     *
     * Active kosts are published and visible in marketplace. Each gets:
     * - Address
     * - 4 images
     * - 3 document requirements
     * - 2 room types (each with 3 price schemes, 2 images, 5 rooms)
     *
     * @param  int  $count  Number of active kosts to create
     */
    protected function seedActiveKosts(int $count): void
    {
        $this->command->info("  Seeding {$count} active kosts...");

        $kostIds = $this->createKostsBatch($count, 'active');
        $this->createChildRecordsForBatch($kostIds, hasRoomTypes: true);

        $this->command->info("  ✓ {$count} active kosts created");
    }

    /**
     * Seed draft kosts distributed by city percentage.
     *
     * Draft kosts are work-in-progress, not submitted for review yet.
     * Gets basic child records but NO room types (incomplete listing).
     *
     * @param  int  $count  Number of draft kosts to create
     */
    protected function seedDraftKosts(int $count): void
    {
        $this->command->info("  Seeding {$count} draft kosts...");

        $kostIds = $this->createKostsBatch($count, 'draft');
        $this->createChildRecordsForBatch($kostIds, hasRoomTypes: false);

        $this->command->info("  ✓ {$count} draft kosts created");
    }

    /**
     * Seed pending_review kosts waiting for admin approval.
     *
     * These kosts have been submitted but not yet reviewed. Gets basic
     * child records but NO room types (can't add rooms until approved).
     *
     * @param  int  $count  Number of pending_review kosts to create
     */
    protected function seedPendingReviewKosts(int $count): void
    {
        $this->command->info("  Seeding {$count} pending_review kosts...");

        $kostIds = $this->createKostsBatch($count, 'pending_review');
        $this->createChildRecordsForBatch($kostIds, hasRoomTypes: false);

        $this->command->info("  ✓ {$count} pending_review kosts created");
    }

    /**
     * Seed approved kosts ready to be published.
     *
     * Approved kosts passed review but owner hasn't published yet.
     * Gets complete child records including room types.
     *
     * @param  int  $count  Number of approved kosts to create
     */
    protected function seedApprovedKosts(int $count): void
    {
        $this->command->info("  Seeding {$count} approved kosts...");

        $kostIds = $this->createKostsBatch($count, 'approved');
        $this->createChildRecordsForBatch($kostIds, hasRoomTypes: true);

        $this->command->info("  ✓ {$count} approved kosts created");
    }

    /**
     * Seed rejected kosts that failed review.
     *
     * Rejected kosts failed admin review with rejection reason.
     * Gets basic child records but NO room types (incomplete/invalid).
     *
     * @param  int  $count  Number of rejected kosts to create
     */
    protected function seedRejectedKosts(int $count): void
    {
        $this->command->info("  Seeding {$count} rejected kosts...");

        $kostIds = $this->createKostsBatch($count, 'rejected');
        $this->createChildRecordsForBatch($kostIds, hasRoomTypes: false);

        $this->command->info("  ✓ {$count} rejected kosts created");
    }

    /**
     * Create single kost with specified attributes.
     *
     * Used for preserved test kosts with known slugs. Assigns random admin
     * owner and super admin approver (if applicable).
     *
     * @param  string  $name  Kost name
     * @param  string  $slug  URL-friendly slug
     * @param  string  $status  Kost status (draft|pending_review|approved|active|rejected)
     * @param  string  $city  City name for address generation
     * @return Kost Created kost instance
     */
    protected function createKost(string $name, string $slug, string $status, string $city): Kost
    {
        $owner = $this->adminUsers->random();
        $approver = $this->superAdminUsers->random();

        $attributes = [
            'user_id' => $owner->id,
            'name' => $name,
            'slug' => $slug,
            'description' => fake()->paragraphs(3, true),
            'contact_number' => fake()->numerify('08##########'),
            'facilities' => ['WiFi', 'AC', 'Kasur', 'Lemari', 'Dapur Bersama'],
            'rules' => ['Dilarang merokok', 'Jam malam 22:00', 'Tamu wajib lapor'],
            'qris_image_path' => 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data='.urlencode('QRIS-'.$this->imageCounter++),
            'bank_name' => fake()->randomElement(['BCA', 'BNI', 'Mandiri', 'BRI']),
            'account_number' => fake()->numerify('##########'),
            'account_holder_name' => $owner->first_name.($owner->last_name ? ' '.$owner->last_name : ''),
        ];

        // Set status-specific timestamps
        $kost = new Kost($attributes);
        $kost->status = $status;

        if (in_array($status, ['approved', 'active'])) {
            $kost->approved_at = now()->subDays(rand(2, 30));
            $kost->approved_by = $approver->id;
        }

        if ($status === 'active') {
            $kost->published_at = now()->subDays(rand(1, 20));
        }

        if ($status === 'rejected') {
            $kost->rejected_at = now()->subDays(rand(1, 10));
            $kost->rejected_by = $approver->id;
            $kost->rejected_reason = fake()->randomElement([
                'Data alamat tidak lengkap. Mohon lengkapi dengan detail yang benar.',
                'Foto kost kurang jelas. Harap upload foto dengan resolusi lebih baik.',
                'Dokumen kepemilikan tidak valid. Silakan upload dokumen yang sesuai.',
                'Informasi kontak tidak dapat diverifikasi. Mohon periksa kembali.',
            ]);
        }

        $kost->save();

        return $kost;
    }

    /**
     * Create batch of kosts with specified status using Eloquent factories.
     *
     * Uses factory states for status-specific attributes. Returns kost IDs
     * for bulk child record creation. Distributes kosts by city percentage.
     *
     * @param  int  $count  Number of kosts to create
     * @param  string  $status  Kost status (draft|pending_review|approved|active|rejected)
     * @return array<int, int> Array of created kost IDs
     */
    protected function createKostsBatch(int $count, string $status): array
    {
        $kostIds = [];
        $cityDistribution = $this->calculateCityDistribution($count);

        foreach ($cityDistribution as $city => $cityCount) {
            for ($i = 0; $i < $cityCount; $i++) {
                $owner = $this->adminUsers->random();

                $factory = Kost::factory()
                    ->for($owner, 'owner');

                // Apply status-specific state
                $factory = match ($status) {
                    'draft' => $factory->draft(),
                    'pending_review' => $factory->pendingReview(),
                    'approved' => $factory->approved(),
                    'active' => $factory->active(),
                    'rejected' => $factory->rejected(),
                    default => $factory,
                };

                // Prepare attributes to override factory defaults
                $attributes = [
                    'qris_image_path' => 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data='.urlencode('QRIS-'.$this->imageCounter++),
                ];

                // Override approved_by with existing super admin (avoid inline factory user creation)
                // Factory creates new user inline which can cause transaction issues
                if (in_array($status, ['approved', 'active'])) {
                    $attributes['approved_by'] = $this->superAdminUsers->random()->id;
                }

                // Use make() + manual slug generation + save()
                // Factory make() doesn't trigger creating event, so we generate slug manually
                $kost = $factory->make($attributes);

                // Generate unique slug (replicates model boot logic)
                if (empty($kost->slug)) {
                    $baseSlug = Str::slug($kost->name);
                    $slug = $baseSlug;
                    $counter = 1;

                    while (Kost::where('slug', $slug)->exists()) {
                        $slug = $baseSlug.'-'.$counter;
                        $counter++;
                    }

                    $kost->slug = $slug;
                }

                $kost->save();

                $kostIds[] = $kost->id;
            }
        }

        return $kostIds;
    }

    /**
     * Create all child records for single kost.
     *
     * Creates address, images, documents, categories, and optionally room types based
     * on kost status. Room types only created for active/approved kosts.
     *
     * @param  Kost  $kost  Parent kost instance
     */
    protected function createChildRecords(Kost $kost): void
    {
        // Create address (1:1)
        $this->createAddress($kost);

        // Create images (bulk insert)
        $this->bulkInsertKostImages([$kost->id]);

        // Create document requirements (bulk insert)
        $this->bulkInsertDocumentRequirements([$kost->id]);

        // Create room types only for active/approved (has pricing)
        $hasRoomTypes = in_array($kost->status, ['active', 'approved']);
        if ($hasRoomTypes) {
            $this->createRoomTypesForKosts([$kost->id]);
        }

        // Attach categories (after room types for price-based logic)
        $this->attachCategoriesToKosts([$kost->id], $hasRoomTypes);
    }

    /**
     * Create child records for batch of kosts using bulk inserts.
     *
     * Performance-optimized method using bulk inserts instead of Eloquent
     * to minimize database round-trips. Chunks operations to prevent memory
     * exhaustion on large batches.
     *
     * @param  array<int, int>  $kostIds  Array of kost IDs
     * @param  bool  $hasRoomTypes  Whether to create room types (active/approved only)
     */
    protected function createChildRecordsForBatch(array $kostIds, bool $hasRoomTypes): void
    {
        // Create addresses (1:1, use Eloquent for relation)
        foreach ($kostIds as $kostId) {
            $kost = Kost::find($kostId);
            $this->createAddress($kost);
        }

        // Bulk insert images
        $this->bulkInsertKostImages($kostIds);

        // Bulk insert document requirements
        $this->bulkInsertDocumentRequirements($kostIds);

        // Create room types if applicable (must be before category attachment for price-based categories)
        if ($hasRoomTypes) {
            $this->createRoomTypesForKosts($kostIds);
        }

        // Attach categories (after room types so price-based categories work)
        $this->attachCategoriesToKosts($kostIds, $hasRoomTypes);
    }

    /**
     * Create address for kost using city-appropriate data.
     *
     * Uses Eloquent because Address has 1:1 relation with Kost and we need
     * to determine city from kost name/slug pattern.
     *
     * @param  Kost  $kost  Parent kost
     */
    protected function createAddress(Kost $kost): void
    {
        // Determine city from kost slug or random from distribution
        $city = $this->determineCityFromSlug($kost->slug);

        $cityData = $this->getCityData($city);

        Address::create([
            'kost_id' => $kost->id,
            'full_address' => fake()->streetAddress().', '.$cityData['district'],
            'district' => $cityData['district'],
            'city' => $city,
            'province' => $cityData['province'],
            'postal_code' => $this->getPostalCodeForCity($city),
            'country' => 'Indonesia',
            'latitude' => $cityData['latitude'],
            'longitude' => $cityData['longitude'],
        ]);
    }

    /**
     * Bulk insert kost images for multiple kosts.
     *
     * Creates 4 images per kost (1 thumbnail + 3 gallery) using raw SQL
     * for performance. Chunks inserts to prevent query size limits.
     *
     * @param  array<int, int>  $kostIds  Array of kost IDs
     */
    protected function bulkInsertKostImages(array $kostIds): void
    {
        $images = [];
        $now = now();

        foreach ($kostIds as $kostId) {
            for ($i = 0; $i < 4; $i++) {
                $images[] = [
                    'kost_id' => $kostId,
                    'image_path' => ImageUrlGenerator::kostImage($this->imageCounter++),
                    'is_thumbnail' => $i === 0, // First image is thumbnail
                    'sort_order' => $i,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Chunk to prevent query size limits
        foreach (array_chunk($images, $this->chunkSize) as $chunk) {
            DB::table('kost_images')->insert($chunk);
        }
    }

    /**
     * Bulk insert document requirements for multiple kosts.
     *
     * Creates 3 standard documents per kost (KTP, selfie, payment proof)
     * using raw SQL for performance.
     *
     * @param  array<int, int>  $kostIds  Array of kost IDs
     */
    protected function bulkInsertDocumentRequirements(array $kostIds): void
    {
        $documents = [];
        $now = now();

        $documentTypes = [
            ['type' => 'ktp', 'required' => true, 'reason' => 'Verifikasi identitas penyewa'],
            ['type' => 'selfie_with_ktp', 'required' => true, 'reason' => 'Konfirmasi kesesuaian identitas'],
            ['type' => 'student_card', 'required' => false, 'reason' => 'Bukti status mahasiswa/pelajar (opsional)'],
        ];

        foreach ($kostIds as $kostId) {
            foreach ($documentTypes as $doc) {
                $documents[] = [
                    'kost_id' => $kostId,
                    'document_type' => $doc['type'],
                    'is_required' => $doc['required'],
                    'reason' => $doc['reason'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Chunk to prevent query size limits
        foreach (array_chunk($documents, $this->chunkSize) as $chunk) {
            DB::table('kost_document_requirements')->insert($chunk);
        }
    }

    /**
     * Attach categories to kosts based on characteristics.
     *
     * Each kost gets 1-3 categories:
     * - 1 gender category (Putra/Putri/Campur) - always
     * - 0-1 price category (Budget/Premium) - based on room prices (only if hasRoomTypes)
     * - 0-1 lifestyle category (Syariah/Mahasiswa/Karyawan) - 50% chance
     *
     * Uses bulk insert for performance.
     *
     * @param  array<int, int>  $kostIds  Array of kost IDs
     * @param  bool  $hasRoomTypes  Whether kost has room types (for price-based categories)
     */
    protected function attachCategoriesToKosts(array $kostIds, bool $hasRoomTypes): void
    {
        $categoryKostRecords = [];

        foreach ($kostIds as $kostId) {
            // 1. Gender category (always 1)
            $genderCategory = $this->categories->whereIn('slug', ['putra', 'putri', 'campur'])->random();
            $categoryKostRecords[] = [
                'category_id' => $genderCategory->id,
                'kost_id' => $kostId,
            ];

            // 2. Price category (optional, only for kosts with room types)
            if ($hasRoomTypes) {
                $kost = Kost::with('roomTypes.priceSchemes')->find($kostId);

                if ($kost && $kost->roomTypes->isNotEmpty()) {
                    $minPrice = PHP_INT_MAX;
                    foreach ($kost->roomTypes as $roomType) {
                        foreach ($roomType->priceSchemes as $priceScheme) {
                            if ($priceScheme->price < $minPrice) {
                                $minPrice = $priceScheme->price;
                            }
                        }
                    }

                    // Budget: < 1.5 juta/bulan
                    if ($minPrice < 1500000) {
                        $budgetCategory = $this->categories->get('budget');
                        if ($budgetCategory) {
                            $categoryKostRecords[] = [
                                'category_id' => $budgetCategory->id,
                                'kost_id' => $kostId,
                            ];
                        }
                    }

                    // Premium: > 3 juta/bulan
                    if ($minPrice > 3000000) {
                        $premiumCategory = $this->categories->get('premium');
                        if ($premiumCategory) {
                            $categoryKostRecords[] = [
                                'category_id' => $premiumCategory->id,
                                'kost_id' => $kostId,
                            ];
                        }
                    }
                }
            }

            // 3. Lifestyle category (optional, 50% chance)
            if (fake()->boolean(50)) {
                $lifestyleCategory = $this->categories->whereIn('slug', ['syariah', 'mahasiswa', 'karyawan'])->random();
                $categoryKostRecords[] = [
                    'category_id' => $lifestyleCategory->id,
                    'kost_id' => $kostId,
                ];
            }
        }

        // Bulk insert all category attachments
        if (! empty($categoryKostRecords)) {
            foreach (array_chunk($categoryKostRecords, $this->chunkSize) as $chunk) {
                DB::table('category_kost')->insert($chunk);
            }
        }
    }

    /**
     * Create room types with pricing and rooms for kosts.
     *
     * Creates 2 room types per kost, each with:
     * - 3 price schemes (daily, weekly, monthly)
     * - 2 room type images
     * - 5 individual rooms
     *
     * Uses Eloquent for room types (has slug logic) but bulk inserts for
     * child records (images, price schemes, rooms).
     *
     * @param  array<int, int>  $kostIds  Array of kost IDs
     */
    protected function createRoomTypesForKosts(array $kostIds): void
    {
        $roomTypeImages = [];
        $priceSchemes = [];
        $rooms = [];
        $now = now();

        $roomTypeCounter = 1;

        foreach ($kostIds as $kostId) {
            // Create 2 room types per kost
            for ($rtIndex = 0; $rtIndex < 2; $rtIndex++) {
                // Generate unique name without using Faker unique() to avoid overflow
                $typeName = fake()->randomElement(['Single Bed', 'Double Bed', 'Suite', 'Standard', 'Deluxe', 'VIP']);
                $uniqueName = $typeName.' '.str_pad((string) $roomTypeCounter++, 4, '0', STR_PAD_LEFT);

                // Create room type manually to avoid factory's unique() overflow
                $roomType = RoomType::create([
                    'kost_id' => $kostId,
                    'name' => $uniqueName,
                    'slug' => Str::slug($uniqueName),
                    'description' => fake()->paragraph(3),
                    'room_size' => fake()->randomElement(['3x3 m', '3x4 m', '4x4 m', '4x5 m', '5x5 m']),
                    'max_occupants' => fake()->numberBetween(1, 4),
                    'security_deposit' => fake()->randomElement([500000, 750000, 1000000, 1500000, 2000000]),
                    'facilities' => fake()->randomElements([
                        'AC', 'Kasur', 'Lemari', 'Meja Belajar', 'Kursi',
                        'Kipas Angin', 'Jendela', 'Kamar Mandi Dalam', 'Wastafel', 'Cermin',
                    ], fake()->numberBetween(2, 5)),
                    'rules' => fake()->randomElements([
                        'Dilarang merokok', 'Dilarang membawa hewan', 'Tamu lawan jenis dilarang',
                        'Jam malam 22:00', 'Jaga kebersihan',
                    ], fake()->numberBetween(2, 4)),
                ]);

                // 2 room type images
                for ($i = 0; $i < 2; $i++) {
                    $roomTypeImages[] = [
                        'room_type_id' => $roomType->id,
                        'image_path' => ImageUrlGenerator::roomImage($this->imageCounter++),
                        'is_thumbnail' => $i === 0,
                        'sort_order' => $i,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // 3 price schemes (varied pricing)
                $basePrice = fake()->randomElement([800000, 1000000, 1200000, 1500000, 1800000, 2000000]);

                $priceSchemes[] = [
                    'room_type_id' => $roomType->id,
                    'name' => 'Harian',
                    'description' => 'Sewa per hari',
                    'price' => $basePrice / 30,
                    'duration_value' => 1,
                    'duration_unit' => 'day',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $priceSchemes[] = [
                    'room_type_id' => $roomType->id,
                    'name' => 'Mingguan',
                    'description' => 'Sewa per minggu',
                    'price' => ($basePrice / 30) * 7 * 0.9, // 10% discount
                    'duration_value' => 1,
                    'duration_unit' => 'week',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $priceSchemes[] = [
                    'room_type_id' => $roomType->id,
                    'name' => 'Bulanan',
                    'description' => 'Sewa per bulan',
                    'price' => $basePrice,
                    'duration_value' => 1,
                    'duration_unit' => 'month',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // 5 rooms per room type
                for ($roomIndex = 0; $roomIndex < 5; $roomIndex++) {
                    $rooms[] = [
                        'kost_id' => $kostId,
                        'room_type_id' => $roomType->id,
                        'code' => 'R-'.str_pad((string) $this->roomCodeCounter++, 3, '0', STR_PAD_LEFT),
                        'status' => fake()->randomElement(['available', 'available', 'available', 'unavailable']), // 75% available
                        'internal_notes' => fake()->optional(0.2)->sentence(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Bulk insert all child records
        foreach (array_chunk($roomTypeImages, $this->chunkSize) as $chunk) {
            DB::table('room_type_images')->insert($chunk);
        }

        foreach (array_chunk($priceSchemes, $this->chunkSize) as $chunk) {
            DB::table('price_schemes')->insert($chunk);
        }

        foreach (array_chunk($rooms, $this->chunkSize) as $chunk) {
            DB::table('rooms')->insert($chunk);
        }
    }

    /**
     * Calculate city distribution for given count.
     *
     * Distributes kosts across cities according to percentage in config.
     * Ensures total equals input count (handles rounding).
     *
     * @param  int  $totalCount  Total kosts to distribute
     * @return array<string, int> City => count mapping
     */
    protected function calculateCityDistribution(int $totalCount): array
    {
        $distribution = [];
        $allocated = 0;

        foreach ($this->cities as $city => $percentage) {
            $count = (int) round($totalCount * ($percentage / 100));
            $distribution[$city] = $count;
            $allocated += $count;
        }

        // Adjust for rounding errors (add/subtract from largest city)
        $diff = $totalCount - $allocated;
        if ($diff !== 0) {
            $largestCity = array_key_first($distribution);
            $distribution[$largestCity] += $diff;
        }

        return $distribution;
    }

    /**
     * Determine city from kost slug pattern.
     *
     * Extracts city from slug suffix (e.g., 'kost-mawar-bandung' => 'Bandung').
     * Falls back to random city from distribution if pattern not found.
     *
     * @param  string  $slug  Kost slug
     * @return string City name
     */
    protected function determineCityFromSlug(string $slug): string
    {
        // Check slug suffix for city names
        foreach (array_keys($this->cities) as $city) {
            if (str_contains(strtolower($slug), strtolower($city))) {
                return $city;
            }
        }

        // Fallback: random city from distribution
        return fake()->randomElement(array_keys($this->cities));
    }

    /**
     * Get city-specific data for address generation.
     *
     * Returns district, province, and coordinates appropriate for city.
     *
     * @param  string  $city  City name
     * @return array{district: string, province: string, latitude: float, longitude: float}
     */
    protected function getCityData(string $city): array
    {
        return match ($city) {
            'Bandung' => [
                'district' => fake()->randomElement(['Cibeunying', 'Coblong', 'Bandung Wetan', 'Sumur Bandung', 'Dago']),
                'province' => 'Jawa Barat',
                'latitude' => fake()->latitude(-6.95, -6.85),
                'longitude' => fake()->longitude(107.55, 107.65),
            ],
            'Jakarta' => [
                'district' => fake()->randomElement(['Menteng', 'Gambir', 'Tanah Abang', 'Cikini', 'Kuningan']),
                'province' => 'DKI Jakarta',
                'latitude' => fake()->latitude(-6.25, -6.15),
                'longitude' => fake()->longitude(106.80, 106.90),
            ],
            'Yogyakarta' => [
                'district' => fake()->randomElement(['Gondokusuman', 'Umbulharjo', 'Mergangsan', 'Jetis', 'Ngampilan']),
                'province' => 'DI Yogyakarta',
                'latitude' => fake()->latitude(-7.82, -7.75),
                'longitude' => fake()->longitude(110.35, 110.40),
            ],
            'Surabaya' => [
                'district' => fake()->randomElement(['Gubeng', 'Wonokromo', 'Tegalsari', 'Genteng', 'Sawahan']),
                'province' => 'Jawa Timur',
                'latitude' => fake()->latitude(-7.30, -7.25),
                'longitude' => fake()->longitude(112.70, 112.75),
            ],
            default => [
                'district' => fake()->randomElement(['Cibeunying', 'Coblong', 'Bandung Wetan', 'Sumur Bandung']),
                'province' => 'Jawa Barat',
                'latitude' => fake()->latitude(-6.95, -6.85),
                'longitude' => fake()->longitude(107.55, 107.65),
            ],
        };
    }

    /**
     * Get postal code prefix for city.
     *
     * Returns realistic postal code for Indonesian cities.
     *
     * @param  string  $city  City name
     * @return string 5-digit postal code
     */
    protected function getPostalCodeForCity(string $city): string
    {
        return match ($city) {
            'Bandung' => fake()->numerify('40###'),
            'Jakarta' => fake()->numerify('10###'),
            'Yogyakarta' => fake()->numerify('55###'),
            'Surabaya' => fake()->numerify('60###'),
            default => fake()->numerify('40###'),
        };
    }
}
