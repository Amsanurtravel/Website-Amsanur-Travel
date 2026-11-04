<?php

namespace Tests\Feature;

use App\Filament\Resources\Mentors\Pages\CreateMentor;
use App\Filament\Resources\Packages\Pages\CreatePackage;
use App\Models\Article;
use App\Models\Mentor;
use App\Models\Package;
use App\Models\User;
use App\Models\WhatsappClick;
use App\Providers\AppServiceProvider;
use App\Support\WhatsApp;
use Database\Seeders\ContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression tests for the issues found in the independent review (3 November 2026).
 */
class HardeningTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/130.0 Mobile Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_array_query_parameters_are_ignored_instead_of_crashing(): void
    {
        config(['site.whatsapp' => '0821-1222-0576']);
        $package = Package::factory()->create();

        $this->get('/umroh?bulan[]='.$package->departure_date->format('Y-m').'&bintang[]=3&urut[]=harga')->assertOk();
        $this->get('/wa?paket[]=x', ['User-Agent' => self::BROWSER])->assertRedirectContains('https://wa.me/6282112220576');
        $this->get('/wa?dari[]=x', ['User-Agent' => self::BROWSER])->assertRedirect();
    }

    public function test_whatsapp_numbers_are_normalised_the_way_people_type_them(): void
    {
        foreach (['0812-5550-1234', '62812 5550 1234', '+62 812-5550-1234', '+62 0812 5550 1234', '0062 812-5550-1234', '812-5550-1234'] as $typed) {
            $this->assertSame('6281255501234', WhatsApp::number($typed), $typed);
        }

        $this->assertTrue(WhatsApp::isValid('0821-1222-0576'));
        $this->assertFalse(WhatsApp::isValid('0812'));
        $this->assertFalse(WhatsApp::isValid('+1 555 0100'));
    }

    public function test_hidden_embeds_of_the_whatsapp_link_are_not_counted(): void
    {
        config(['site.whatsapp' => '0821-1222-0576']);

        $this->get('/wa?dari=beranda', ['User-Agent' => self::BROWSER, 'Sec-Fetch-Mode' => 'no-cors', 'Sec-Fetch-Dest' => 'image'])->assertRedirect();
        $this->assertSame(0, WhatsappClick::count());

        $this->get('/wa?dari=beranda', ['User-Agent' => self::BROWSER, 'Sec-Fetch-Mode' => 'navigate', 'Sec-Fetch-Dest' => 'document'])->assertRedirect();
        $this->assertSame(1, WhatsappClick::count());
    }

    public function test_a_hidden_mentor_disappears_from_package_pages_too(): void
    {
        $mentor = Mentor::factory()->create(['name' => 'Ustadz Disembunyikan', 'is_active' => false]);
        $package = Package::factory()->create(['mentor_id' => $mentor->id]);

        $this->get(route('packages.show', $package))->assertOk()->assertDontSee('Ustadz Disembunyikan');
    }

    public function test_haji_package_description_reads_well_and_keeps_the_price(): void
    {
        $package = Package::factory()->hajiKhusus()->make([
            'airline' => 'Saudia',
            'summary' => str_repeat('Paket haji khusus dengan hotel bintang lima dan layanan lengkap. ', 4),
        ]);

        $description = $package->metaDescription();

        $this->assertStringStartsWith('Keberangkatan: Mengikuti nomor porsi. Mulai USD 16.500 per orang.', $description);
        $this->assertLessThanOrEqual(160, mb_strlen($description));
        $this->assertStringNotContainsString('....', $description);
    }

    public function test_price_sort_never_mixes_rupiah_and_dollars(): void
    {
        Package::factory()->create(['name' => 'Umroh Rupiah Mahal', 'price_quad' => 45_000_000]);
        Package::factory()->create(['name' => 'Umroh Rupiah Murah', 'price_quad' => 30_000_000]);
        Package::factory()->create(['name' => 'Umroh Plus Dolar', 'currency' => 'USD', 'price_quad' => 3_200, 'price_triple' => null, 'price_double' => null]);

        $this->get(route('umrah.index', ['urut' => 'harga']))
            ->assertOk()
            ->assertSeeInOrder(['Umroh Rupiah Murah', 'Umroh Rupiah Mahal', 'Umroh Plus Dolar']);
    }

    public function test_content_seeder_never_brings_back_deleted_content(): void
    {
        $this->seed(ContentSeeder::class);

        Package::firstWhere('slug', 'umroh-ekonomis-12-hari-11-november-2026')->delete();
        Mentor::first()->update(['name' => 'KH. M. Aminullah']);

        $this->seed(ContentSeeder::class);

        $this->assertSame(6, Package::count());
        $this->assertSame(1, Mentor::count());
    }

    public function test_empty_article_section_stays_out_of_menu_sitemap_and_index(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('>Artikel</a>', false);
        $this->get(route('articles.index'))->assertOk()->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get(route('sitemap'))->assertOk()->assertDontSee(route('articles.index').'<', false);

        Article::factory()->create();

        $this->get(route('home'))->assertSee('>Artikel</a>', false);
        $this->get(route('articles.index'))->assertDontSee('noindex', false);
        $this->get(route('sitemap'))->assertSee(route('articles.index').'<', false);
    }

    public function test_privacy_page_is_public_and_linked_from_every_page(): void
    {
        $this->get(route('privacy'))->assertOk()->assertSee('Pelindungan Data Pribadi')->assertHeaderMissing('Set-Cookie');
        $this->get(route('home'))->assertSee(route('privacy'), false);
    }

    public function test_a_markdown_h1_becomes_an_h2_so_the_page_keeps_one_h1(): void
    {
        $this->assertSame("<h2>Subjudul</h2>\n", Article::renderMarkdown('# Subjudul'));
    }

    public function test_a_replaced_mentor_photo_is_deleted_from_the_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('mentors/lama.jpg', 'x');
        Storage::disk('public')->put('mentors/baru.jpg', 'x');

        $mentor = Mentor::factory()->create(['photo_path' => 'mentors/lama.jpg']);
        $mentor->update(['photo_path' => 'mentors/baru.jpg']);

        Storage::disk('public')->assertMissing('mentors/lama.jpg');

        $mentor->delete();

        Storage::disk('public')->assertMissing('mentors/baru.jpg');
    }

    public function test_admin_rejects_a_mistyped_whatsapp_number_and_stores_a_clean_one(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');

        Livewire::test(CreatePackage::class)
            ->fillForm(['type' => Package::UMRAH, 'name' => 'Umroh Uji', 'whatsapp' => '0812'])
            ->call('create')
            ->assertHasFormErrors(['whatsapp']);

        Livewire::test(CreatePackage::class)
            ->fillForm(['type' => Package::UMRAH, 'name' => 'Umroh Uji', 'whatsapp' => '+62 0812-5550-1234'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('6281255501234', Package::firstWhere('name', 'Umroh Uji')->whatsapp);
    }

    public function test_admin_refuses_svg_uploads(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');

        $svg = UploadedFile::fake()->createWithContent('foto.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        Livewire::test(CreateMentor::class)
            ->fillForm(['name' => 'Pembimbing Uji', 'photo_path' => $svg])
            ->call('create')
            ->assertHasFormErrors(['photo_path']);
    }

    public function test_production_links_always_use_app_url(): void
    {
        config(['app.url' => 'https://amsanur.example']);
        $this->app->detectEnvironment(fn () => 'production');
        $this->app->getProvider(AppServiceProvider::class)->boot();

        $this->get('http://www.other-host.test/legalitas')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://amsanur.example/legalitas">', false);
    }
}
