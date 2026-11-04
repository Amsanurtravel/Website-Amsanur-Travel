<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Support\StructuredData;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ArticleController extends Controller
{
    private const PER_PAGE = 9;

    public function index(): View
    {
        $articles = Article::published()
            ->with('category')
            ->latest('published_at')
            ->simplePaginate(self::PER_PAGE)
            ->withPath(route('articles.index'));

        abort_if($articles->isEmpty() && $articles->currentPage() > 1, 404);

        return view('articles.index', [
            'articles' => $articles,
            'category' => null,
            'categories' => $this->categories(),
            'canonical' => $this->canonical($articles, route('articles.index')),
        ]);
    }

    public function category(Category $category): View
    {
        $articles = $category->articles()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->simplePaginate(self::PER_PAGE)
            ->withPath(route('articles.category', $category));

        // A category without published articles would be a thin page.
        abort_if($articles->isEmpty(), 404);

        return view('articles.index', [
            'articles' => $articles,
            'category' => $category,
            'categories' => $this->categories(),
            'canonical' => $this->canonical($articles, route('articles.category', $category)),
        ]);
    }

    public function show(Article $article): View
    {
        $article->load('category');

        // Same-category articles first, then the newest others.
        $related = Article::published()
            ->with('category')
            ->whereKeyNot($article->getKey())
            ->latest('published_at')
            ->limit(12)
            ->get()
            ->sortByDesc(fn (Article $other) => $other->category_id === $article->category_id)
            ->take(3);

        return view('articles.show', [
            'article' => $article,
            'related' => $related,
            'schema' => $this->schema($article),
        ]);
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return Category::query()
            ->whereHas('articles', fn (Builder $query) => $query->published())
            ->orderBy('name')
            ->get();
    }

    private function canonical(Paginator $articles, string $firstPageUrl): string
    {
        return $articles->currentPage() > 1 ? $articles->url($articles->currentPage()) : $firstPageUrl;
    }

    /**
     * Structured data for search engines: the article itself and its breadcrumb.
     *
     * @return list<array<string, mixed>>
     */
    private function schema(Article $article): array
    {
        $url = route('articles.show', $article);

        // One organisation node for publisher and the author fallback, so search engines see one entity.
        $organization = ['@type' => 'TravelAgency', '@id' => StructuredData::organizationId(), 'name' => config('site.brand'), 'url' => route('home')];

        $posting = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $article->title,
            'description' => $article->metaDescription(),
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'datePublished' => $article->published_at->toAtomString(),
            'dateModified' => $article->modifiedAt()->toAtomString(),
            'author' => $article->author_name ? ['@type' => 'Person', 'name' => $article->author_name] : $organization,
            'publisher' => $organization,
            'image' => [$article->coverUrl() ?: asset('images/og-amsa-nur.jpg')],
        ];

        $trail = [['Beranda', route('home')], ['Artikel', route('articles.index')]];

        if ($article->category) {
            $trail[] = [$article->category->name, route('articles.category', $article->category)];
        }

        $trail[] = [$article->title, $url];

        return [$posting, StructuredData::breadcrumbs($trail)];
    }
}
