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
        Schema::table('rentals', function (Blueprint $table) {
            // Add confirmed_at timestamp
            $table->timestamp('confirmed_at')->nullable()->after('cancelled_at');

            // Update status enum to include documents_pending
            $table->enum('status', ['pending', 'paid', 'documents_pending', 'confirmed', 'active', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('confirmed_at');

            // Revert status enum to original values
            $table->enum('status', ['pending', 'paid', 'confirmed', 'active', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }
};
