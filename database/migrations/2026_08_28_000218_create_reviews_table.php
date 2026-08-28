<?php

declare(strict_types=1);

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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->unsignedTinyInteger('kost_rating')->nullable()->comment('1-5 rating for kost');
            $table->text('kost_comment')->nullable();
            $table->unsignedTinyInteger('room_rating')->nullable()->comment('1-5 rating for room');
            $table->text('room_comment')->nullable();
            $table->json('images')->nullable()->comment('Array of image paths');
            $table->timestamps();

            $table->unique('rental_id');
            $table->index(['kost_rating', 'room_rating', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
