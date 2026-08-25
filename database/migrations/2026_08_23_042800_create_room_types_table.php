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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kost_id')->constrained('kosts')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->string('room_size', 50);
            $table->tinyInteger('max_occupants')->unsigned();
            $table->decimal('security_deposit', 12, 2);
            $table->json('facilities')->nullable();
            $table->json('rules')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kost_id');
            $table->unique(['kost_id', 'slug']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
