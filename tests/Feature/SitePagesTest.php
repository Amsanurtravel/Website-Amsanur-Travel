<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Mentor;
use App\Models\Package;
use App\Models\Testimonial;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_home_shows_licences_next_departure_and_whatsapp_on_the_first_screen(): void
    {
        $next = Package::factory()->create(['name' => 'Umroh Paling Dekat', 'departure_date' => today()->addDays(9)]);
        Package::factory()->create(['name' => 'Umroh Berikutnya', 'departure_date' => today()->addDays(40)]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['PPIU 1031/2019', 'PIHK 147/2020', 'Cek keaslian izin', '<h1', 'Keberangkatan terdekat', 'Umroh Paling Dekat'], false)
            ->assertSee(route('whatsapp', ['dari' => 'beranda']), false)
            ->assertSee(route('packages.show', $next), false);
    }

    public function test_home_has_business_and_licence_structured_data(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('"@type":"TravelAgency"', false)
            ->assertSee('"legalName":"PT Amsa Nur Indah Mandiri"', false)
            ->assertSee('"propertyID":"PPIU","name":"Penyelenggara Perjalanan Ibadah Umrah","value":"1031 Tahun 2019"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('"@type":"WebSite"', false);
    }

    public function test_home_hides_sections_without_content_and_shows_them_when_filled(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Jadwal umroh berikutnya sedang disiapkan')
            ->assertDontSee('Pengalaman jemaah kami')
            ->assertDontSee('Artikel terbaru');

        Testimonial::factory()->create(['quote' => 'Pelayanan sangat membantu.']);
        Testimonial::factory()->create(['quote' => 'Belum boleh tampil.', 'is_published' => false]);
        Article::factory()->create(['title' => 'Panduan Uji']);

        $this->get(route('home'))
            ->assertSee('Pelayanan sangat membantu.')
            ->assertDontSee('Belum boleh tampil.')
            ->assertSee('Panduan Uji');
    }

    public function test_public_pages_are_cookie_free_and_cacheable(): void
    {
        $package = Package::factory()->create();

        foreach ([route('home'), route('umrah.index'), route('haji.index'), route('packages.show', $package), route('legal'), route('about')] as $url) {
            $response = $this->get($url)->assertOk();

            $response->assertHeaderMissing('Set-Cookie');
            $this->assertStringContainsString('public', $response->headers->get('Cache-Control'), $url);
        }
    }

    public function test_legal_page_lists_licences_bank_account_and_how_to_check(): void
    {
        $this->get(route('legal'))
            ->assertOk()
            ->assertSee('1031 Tahun 2019')
            ->assertSee(config('site.permits.1.number'))
            ->assertSee('B (82,54)')
            ->assertSee('PT Amsa Nur Indah Mandiri')
            ->assertSee('id="cara-cek-izin"', false)
            ->assertSee('0811-888-5888')
            ->assertSee('708-544-6791')
            ->assertSee('bank penerima setoran atas nama Menteri')
            ->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_about_page_shows_active_mentors_only(): void
    {
        Mentor::factory()->create(['name' => 'KH. Drs. M. Aminullah']);
        Mentor::factory()->create(['name' => 'Pembimbing Nonaktif', 'is_active' => false]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('KH. Drs. M. Aminullah')
            ->assertSee('>MA</span>', false) // initials without the "KH." and "Drs." titles
            ->assertDontSee('Pembimbing Nonaktif');
    }

    public function test_sitemap_lists_pages_and_upcoming_packages_only(): void
    {
        $upcoming = Package::factory()->create();
        $departed = Package::factory()->departed()->create();
        $draft = Package::factory()->draft()->create();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('umrah.index'), false)
            ->assertSee(route('haji.index'), false)
            ->assertSee(route('legal'), false)
            ->assertSee(route('packages.show', $upcoming), false)
            ->assertDontSee($departed->slug, false)
            ->assertDontSee($draft->slug, false);
    }

    public function test_robots_blocks_everything_outside_production(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee("Disallow: /\n", false)
            ->assertDontSee('Sitemap:', false);

        $this->app->detectEnvironment(fn () => 'production');

        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }

    public function test_content_seeder_adds_the_brochure_packages_once_and_keeps_admin_edits(): void
    {
        $this->seed(ContentSeeder::class);

        $this->assertSame(6, Package::ofType(Package::UMRAH)->count());
        $this->assertSame(1, Package::ofType(Package::HAJI_KHUSUS)->count());
        $this->assertSame('KH. Drs. M. Aminullah Tsamud', Package::firstWhere('slug', 'umroh-ekonomis-12-hari-2-desember-2026')->mentor->name);

        Package::firstWhere('slug', 'umroh-ekonomis-12-hari-2-desember-2026')->update(['price_quad' => 34_000_000]);

        $this->seed(ContentSeeder::class);

        $this->assertSame(7, Package::count());
        $this->assertSame(1, Mentor::count());
        $this->assertSame(34_000_000, Package::firstWhere('slug', 'umroh-ekonomis-12-hari-2-desember-2026')->price_quad);

        // The researched guides arrive as drafts for a person to review, never published.
        $this->assertSame(4, Article::count());
        $this->assertSame(0, Article::published()->count());
        $this->assertStringContainsString('<h2>', Article::firstWhere('slug', 'cara-cek-travel-umrah-dan-haji-resmi')->body_html);
    }
}
