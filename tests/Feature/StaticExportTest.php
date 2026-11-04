<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Mentor;
use App\Models\Package;
use App\Models\WhatsappClick;
use App\Support\WhatsApp;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

/**
 * The static copy of the public site for the client preview on Vercel
 * (php artisan site:export), and the 404 page it ships with.
 */
class StaticExportTest extends TestCase
{
    use RefreshDatabase;

    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->folder = storage_path('framework/testing/ekspor-'.Str::lower(Str::random(8)));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->folder);

        parent::tearDown();
    }

    public function test_the_public_site_is_exported_as_static_files(): void
    {
        $this->seed(ContentSeeder::class);
        $next = Package::published()->upcoming()->inDepartureOrder()->first();

        $this->export()->assertSuccessful();

        foreach (['index', 'umroh', 'haji-khusus', 'legalitas', 'tentang-kami', 'perwakilan', 'pembimbing', 'kebijakan-privasi', 'paket/'.$next->slug] as $page) {
            $this->assertFileExists("{$this->folder}/{$page}.html");
        }

        foreach (['favicon.ico', 'apple-touch-icon.png', 'images/og-amsa-nur.jpg', 'images/amsa-nur-logo.png', 'images/jemaah/ziarah-madinah-480.webp'] as $file) {
            $this->assertFileEquals(public_path($file), "{$this->folder}/{$file}");
        }

        // WhatsApp buttons skip the click counter: there is no server behind a static copy.
        $home = File::get("{$this->folder}/index.html");
        $this->assertStringContainsString('href="https://pratinjau.test/umroh"', $home);
        $this->assertStringNotContainsString('https://pratinjau.test/wa', $home);
        $this->assertStringContainsString('https://wa.me/'.WhatsApp::number(config('site.whatsapp')).'?text=', $home);
        $this->assertSame(0, WhatsappClick::count());

        $this->assertStringContainsString('Disallow: /', File::get("{$this->folder}/robots.txt"));
        $this->assertStringContainsString('<loc>https://pratinjau.test/legalitas</loc>', File::get("{$this->folder}/sitemap.xml"));
        $this->assertStringContainsString('Halaman tidak ditemukan', File::get("{$this->folder}/404.html"));
        $this->assertFileDoesNotExist("{$this->folder}.partial");
    }

    public function test_uploaded_photos_are_copied_with_the_preview_address(): void
    {
        $photo = 'mentors/uji-ekspor-'.Str::lower(Str::random(6)).'.jpg';
        File::ensureDirectoryExists(storage_path('app/public/mentors'));
        File::copy(public_path('images/og-amsa-nur.jpg'), storage_path("app/public/{$photo}"));

        try {
            Mentor::factory()->create(['photo_path' => $photo]);

            $this->export()->assertSuccessful();

            $this->assertStringContainsString("https://pratinjau.test/storage/{$photo}", File::get("{$this->folder}/pembimbing.html"));
            $this->assertFileEquals(storage_path("app/public/{$photo}"), "{$this->folder}/storage/{$photo}");
        } finally {
            File::delete(storage_path("app/public/{$photo}"));
        }
    }

    public function test_umrah_filters_become_pages_of_their_own(): void
    {
        // 35 days apart, so always two different months.
        $soon = Package::factory()->create(['departure_date' => today()->addDays(10)]);
        Package::factory()->create(['departure_date' => today()->addDays(45)]);
        $month = $soon->departure_date->format('Y-m');

        $this->export()->assertSuccessful();

        $umrah = File::get("{$this->folder}/umroh.html");
        $this->assertStringContainsString("href=\"https://pratinjau.test/umroh/filter/bulan-{$month}\"", $umrah);
        $this->assertStringContainsString('href="https://pratinjau.test/umroh/filter/urut-harga"', $umrah);
        $this->assertStringNotContainsString('umroh?', $umrah);

        $filtered = File::get("{$this->folder}/umroh/filter/bulan-{$month}.html");
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $filtered);
        $this->assertStringContainsString($soon->name, $filtered);
        $this->assertFileExists("{$this->folder}/umroh/filter/urut-harga.html");
    }

    public function test_a_new_export_replaces_the_previous_one(): void
    {
        $this->export()->assertSuccessful();
        File::put("{$this->folder}/sisa-lama.html", 'lama');

        $this->export()->assertSuccessful();

        $this->assertFileExists("{$this->folder}/index.html");
        $this->assertFileDoesNotExist("{$this->folder}/sisa-lama.html");
    }

    public function test_a_folder_that_is_not_an_export_is_never_replaced(): void
    {
        File::ensureDirectoryExists($this->folder);
        File::put("{$this->folder}/catatan.txt", 'penting');

        $this->export()->assertFailed();

        $this->assertFileExists("{$this->folder}/catatan.txt");
        $this->assertFileDoesNotExist("{$this->folder}/index.html");
    }

    public function test_placeholder_articles_never_reach_the_preview(): void
    {
        $this->seed(ArticleSeeder::class);

        $this->export()->assertFailed();
        $this->assertDirectoryDoesNotExist($this->folder);

        // Back to drafts, as in the admin: the export goes ahead.
        Article::where('slug', 'like', 'contoh-%')->update(['published_at' => null]);
        $this->export()->assertSuccessful();
    }

    public function test_an_invalid_address_is_refused(): void
    {
        $this->artisan('site:export', ['--url' => 'pratinjau-tanpa-https', '--path' => $this->folder])->assertFailed();

        $this->assertDirectoryDoesNotExist($this->folder);
    }

    public function test_missing_pages_show_the_site_404_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee(route('umrah.index'), false);
    }

    private function export(): PendingCommand
    {
        return $this->artisan('site:export', ['--url' => 'https://pratinjau.test', '--path' => $this->folder]);
    }
}
