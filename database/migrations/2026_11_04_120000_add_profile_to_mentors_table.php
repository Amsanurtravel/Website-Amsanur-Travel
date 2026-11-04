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
        // The full profile for /pembimbing; "bio" stays the short text on the cards.
        Schema::table('mentors', function (Blueprint $table) {
            $table->text('profile')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentors', function (Blueprint $table) {
            $table->dropColumn('profile');
        });
    }
};
