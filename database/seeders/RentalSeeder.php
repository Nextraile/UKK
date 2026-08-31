<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Identity\Models\User;
use App\Domain\Kost\Models\Kost;
use App\Domain\Kost\Models\Room;
use App\Domain\Rental\Models\Payment;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalDocument;
use App\Domain\Rental\Models\RentalStatusHistory;
use Illuminate\Database\Seeder;

class RentalSeeder extends Seeder
{
    /**
     * Seed rental data with comprehensive status distribution.
     *
     * Creates 20 rentals across all statuses:
     * - 2 pending (awaiting payment)
     * - 3 paid (payment uploaded, awaiting verification)
     * - 3 confirmed (payment verified, awaiting document upload)
     * - 6 active (documents verified, rental ongoing)
     * - 4 completed (rental period ended)
     * - 2 cancelled (payment expired or tenant cancelled)
     *
     * Each rental includes:
     * - Payment record with appropriate status
     * - Status history entries
     * - Documents (KTP, selfie) for confirmed/active/completed rentals
     */
    public function run(): void
    {
        $this->command->info('🏠 Seeding rentals with full lifecycle...');
        $this->command->newLine();

        // Get verified tenants (exclude unverified and deleted)
        $tenants = User::where('role', 'user')
            ->whereNotNull('email_verified_at')
            ->whereNull('deleted_at')
            ->get();

        if ($tenants->count() < 10) {
            $this->command->error('❌ Not enough verified tenants. Run UserSeeder first.');

            return;
        }

        // Get active kosts with rooms
        $activeKosts = Kost::where('status', 'active')
            ->with(['rooms.roomType.priceSchemes'])
            ->get();

        if ($activeKosts->count() < 5) {
            $this->command->error('❌ Not enough active kosts. Run KostSeeder first.');

            return;
        }

        $rentalCount = 0;

        // Status distribution configuration
        $statusConfig = [
            // 2 Pending rentals (payment not uploaded yet)
            ['status' => 'pending', 'count' => 2],

            // 3 Paid rentals (payment uploaded, awaiting verification)
            ['status' => 'paid', 'count' => 3],

            // 3 Confirmed rentals (payment verified, awaiting documents)
            ['status' => 'confirmed', 'count' => 3],

            // 6 Active rentals (documents verified, ongoing)
            ['status' => 'active', 'count' => 6],

            // 4 Completed rentals (rental period ended)
            ['status' => 'completed', 'count' => 4],

            // 2 Cancelled rentals
            ['status' => 'cancelled', 'count' => 2],
        ];

        foreach ($statusConfig as $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $this->createRental($config['status'], $tenants, $activeKosts, ++$rentalCount);
            }
        }

