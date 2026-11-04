@props(['paginator'])

@if ($paginator->hasPages())
    <nav aria-label="Halaman" class="mt-10 flex items-center justify-between gap-4 text-sm font-semibold">
        @unless ($paginator->onFirstPage())
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-full border border-slate-300 px-5 py-3 text-slate-700 hover:border-brand">&larr; Sebelumnya</a>
        @else
            <span></span>
        @endunless

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-full border border-slate-300 px-5 py-3 text-slate-700 hover:border-brand">Berikutnya &rarr;</a>
        @endif
    </nav>
@endif
