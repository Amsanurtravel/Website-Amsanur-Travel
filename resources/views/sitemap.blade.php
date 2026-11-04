{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($pages as $page)
        <url><loc>{{ $page }}</loc></url>
    @endforeach
    @foreach ($packages as $package)
        <url>
            <loc>{{ route('packages.show', $package) }}</loc>
            <lastmod>{{ $package->modifiedAt()->toAtomString() }}</lastmod>
        </url>
    @endforeach
    @foreach ($categories as $category)
        <url><loc>{{ route('articles.category', $category) }}</loc></url>
    @endforeach
    @foreach ($articles as $article)
        <url>
            <loc>{{ route('articles.show', $article) }}</loc>
            <lastmod>{{ $article->modifiedAt()->toAtomString() }}</lastmod>
        </url>
    @endforeach
</urlset>
