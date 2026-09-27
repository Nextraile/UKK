<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kost_id')->unique()->constrained('kosts')->onDelete('cascade');
            $table->text('full_address');
            $table->string('district', 100);
            $table->string('city', 100);
            $table->string('province', 100);
            $table->string('postal_code', 10)->nullable();
            $table->string('country', 100)->default('Indonesia');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('city');
            $table->index('district');
            $table->index(['city', 'district'], 'idx_location_search');
        });

        // Add FULLTEXT index for optimized search queries (marketplace search performance)
        DB::statement('ALTER TABLE addresses ADD FULLTEXT INDEX idx_ft_address_search (full_address, district, city)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE addresses DROP INDEX IF EXISTS idx_ft_address_search');
        Schema::dropIfExists('addresses');
    }
};
