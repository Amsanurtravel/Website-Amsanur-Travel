<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_index_lists_only_published_articles(): void
    {
        Article::factory()->create(['title' => 'Artikel terbit']);
        Article::factory()->draft()->create(['title' => 'Artikel draf']);
        Article::factory()->scheduled()->create(['title' => 'Artikel terjadwal']);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee('Artikel terbit')
            ->assertDontSee('Artikel draf')
            ->assertDontSee('Artikel terjadwal');
    }

    public function test_index_is_paginated_and_out_of_range_pages_are_not_found(): void
    {
        Article::factory()->count(10)->create();

        $this->get(route('articles.index'))->assertOk()->assertSee('rel="next"', false);
        $this->get(route('articles.index', ['page' => 2]))->assertOk()->assertSee('rel="prev"', false);
        $this->get(route('articles.index', ['page' => 3]))->assertNotFound();
    }

    public function test_show_renders_seo_tags_and_structured_data(): void
    {
        $category = Category::factory()->create(['name' => 'Panduan Umrah']);
        $article = Article::factory()->for($category)->create([
            'title' => 'Dokumen sebelum berangkat',
            'seo_title' => 'Dokumen Umrah yang Perlu Disiapkan',
            'seo_description' => 'Daftar dokumen untuk calon jemaah.',
        ]);

        $url = route('articles.show', $article);

        $this->get($url)
            ->assertOk()
            ->assertSee('<title>Dokumen Umrah yang Perlu Disiapkan | Amsa Nur</title>', false)
            ->assertSee('<link rel="canonical" href="'.$url.'">', false)
            ->assertSee('<meta name="description" content="Daftar dokumen untuk calon jemaah.">', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('<html lang="id">', false);
    }

    public function test_cover_image_is_sized_and_prioritised(): void
    {
        $article = Article::factory()->create([
            'cover_path' => 'articles/cover.webp',
            'cover_alt' => 'Jemaah di Masjidil Haram',
            'cover_width' => 1200,
            'cover_height' => 675,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('width="1200" height="675" fetchpriority="high"', false)
            ->assertSee('alt="Jemaah di Masjidil Haram"', false);
    }

    public function test_cover_size_is_read_from_the_stored_file(): void
    {
        Storage::fake('public');
        UploadedFile::fake()->image('cover.jpg', 800, 450)->storeAs('articles', 'cover.jpg', 'public');

        $article = Article::factory()->create(['cover_path' => 'articles/cover.jpg']);

        $this->assertSame(800, $article->cover_width);
        $this->assertSame(450, $article->cover_height);

        $article->update(['cover_path' => null]);

        $this->assertNull($article->cover_width);
        $this->assertNull($article->cover_height);
    }

    public function test_status_follows_published_at(): void
    {
        $this->assertSame('draft', Article::factory()->draft()->make()->status);
        $this->assertSame('scheduled', Article::factory()->scheduled()->make()->status);
        $this->assertSame('published', Article::factory()->make()->status);
    }

    public function test_drafts_and_scheduled_articles_are_not_found(): void
    {
        $draft = Article::factory()->draft()->create();
        $scheduled = Article::factory()->scheduled()->create();

        $this->get(route('articles.show', $draft))->assertNotFound();
        $this->get(route('articles.show', $scheduled))->assertNotFound();
    }

    public function test_category_page_lists_its_published_articles_only(): void
    {
        $category = Category::factory()->create();
        Article::factory()->for($category)->create(['title' => 'Milik kategori']);
        Article::factory()->create(['title' => 'Kategori lain']);

        $this->get(route('articles.category', $category))
            ->assertOk()
            ->assertSee('Milik kategori')
            ->assertDontSee('Kategori lain');
    }

    public function test_category_without_published_articles_is_not_found(): void
    {
        $category = Category::factory()->create();
        Article::factory()->draft()->for($category)->create();

        $this->get(route('articles.category', $category))->assertNotFound();
    }

    public function test_markdown_is_sanitised(): void
    {
        $article = Article::factory()->create([
            'body' => "Halo <script>alert('x')</script>\n\n[klik](javascript:alert(1))\n\n<div onclick=\"x()\">blok</div>",
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('javascript:', false)
            ->assertDontSee('onclick', false);
    }

    public function test_body_html_follows_body_and_images_are_lazy(): void
    {
        $article = Article::factory()->create(['body' => '![Foto](/foto.jpg)']);

        $this->assertStringContainsString('<img loading="lazy" decoding="async" src="/foto.jpg"', $article->body_html);

        $article->update(['body' => 'Teks baru']);

        $this->assertStringContainsString('Teks baru', $article->fresh()->body_html);
        $this->assertStringNotContainsString('foto.jpg', $article->fresh()->body_html);
    }

    public function test_slug_is_generated_and_kept_unique(): void
    {
        $first = Article::factory()->create(['title' => 'Judul Sama', 'slug' => null]);
        $second = Article::factory()->create(['title' => 'Judul Sama', 'slug' => null]);

        $this->assertSame('judul-sama', $first->slug);
        $this->assertSame('judul-sama-2', $second->slug);

        $first->update(['title' => 'Judul Diubah']);

        $this->assertSame('judul-sama', $first->fresh()->slug);
    }

    public function test_sitemap_lists_published_articles_only(): void
    {
        $published = Article::factory()->create();
        $draft = Article::factory()->draft()->create();

        $response = $this->get(route('sitemap'))->assertOk();

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee(route('articles.show', $published), false);
        $response->assertDontSee($draft->slug, false);
    }

    public function test_public_pages_set_no_cookies_and_are_cacheable(): void
    {
        $article = Article::factory()->create();

        $response = $this->get(route('articles.show', $article))->assertOk();

        $response->assertHeaderMissing('Set-Cookie');
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=300', $response->headers->get('Cache-Control'));
        $this->assertNotNull($response->headers->get('ETag'));
    }

    public function test_conditional_request_gets_not_modified(): void
    {
        $article = Article::factory()->create();
        $url = route('articles.show', $article);

        $etag = $this->get($url)->headers->get('ETag');

        $this->get($url, ['If-None-Match' => $etag])->assertStatus(304);
    }

    public function test_errors_are_not_marked_cacheable(): void
    {
        $draft = Article::factory()->draft()->create();

        $response = $this->get(route('articles.show', $draft))->assertNotFound();

        $this->assertStringNotContainsString('public', (string) $response->headers->get('Cache-Control'));
    }

    public function test_whatsapp_cta_appears_only_when_configured_and_goes_through_the_click_counter(): void
    {
        $article = Article::factory()->create();
        $url = route('articles.show', $article);

        config(['site.whatsapp' => null]);
        $this->get($url)->assertDontSee(route('whatsapp'), false);

        config(['site.whatsapp' => '0812-3456-7890']);
        $this->get($url)->assertSee(route('whatsapp', ['dari' => 'artikel']), false);
    }

    public function test_every_page_shows_the_licence_numbers(): void
    {
        $article = Article::factory()->create();

        config(['site.permits.0.short' => 'PPIU 123/2026']);

        $this->get(route('articles.show', $article))->assertSee('PPIU 123/2026');
    }
}
