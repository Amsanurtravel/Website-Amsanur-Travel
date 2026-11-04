<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Day-by-day schedule, e.g. the "Estimasi Perjalanan Haji Plus" table:
     * a list of {day, date, activity}.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('itinerary')->nullable()->after('requirements');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('itinerary');
        });
    }
};
