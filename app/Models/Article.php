<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Carbon\CarbonInterface;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'category_id',
    'title',
    'slug',
    'excerpt',
    'body',
    'cover_path',
    'cover_alt',
    'cover_width',
    'cover_height',
    'seo_title',
    'seo_description',
    'author_name',
    'reviewer_name',
    'published_at',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use GeneratesSlug, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * The Markdown source. Assigning it also renders `body_html`, so the stored
     * HTML cannot drift from the source and is never mass-assigned.
     */
    protected function body(): Attribute
    {
        return Attribute::set(fn (?string $value) => [
            'body' => $value,
            'body_html' => static::renderMarkdown($value),
        ]);
    }

    /**
     * Assigning a cover also records its pixel size, so the page can reserve the
     * space and avoid layout shift. The size is left untouched when the file
     * cannot be read, for example a path set before the upload exists.
     */
    protected function coverPath(): Attribute
    {
        return Attribute::set(function (?string $value) {
            if ($value === null) {
                return ['cover_path' => null, 'cover_width' => null, 'cover_height' => null];
            }

            $size = $this->coverSize($value);

            return $size === null
                ? ['cover_path' => $value]
                : ['cover_path' => $value, 'cover_width' => $size[0], 'cover_height' => $size[1]];
        });
    }

    /**
     * One of draft, scheduled or published, derived from `published_at`.
     */
    protected function status(): Attribute
    {
        return Attribute::get(fn () => match (true) {
            $this->published_at === null => 'draft',
            $this->published_at->isFuture() => 'scheduled',
            default => 'published',
        });
    }

    /**
     * A replaced or removed cover is deleted from the public disk too.
     */
    protected static function booted(): void
    {
        static::updated(function (Article $article) {
            $old = $article->getOriginal('cover_path');

            if ($article->wasChanged('cover_path') && filled($old)) {
                Storage::disk('public')->delete($old);
            }
        });

        static::deleted(function (Article $article) {
            if (filled($article->cover_path)) {
                Storage::disk('public')->delete($article->cover_path);
            }
        });
    }

    /**
     * Render Markdown to safe HTML: raw HTML is stripped and unsafe links are
     * dropped, so editors cannot inject scripts. Images are lazy-loaded, and a
     * "# heading" becomes an h2 because the page title is the only h1.
     */
    public static function renderMarkdown(?string $markdown): string
    {
        $html = Str::markdown($markdown ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $html = preg_replace('#<(/?)h1>#', '<$1h2>', $html);

        return str_replace('<img ', '<img loading="lazy" decoding="async" ', $html);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Only published articles are reachable by URL; drafts and scheduled
     * articles resolve to a 404.
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->newQuery()
            ->published()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->first();
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function metaDescription(): string
    {
        return $this->seo_description
            ?: $this->excerpt
            ?: Str::of(strip_tags($this->body_html))->squish()->limit(160)->toString();
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(Str::wordCount(strip_tags($this->body_html)) / 200));
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    /**
     * The later of the publish and last-edit times, so `dateModified` is never
     * earlier than `datePublished` for articles that were scheduled in advance.
     */
    public function modifiedAt(): CarbonInterface
    {
        return $this->published_at && $this->published_at->greaterThan($this->updated_at)
            ? $this->published_at
            : $this->updated_at;
    }

    public function wasUpdatedAfterPublishing(): bool
    {
        return $this->updated_at->toDateString() > $this->published_at->toDateString();
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    private function coverSize(string $path): ?array
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $size = @getimagesize($disk->path($path));

        return $size === false ? null : [$size[0], $size[1]];
    }
}
