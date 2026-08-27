<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('room_id')->constrained('rooms')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('price_scheme_id')->constrained('price_schemes')->onDelete('restrict');

            // Snapshot data (price can change, rental stores snapshot)
            $table->unsignedSmallInteger('duration_value');
            $table->enum('duration_unit', ['day', 'week', 'month']);
            $table->decimal('room_price', 12, 2);
            $table->decimal('security_deposit', 12, 2);
            $table->decimal('grand_total', 12, 2);

            // Timeline
            $table->dateTime('start_date');
            $table->dateTime('end_date');

            // Status & cancellation
            $table->enum('status', ['pending', 'paid', 'confirmed', 'active', 'completed', 'cancelled'])
                ->default('pending');
            $table->text('cancelled_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Indexes for performance (ADR-017 occupancy queries)
            $table->index(['room_id', 'status', 'start_date'], 'idx_room_status_date');
            $table->index('user_id');
            $table->index('status');
            $table->index(['start_date', 'end_date'], 'idx_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
