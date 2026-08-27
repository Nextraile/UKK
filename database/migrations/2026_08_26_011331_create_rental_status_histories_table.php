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
        Schema::create('rental_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade');

            $table->enum('status', ['pending', 'paid', 'confirmed', 'active', 'completed', 'cancelled']);
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->text('internal_notes')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('rental_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_status_histories');
    }
};
