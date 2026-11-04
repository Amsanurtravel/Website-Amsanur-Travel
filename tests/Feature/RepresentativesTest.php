<?php

namespace Tests\Feature;

use App\Filament\Resources\Representatives\Pages\CreateRepresentative;
use App\Filament\Resources\Representatives\Pages\ListRepresentatives;
use App\Models\Representative;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Perwakilan daerah: the regional representatives page, its menu entry and the admin.
 */
class RepresentativesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_the_profile_representatives_are_listed_by_region(): void
    {
        $this->seed(ContentSeeder::class);

        $this->assertSame(15, Representative::count());

        $this->get(route('representatives'))
            ->assertOk()
            ->assertSeeInOrder(['DKI Jakarta', 'H. Jaetul Muchlis', 'Ida Rosa', 'Jawa Tengah', 'KBIH Al Wardah', 'Madura', 'H. Fahmi Fattah', 'Papua', 'Kumaya Sari'])
            ->assertSee('Kalimantan Timur')
            ->assertSee('href="#wilayah-kalimantan-timur"', false)
            ->assertSee('rekening atas nama PT Amsa Nur Indah Mandiri')
            ->assertSee(route('whatsapp', ['dari' => 'perwakilan']), false);
    }

    public function test_hidden_representatives_are_not_shown(): void
    {
        Representative::factory()->create(['region' => 'Bali', 'name' => 'Perwakilan Aktif']);
        Representative::factory()->create(['region' => 'Bali', 'name' => 'Sudah Berhenti', 'is_active' => false]);

        $this->get(route('representatives'))
            ->assertOk()
            ->assertSee('Perwakilan Aktif')
            ->assertDontSee('Sudah Berhenti');
    }

    public function test_the_menu_and_sitemap_list_the_page_only_when_someone_is_listed(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee(route('representatives'));
        $this->get(route('sitemap'))->assertOk()->assertDontSee(route('representatives'));
        $this->get(route('representatives'))->assertOk()->assertSee('Daftar perwakilan daerah sedang kami perbarui');

        Representative::factory()->create();

        $this->get(route('home'))->assertOk()->assertSee('>Perwakilan</a>', false)->assertSee(route('representatives'));
        $this->get(route('sitemap'))->assertOk()->assertSee(route('representatives'));
        $this->get(route('legal'))->assertOk()->assertSee('Perwakilan resmi kami di daerah');
    }

    public function test_the_whatsapp_button_on_the_page_asks_about_representatives(): void
    {
        $this->get(route('whatsapp', ['dari' => 'perwakilan']))
            ->assertRedirectContains('https://wa.me/6282112220576?text=')
            ->assertRedirectContains(rawurlencode('perwakilan resmi'));

        $this->assertDatabaseHas('whatsapp_clicks', ['source' => 'perwakilan']);
    }

    public function test_admin_can_add_and_list_representatives(): void
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel('admin');

        Livewire::test(CreateRepresentative::class)
            ->fillForm(['region' => 'Jawa Barat', 'name' => 'H. Uji Coba', 'address' => 'Jl. Contoh No. 1, Bandung', 'phone' => '0812-0000-0000'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('representatives', ['region' => 'Jawa Barat', 'name' => 'H. Uji Coba', 'is_active' => true]);

        Livewire::test(ListRepresentatives::class)->assertOk()->assertSee('H. Uji Coba');

        $this->get(route('representatives'))->assertOk()->assertSee('Jawa Barat')->assertSee('tel:081200000000', false);
    }
}
