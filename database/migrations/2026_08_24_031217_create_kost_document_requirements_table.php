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
        Schema::create('kost_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kost_id')->constrained('kosts')->onDelete('cascade');
            $table->string('document_type', 50);
            $table->boolean('is_required')->default(false);
            $table->text('reason')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('kost_id');

            // Unique: 1 document type per kost
            $table->unique(['kost_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kost_document_requirements');
    }
};
