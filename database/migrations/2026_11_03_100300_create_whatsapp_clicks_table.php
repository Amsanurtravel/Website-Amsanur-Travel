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
        // Counts only: no IP address, user agent or other personal data.
        Schema::create('whatsapp_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 100);
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_clicks');
    }
};
