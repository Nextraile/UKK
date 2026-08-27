<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Rental\Mail\RentalCancelledMail;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CancelOverdueRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentals:cancel-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-cancel rentals in pending status for more than 7 days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for overdue pending rentals...');

        $sevenDaysAgo = now()->subDays(7);
        $overdueRentals = Rental::where('status', 'pending')
            ->where('created_at', '<=', $sevenDaysAgo)
            ->get();

        if ($overdueRentals->isEmpty()) {
            $this->info('No overdue pending rentals found.');

            return self::SUCCESS;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($overdueRentals as $rental) {
            try {
                DB::transaction(function () use ($rental) {
                    // Update rental status
                    $rental->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancelled_reason' => 'Auto-cancelled: Payment not received within 7 days',
                    ]);

                    // Record status history
                    RentalStatusHistory::create([
                        'rental_id' => $rental->id,
                        'status' => 'cancelled',
                        'changed_by' => 1, // System user
                        'internal_notes' => 'Auto-cancelled: Payment not received within 7 days',
                    ]);

                    // Queue email notification
                    Mail::to($rental->user->email)->queue(new RentalCancelledMail($rental));
                });

                $this->info("Cancelled rental #{$rental->id}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to cancel rental #{$rental->id}: {$e->getMessage()}");
                Log::error("Failed to cancel overdue rental #{$rental->id}", [
                    'rental_id' => $rental->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errorCount++;
            }
        }

        $this->info("Processed {$overdueRentals->count()} overdue rentals: {$successCount} cancelled, {$errorCount} errors.");

        return self::SUCCESS;
    }
}
