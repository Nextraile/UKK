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
        Schema::create('price_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->smallInteger('duration_value')->unsigned();
            $table->enum('duration_unit', ['day', 'week', 'month']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Index for active price scheme queries
            $table->index(['room_type_id', 'is_active', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_schemes');
    }
};
