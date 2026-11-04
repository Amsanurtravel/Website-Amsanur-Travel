<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use Carbon\CarbonInterface;
use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'type',
    'name',
    'slug',
    'program_label',
    'summary',
    'departure_date',
    'departure_note',
    'duration_days',
    'duration_approximate',
    'hotel_makkah',
    'hotel_makkah_stars',
    'hotel_madinah',
    'hotel_madinah_stars',
    'accommodation_note',
    'airline',
    'route_departure',
    'route_return',
    'currency',
    'price_quad',
    'price_triple',
    'price_double',
    'extra_costs',
    'highlights',
    'includes',
    'excludes',
    'requirements',
    'itinerary',
    'notes',
    'mentor_id',
    'seats_left',
    'whatsapp',
    'seo_title',
    'seo_description',
    'published_at',
])]
class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use GeneratesSlug, HasFactory;

    public const UMRAH = 'umrah';

    public const HAJI_KHUSUS = 'haji_khusus';

    public const TYPES = [
        self::UMRAH => 'Umroh',
        self::HAJI_KHUSUS => 'Haji Khusus',
    ];

    /**
     * Room types in brochure order: label and what it means for the pilgrim.
     */
    public const ROOMS = [
        'quad' => ['Quad', 'Sekamar berempat'],
        'triple' => ['Triple', 'Sekamar bertiga'],
        'double' => ['Double', 'Sekamar berdua'],
    ];

    public const CURRENCIES = [
        'IDR' => 'Rupiah (Rp)',
        'USD' => 'Dolar AS (USD)',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'duration_days' => 'integer',
            'duration_approximate' => 'boolean',
            'hotel_makkah_stars' => 'integer',
            'hotel_madinah_stars' => 'integer',
            'price_quad' => 'integer',
            'price_triple' => 'integer',
            'price_double' => 'integer',
            'extra_costs' => 'array',
            'highlights' => 'array',
            'includes' => 'array',
            'excludes' => 'array',
            'requirements' => 'array',
            'itinerary' => 'array',
            'seats_left' => 'integer',
            'published_at' => 'datetime',
        ];
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
     * Name plus departure date, so two departures of the same package get
     * readable, distinct addresses.
     */
    protected function slugBasis(): Attribute
    {
        // The name is shortened first so the date always survives in the address.
        return Attribute::get(fn () => trim(Str::limit($this->name, 120, '').' '.($this->departure_date?->translatedFormat('j F Y') ?? '')));
    }

    /**
     * While a package has never been published, its address follows the name
     * and date (a duplicated departure gets its new date in the URL). Once
     * published the slug is fixed, so shared links keep working.
     */
    protected static function booted(): void
    {
        static::saving(function (Package $package) {
            $neverPublished = $package->exists
                && $package->getOriginal('published_at') === null
                && $package->published_at === null;

            if ($neverPublished && $package->isDirty(['name', 'departure_date']) && ! $package->isDirty('slug')) {
                $package->slug = $package->uniqueSlugFor($package->slug_basis);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Published packages are reachable by URL, including departures that have
     * already left (links shared on WhatsApp keep working); drafts are a 404.
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

    /**
     * Departures from today onwards, plus packages without a fixed date.
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where(fn (Builder $query) => $query
            ->whereNull('departure_date')
            ->orWhereDate('departure_date', '>=', today()));
    }

    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    /**
     * Soonest departure first; undated packages last.
     */
    public function scopeInDepartureOrder(Builder $query): void
    {
        $query->orderByRaw('departure_date is null')->orderBy('departure_date')->orderBy('id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(Mentor::class);
    }

    public function whatsappClicks(): HasMany
    {
        return $this->hasMany(WhatsappClick::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? Str::headline($this->type);
    }

    public function hasDeparted(): bool
    {
        return $this->departure_date !== null && $this->departure_date->lt(today());
    }

    public function departureLabel(): ?string
    {
        return $this->departure_date?->translatedFormat('l, j F Y') ?? $this->departure_note;
    }

    public function durationLabel(): ?string
    {
        if (! $this->duration_days) {
            return null;
        }

        return ($this->duration_approximate ? '± ' : '').$this->duration_days.' hari';
    }

    /**
     * Prices that are filled in, cheapest room type first.
     *
     * @return list<array{room: string, label: string, note: string, amount: int}>
     */
    public function prices(): array
    {
        $prices = [];

        foreach (self::ROOMS as $room => [$label, $note]) {
            if ($this->{"price_{$room}"}) {
                $prices[] = ['room' => $room, 'label' => $label, 'note' => $note, 'amount' => $this->{"price_{$room}"}];
            }
        }

        usort($prices, fn (array $a, array $b) => $a['amount'] <=> $b['amount']);

        return $prices;
    }

    public function lowestPrice(): ?int
    {
        return $this->prices()[0]['amount'] ?? null;
    }

    public function money(int $amount): string
    {
        $number = number_format($amount, 0, ',', '.');

        return $this->currency === 'USD' ? "USD {$number}" : "Rp {$number}";
    }

    /**
     * @return list<array{label: string, amount: ?int}>
     */
    public function extraCosts(): array
    {
        return collect($this->extra_costs ?? [])
            ->filter(fn ($cost) => filled($cost['label'] ?? null))
            ->map(fn (array $cost) => ['label' => $cost['label'], 'amount' => filled($cost['amount'] ?? null) ? (int) $cost['amount'] : null])
            ->values()
            ->all();
    }

    /**
     * The day-by-day schedule, with consecutive days that share an activity
     * merged into one row ("12-17 | 15-20 Dzulhijjah | Makkah") so the
     * table stays short on a phone.
     *
     * @return list<array{days: string, date: ?string, activity: string}>
     */
    public function itineraryRows(): array
    {
        $rows = [];

        foreach ($this->itinerary ?? [] as $item) {
            $day = (int) ($item['day'] ?? 0);
            $activity = trim((string) ($item['activity'] ?? ''));

            if ($day < 1 || $activity === '') {
                continue;
            }

            $date = filled($item['date'] ?? null) ? trim($item['date']) : null;
            $last = array_key_last($rows);

            if ($last !== null && $rows[$last]['activity'] === $activity && $rows[$last]['to'] === $day - 1) {
                $rows[$last]['to'] = $day;
                $rows[$last]['dates'][] = $date;

                continue;
            }

            $rows[] = ['from' => $day, 'to' => $day, 'dates' => [$date], 'activity' => $activity];
        }

        return array_map(fn (array $row): array => [
            'days' => $row['from'] === $row['to'] ? (string) $row['from'] : $row['from'].'–'.$row['to'],
            'date' => self::dateRange($row['dates']),
            'activity' => $row['activity'],
        ], $rows);
    }

    /**
     * "15 Dzulhijjah" + "20 Dzulhijjah" becomes "15–20 Dzulhijjah".
     *
     * @param  list<?string>  $dates
     */
    private static function dateRange(array $dates): ?string
    {
        $first = reset($dates);
        $final = end($dates);

        if ($first === null || $final === null || $first === $final) {
            return $first ?: null;
        }

        if (preg_match('/^(\d+)\s+(.+)$/u', $first, $a) && preg_match('/^(\d+)\s+(.+)$/u', $final, $b) && $a[2] === $b[2]) {
            return $a[1].'–'.$b[1].' '.$a[2];
        }

        return $first.' – '.$final;
    }

    /**
     * @return list<string>
     */
    public function listOf(string $attribute): array
    {
        return collect($this->{$attribute} ?? [])
            ->map(fn ($item) => is_array($item) ? ($item['item'] ?? null) : $item)
            ->filter(fn ($item) => filled($item))
            ->values()
            ->all();
    }

    public function metaTitle(): string
    {
        return $this->seo_title ?: Str::limit($this->name.($this->departure_date ? ' '.$this->departure_date->translatedFormat('j F Y') : ''), 70, '');
    }

    public function metaDescription(): string
    {
        if ($this->seo_description) {
            return $this->seo_description;
        }

        // Short facts first, so a long summary never pushes the price out of the snippet.
        $departure = match (true) {
            $this->departure_date !== null => 'Keberangkatan '.$this->departure_date->translatedFormat('j F Y').'.',
            filled($this->departure_note) => 'Keberangkatan: '.rtrim($this->departure_note, '. ').'.',
            default => null,
        };

        $text = implode(' ', array_filter([
            $departure,
            $this->lowestPrice() ? 'Mulai '.$this->money($this->lowestPrice()).' per orang.' : null,
            $this->airline ? 'Maskapai '.$this->airline.'.' : null,
            $this->summary,
        ]));

        return mb_strlen($text) <= 160 ? $text : rtrim(Str::limit($text, 157, '', true), ' .,;:').'…';
    }

    public function whatsappMessage(): string
    {
        $when = $this->departure_date ? ' keberangkatan '.$this->departure_date->translatedFormat('j F Y') : '';

        return "Assalamu'alaikum, saya ingin bertanya tentang paket {$this->name}{$when}.";
    }

    public function modifiedAt(): CarbonInterface
    {
        return $this->published_at && $this->published_at->greaterThan($this->updated_at)
            ? $this->published_at
            : $this->updated_at;
    }

    protected function slugSource(): string
    {
        return 'slug_basis';
    }
}
