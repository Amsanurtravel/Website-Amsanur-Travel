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
        // Pembimbing ibadah: shown on the home page, "Tentang Kami" and each package.
        Schema::create('mentors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('role', 120)->default('Pembimbing Ibadah');
            $table->string('affiliation', 200)->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedSmallInteger('photo_width')->nullable();
            $table->unsignedSmallInteger('photo_height')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentors');
    }
};
