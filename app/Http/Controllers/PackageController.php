<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Support\Faq;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Upcoming umrah departures with simple filters that work without
     * JavaScript: month (?bulan=2026-12), hotel stars (?bintang=4) and sort
     * order (?urut=harga). Unknown values are ignored rather than erroring.
     */
    public function umrah(Request $request): View
    {
        $all = Package::published()
            ->upcoming()
            ->ofType(Package::UMRAH)
            ->with('mentor')
            ->inDepartureOrder()
            ->get();

        $months = $all
            ->filter(fn (Package $package) => $package->departure_date !== null)
            ->mapWithKeys(fn (Package $package) => [$package->departure_date->format('Y-m') => $package->departure_date->translatedFormat('F Y')])
            ->unique();

        $stars = $all->pluck('hotel_makkah_stars')->filter()->unique()->sort()->values();

        // Only plain strings count: ?bulan[]=... is ignored like any unknown value.
        $bulan = $request->query('bulan');
        $bintang = $request->query('bintang');

        $month = is_string($bulan) && $months->has($bulan) ? $bulan : null;
        $star = is_string($bintang) && $stars->contains((int) $bintang) ? (int) $bintang : null;
        $sort = $request->query('urut') === 'harga' ? 'harga' : 'tanggal';

        // Rupiah and dollar prices are never compared with each other: rupiah first, then dollars.
        $packages = $all
            ->when($month, fn (Collection $packages) => $packages->filter(fn (Package $package) => $package->departure_date?->format('Y-m') === $month))
            ->when($star, fn (Collection $packages) => $packages->where('hotel_makkah_stars', $star))
            ->when($sort === 'harga', fn (Collection $packages) => $packages->sortBy(fn (Package $package) => [$package->currency === 'IDR' ? 0 : 1, $package->lowestPrice() ?? PHP_INT_MAX]))
            ->values();

        return view('packages.umrah', [
            'packages' => $packages,
            'total' => $all->count(),
            'months' => $months,
            'stars' => $stars->count() > 1 ? $stars : collect(),
            'filters' => ['bulan' => $month, 'bintang' => $star, 'urut' => $sort],
            'schema' => [StructuredData::breadcrumbs([['Beranda', route('home')], ['Umroh', route('umrah.index')]])],
        ]);
    }

    public function haji(): View
    {
        $faqs = Faq::haji();

        return view('packages.haji', [
            'packages' => Package::published()->upcoming()->ofType(Package::HAJI_KHUSUS)->with('mentor')->inDepartureOrder()->get(),
            'faqs' => $faqs,
            'schema' => [
                StructuredData::faq($faqs),
                StructuredData::breadcrumbs([['Beranda', route('home')], ['Haji Khusus', route('haji.index')]]),
            ],
        ]);
    }

    public function show(Package $package): View
    {
        // A mentor hidden in the admin panel is hidden here too.
        $package->load(['mentor' => fn ($query) => $query->where('is_active', true)]);

        $listing = $package->type === Package::HAJI_KHUSUS
            ? ['Haji Khusus', route('haji.index')]
            : ['Umroh', route('umrah.index')];

        return view('packages.show', [
            'package' => $package,
            'listing' => $listing,
            'others' => Package::published()
                ->upcoming()
                ->ofType($package->type)
                ->whereKeyNot($package->getKey())
                ->inDepartureOrder()
                ->limit(3)
                ->get(),
            'schema' => array_values(array_filter([
                $package->hasDeparted() ? null : StructuredData::trip($package),
                StructuredData::breadcrumbs([['Beranda', route('home')], $listing, [$package->name, route('packages.show', $package)]]),
            ])),
        ]);
    }
}
