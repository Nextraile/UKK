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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->unique()->constrained('rentals')->onDelete('cascade');

            // Payment info (snapshot from kost)
            $table->string('qris_image_path');
            $table->decimal('amount', 12, 2); // Copy from rental.grand_total

            // Proof upload
            $table->string('proof_of_payment_path')->nullable();

            // Status & verification
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Timeline
            $table->timestamp('expired_at'); // created_at + 48 hours
            $table->timestamp('paid_at')->nullable(); // Set when status = success

            $table->timestamps();

            $table->index('status');
            $table->index('verified_by');
            $table->index('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
