<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Rental\Models\Rental;
use App\Domain\Review\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Seed review data for completed rentals.
     *
     * Creates 8+ reviews for completed rentals with:
     * - Variety of ratings (3-5 stars for kost, 3-5 stars for room)
     * - Realistic Indonesian comments
     * - No images (seed data limitation)
     */
    public function run(): void
    {
        $this->command->info('⭐ Seeding reviews for completed rentals...');
        $this->command->newLine();

        // Get completed rentals (only completed rentals can be reviewed)
        $completedRentals = Rental::where('status', 'completed')
            ->with(['room.kost', 'user'])
            ->get();

        if ($completedRentals->count() < 1) {
            $this->command->warn('⚠️  No completed rentals found. Run RentalSeeder first.');

            return;
        }

        $reviewCount = 0;

        // Create reviews for completed rentals (not all rentals get reviews)
        foreach ($completedRentals as $rental) {
            // 80% chance of getting a review (realistic: not all tenants leave reviews)
            if (rand(1, 100) <= 80) {
                $this->createReview($rental, ++$reviewCount);
            }
        }

        $this->command->newLine();
        $this->command->info("✅ Reviews seeded: {$reviewCount} total for completed rentals");
    }

    /**
     * Create a single review for a completed rental.
     */
    protected function createReview(Rental $rental, int $reviewNumber): void
    {
        // Random ratings (3-5 stars for realistic distribution)
        $kostRating = rand(3, 5);
        $roomRating = rand(3, 5);

        // Generate realistic Indonesian comments based on ratings
        $kostComment = $this->generateKostComment($kostRating);
        $roomComment = $this->generateRoomComment($roomRating);

        Review::create([
            'rental_id' => $rental->id,
            'kost_rating' => $kostRating,
            'kost_comment' => $kostComment,
            'room_rating' => $roomRating,
            'room_comment' => $roomComment,
            'images' => null, // No images for seed data
        ]);

        $tenant = $rental->user;
        $kost = $rental->room->kost;
        $this->command->info("Created review {$reviewNumber}: {$tenant->first_name} → {$kost->name} (Kost: {$kostRating}⭐, Room: {$roomRating}⭐)");
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
