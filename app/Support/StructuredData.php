<?php

namespace App\Support;

use App\Models\Package;

/**
 * JSON-LD blocks for search engines. Everything is built from the same
 * config and models the pages render, so the markup cannot drift from what
 * visitors see.
 */
class StructuredData
{
    public static function organizationId(): string
    {
        return route('home').'#organisasi';
    }

    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $site = config('site');

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'TravelAgency',
            '@id' => static::organizationId(),
            'name' => $site['brand'],
            'legalName' => $site['legal_name'],
            'url' => route('home'),
            // Square, at least 112 px as Google requires for an organisation logo.
            'logo' => asset('images/amsa-nur-logo-512.png'),
            'image' => asset('images/og-amsa-nur.jpg'),
            'slogan' => $site['tagline'],
            'areaServed' => 'ID',
            'identifier' => collect($site['permits'])->map(fn (array $permit) => [
                '@type' => 'PropertyValue',
                'propertyID' => $permit['code'],
                'name' => $permit['name'],
                'value' => $permit['number'],
            ])->all(),
        ];

        if ($site['phone']) {
            $data['telephone'] = static::internationalPhone($site['phone']);
        }

        if ($site['email']) {
            $data['email'] = $site['email'];
        }

        $office = $site['office'];

        if ($office['street']) {
            $data['address'] = array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $office['street'],
                'addressLocality' => $office['locality'],
                'addressRegion' => $office['region'],
                'postalCode' => $office['postal_code'],
                'addressCountry' => 'ID',
            ]);
        }

        $open = collect($office['hours'] ?? [])->filter(fn (array $row) => $row['opens'] && $row['day_codes']);

        if ($open->isNotEmpty()) {
            $data['openingHoursSpecification'] = $open->map(fn (array $row) => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $row['day_codes'],
                'opens' => $row['opens'],
                'closes' => $row['closes'],
            ])->values()->all();
        }

        if ($site['founded'] ?? null) {
            $data['foundingDate'] = (string) $site['founded'];
        }

        if ($site['socials']) {
            $data['sameAs'] = array_values($site['socials']);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('site.brand'),
            'url' => route('home'),
            'inLanguage' => 'id-ID',
            'publisher' => ['@id' => static::organizationId()],
        ];
    }

    /**
     * @param  list<array{q: string, a: string}>  $items
     * @return array<string, mixed>
     */
    public static function faq(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($items)->map(fn (array $item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
            ])->all(),
        ];
    }

    /**
     * @param  list<array{0: string, 1: string}>  $trail  [name, url] pairs, home first
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $trail): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($trail)->values()->map(fn (array $crumb, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb[0],
                'item' => $crumb[1],
            ])->all(),
        ];
    }

    /**
     * One departure, with an offer per room type.
     *
     * @return array<string, mixed>
     */
    public static function trip(Package $package): array
    {
        $url = route('packages.show', $package);

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $package->name,
            'description' => $package->metaDescription(),
            'url' => $url,
            'touristType' => $package->type === Package::HAJI_KHUSUS ? 'Jemaah haji khusus' : 'Jemaah umroh',
            'provider' => ['@id' => static::organizationId(), '@type' => 'TravelAgency', 'name' => config('site.brand')],
            'offers' => collect($package->prices())->map(fn (array $price) => [
                '@type' => 'Offer',
                'name' => "Kamar {$price['label']} ({$price['note']})",
                'price' => $price['amount'],
                'priceCurrency' => $package->currency,
                'availability' => $package->seats_left === 0 ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
                'url' => $url,
            ])->all(),
        ];

        if ($package->departure_date) {
            $data['departureTime'] = $package->departure_date->toDateString();
        }

        return $data;
    }

    /**
     * "0821-1222-0576" becomes "+62-821-1222-0576".
     */
    public static function internationalPhone(string $phone): string
    {
        $phone = trim($phone);

        return str_starts_with($phone, '0') ? '+62-'.substr($phone, 1) : $phone;
    }
}
