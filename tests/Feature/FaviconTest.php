<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaviconTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_every_kind_of_page_declares_the_icons(): void
    {
        $article = Article::factory()->create();

        foreach ([
            url('/'),
            route('articles.index'),
            route('articles.show', $article),
            url('/admin/login'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('/favicon.ico?v=', false)
                ->assertSee('/apple-touch-icon.png?v=', false);
        }
    }

    public function test_the_icon_files_are_real_and_small(): void
    {
        $ico = public_path('favicon.ico');
        $apple = public_path('apple-touch-icon.png');

        $this->assertFileExists($ico);
        $this->assertSame("\x00\x00\x01\x00", file_get_contents($ico, length: 4), 'favicon.ico is not an ICO file.');
        $this->assertLessThanOrEqual(15 * 1024, filesize($ico), 'favicon.ico is over 15 KB: compress it.');

        $this->assertFileExists($apple);
        $this->assertSame([180, 180], array_slice(getimagesize($apple), 0, 2));
        $this->assertLessThanOrEqual(30 * 1024, filesize($apple), 'apple-touch-icon.png is over 30 KB: compress it.');
    }
}
