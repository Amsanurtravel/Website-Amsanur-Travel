<?php

namespace Tests\Feature;

use App\Filament\Resources\Packages\Pages\CreatePackage;
use App\Models\Package;
use App\Models\User;
use App\Support\StructuredData;
use Database\Seeders\ContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The client's answers of 4 November 2026: licence decrees, company profile,
 * contact details, new brochures and the hajj itinerary.
 */
class ClientUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_consecutive_days_with_the_same_activity_are_merged(): void
    {
        $package = Package::factory()->make(['itinerary' => [
            ['day' => 1, 'date' => '4 Dzulhijjah', 'activity' => 'Manasik di Jakarta'],
            ['day' => 2, 'date' => '5 Dzulhijjah', 'activity' => 'Manasik di Jakarta'],
            ['day' => 3, 'date' => '6 Dzulhijjah', 'activity' => 'Jakarta - Jeddah - Makkah'],
            ['day' => 4, 'date' => null, 'activity' => 'Makkah'],
            ['day' => 6, 'date' => null, 'activity' => 'Makkah'], // a gap is not merged
            ['day' => 7, 'date' => null, 'activity' => ''],       // incomplete rows are skipped
        ]]);

        $this->assertSame([
            ['days' => '1–2', 'date' => '4–5 Dzulhijjah', 'activity' => 'Manasik di Jakarta'],
            ['days' => '3', 'date' => '6 Dzulhijjah', 'activity' => 'Jakarta - Jeddah - Makkah'],
            ['days' => '4', 'date' => null, 'activity' => 'Makkah'],
            ['days' => '6', 'date' => null, 'activity' => 'Makkah'],
        ], $package->itineraryRows());
    }

    public function test_the_hajj_package_shows_the_25_day_schedule(): void
    {
        $this->seed(ContentSeeder::class);
        $haji = Package::firstWhere('slug', 'haji-plus-haji-khusus-2027');

        $this->assertCount(25, $haji->itinerary);
        $this->assertSame(['days' => '12–17', 'date' => '15–20 Dzulhijjah', 'activity' => 'Makkah'], $haji->itineraryRows()[9]);

        $this->get(route('packages.show', $haji))
            ->assertOk()
            ->assertSeeInOrder(['Perkiraan jadwal perjalanan', 'Wukuf di Arafah, Muzdalifah', '19–23', 'Madinah', 'Tiba di Jakarta'])
            ->assertSee('Jadwal sewaktu-waktu dapat berubah');

        // Umrah packages without a schedule show no empty table.
        $this->get(route('packages.show', Package::firstWhere('slug', 'umroh-ekonomis-12-hari-13-januari-2027')))
            ->assertOk()
            ->assertSee('Snood Hotel')
            ->assertDontSee('Jadwal perjalanan');
    }

    public function test_a_departure_without_an_exact_date_is_listed_by_its_note(): void
    {
        $this->seed(ContentSeeder::class);

        $this->get(route('umrah.index'))
            ->assertOk()
            ->assertSee('Umroh Promo Milad 9 Hari')
            ->assertSee('Akhir Juli 2027')
            ->assertSee('Rp 28.500.000');
    }

    public function test_admin_can_enter_a_schedule(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');

        Livewire::test(CreatePackage::class)
            ->fillForm([
                'type' => Package::UMRAH,
                'name' => 'Umroh Uji Jadwal',
                'itinerary' => [['day' => 1, 'date' => null, 'activity' => 'Jakarta - Jeddah']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('Jakarta - Jeddah', Package::firstWhere('name', 'Umroh Uji Jadwal')->itineraryRows()[0]['activity']);
    }

    public function test_legal_page_shows_decree_dates_registry_note_company_and_accounts(): void
    {
        $this->get(route('legal'))
            ->assertOk()
            ->assertSee('Tanggal 28 Februari 2020')
            ->assertSee('KMA Nomor 839 Tahun 2016')
            ->assertSee('C-12492 HT.01.01.TH.2002')
            ->assertSee('11/Kesthuri/2014')
            ->assertSee('006-000-7533296')
            ->assertSee('708-544-6791')
            ->assertSee('Senin–Jumat');
    }

    public function test_every_page_shows_hours_socials_and_the_full_address(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Jakarta Pusat, DKI Jakarta 10440')
            ->assertSee('Sabtu')
            ->assertSee('09.00–12.00 WIB')
            ->assertSee('https://www.instagram.com/amsanurtourtravel/', false)
            ->assertSee('https://www.tiktok.com/@amsanurtravel', false)
            ->assertSee('Berizin sejak 2002');
    }

    public function test_structured_data_has_opening_hours_and_founding_year(): void
    {
        $organization = StructuredData::organization();

        $this->assertSame('2002', $organization['foundingDate']);
        $this->assertSame('10440', $organization['address']['postalCode']);
        $this->assertSame(['Saturday'], $organization['openingHoursSpecification'][1]['dayOfWeek']);
        $this->assertSame('12:00', $organization['openingHoursSpecification'][1]['closes']);
        $this->assertCount(2, $organization['openingHoursSpecification']);
        $this->assertContains('https://www.tiktok.com/@amsanurtravel', $organization['sameAs']);
    }

    public function test_about_page_tells_the_company_story(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Melayani tamu Allah sejak 2002')
            ->assertSee('Drs. H. M. Aminullah Tsamud')
            ->assertSee("Pesantren Tadabbur Al-Qur'an Amsa di Gadog, Megamendung", false)
            ->assertSee('Umroh plus Istanbul')
            ->assertSee('minimal 35 jemaah');
    }

    public function test_the_mentor_is_introduced_as_the_director(): void
    {
        $this->seed(ContentSeeder::class);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('KH. Drs. M. Aminullah')
            ->assertSee('Beliau juga Direktur PT Amsa Nur Indah Mandiri');
    }

    public function test_the_directors_number_is_listed_as_official_but_buttons_use_the_office(): void
    {
        $this->seed(ContentSeeder::class);

        $this->get(route('legal'))
            ->assertOk()
            ->assertSee('WhatsApp Direktur 0812-1319-0005')
            ->assertSee('https://wa.me/6281213190005', false);

        $this->assertNull(Package::firstWhere('slug', 'umroh-ekonomis-12-hari-13-januari-2027')->whatsapp);
    }

    public function test_pilgrim_photos_are_shown_small_and_lazy(): void
    {
        $photos = config('site.gallery');
        $this->assertCount(8, $photos);

        foreach ($photos as $photo) {
            foreach (['480', '960'] as $width) {
                $path = public_path("images/jemaah/{$photo['file']}-{$width}.webp");
                $this->assertFileExists($path);
                $this->assertLessThanOrEqual(100 * 1024, filesize($path), $path);
            }
            $this->assertSame($photo['width'], getimagesize(public_path("images/jemaah/{$photo['file']}-960.webp"))[0]);
            $this->assertNotEmpty($photo['alt']);
        }

        $home = $this->get(route('home'))->assertOk()->assertSee('Bersama jemaah Amsa Nur');
        $this->assertSame(4, substr_count($home->getContent(), '-480.webp 480w'));
        $home->assertSee('loading="lazy"', false)->assertDontSee('pelataran-nabawi');

        $this->get(route('about'))->assertOk()->assertSee('Dokumentasi jemaah')->assertSee('pelataran-nabawi-960.webp');
    }
}
