@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'published' => null,
    'modified' => null,
    'noindex' => false,
    'schema' => [],
    'floatingWhatsapp' => true,
    'package' => null,
])

@php
    $siteName = config('app.name');
    $canonical ??= url()->current();
    $image ??= asset('images/og-amsa-nur.jpg');

    // "Perwakilan" and "Artikel" stay out of the menu until they have something in them, so nobody lands on an empty page.
    $nav = array_values(array_filter([
        ['Umroh', route('umrah.index'), 'umrah.*'],
        ['Haji Khusus', route('haji.index'), 'haji.*'],
        ['Legalitas', route('legal'), 'legal'],
        \App\Models\Representative::active()->exists() ? ['Perwakilan', route('representatives'), 'representatives'] : null,
        \App\Models\Article::published()->exists() ? ['Artikel', route('articles.index'), 'articles.*'] : null,
        ['Tentang Kami', route('about'), 'about'],
    ]));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' | '.$siteName : config('site.brand') }}</title>
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif
        @if ($noindex)
            <meta name="robots" content="noindex, follow">
        @endif
        <link rel="canonical" href="{{ $canonical }}">
        <x-favicons />
        <meta name="theme-color" content="#0f2a5a">

        <meta property="og:site_name" content="{{ config('site.brand') }}">
        <meta property="og:locale" content="id_ID">
        <meta property="og:type" content="{{ $type }}">
        <meta property="og:title" content="{{ $title ?? config('site.brand') }}">
        @if ($description)
            <meta property="og:description" content="{{ $description }}">
        @endif
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $image }}">
        @if ($type === 'article' && $published)
            <meta property="article:published_time" content="{{ $published->toAtomString() }}">
        @endif
        @if ($type === 'article' && $modified)
            <meta property="article:modified_time" content="{{ $modified->toAtomString() }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">

        @foreach ($schema as $block)
            <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
        @endforeach

        @vite('resources/css/app.css')

        @if ($ga4 = config('site.ga4_id'))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 }}"></script>
            <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@json($ga4));</script>
        @endif
    </head>
    <body id="atas" class="flex min-h-screen flex-col bg-white text-base text-slate-800 antialiased">
        <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-3 focus:font-semibold focus:text-brand-dark focus:shadow">Lewati ke konten</a>

        <header>
            {{-- Licence proof on every page, first thing on a phone screen. --}}
            <div class="bg-brand-dark text-white">
                <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-center gap-x-3 gap-y-1 px-4 py-2 text-[13px] leading-snug sm:justify-between">
                    <p class="flex items-center gap-1.5">
                        <x-site-icon name="shield-check" class="size-4 text-gold-light" />
                        <span>Berizin resmi:
                            @foreach (config('site.permits') as $permit)
                                <strong class="font-semibold text-gold-light">{{ $permit['short'] }}</strong>@unless ($loop->last)<span aria-hidden="true"> &middot; </span>@endunless
                            @endforeach
                        </span>
                    </p>
                    <a href="{{ route('legal') }}#cara-cek-izin" class="-my-3 inline-block py-3 font-semibold underline underline-offset-2 hover:text-gold-light">Cek keaslian izin</a>
                </div>
            </div>

            <div class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
                    <a href="{{ route('home') }}" class="shrink-0 rounded-lg" aria-label="{{ config('site.brand') }}, ke beranda">
                        <img src="{{ asset('images/amsa-nur-logo.png') }}" alt="" width="452" height="147" class="h-12 w-auto lg:h-14">
                    </a>

                    <nav aria-label="Utama" class="hidden items-center gap-1 text-[15px] font-semibold lg:flex">
                        @foreach ($nav as [$label, $url, $pattern])
                            <a href="{{ $url }}" @class(['rounded-full px-3 py-2.5 hover:bg-cream', 'text-brand' => request()->routeIs($pattern), 'text-slate-700' => ! request()->routeIs($pattern)]) @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a>
                        @endforeach
                        <x-whatsapp-link :package="$package" class="ml-2 inline-flex min-h-11 items-center gap-2 rounded-full bg-wa px-4 text-white hover:bg-wa-dark">
                            <x-site-icon name="whatsapp" />
                            WhatsApp
                        </x-whatsapp-link>
                    </nav>

                    <details class="site-menu relative lg:hidden">
                        <summary class="flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-full border border-slate-300 px-4 text-sm font-semibold text-brand-dark">
                            <x-site-icon name="menu" class="site-menu-open" />
                            <x-site-icon name="x-mark" class="site-menu-close" />
                            Menu
                        </summary>
                        <nav aria-label="Utama" class="absolute right-0 z-40 mt-2 w-72 max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                            <ul class="text-base font-semibold">
                                @foreach ($nav as [$label, $url, $pattern])
                                    <li>
                                        <a href="{{ $url }}" @class(['flex items-center justify-between rounded-xl px-4 py-3 hover:bg-cream', 'text-brand' => request()->routeIs($pattern), 'text-slate-800' => ! request()->routeIs($pattern)]) @if (request()->routeIs($pattern)) aria-current="page" @endif>
                                            {{ $label }}
                                            <x-site-icon name="chevron-right" class="size-4 text-slate-400" />
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                    </details>
                </div>
            </div>
        </header>

        <main id="konten" class="flex-1">
            {{ $slot }}
        </main>

        <footer class="bg-pattern text-slate-200 {{ $floatingWhatsapp ? 'pb-24 lg:pb-0' : 'pb-28 lg:pb-0' }}">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.6fr_auto]">
                <div>
                    <a href="{{ route('home') }}" class="inline-block rounded-xl bg-white p-2" aria-label="{{ config('site.brand') }}, ke beranda">
                        <img src="{{ asset('images/amsa-nur-logo.png') }}" alt="{{ config('site.brand') }}" width="452" height="147" loading="lazy" class="h-auto w-44">
                    </a>
                    <p class="mt-4 text-sm font-semibold text-white">{{ config('site.legal_name') }}</p>
                    <p class="mt-4 text-sm leading-relaxed text-slate-300 italic">&ldquo;{{ config('site.tagline') }}&rdquo;</p>
                </div>

                <div>
                    <p class="text-sm font-semibold tracking-wide text-gold-light uppercase">Izin resmi</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        @foreach (config('site.permits') as $permit)
                            <li><span class="font-semibold text-white">{{ $permit['code'] }}</span> Nomor {{ $permit['number'] }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('legal') }}#cara-cek-izin" class="mt-2 inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-gold-light underline underline-offset-2">Cara cek keaslian izin <x-site-icon name="arrow-right" class="size-4" /></a>
                </div>

                <div>
                    <p class="text-sm font-semibold tracking-wide text-gold-light uppercase">Hubungi kami</p>
                    <ul class="mt-3 space-y-1 text-sm">
                        <li>
                            <x-whatsapp-link class="inline-flex min-h-11 items-center gap-3 hover:text-white lg:min-h-9">
                                <x-site-icon name="whatsapp" class="size-4 text-gold-light" /> WhatsApp {{ config('site.phone') }}
                            </x-whatsapp-link>
                        </li>
                        @if (config('site.office.phone'))
                            <li><a href="tel:{{ preg_replace('/[^\d+]/', '', config('site.office.phone')) }}" class="inline-flex min-h-11 items-center gap-3 hover:text-white lg:min-h-9"><x-site-icon name="phone" class="size-4 text-gold-light" /> Kantor {{ config('site.office.phone') }}</a></li>
                        @endif
                        @if (config('site.email'))
                            <li><a href="mailto:{{ config('site.email') }}" class="inline-flex min-h-11 items-center gap-3 hover:text-white lg:min-h-9"><x-site-icon name="envelope" class="size-4 text-gold-light" /> {{ config('site.email') }}</a></li>
                        @endif
                        @if (config('site.office.address'))
                            <li class="flex gap-3 pt-3"><x-site-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-gold-light" /> <span class="leading-relaxed"><span class="font-semibold text-white">{{ config('site.office.name') }}</span><br>{{ config('site.office.address') }}</span></li>
                        @endif
                        @if (config('site.office.hours'))
                            <li class="flex gap-3 pt-3">
                                <x-site-icon name="clock" class="mt-0.5 size-4 shrink-0 text-gold-light" />
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-white">Jam buka kantor</p>
                                    <x-office-hours class="mt-1 text-slate-300" />
                                </div>
                            </li>
                        @endif
                    </ul>
                    <x-social-links class="mt-5" />
                </div>

                <div>
                    <p class="text-sm font-semibold tracking-wide text-gold-light uppercase">Menu</p>
                    <ul class="mt-2 text-sm">
                        <li><a href="{{ route('home') }}" class="inline-flex min-h-11 items-center hover:text-white lg:min-h-9">Beranda</a></li>
                        @foreach ($nav as [$label, $url])
                            <li><a href="{{ $url }}" class="inline-flex min-h-11 items-center hover:text-white lg:min-h-9">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/10">
                <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-xs text-slate-400 sm:flex-row sm:justify-between">
                    <p>&copy; {{ now()->year }} {{ config('site.legal_name') }}</p>
                    <p>Pembayaran kepada kami hanya ke rekening atas nama {{ config('site.legal_name') }}. Setoran BIPIH haji khusus ke rekening atas nama Menteri.</p>
                    <p><a href="{{ route('privacy') }}" class="-my-3 inline-block py-3 underline underline-offset-2 hover:text-white">Kebijakan privasi</a></p>
                </div>
            </div>
        </footer>

        @if ($floatingWhatsapp)
            <x-whatsapp-link class="fixed right-4 bottom-[max(1rem,env(safe-area-inset-bottom))] z-30 inline-flex size-14 items-center justify-center gap-2 rounded-full bg-wa font-semibold sm:size-auto sm:min-h-14 sm:px-5 text-white shadow-lg shadow-black/25 hover:bg-wa-dark" aria-label="Chat WhatsApp {{ config('site.brand') }}">
                <x-site-icon name="whatsapp" class="size-6" />
                <span class="sr-only sm:not-sr-only">Chat</span>
            </x-whatsapp-link>
        @endif

        {{-- Back to top: sits above the WhatsApp button, or above the package price bar on phones. Hidden until the visitor has scrolled. --}}
        <a href="#atas" id="ke-atas" class="back-to-top fixed z-30 inline-flex size-12 items-center justify-center rounded-full border border-gold-light/60 bg-brand-dark text-gold-light shadow-lg shadow-black/25 hover:bg-brand {{ $floatingWhatsapp ? 'right-5 sm:right-4 bottom-[calc(max(1rem,env(safe-area-inset-bottom))+4.5rem)]' : 'right-4 bottom-[calc(max(0.75rem,env(safe-area-inset-bottom))+5.5rem)] lg:bottom-6' }}" aria-label="Kembali ke atas">
            <x-site-icon name="arrow-up" class="size-5" />
        </a>
        <script>(()=>{const b=document.getElementById('ke-atas'),f=()=>b.classList.toggle('is-visible',scrollY>600);f();addEventListener('scroll',f,{passive:true})})()</script>
    </body>
</html>
