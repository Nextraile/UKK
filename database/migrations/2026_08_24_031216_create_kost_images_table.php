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
        Schema::create('kost_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kost_id')->constrained('kosts')->onDelete('cascade');
            $table->string('image_path');
            $table->boolean('is_thumbnail')->default(false);
            $table->smallInteger('sort_order')->unsigned();
            $table->timestamps();

            // Indexes
            $table->index('kost_id');
            $table->index('is_thumbnail');

            // Note: Unique constraint only 1 thumbnail per kost is enforced in application logic
            // (MySQL doesn't support partial unique indexes like PostgreSQL)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kost_images');
    }
};
