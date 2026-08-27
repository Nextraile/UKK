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
        Schema::table('rental_status_histories', function (Blueprint $table) {
            // Update status enum to include documents_pending
            $table->enum('status', ['pending', 'paid', 'documents_pending', 'confirmed', 'active', 'completed', 'cancelled'])
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_status_histories', function (Blueprint $table) {
            // Revert status enum to original values
            $table->enum('status', ['pending', 'paid', 'confirmed', 'active', 'completed', 'cancelled'])
                ->change();
        });
    }
};
