<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(6), '.');

        return [
            'category_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(16),
            'body' => "Paragraf pembuka artikel contoh.\n\n## Subjudul\n\nIsi bagian ini.\n\n- Poin pertama\n- Poin kedua\n",
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * Indicate that the article is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the article is scheduled for the future.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => now()->addDays(3),
        ]);
    }
}
