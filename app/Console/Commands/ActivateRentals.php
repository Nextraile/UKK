<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Rental\Mail\RentalActivatedMail;
use App\Domain\Rental\Models\Rental;
use App\Domain\Rental\Models\RentalStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ActivateRentals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rentals:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-activate rentals on their start_date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for rentals to activate...');

        $rentalsToActivate = Rental::where('status', 'confirmed')
            ->whereDate('start_date', '=', now()->toDateString())
            ->get();

        if ($rentalsToActivate->isEmpty()) {
            $this->info('No rentals to activate today.');

            return self::SUCCESS;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($rentalsToActivate as $rental) {
            try {
                DB::transaction(function () use ($rental) {
                    // Update rental status
                    $rental->update([
                        'status' => 'active',
                        'activated_at' => now(),
                    ]);

                    // Record status history
                    RentalStatusHistory::create([
                        'rental_id' => $rental->id,
                        'status' => 'active',
                        'changed_by' => 1, // System user
                        'internal_notes' => 'Auto-activated on start date',
                    ]);

                    // Queue email notification
                    Mail::to($rental->user->email)->queue(new RentalActivatedMail($rental));
                });

                $this->info("Activated rental #{$rental->id}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to activate rental #{$rental->id}: {$e->getMessage()}");
                Log::error("Failed to activate rental #{$rental->id}", [
                    'rental_id' => $rental->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errorCount++;
            }
        }

        $this->info("Processed {$rentalsToActivate->count()} rentals: {$successCount} activated, {$errorCount} errors.");

        return self::SUCCESS;
    }
}
