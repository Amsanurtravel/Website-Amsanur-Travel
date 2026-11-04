@props(['trail', 'tone' => 'text-slate-600'])

{{-- $trail: [label, url] pairs, home first; the current page is left out. $tone replaces the text colour (for dark backgrounds). --}}
<nav aria-label="Breadcrumb" {{ $attributes->class(['text-sm', $tone]) }}>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
        @foreach ($trail as [$label, $url])
            <li><a href="{{ $url }}" class="-my-3 inline-block py-3 hover:underline">{{ $label }}</a></li>
            @unless ($loop->last)
                <li aria-hidden="true">&rsaquo;</li>
            @endunless
        @endforeach
    </ol>
</nav>
