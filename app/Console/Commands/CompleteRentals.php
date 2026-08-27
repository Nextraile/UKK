<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Rental\Mail\RentalCompletedMail;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CompleteRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentals:complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-complete rentals on or after their end_date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for rentals to complete...');

        $rentalsToComplete = Rental::where('status', 'active')
            ->whereDate('end_date', '<=', now()->toDateString())
            ->get();

        if ($rentalsToComplete->isEmpty()) {
            $this->info('No rentals to complete today.');

            return self::SUCCESS;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($rentalsToComplete as $rental) {
            try {
                DB::transaction(function () use ($rental) {
                    // Update rental status
                    $rental->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    // Record status history
                    RentalStatusHistory::create([
                        'rental_id' => $rental->id,
                        'status' => 'completed',
                        'changed_by' => 1, // System user
                        'internal_notes' => 'Auto-completed on end date',
                    ]);

                    // Queue email notification
                    Mail::to($rental->user->email)->queue(new RentalCompletedMail($rental));
                });

                $this->info("Completed rental #{$rental->id}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to complete rental #{$rental->id}: {$e->getMessage()}");
                Log::error("Failed to complete rental #{$rental->id}", [
                    'rental_id' => $rental->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errorCount++;
            }
        }

        $this->info("Processed {$rentalsToComplete->count()} rentals: {$successCount} completed, {$errorCount} errors.");

        return self::SUCCESS;
    }
}
