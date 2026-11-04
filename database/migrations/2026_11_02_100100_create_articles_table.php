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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 160);
            $table->string('slug', 160)->unique();
            $table->string('excerpt', 300)->nullable();
            $table->longText('body');
            $table->longText('body_html');
            $table->string('cover_path')->nullable();
            $table->string('cover_alt')->nullable();
            $table->unsignedSmallInteger('cover_width')->nullable();
            $table->unsignedSmallInteger('cover_height')->nullable();
            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 170)->nullable();
            $table->string('author_name', 100)->nullable();
            $table->string('reviewer_name', 100)->nullable();
            // Null = draft, future = scheduled, past = published.
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
