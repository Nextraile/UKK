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
        Schema::create('room_type_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('cascade');
            $table->string('image_path', 255);
            $table->boolean('is_thumbnail')->default(false);
            $table->smallInteger('sort_order')->unsigned();
            $table->timestamps();

            // Indexes
            $table->index('room_type_id');
            $table->index('is_thumbnail');

            // Note: Unique partial index (1 thumbnail per room_type) handled in app logic
            // Laravel doesn't support partial unique indexes natively
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_type_images');
    }
};
