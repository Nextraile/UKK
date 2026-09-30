<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Payment\Models\Payment;
use App\Domain\Rental\Models\Rental;
use App\Domain\RoomInventory\Models\Room;
use App\Domain\Seeding\ImageUrlGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RentalSeeder extends Seeder
{
    /**
     * Seed rental data with scaled generation for 500 rentals.
     *
     * Distribution (Large preset):
     * - 50 payment_pending (awaiting payment upload)
     * - 75 paid (payment uploaded, awaiting verification)
     * - 75 confirmed (payment verified, awaiting activation)
     * - 150 active (documents verified, rental ongoing)
     * - 100 completed (rental period ended)
     * - 50 cancelled (payment expired or tenant cancelled)
     *
     * Each rental includes:
     * - Payment record with appropriate status
     * - Complete status history (lifecycle transitions)
     * - Documents (KTP, selfie) for confirmed/active/completed rentals
     *
     * Performance: Bulk inserts (100 records/batch), <15s execution time.
     */
    public function run(): void
    {
        $this->command->info('🏠 Seeding 500 rentals with full lifecycle...');
        $this->command->newLine();

        // Get verified tenants (exclude unverified and deleted)
        $tenants = User::where('role', 'user')
            ->whereNotNull('email_verified_at')
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        if (count($tenants) < 10) {
            $this->command->error('❌ Not enough verified tenants. Run UserSeeder first.');

            return;
        }

        // Get available rooms with relationships
        $availableRooms = Room::where('status', 'available')
            ->with(['roomType.priceSchemes', 'roomType.kost.owner'])
            ->get();

        if ($availableRooms->count() < 50) {
            $this->command->error('❌ Not enough available rooms. Run KostSeeder first.');

            return;
        }

        // Get system user for automated transitions
        $systemUser = User::find(1);
        if (! $systemUser) {
            $this->command->error('❌ System user not found. Run SystemUserSeeder first.');

            return;
        }

        // Status distribution configuration (Large preset from config/seeding.php)
        $statusConfig = [
            ['status' => 'payment_pending', 'count' => 50],
            ['status' => 'paid', 'count' => 75],
            ['status' => 'confirmed', 'count' => 75],
            ['status' => 'active', 'count' => 150],
            ['status' => 'completed', 'count' => 100],
            ['status' => 'cancelled', 'count' => 50],
        ];

        $totalRentals = 500;
        $rentalCount = 0;

        foreach ($statusConfig as $config) {
            $status = $config['status'];
            $count = $config['count'];

            $this->command->getOutput()->progressStart($count);
            $this->command->info("Creating {$count} {$status} rentals...");

            $this->createRentalsBatch($status, $count, $tenants, $availableRooms, $systemUser, $rentalCount);

            $this->command->getOutput()->progressFinish();
            $rentalCount += $count;
        }

        $this->command->newLine();
        $this->command->info('✅ Rentals seeded: 500 total');
        $this->command->info('   - 50 Payment Pending');
        $this->command->info('   - 75 Paid');
        $this->command->info('   - 75 Confirmed');
        $this->command->info('   - 150 Active');
        $this->command->info('   - 100 Completed');
        $this->command->info('   - 50 Cancelled');
    }

    /**
     * Create batch of rentals with specified status.
     *
     * Uses bulk inserts for payments, status histories, and documents.
     * Progress bar shows incremental progress per rental created.
     *
     * @param  string  $status  Final rental status
     * @param  int  $count  Number of rentals to create
     * @param  array<int>  $tenantIds  Available tenant IDs
     * @param  Collection  $availableRooms  Available rooms collection
     * @param  User  $systemUser  System user for automated transitions
     * @param  int  $startingCount  Starting rental count for logging
     */
    protected function createRentalsBatch(
        string $status,
        int $count,
        array $tenantIds,
        $availableRooms,
        User $systemUser,
        int $startingCount
    ): void {
        $rentals = [];
        $payments = [];
        $statusHistories = [];
        $documents = [];

        for ($i = 0; $i < $count; $i++) {
            // Pick random tenant and room (distribute evenly across available rooms)
            $tenantId = $tenantIds[array_rand($tenantIds)];
            $room = $availableRooms->random();

            // Get random active price scheme
            $priceScheme = $room->roomType->priceSchemes
                ->where('is_active', true)
                ->random();

            if (! $priceScheme) {
                $this->command->warn("⚠️  Skipping rental: No active price schemes for room {$room->code}");

                continue;
            }

            // Calculate dates based on status (ADR-016: min start_date = today+4 days)
            $startDate = match ($status) {
                'payment_pending', 'paid', 'confirmed' => now()->addDays(4 + rand(0, 10)),
                'active' => now()->subDays(rand(10, 80)),
                'completed' => now()->subDays(rand(100, 150)),
                'cancelled' => now()->addDays(4 + rand(0, 10)),
                default => now()->addDays(4),
            };

            $endDate = $startDate->copy()->addMonths($priceScheme->duration_value);

            // Calculate grand total (room price + security deposit)
            $grandTotal = $priceScheme->price + $room->roomType->security_deposit;

            // Prepare rental data
            $rentalData = [
                'room_id' => $room->id,
                'user_id' => $tenantId,
                'price_scheme_id' => $priceScheme->id,
                'duration_value' => $priceScheme->duration_value,
                'duration_unit' => $priceScheme->duration_unit,
                'room_price' => $priceScheme->price,
                'security_deposit' => $room->roomType->security_deposit,
                'grand_total' => $grandTotal,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'cancelled_reason' => $status === 'cancelled' ? 'Pembatalan oleh penyewa' : null,
                'cancelled_at' => $status === 'cancelled' ? now()->subDays(1) : null,
                'confirmed_at' => in_array($status, ['confirmed', 'active', 'completed']) ? now()->subDays(rand(3, 5)) : null,
                'activated_at' => in_array($status, ['active', 'completed']) ? $startDate : null,
                'completed_at' => $status === 'completed' ? $endDate : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $rentals[] = $rentalData;

            $this->command->getOutput()->progressAdvance();
        }

        // Bulk insert rentals
        DB::table('rentals')->insert($rentals);

        // Get inserted rental IDs (last N rentals)
        $insertedRentals = Rental::orderBy('id', 'desc')
            ->limit(count($rentals))
            ->get()
            ->reverse()
            ->values();

        // Prepare bulk data for payments, status histories, and documents
        foreach ($insertedRentals as $index => $rental) {
            $rentalData = $rentals[$index];
            $room = $availableRooms->firstWhere('id', $rental->room_id);
            $kost = $room->roomType->kost;
            $adminUserId = $kost->user_id;

            // Prepare payment data
            $paymentStatus = match ($status) {
                'payment_pending' => 'pending',
                'paid', 'confirmed', 'active', 'completed' => 'success',
                'cancelled' => 'failed',
                default => 'pending',
            };

            $payments[] = [
                'rental_id' => $rental->id,
                'qris_image_path' => $kost->qris_image_path ?? ImageUrlGenerator::qrisCode(
                    '0000000000000000',
                    'SewaKost Placeholder',
                    'Jakarta',
                    '10000',
                    $rental->id
                ),
                'amount' => $rental->grand_total,
                'proof_of_payment_path' => in_array($status, ['paid', 'confirmed', 'active', 'completed'])
                    ? ImageUrlGenerator::document($rental->id % 1000)
                    : null,
                'status' => $paymentStatus,
                'verified_by' => in_array($status, ['confirmed', 'active', 'completed'])
                    ? $adminUserId
                    : null,
                'verified_at' => in_array($status, ['confirmed', 'active', 'completed'])
                    ? now()->subDays(rand(2, 5))
                    : null,
                'expired_at' => $rental->created_at->copy()->addHours(48),
                'paid_at' => in_array($status, ['paid', 'confirmed', 'active', 'completed'])
                    ? now()->subDays(rand(3, 6))
                    : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Prepare status history data (complete lifecycle)
            $this->prepareStatusHistories($rental, $status, $rentalData, $adminUserId, $systemUser->id, $statusHistories);

            // Prepare document data for confirmed/active/completed rentals
            if (in_array($status, ['confirmed', 'active', 'completed'])) {
                $this->prepareDocuments($rental, $adminUserId, $documents);
            }
        }

        // Bulk insert payments (batch size: 100)
        foreach (array_chunk($payments, 100) as $paymentChunk) {
            DB::table('payments')->insert($paymentChunk);
        }

        // Bulk insert status histories (batch size: 100)
        foreach (array_chunk($statusHistories, 100) as $historyChunk) {
            DB::table('rental_status_histories')->insert($historyChunk);
        }

        // Bulk insert documents (batch size: 100)
        if (! empty($documents)) {
            foreach (array_chunk($documents, 100) as $documentChunk) {
                DB::table('rental_documents')->insert($documentChunk);
            }
        }
    }

    /**
     * Prepare status history entries for a rental.
     *
     * Creates complete lifecycle transitions based on final status.
     * All rentals start as payment_pending, then progress through lifecycle.
     *
     * @param  Rental  $rental  Rental model
     * @param  string  $finalStatus  Final rental status
     * @param  array<string, mixed>  $rentalData  Original rental data array
     * @param  int  $adminUserId  Admin user ID (kost owner)
     * @param  int  $systemUserId  System user ID
     * @param  array<array<string, mixed>>  $statusHistories  Reference to status histories array
     */
    protected function prepareStatusHistories(
        Rental $rental,
        string $finalStatus,
        array $rentalData,
        int $adminUserId,
        int $systemUserId,
        array &$statusHistories
    ): void {
        // All rentals start as payment_pending
        $statusHistories[] = [
            'rental_id' => $rental->id,
            'status' => 'payment_pending',
            'changed_by' => $rental->user_id,
            'internal_notes' => 'Rental dibuat oleh tenant',
            'created_at' => $rental->created_at,
        ];

        if ($finalStatus === 'cancelled') {
            $statusHistories[] = [
                'rental_id' => $rental->id,
                'status' => 'cancelled',
                'changed_by' => $rental->user_id,
                'internal_notes' => $rentalData['cancelled_reason'] ?? 'Rental dibatalkan oleh tenant',
                'created_at' => $rentalData['cancelled_at'],
            ];

            return;
        }

        if (in_array($finalStatus, ['paid', 'confirmed', 'active', 'completed'])) {
            $statusHistories[] = [
                'rental_id' => $rental->id,
                'status' => 'paid',
                'changed_by' => $adminUserId,
                'internal_notes' => 'Pembayaran berhasil diverifikasi oleh admin',
                'created_at' => now()->subDays(rand(4, 7)),
            ];
        }

        if (in_array($finalStatus, ['confirmed', 'active', 'completed'])) {
            $statusHistories[] = [
                'rental_id' => $rental->id,
                'status' => 'confirmed',
                'changed_by' => $adminUserId,
                'internal_notes' => 'Dokumen telah diverifikasi, rental dikonfirmasi',
                'created_at' => $rentalData['confirmed_at'],
            ];
        }

        if (in_array($finalStatus, ['active', 'completed'])) {
            $statusHistories[] = [
                'rental_id' => $rental->id,
                'status' => 'active',
                'changed_by' => $systemUserId,
                'internal_notes' => 'Rental otomatis diaktifkan pada tanggal mulai',
                'created_at' => $rentalData['activated_at'],
            ];
        }

        if ($finalStatus === 'completed') {
            $statusHistories[] = [
                'rental_id' => $rental->id,
                'status' => 'completed',
                'changed_by' => $systemUserId,
                'internal_notes' => 'Rental otomatis diselesaikan pada tanggal akhir',
                'created_at' => $rentalData['completed_at'],
            ];
        }
    }

    /**
     * Prepare rental documents (KTP + Selfie with KTP).
     *
     * Uses ImageUrlGenerator for document URLs with cycling pattern.
     * All documents are pre-approved for confirmed/active/completed rentals.
     *
     * @param  Rental  $rental  Rental model
     * @param  int  $adminUserId  Admin user ID (verifier)
     * @param  array<array<string, mixed>>  $documents  Reference to documents array
     */
    protected function prepareDocuments(Rental $rental, int $adminUserId, array &$documents): void
    {
        // Map rental ID to document ID (cycle through 1-1000 available docs)
        $docId = (($rental->id - 1) % 1000) + 1;

        // KTP document
        $documents[] = [
            'rental_id' => $rental->id,
            'document_type' => 'ktp',
            'document_path' => ImageUrlGenerator::document($docId),
            'uploaded_at' => now()->subDays(rand(2, 3)),
            'verification_status' => 'approved',
            'verified_by' => $adminUserId,
            'verified_at' => now()->subDays(1),
            'rejection_reason' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Selfie with KTP document
        $documents[] = [
            'rental_id' => $rental->id,
            'document_type' => 'selfie_with_ktp',
            'document_path' => ImageUrlGenerator::document($docId + 1000),
            'uploaded_at' => now()->subDays(rand(2, 3)),
            'verification_status' => 'approved',
            'verified_by' => $adminUserId,
            'verified_at' => now()->subDays(1),
            'rejection_reason' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
