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
        // One row per departure, the way the brochures are published.
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index(); // umrah | haji_khusus
            $table->string('name', 160);
            $table->string('slug', 160)->unique();
            $table->string('program_label', 100)->nullable(); // e.g. "Awal Ramadhan"
            $table->string('summary', 300)->nullable();

            $table->date('departure_date')->nullable()->index();
            $table->string('departure_note', 200)->nullable(); // used when there is no fixed date (haji khusus)
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->boolean('duration_approximate')->default(false);

            $table->string('hotel_makkah', 120)->nullable();
            $table->unsignedTinyInteger('hotel_makkah_stars')->nullable();
            $table->string('hotel_madinah', 120)->nullable();
            $table->unsignedTinyInteger('hotel_madinah_stars')->nullable();
            $table->string('accommodation_note', 200)->nullable();

            $table->string('airline', 120)->nullable();
            $table->string('route_departure', 120)->nullable();
            $table->string('route_return', 120)->nullable();

            $table->string('currency', 3)->default('IDR');
            $table->unsignedBigInteger('price_quad')->nullable();
            $table->unsignedBigInteger('price_triple')->nullable();
            $table->unsignedBigInteger('price_double')->nullable();
            $table->json('extra_costs')->nullable(); // [{label, amount}]

            $table->json('highlights')->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->json('requirements')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('mentor_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('seats_left')->nullable();
            $table->string('whatsapp', 30)->nullable(); // overrides the site number for this departure

            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 170)->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
