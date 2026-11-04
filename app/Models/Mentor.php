<?php

namespace App\Models;

use Database\Factories\MentorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'role',
    'affiliation',
    'bio',
    'profile',
    'photo_path',
    'photo_width',
    'photo_height',
    'sort',
    'is_active',
])]
class Mentor extends Model
{
    /** @use HasFactory<MentorFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /**
     * Assigning a photo also records its pixel size, so the page can reserve
     * the space and avoid layout shift.
     */
    protected function photoPath(): Attribute
    {
        return Attribute::set(function (?string $value) {
            if ($value === null) {
                return ['photo_path' => null, 'photo_width' => null, 'photo_height' => null];
            }

            $disk = Storage::disk('public');
            $size = $disk->exists($value) ? @getimagesize($disk->path($value)) : false;

            return $size === false
                ? ['photo_path' => $value]
                : ['photo_path' => $value, 'photo_width' => $size[0], 'photo_height' => $size[1]];
        });
    }

    /**
     * A replaced or removed photo is deleted from the public disk, so it is no
     * longer reachable by its old URL (for example after consent is withdrawn).
     */
    protected static function booted(): void
    {
        static::updated(function (Mentor $mentor) {
            $old = $mentor->getOriginal('photo_path');

            if ($mentor->wasChanged('photo_path') && filled($old)) {
                Storage::disk('public')->delete($old);
            }
        });

        static::deleted(function (Mentor $mentor) {
            if (filled($mentor->photo_path)) {
                Storage::disk('public')->delete($mentor->photo_path);
            }
        });
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    /**
     * The full profile split into paragraphs at blank lines, for the mentor page.
     *
     * @return list<string>
     */
    public function profileParagraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', (string) $this->profile))));
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    /**
     * Initials for the placeholder avatar, skipping titles such as "KH." and
     * "Drs.": "KH. Drs. M. Aminullah" becomes "MA".
     */
    public function initials(): string
    {
        $titles = ['kh', 'k.h', 'drs', 'dr', 'prof', 'h', 'hj', 'ust', 'ustadz', 'ustadzah', 'lc', 'ir', 'buya', 'habib', 'syekh', 'kyai', 'kiai'];

        return collect(preg_split('/\s+/', trim($this->name)))
            ->reject(fn (string $word) => in_array(Str::lower(rtrim($word, '.,')), $titles, true))
            ->take(2)
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');
    }
}
