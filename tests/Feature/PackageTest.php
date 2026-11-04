<?php

namespace Tests\Feature;

use App\Models\Mentor;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PackageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_umrah_list_shows_only_published_upcoming_umrah_departures(): void
    {
        Package::factory()->create(['name' => 'Umroh Terbit']);
        Package::factory()->draft()->create(['name' => 'Umroh Draf']);
        Package::factory()->departed()->create(['name' => 'Umroh Sudah Berangkat']);
        Package::factory()->hajiKhusus()->create(['name' => 'Haji Khusus Satu']);

        $this->get(route('umrah.index'))
            ->assertOk()
            ->assertSee('Umroh Terbit')
            ->assertDontSee('Umroh Draf')
            ->assertDontSee('Umroh Sudah Berangkat')
            ->assertDontSee('Haji Khusus Satu');
    }

    public function test_umrah_list_filters_by_month_and_sorts_by_price(): void
    {
        Package::factory()->create(['name' => 'Umroh Mahal Desember', 'departure_date' => today()->addMonthsNoOverflow(2)->startOfMonth()->addDays(1), 'price_quad' => 45_000_000]);
        Package::factory()->create(['name' => 'Umroh Murah Desember', 'departure_date' => today()->addMonthsNoOverflow(2)->startOfMonth()->addDays(9), 'price_quad' => 30_000_000]);
        Package::factory()->create(['name' => 'Umroh Bulan Lain', 'departure_date' => today()->addMonthsNoOverflow(4)->startOfMonth()->addDays(3)]);

        $month = today()->addMonthsNoOverflow(2)->format('Y-m');

        $this->get(route('umrah.index', ['bulan' => $month]))
            ->assertOk()
            ->assertSee('Umroh Mahal Desember')
            ->assertSee('Umroh Murah Desember')
            ->assertDontSee('Umroh Bulan Lain')
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="'.e(route('umrah.index', ['bulan' => $month])).'">', false);

        $this->get(route('umrah.index', ['urut' => 'harga']))
            ->assertOk()
            ->assertSeeInOrder(['Umroh Murah Desember', 'Umroh Mahal Desember']);

        // Unknown filter values are ignored instead of producing an error.
        $this->get(route('umrah.index', ['bulan' => 'bukan-bulan', 'bintang' => 9]))
            ->assertOk()
            ->assertSee('Umroh Bulan Lain')
            ->assertDontSee('noindex', false);
    }

    public function test_package_page_shows_prices_details_and_licence(): void
    {
        Carbon::setTestNow('2026-11-03 10:00');

        $mentor = Mentor::factory()->create(['name' => 'Ustadz Pembimbing']);
        $package = Package::factory()->create([
            'name' => 'Umroh Ekonomis 12 Hari',
            'departure_date' => '2026-12-02',
            'mentor_id' => $mentor->id,
            'includes' => ['Visa umroh'],
            'excludes' => ['Paspor dan vaksin'],
        ]);

        $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee('Umroh Ekonomis 12 Hari')
            ->assertSee('Rabu, 2 Desember 2026')
            ->assertSee('Rp 35.000.000')
            ->assertSee('Rp 36.000.000')
            ->assertSee('Rp 38.000.000')
            ->assertSee('Perlengkapan dan asuransi')
            ->assertSee('Rp 1.750.000')
            ->assertSee('Visa umroh')
            ->assertSee('Paspor dan vaksin')
            ->assertSee('Ustadz Pembimbing')
            ->assertSee('PPIU Nomor 1031 Tahun 2019')
            ->assertSee(e(route('whatsapp', ['dari' => 'paket', 'paket' => $package->slug])), false)
            ->assertSee('"@type":"TouristTrip"', false)
            ->assertSee('"price":35000000', false)
            ->assertDontSee('noindex', false);
    }

    public function test_haji_package_page_uses_the_pihk_licence_and_dollar_prices(): void
    {
        $package = Package::factory()->hajiKhusus()->create([
            'requirements' => ['Usia minimal 12 tahun saat mendaftar.'],
        ]);

        $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee('PIHK Nomor 147 Tahun 2020')
            ->assertSee('USD 16.500')
            ->assertSee('± 25 hari')
            ->assertSee('Mengikuti nomor porsi')
            ->assertSee('Usia minimal 12 tahun saat mendaftar.');
    }

    public function test_drafts_are_not_found_and_departed_packages_stay_reachable_but_unindexed(): void
    {
        $draft = Package::factory()->draft()->create();
        $departed = Package::factory()->departed()->create();

        $this->get(route('packages.show', $draft))->assertNotFound();

        $this->get(route('packages.show', $departed))
            ->assertOk()
            ->assertSee('Keberangkatan ini sudah lewat.')
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertDontSee('"@type":"TouristTrip"', false)
            ->assertDontSee('paket='.$departed->slug, false);
    }

    public function test_haji_page_lists_haji_packages_and_answers_questions(): void
    {
        Package::factory()->hajiKhusus()->create(['name' => 'Haji Plus Uji']);
        Package::factory()->create(['name' => 'Umroh Bukan Haji']);

        $this->get(route('haji.index'))
            ->assertOk()
            ->assertSee('Haji Plus Uji')
            ->assertDontSee('Umroh Bukan Haji')
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('haji furoda');
    }

    public function test_slug_comes_from_name_and_departure_date_and_stays_unique(): void
    {
        $first = Package::factory()->create(['name' => 'Umroh Ekonomis 12 Hari', 'departure_date' => '2026-12-02', 'slug' => null]);
        $second = Package::factory()->create(['name' => 'Umroh Ekonomis 12 Hari', 'departure_date' => '2026-12-02', 'slug' => null]);
        $undated = Package::factory()->hajiKhusus()->create(['name' => 'Haji Plus 2027', 'slug' => null]);

        $this->assertSame('umroh-ekonomis-12-hari-2-desember-2026', $first->slug);
        $this->assertSame('umroh-ekonomis-12-hari-2-desember-2026-2', $second->slug);
        $this->assertSame('haji-plus-2027', $undated->slug);
    }

    public function test_prices_are_sorted_cheapest_first_and_formatted_per_currency(): void
    {
        $package = Package::factory()->make(['price_quad' => 40_000_000, 'price_triple' => null, 'price_double' => 38_000_000]);

        $this->assertSame(['double', 'quad'], array_column($package->prices(), 'room'));
        $this->assertSame(38_000_000, $package->lowestPrice());
        $this->assertSame('Rp 38.000.000', $package->money(38_000_000));
        $this->assertSame('USD 16.500', Package::factory()->hajiKhusus()->make()->money(16_500));
    }
}