        $this->command->newLine();
        $this->command->info('✅ Rentals seeded: 20 total');
        $this->command->info('   - 2 Pending');
        $this->command->info('   - 3 Paid');
        $this->command->info('   - 3 Confirmed');
        $this->command->info('   - 6 Active');
        $this->command->info('   - 4 Completed');
        $this->command->info('   - 2 Cancelled');
    }

    /**
     * Create a single rental with appropriate status and related data.
     */
    protected function createRental(string $status, $tenants, $activeKosts, int $rentalNumber): void
    {
        // Pick random tenant and kost
        $tenant = $tenants->random();
        $kost = $activeKosts->random();

        // Get random available room
        $room = $kost->rooms()
            ->where('status', 'available')
            ->with('roomType.priceSchemes')
            ->inRandomOrder()
            ->first();

        if (! $room || ! $room->roomType) {
            $this->command->warn("⚠️  Skipping rental {$rentalNumber}: No available rooms for kost {$kost->name}");

            return;
        }

        // Get random price scheme
        $priceScheme = $room->roomType->priceSchemes()
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (! $priceScheme) {
            $this->command->warn("⚠️  Skipping rental {$rentalNumber}: No active price schemes");

            return;
        }

        // Calculate dates based on status (ADR-016: min start_date = today+4 days)
        $startDate = match ($status) {
            'pending', 'paid', 'confirmed' => now()->addDays(4),
            'active' => now()->subDays(rand(10, 80)),
            'completed' => now()->subDays(rand(100, 150)),
            'cancelled' => now()->addDays(4),
        };

        $endDate = $startDate->copy()->addMonths($priceScheme->duration_value);

        // Calculate grand total (room price + security deposit)
        $grandTotal = $priceScheme->price + $room->roomType->security_deposit;

        // Create rental
        $rental = Rental::create([
            'room_id' => $room->id,
            'user_id' => $tenant->id,
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
        ]);

        // Create payment record
        $this->createPayment($rental, $status);

        // Create status history
        $this->createStatusHistory($rental, $status);

        // Create documents for confirmed/active/completed rentals
        if (in_array($status, ['confirmed', 'active', 'completed'])) {
            $this->createDocuments($rental);
        }

        $this->command->info("Created rental {$rentalNumber}/20: {$tenant->first_name} → {$kost->name} ({$status})");
    }

    /**
     * Create payment record with appropriate status.
     */
    protected function createPayment(Rental $rental, string $rentalStatus): void
    {
        // Payment status mapping (only 3 valid values: pending, success, failed)
        $paymentStatus = match ($rentalStatus) {
            'pending' => 'pending',
            'paid', 'confirmed', 'active', 'completed' => 'success',
            'cancelled' => 'failed',
        };

        // Load kost relationship
        $rental->load('room.roomType.kost');
        $kost = $rental->room->roomType->kost;

        Payment::create([
            'rental_id' => $rental->id,
            'qris_image_path' => $kost->qris_image_path ?? 'kost-images/qris-seed-placeholder.jpg',
            'amount' => $rental->grand_total,
            'proof_of_payment_path' => in_array($rentalStatus, ['paid', 'confirmed', 'active', 'completed'])
                ? 'payment-proofs/seed-payment-proof-'.$rental->id.'.jpg'
                : null,
            'status' => $paymentStatus,
            'verified_by' => in_array($rentalStatus, ['confirmed', 'active', 'completed'])
                ? $kost->user_id
                : null,
            'verified_at' => in_array($rentalStatus, ['confirmed', 'active', 'completed'])
                ? now()->subDays(rand(2, 5))
                : null,
            'expired_at' => $rental->created_at->copy()->addHours(48),
            'paid_at' => in_array($rentalStatus, ['paid', 'confirmed', 'active', 'completed'])
                ? now()->subDays(rand(3, 6))
                : null,
        ]);
    }

    /**
     * Create rental status history entries.
     */
    protected function createStatusHistory(Rental $rental, string $finalStatus): void
    {
        // Get system user for automated status changes
        $systemUser = User::find(1); // System user from SystemUserSeeder

        // All rentals start as pending
        RentalStatusHistory::create([
            'rental_id' => $rental->id,
            'status' => 'pending',
            'changed_by' => $rental->user_id, // Tenant created the rental
            'internal_notes' => 'Rental dibuat oleh tenant',
            'created_at' => $rental->created_at,
        ]);
        if ($finalStatus === 'cancelled') {
            RentalStatusHistory::create([
                'rental_id' => $rental->id,
                'status' => 'cancelled',
                'changed_by' => $rental->user_id, // Tenant cancelled
                'internal_notes' => $rental->cancelled_reason ?? 'Rental dibatalkan oleh tenant',
                'created_at' => $rental->cancelled_at,
            ]);

            return;
        }

        if (in_array($finalStatus, ['paid', 'confirmed', 'active', 'completed'])) {
            // Admin verified payment - load kost owner
            $rental->load('room.roomType.kost.owner');
            $adminUser = $rental->room->roomType->kost->owner;

            RentalStatusHistory::create([
                'rental_id' => $rental->id,
                'status' => 'paid',
                'changed_by' => $adminUser->id,
                'internal_notes' => 'Pembayaran berhasil diverifikasi oleh admin',
                'created_at' => now()->subDays(rand(4, 7)),
            ]);
        }

        if (in_array($finalStatus, ['confirmed', 'active', 'completed'])) {
            // Admin confirmed documents
            $rental->load('room.roomType.kost.owner');
            $adminUser = $rental->room->roomType->kost->owner;

            RentalStatusHistory::create([
                'rental_id' => $rental->id,
                'status' => 'confirmed',
                'changed_by' => $adminUser->id,
                'internal_notes' => 'Dokumen telah diverifikasi, rental dikonfirmasi',
                'created_at' => $rental->confirmed_at,
            ]);
        }

        if (in_array($finalStatus, ['active', 'completed'])) {
            // System automatically activates rental on start_date
            RentalStatusHistory::create([
                'rental_id' => $rental->id,
                'status' => 'active',
                'changed_by' => $systemUser->id, // System user
                'internal_notes' => 'Rental otomatis diaktifkan pada tanggal mulai',
                'created_at' => $rental->activated_at,
            ]);
        }

        if ($finalStatus === 'completed') {
            // System automatically completes rental on end_date
            RentalStatusHistory::create([
                'rental_id' => $rental->id,
                'status' => 'completed',
                'changed_by' => $systemUser->id, // System user
                'internal_notes' => 'Rental otomatis diselesaikan pada tanggal akhir',
                'created_at' => $rental->completed_at,
            ]);
        }
    }

    /**
     * Create rental documents (KTP + Selfie with KTP).
     */
    protected function createDocuments(Rental $rental): void
    {
        // Load full relationships to get admin user (kost owner)
        $rental->load('room.roomType.kost.owner');
        $admin = $rental->room->roomType->kost->owner;

        if (! $admin) {
            $this->command->warn("⚠️  Admin not found for rental {$rental->id}, skipping documents");

            return;
        }

        // Map rental ID to document ID (cycle through 1-20 available docs)
        $docId = (($rental->id - 1) % 20) + 1;

        // KTP document
        RentalDocument::create([
            'rental_id' => $rental->id,
            'document_type' => 'ktp',
            'document_path' => "rental-documents/seed-ktp-{$docId}.jpg",
            'uploaded_at' => now()->subDays(rand(2, 3)),
            'verification_status' => 'approved',
            'verified_by' => $admin->id,
            'verified_at' => now()->subDays(1),
        ]);

        // Selfie with KTP document
        RentalDocument::create([
            'rental_id' => $rental->id,
            'document_type' => 'selfie_with_ktp',
            'document_path' => "rental-documents/seed-selfie-{$docId}.jpg",
            'uploaded_at' => now()->subDays(rand(2, 3)),
            'verification_status' => 'approved',
            'verified_by' => $admin->id,
            'verified_at' => now()->subDays(1),
        ]);
    }
}
