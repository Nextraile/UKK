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
        // Add 'rejected' status to rentals table
        Schema::table('rentals', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'rejected', 'documents_pending', 'confirmed', 'active', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });

        // Add 'rejected' status to rental_status_histories table
        Schema::table('rental_status_histories', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'rejected', 'documents_pending', 'confirmed', 'active', 'completed', 'cancelled'])
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert rentals table to previous status enum
        Schema::table('rentals', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'documents_pending', 'confirmed', 'active', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });

        // Revert rental_status_histories table to previous status enum
        Schema::table('rental_status_histories', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'documents_pending', 'confirmed', 'active', 'completed', 'cancelled'])
                ->change();
        });
    }
};
