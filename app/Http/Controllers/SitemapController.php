<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Mentor;
use App\Models\Package;
use App\Models\Representative;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $articles = Article::published()
            ->latest('published_at')
            ->get(['id', 'slug', 'published_at', 'updated_at']);

        return response()
            ->view('sitemap', [
                // The article index, representatives and mentor pages are listed only once they have something in them.
                'pages' => array_values(array_filter([
                    route('home'),
                    route('umrah.index'),
                    route('haji.index'),
                    route('legal'),
                    route('about'),
                    Representative::active()->exists() ? route('representatives') : null,
                    Mentor::active()->exists() ? route('mentors') : null,
                    route('privacy'),
                    $articles->isNotEmpty() ? route('articles.index') : null,
                ])),
                // Departed packages stay reachable for old links but are noindex, so they are left out.
                'packages' => Package::published()
                    ->upcoming()
                    ->inDepartureOrder()
                    ->get(['id', 'slug', 'published_at', 'updated_at']),
                'categories' => Category::query()
                    ->whereHas('articles', fn (Builder $query) => $query->published())
                    ->orderBy('slug')
                    ->get(['id', 'slug']),
                'articles' => $articles,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
