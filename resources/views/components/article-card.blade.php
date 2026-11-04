@props(['article', 'headingLevel' => 'h2'])

<article class="flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white">
    @if ($article->coverUrl())
        <a href="{{ route('articles.show', $article) }}" tabindex="-1" aria-hidden="true">
            <img src="{{ $article->coverUrl() }}" alt="" width="{{ $article->cover_width }}" height="{{ $article->cover_height }}" loading="lazy" decoding="async" class="aspect-video w-full object-cover">
        </a>
    @endif

    <div class="flex flex-1 flex-col p-4">
        @if ($article->category)
            <p class="text-xs font-semibold tracking-wide text-brand uppercase">{{ $article->category->name }}</p>
        @endif

        <{{ $headingLevel }} class="mt-1 text-lg leading-snug font-bold text-slate-900">
            <a href="{{ route('articles.show', $article) }}" class="hover:underline">{{ $article->title }}</a>
        </{{ $headingLevel }}>

        @if ($article->excerpt)
            <p class="mt-2 text-sm text-slate-600">{{ $article->excerpt }}</p>
        @endif

        <p class="mt-auto pt-4 text-xs text-slate-500">
            <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->translatedFormat('j F Y') }}</time>
            &middot; {{ $article->readingMinutes() }} menit baca
        </p>
    </div>
</article>
