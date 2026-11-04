<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Fills a blank `slug` from the model's source attribute and keeps it unique.
 * Existing slugs are never rewritten, so published URLs stay stable.
 */
trait GeneratesSlug
{
    /**
     * The attribute the slug is derived from.
     */
    abstract protected function slugSource(): string;

    protected static function bootGeneratesSlug(): void
    {
        static::saving(function ($model) {
            if (blank($model->slug)) {
                $model->slug = $model->uniqueSlugFor((string) $model->{$model->slugSource()});
            }
        });
    }

    protected function uniqueSlugFor(string $value): string
    {
        // 150 characters leaves room for a "-N" suffix within the 160-character columns.
        $base = rtrim(Str::limit(Str::slug($value) ?: 'item', 150, ''), '-');
        $slug = $base;

        for ($i = 2; $this->slugExists($slug); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->exists();
    }
}
