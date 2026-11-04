<?php

namespace Tests\Feature;

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Filament::setCurrentPanel('admin');
    }

    private function signIn(): User
    {
        return tap(User::factory()->create(), fn (User $user) => $this->actingAs($user));
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/articles')->assertRedirect('/admin/login');
        $this->get('/admin/categories')->assertRedirect('/admin/login');
    }

    public function test_the_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_staff_can_open_the_admin_pages(): void
    {
        $this->signIn();
        $article = Article::factory()->create();

        $this->get('/admin')->assertOk();
        $this->get('/admin/articles')->assertOk();
        $this->get('/admin/articles/create')->assertOk();
        $this->get("/admin/articles/{$article->getKey()}/edit")->assertOk();
        $this->get('/admin/categories')->assertOk();
    }

    public function test_staff_can_see_drafts_and_published_articles_in_the_list(): void
    {
        $this->signIn();
        $articles = collect([
            Article::factory()->create(),
            Article::factory()->draft()->create(),
            Article::factory()->scheduled()->create(),
        ]);

        Livewire::test(ListArticles::class)->assertCanSeeTableRecords($articles);
    }

    public function test_staff_can_create_a_draft_with_a_generated_slug(): void
    {
        $this->signIn();

        Livewire::test(CreateArticle::class)
            ->fillForm(['title' => 'Syarat Umrah', 'body' => 'Isi **tebal**.'])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::firstWhere('title', 'Syarat Umrah');

        $this->assertSame('syarat-umrah', $article->slug);
        $this->assertNull($article->published_at);
        $this->assertStringContainsString('<strong>tebal</strong>', $article->body_html);
    }

    public function test_html_in_the_body_is_dropped_when_saved(): void
    {
        $this->signIn();

        Livewire::test(CreateArticle::class)
            ->fillForm(['title' => 'Coba', 'body' => "Halo <script>alert('x')</script>"])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertStringNotContainsString('<script', Article::firstWhere('title', 'Coba')->body_html);
    }

    public function test_title_and_body_are_required(): void
    {
        $this->signIn();

        Livewire::test(CreateArticle::class)
            ->fillForm(['title' => null, 'body' => null])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'body' => 'required']);
    }

    public function test_slug_must_be_unique_and_well_formed(): void
    {
        $this->signIn();
        Article::factory()->create(['slug' => 'sudah-ada']);

        Livewire::test(CreateArticle::class)
            ->fillForm(['title' => 'Baru', 'body' => 'Isi', 'slug' => 'sudah-ada'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);

        Livewire::test(CreateArticle::class)
            ->fillForm(['title' => 'Baru', 'body' => 'Isi', 'slug' => 'Tidak Valid!'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'regex']);
    }

    public function test_cover_needs_alt_text_and_its_size_is_recorded(): void
    {
        Storage::fake('public');
        $this->signIn();

        $cover = fn () => UploadedFile::fake()->image('cover.jpg', 1200, 675);

        Livewire::test(CreateArticle::class)
            ->fillForm(['title' => 'Dengan sampul', 'body' => 'Isi', 'cover_path' => $cover()])
            ->call('create')
            ->assertHasFormErrors(['cover_alt' => 'required']);

        Livewire::test(CreateArticle::class)
            ->fillForm(['title' => 'Dengan sampul', 'body' => 'Isi', 'cover_path' => $cover(), 'cover_alt' => 'Jemaah berfoto'])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::firstWhere('title', 'Dengan sampul');

        Storage::disk('public')->assertExists($article->cover_path);
        $this->assertSame(1200, $article->cover_width);
        $this->assertSame(675, $article->cover_height);
    }

    public function test_staff_can_publish_by_setting_a_time(): void
    {
        $this->signIn();
        $article = Article::factory()->draft()->create();

        Livewire::test(EditArticle::class, ['record' => $article->getKey()])
            ->fillForm(['published_at' => now()->subHour()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('published', $article->fresh()->status);
    }

    public function test_staff_can_add_a_category_and_it_gets_a_slug(): void
    {
        $this->signIn();

        Livewire::test(CreateCategory::class)
            ->fillForm(['name' => 'Panduan Umrah'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('panduan-umrah', Category::firstWhere('name', 'Panduan Umrah')->slug);
    }

    public function test_public_pages_still_send_no_cookies_with_the_admin_installed(): void
    {
        $article = Article::factory()->create();

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertHeaderMissing('Set-Cookie')
            ->assertDontSee('livewire', false);
    }
}
