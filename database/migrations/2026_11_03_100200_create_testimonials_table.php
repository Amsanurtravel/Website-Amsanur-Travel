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
        // Real pilgrims only, published with their consent.
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('city', 100)->nullable();
            $table->string('trip', 120)->nullable(); // e.g. "Umroh Desember 2025"
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
