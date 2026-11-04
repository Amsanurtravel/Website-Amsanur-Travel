<?php

namespace Tests\Feature;

use App\Filament\Resources\Mentors\Pages\EditMentor;
use App\Models\Mentor;
use App\Models\Package;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The user's review of 4 November 2026: home copy, footer, header logo, the
 * licence pages without downloads or the long how-to, and the mentor profile.
 */
class ReviewChangesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_home_copy_follows_the_review(): void
    {
        $this->seed(ContentSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Syarat, setoran awal, dan perkiraan masa tunggunya kami jelaskan secara terbuka.')
            ->assertSee('Mengapa Amsa Nur?')
            ->assertDontSee('Didampingi pembimbing')
            ->assertSee('Kantor sendiri di Jakarta')
            ->assertSee('Harga per-tipe kamar, biaya tambahan, dan biaya termasuk serta biaya tidak termasuk lainnya sudah tertulis jelas di setiap paket.')
            ->assertSee('href="'.route('legal').'#cara-cek-izin" target="_blank" rel="noopener"', false);
    }

    public function test_the_hero_leaves_the_licences_to_the_top_bar(): void
    {
        $this->seed(ContentSeeder::class);
        $html = $this->get(route('home'))->assertOk()->getContent();

        $header = substr($html, strpos($html, '<header'), strpos($html, '</header>') - strpos($html, '<header'));
        $this->assertStringContainsString(config('site.permits.0.short'), $header);
        $this->assertStringContainsString(config('site.permits.1.short'), $header);

        $start = strpos($html, '<h1');
        $hero = substr($html, $start, strpos($html, '</section>', $start) - $start);
        $this->assertStringContainsString('Keberangkatan terdekat', $hero);
        $this->assertStringNotContainsString(config('site.permits.0.number'), $hero);
        $this->assertStringNotContainsString(config('site.permits.1.number'), $hero);
    }

    public function test_on_phones_the_all_schedules_link_follows_the_cards(): void
    {
        $this->seed(ContentSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['id="jadwal-umroh"', 'hidden min-h-11', '</ul>', 'sm:hidden">Semua', 'Harga per orang.'], false);
    }

    public function test_every_page_has_a_back_to_top_button(): void
    {
        $this->seed(ContentSeeder::class);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<body id="atas"', false)
            ->assertSee('href="#atas" id="ke-atas"', false)
            ->assertSee('aria-label="Kembali ke atas"', false);

        // Package pages have a price bar on phones instead of the floating WhatsApp button.
        $this->get(route('packages.show', Package::firstWhere('slug', 'umroh-ekonomis-12-hari-13-januari-2027')))
            ->assertOk()
            ->assertSee('id="ke-atas"', false)
            ->assertSee('lg:bottom-6', false);
    }

    public function test_header_shows_the_full_logo_and_the_footer_is_tidied(): void
    {
        $response = $this->get(route('home'))->assertOk();
        $html = $response->getContent();

        $header = substr($html, strpos($html, '<header'), strpos($html, '</header>') - strpos($html, '<header'));
        $this->assertStringContainsString('images/amsa-nur-logo.png', $header);
        $this->assertStringNotContainsString('amsa-nur-mark.png', $html);
        $this->assertFileExists(public_path('images/amsa-nur-logo.png'));

        $response
            ->assertSee('Setoran BIPIH haji khusus ke rekening atas nama Menteri.')
            ->assertSee('Jam buka kantor')
            ->assertSee('aria-label="Media sosial"', false)
            ->assertSee('M13.5 3.5v12.25', false) // TikTok icon
            ->assertSee('M7 2.75h10A4.25', false); // Instagram icon
    }

    public function test_the_legal_page_has_no_downloads_and_no_long_how_to(): void
    {
        $this->get(route('legal'))
            ->assertOk()
            ->assertDontSee('Cara mengecek izin kami sendiri')
            ->assertDontSee('Yang perlu dicocokkan')
            ->assertDontSee('Lihat SK izin')
            ->assertDontSee('dokumen/')
            // The short answer in the FAQ is what the "cek izin" links open.
            ->assertSee('<details id="cara-cek-izin"', false)
            ->assertSee('Bagaimana cara mengecek izin travel?')
            ->assertSee('0811-888-5888');

        $this->assertDirectoryDoesNotExist(public_path('dokumen'));
    }

    public function test_about_and_package_pages_drop_the_licence_check_links(): void
    {
        $this->seed(ContentSeeder::class);

        $this->get(route('about'))->assertOk()->assertDontSee('Lihat legalitas dan cara mengecek izin');

        $this->get(route('packages.show', Package::firstWhere('slug', 'umroh-ekonomis-12-hari-13-januari-2027')))
            ->assertOk()
            ->assertSee('berizin PPIU Nomor 1031 Tahun 2019.')
            ->assertDontSee('Cek keasliannya');
    }

    public function test_the_mentor_card_opens_the_profile_in_a_new_tab(): void
    {
        $this->seed(ContentSeeder::class);
        $mentor = Mentor::firstWhere('name', 'KH. Drs. M. Aminullah Tsamud');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('mentors').'#pembimbing-'.$mentor->id.'" target="_blank" rel="noopener"', false)
            ->assertSee('Lihat profil lengkap');

        $this->get(route('mentors'))
            ->assertOk()
            ->assertSee('id="pembimbing-'.$mentor->id.'"', false)
            ->assertSeeInOrder(['<h1', 'Mengenal Pembimbing Perjalanan Ibadah Spiritual Anda', 'KH. Drs. M. Aminullah Tsamud'], false)
            ->assertSee("Pesantren Tadabbur Al-Qur'an Amsa di Gadog, Megamendung, Kabupaten Bogor, yang berdiri sejak 2021")
            ->assertSee('Direktur PT Amsa Nur Indah Mandiri')
            ->assertSee('>MA</span>', false) // placeholder until a photo is uploaded
            ->assertSee(route('whatsapp', ['dari' => 'pembimbing']), false);

        $this->assertCount(4, $mentor->profileParagraphs());
        $this->get(route('sitemap'))->assertOk()->assertSee(route('mentors'));
    }

    public function test_the_mentor_page_needs_an_active_mentor(): void
    {
        $this->get(route('mentors'))->assertNotFound();
        $this->get(route('sitemap'))->assertOk()->assertDontSee(route('mentors'));

        Mentor::factory()->create(['name' => 'Ustadz Nonaktif', 'is_active' => false]);
        $this->get(route('mentors'))->assertNotFound();
    }

    public function test_admin_can_write_the_full_profile(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');
        $mentor = Mentor::factory()->create();

        Livewire::test(EditMentor::class, ['record' => $mentor->getRouteKey()])
            ->fillForm(['profile' => "Paragraf pertama.\n\nParagraf kedua."])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Paragraf pertama.', 'Paragraf kedua.'], $mentor->fresh()->profileParagraphs());
        $this->get(route('mentors'))->assertOk()->assertSee('Paragraf kedua.');
    }
}
