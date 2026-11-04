{{-- Social media from config('site.socials') as pills with their icons, for dark backgrounds. --}}
@if ($socials = config('site.socials'))
    <ul {{ $attributes->class('flex flex-wrap gap-2 text-sm') }} aria-label="Media sosial">
        @foreach ($socials as $network => $url)
            <li>
                <a href="{{ $url }}" class="inline-flex min-h-11 items-center gap-2 rounded-full border border-white/30 px-4 font-semibold text-white hover:bg-white/10" rel="noopener" target="_blank">
                    <x-site-icon :name="Str::lower($network)" class="size-4" />
                    {{ $network }}
                </a>
            </li>
        @endforeach
    </ul>
@endif
