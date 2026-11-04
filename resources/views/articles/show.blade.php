<x-layouts.site
    :title="$article->metaTitle()"
    :description="$article->metaDescription()"
    :canonical="route('articles.show', $article)"
    :image="$article->coverUrl()"
    type="article"
    :published="$article->published_at"
    :modified="$article->modifiedAt()"
    :schema="$schema"
>
    <article class="mx-auto max-w-3xl px-4 py-8 md:py-12">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
            <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <li><a href="{{ url('/') }}" class="hover:underline">Beranda</a></li>
                <li aria-hidden="true">&rsaquo;</li>
                <li><a href="{{ route('articles.index') }}" class="hover:underline">Artikel</a></li>
                @if ($article->category)
                    <li aria-hidden="true">&rsaquo;</li>
                    <li><a href="{{ route('articles.category', $article->category) }}" class="hover:underline">{{ $article->category->name }}</a></li>
                @endif
            </ol>
        </nav>

        <h1 class="mt-4 text-3xl leading-tight font-bold text-brand-dark md:text-4xl">{{ $article->title }}</h1>

        <p class="mt-3 text-sm text-slate-500">
            <time datetime="{{ $article->published_at->toAtomString() }}">{{ $article->published_at->translatedFormat('j F Y') }}</time>
            @if ($article->wasUpdatedAfterPublishing())
                &middot; Diperbarui <time datetime="{{ $article->updated_at->toAtomString() }}">{{ $article->updated_at->translatedFormat('j F Y') }}</time>
            @endif
            &middot; {{ $article->readingMinutes() }} menit baca
        </p>

        @if ($article->author_name || $article->reviewer_name)
            <p class="mt-1 text-sm text-slate-500">
                @if ($article->author_name)
                    Ditulis oleh {{ $article->author_name }}
                @endif
                @if ($article->reviewer_name)
                    @if ($article->author_name)&middot;@endif
                    Ditinjau oleh {{ $article->reviewer_name }}
                @endif
            </p>
        @endif

        {{-- The cover is usually the largest element on screen, so it loads eagerly and with priority. --}}
        @if ($article->coverUrl())
            <img src="{{ $article->coverUrl() }}" alt="{{ $article->cover_alt }}" width="{{ $article->cover_width }}" height="{{ $article->cover_height }}" fetchpriority="high" decoding="async" class="mt-6 h-auto w-full rounded-xl object-cover">
        @endif

        <div class="article-body mt-8">{!! $article->body_html !!}</div>

        @if (config('site.whatsapp'))
            <aside class="mt-12 rounded-xl border border-brand/20 bg-brand/5 p-5">
                <p class="font-semibold text-brand-dark">Ada pertanyaan seputar paket umrah dan haji?</p>
                <p class="mt-1 text-sm text-slate-600">Tim {{ config('app.name') }} siap membantu lewat WhatsApp.</p>
                <x-whatsapp-link class="mt-4 inline-flex items-center rounded-full bg-brand px-5 py-3 text-sm font-semibold text-white hover:bg-brand-dark">Tanya lewat WhatsApp</x-whatsapp-link>
            </aside>
        @endif
    </article>

    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-5xl px-4" aria-labelledby="artikel-lain">
            <h2 id="artikel-lain" class="text-2xl font-bold text-brand-dark">Artikel lainnya</h2>

            <ul class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $other)
                    <li><x-article-card :article="$other" heading-level="h3" /></li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.site>
