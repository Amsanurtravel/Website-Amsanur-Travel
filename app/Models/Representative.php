<?php

namespace App\Models;

use Database\Factories\RepresentativeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'region',
    'name',
    'address',
    'phone',
    'sort',
    'is_active',
])]
class Representative extends Model
{
    /** @use HasFactory<RepresentativeFactory> */
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

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort')->orderBy('id');
    }
}
