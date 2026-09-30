<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Rental\Models\Rental;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    /**
     * Seed review data for completed rentals using bulk inserts for performance.
     *
     * Business logic:
     * - Target: ~90 reviews from 100 completed rentals
     * - 90% of completed rentals get one review (configurable via reviews_percentage)
     * - One review per rental (rental_id unique constraint)
     * - Ratings: 3-5 stars (realistic distribution favoring higher ratings)
     * - Comments: Indonesian language, rating-appropriate content
     * - Created dates: 1-30 days after rental completion date for data integrity
     * - Images: null (not implemented in seed data)
     *
     * Performance optimization:
     * - Eager load relationships to avoid N+1 queries
     * - Prepare all review data in memory first
     * - Use DB::table()->insert() for bulk inserts (100 records per chunk)
     * - Target execution time: <5 seconds for 90 reviews
     *
     * Why bulk inserts over Eloquent:
     * - 10-20x faster for large datasets (no model events, single query per chunk)
     * - Lower memory footprint (no model instantiation)
     * - Acceptable tradeoff: lose Eloquent events but gain massive performance
     */
    public function run(): void
    {
        $this->command->info('⭐ Seeding reviews for completed rentals...');
        $this->command->newLine();

        // Get completed rentals with eager loaded relationships to avoid N+1
        $completedRentals = Rental::where('status', 'completed')
            ->with(['room.kost', 'user'])
            ->get();

        if ($completedRentals->count() < 1) {
            $this->command->warn('⚠️  No completed rentals found. Run RentalSeeder first.');

            return;
        }

        $this->command->info("📊 Found {$completedRentals->count()} completed rentals");
        $this->command->newLine();

        // Prepare all review data in memory first (faster than incremental inserts)
        $reviews = $this->prepareReviews($completedRentals);

        if (empty($reviews)) {
            $this->command->warn('⚠️  No reviews generated (check percentage configuration)');

            return;
        }

        // Bulk insert reviews in chunks for optimal performance
        $this->bulkInsertReviews($reviews);

        $this->command->newLine();
        $this->command->info('✅ Reviews seeded: '.count($reviews)." total from {$completedRentals->count()} completed rentals");
    }

    /**
     * Prepare all review data in memory for bulk insert.
     *
     * Why prepare first instead of insert-as-we-go:
     * - Allows accurate progress tracking
     * - Enables shuffling for random distribution
     * - Facilitates batch processing optimization
     *
     * Business constraint:
     * - One review per rental (rental_id unique constraint)
     * - Cannot create multiple reviews for same rental
     *
     * @param  Collection<int, Rental>  $completedRentals
     * @return array<int, array<string, mixed>>
     */
    protected function prepareReviews($completedRentals): array
    {
        $reviews = [];
        $reviewPercentage = config('seeding.counts.reviews_percentage', 90);

        $progressBar = $this->command->getOutput()->createProgressBar($completedRentals->count());
        $progressBar->setFormat('Preparing reviews: %current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();

        foreach ($completedRentals as $rental) {
            // 90% of completed rentals get one review (realistic: not all tenants leave reviews)
            // Note: Cannot create multiple reviews per rental (unique constraint on rental_id)
            if (fake()->boolean($reviewPercentage)) {
                $reviews[] = $this->prepareReviewData($rental);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();

        return $reviews;
    }

    /**
     * Prepare single review data array for bulk insert.
     *
     * Why return array instead of creating model:
     * - Bulk insert via DB::table() requires arrays, not models
     * - Avoids overhead of model instantiation for seed data
     *
     * @return array<string, mixed>
     */
    protected function prepareReviewData(Rental $rental): array
    {
        // Ratings: 3-5 stars with realistic distribution (mostly 4-5 stars)
        // Distribution: 3★ (20%), 4★ (40%), 5★ (40%)
        $kostRating = fake()->randomElement([3, 4, 4, 5, 5]);
        $roomRating = fake()->randomElement([3, 4, 4, 5, 5]);

        // Generate Indonesian comments based on ratings
        $kostComment = $this->generateKostComment($kostRating);
        $roomComment = $this->generateRoomComment($roomRating);

        // Review created 1-30 days after rental completion (data integrity)
        $daysAfterCompletion = fake()->numberBetween(1, 30);

        $createdAt = Carbon::parse($rental->end_date)
            ->addDays($daysAfterCompletion)
            ->addHours(fake()->numberBetween(0, 23))
            ->addMinutes(fake()->numberBetween(0, 59));

        $updatedAt = $createdAt; // No edits in seed data

        return [
            'rental_id' => $rental->id,
            'kost_rating' => $kostRating,
            'kost_comment' => $kostComment,
            'room_rating' => $roomRating,
            'room_comment' => $roomComment,
            'images' => null, // No images in seed data
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * Bulk insert reviews in chunks for optimal performance.
     *
     * Why chunking:
     * - Prevents memory exhaustion on large datasets
     * - Avoids max_allowed_packet errors in MySQL
     * - Provides progress feedback for UX
     *
     * @param  array<int, array<string, mixed>>  $reviews
     */
    protected function bulkInsertReviews(array $reviews): void
    {
        $chunkSize = config('seeding.performance.chunk_size', 100);
        $chunks = array_chunk($reviews, $chunkSize);

        $progressBar = $this->command->getOutput()->createProgressBar(count($chunks));
        $progressBar->setFormat('Inserting reviews: %current%/%max% chunks [%bar%] %percent:3s%%');
        $progressBar->start();

        foreach ($chunks as $chunk) {
            DB::table('reviews')->insert($chunk);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    /**
     * Generate realistic kost comment based on rating.
     */
    protected function generateKostComment(int $rating): string
    {
        $comments = [
            5 => [
                'Kost sangat bagus! Fasilitas lengkap, keamanan terjamin, dan pengelola sangat ramah. Sangat recommended!',
                'Puas banget tinggal di kost ini. Lokasinya strategis, dekat dengan kampus dan pusat kota. Fasilitas juga oke semua.',
                'Kost terbaik yang pernah saya tempati. Bersih, nyaman, dan aman. Pengelola sangat responsif dan helpful.',
                'Sangat merekomendasikan kost ini. Lingkungan tenang, tetangga ramah, dan fasilitasnya sangat memadai.',
                'Kost idaman! WiFi kenceng, parkir luas, kamar mandi bersih. Harga sesuai dengan kualitas yang didapat.',
            ],
            4 => [
                'Kost bagus dan nyaman. Lokasinya strategis dan fasilitasnya lengkap. Sedikit kekurangannya cuma di parkir yang kadang penuh.',
                'Overall bagus. Kamar bersih, lingkungan aman. Cuma kadang WiFi agak lemot kalau weekend.',
                'Kost yang cukup recommended. Harga reasonable, fasilitas oke. Pengelola juga ramah dan responsif.',
                'Tempat tinggal yang nyaman. Lokasi strategis dekat kemana-mana. Minor issue di kebersihan area parkir saja.',
            ],
            3 => [
                'Kost standar dengan harga yang cukup terjangkau. Beberapa fasilitas perlu maintenance lebih baik.',
                'Cukup oke untuk harga segini. Lokasi strategis tapi WiFi kadang suka lelet. Kebersihan bisa lebih ditingkatkan.',
                'Lumayan lah untuk kost di harga ini. Ada beberapa yang perlu diperbaiki tapi overall masih layak.',
                'Biasa aja sih. Sesuai harga. Kalau mau yang lebih bagus mungkin perlu budget lebih.',
            ],
        ];

        return $comments[$rating][array_rand($comments[$rating])];
    }

    /**
     * Generate realistic room comment based on rating.
     */
    protected function generateRoomComment(int $rating): string
    {
        $comments = [
            5 => [
                'Kamar sangat nyaman! Luas, bersih, dan perabotan lengkap. AC dingin, kasur empuk. Perfect!',
                'Kamar bersih dan terawat. Ukuran pas, tidak sempit. Kamar mandi dalam juga bersih dan air lancar.',
                'Kamarnya oke banget! Pencahayaan bagus, ventilasi baik, tidak pengap. Fasilitas kamar juga lengkap.',
                'Sangat puas dengan kamarnya. Desain interior bagus, tempat penyimpanan cukup banyak. Recommended!',
            ],
            4 => [
                'Kamar bagus dan cukup luas. AC dingin, kasur nyaman. Cuma lemari pakaian agak kecil.',
                'Kamar bersih dan nyaman. Ukuran standard tapi cukup untuk 1 orang. Kamar mandi kadang air panasnya lama.',
                'Overall kamarnya bagus. Perabotan lumayan lengkap. Hanya saja cermin agak kecil.',
                'Kamar nyaman, tidak pengap. AC bekerja dengan baik. Mungkin bisa ditambah colokan listrik.',
            ],
            3 => [
                'Kamar standar sesuai harga. Ukuran cukup tapi perabotan agak usang. Perlu sedikit perbaikan.',
                'Lumayan lah kamarnya. Bersih sih tapi agak sempit. AC kadang kurang dingin kalau siang.',
                'Biasa aja. Sesuai ekspektasi di harga segini. Cat tembok agak kusam, perlu repaint.',
                'Kamar cukup layak huni. Beberapa fasilitas perlu diganti yang baru. Overall masih oke.',
            ],
        ];

        return $comments[$rating][array_rand($comments[$rating])];
    }
}
