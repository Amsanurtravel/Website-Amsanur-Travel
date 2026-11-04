@php
    $heading = $category?->name ?? 'Artikel';
    $page = $articles->currentPage();
    $pageSuffix = $page > 1 ? ' - Halaman '.$page : '';
    $intro = $category?->description
        ?: ($category
            ? 'Kumpulan artikel '.$category->name.' seputar umrah dan haji dari '.config('app.name').'.'
            : 'Panduan dan informasi seputar umrah dan haji dari '.config('app.name').'.');

    $chip = 'rounded-full border px-4 py-3';
    $chipOn = 'border-brand bg-brand text-white';
    $chipOff = 'border-slate-300 text-slate-700 hover:border-brand';
@endphp

<x-layouts.site
    :title="($category ? $category->name.' - Artikel' : 'Artikel').$pageSuffix"
    :description="$page > 1 ? 'Halaman '.$page.'. '.$intro : $intro"
    :canonical="$canonical"
    :noindex="$articles->isEmpty()"
>
    <div class="mx-auto max-w-5xl px-4 py-8 md:py-12">
        <h1 class="text-3xl font-bold text-brand-dark md:text-4xl">{{ $heading }}</h1>
        <p class="mt-2 max-w-2xl text-slate-600">{{ $intro }}</p>

        @if ($categories->isNotEmpty())
            <nav aria-label="Kategori artikel" class="mt-6 flex flex-wrap gap-2 text-sm font-semibold">
                <a href="{{ route('articles.index') }}" @class([$chip, $chipOn => ! $category, $chipOff => (bool) $category]) @if (! $category) aria-current="page" @endif>Semua</a>

                @foreach ($categories as $item)
                    <a href="{{ route('articles.category', $item) }}" @class([$chip, $chipOn => $category?->is($item), $chipOff => ! $category?->is($item)]) @if ($category?->is($item)) aria-current="page" @endif>{{ $item->name }}</a>
                @endforeach
            </nav>
        @endif

        @if ($articles->isEmpty())
            <p class="mt-10 text-slate-600">Belum ada artikel.</p>
        @else
            <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <li><x-article-card :article="$article" /></li>
                @endforeach
            </ul>

            <x-pager :paginator="$articles" />
        @endif
    </div>
</x-layouts.site>
