<?php

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'city', 'trip', 'quote', 'rating', 'sort', 'is_published'])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'rating' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort')->latest('id');
    }
}
