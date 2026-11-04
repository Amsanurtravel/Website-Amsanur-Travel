@php
    $next = $umrah->first();
    $permits = config('site.permits');
@endphp

<x-layouts.site
    title="Travel Umroh & Haji Khusus Berizin Resmi"
    :description="config('site.brand').', berizin PPIU dan PIHK ('.config('site.legal_name').'). Jadwal umroh dan haji khusus dengan harga per tipe kamar, hotel, dan maskapai yang jelas.'"
    :canonical="route('home')"
    :schema="$schema"
>
    {{-- Hero: name and WhatsApp on the first phone screen. The licences sit in the top bar above it. --}}
    <section class="bg-pattern text-white">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 md:py-16 lg:grid-cols-[1.25fr_1fr] lg:items-center">
            <div>
                <p class="text-sm font-semibold tracking-wide text-gold-light uppercase">Umroh &amp; Haji Khusus</p>
                <h1 class="mt-3 font-serif text-4xl leading-tight font-bold md:text-5xl">Beribadah ke Tanah Suci dengan tenang bersama Amsa Nur</h1>
                <p class="mt-4 max-w-xl text-lg leading-relaxed text-slate-200">{{ config('site.tagline') }}</p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('umrah.index') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-gold-light px-6 font-semibold text-brand-dark hover:bg-white">
                        Lihat jadwal umroh <x-site-icon name="arrow-right" class="size-4" />
                    </a>
                    <x-whatsapp-link class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-wa px-6 font-semibold text-white hover:bg-wa-dark">
                        <x-site-icon name="whatsapp" /> Tanya lewat WhatsApp
                    </x-whatsapp-link>
                </div>
            </div>

            @if ($next)
                <a href="{{ route('packages.show', $next) }}" class="group block rounded-t-[999px] rounded-b-3xl border border-gold-light/40 bg-white/5 px-6 pt-16 pb-6 text-center hover:bg-white/10">
                    <span class="text-sm font-semibold tracking-wide text-gold-light uppercase">Keberangkatan terdekat</span>
                    <span class="mt-3 block font-serif text-3xl font-bold">{{ $next->departure_date?->translatedFormat('j F Y') ?? $next->departure_note }}</span>
                    {{-- Most names already say "12 Hari"; only add the duration when they don't. --}}
                    <span class="mt-2 block text-slate-200">{{ $next->name }}@if ($next->durationLabel() && ! Str::contains(Str::lower($next->name), $next->duration_days.' hari')) &middot; {{ $next->durationLabel() }}@endif</span>
                    @if ($next->lowestPrice())
                        <span class="mt-4 block text-sm text-slate-300">Mulai</span>
                        <span class="block text-2xl font-bold text-gold-light">{{ $next->money($next->lowestPrice()) }}</span>
                        <span class="block text-sm text-slate-300">per orang</span>
                    @endif
                    <span class="mt-5 inline-flex items-center gap-1 font-semibold underline-offset-4 group-hover:underline">Lihat rincian paket <x-site-icon name="arrow-right" class="size-4" /></span>
                </a>
            @endif
        </div>
    </section>

    {{-- Upcoming umrah departures --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="jadwal-umroh">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Jadwal terdekat</p>
                <h2 id="jadwal-umroh" class="mt-1 font-serif text-3xl font-bold text-brand-dark">Paket umroh</h2>
            </div>
            @if ($umrahCount > $umrah->count())
                <a href="{{ route('umrah.index') }}" class="hidden min-h-11 items-center gap-1 font-semibold text-brand hover:underline sm:inline-flex">Semua {{ $umrahCount }} jadwal <x-site-icon name="arrow-right" class="size-4" /></a>
            @endif
        </div>

        @if ($umrah->isEmpty())
            <p class="mt-6 rounded-2xl bg-cream p-6 text-slate-700">Jadwal umroh berikutnya sedang disiapkan. Tanyakan jadwal terbaru lewat WhatsApp.</p>
        @else
            <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($umrah as $package)
                    <li><x-package-card :package="$package" /></li>
                @endforeach
            </ul>
            {{-- Phones: after scrolling past the cards, the next step is right there. --}}
            @if ($umrahCount > $umrah->count())
                <a href="{{ route('umrah.index') }}" class="mt-6 flex min-h-12 items-center justify-center gap-2 rounded-full border-2 border-brand px-6 font-semibold text-brand hover:bg-brand hover:text-white sm:hidden">Semua {{ $umrahCount }} jadwal <x-site-icon name="arrow-right" class="size-4" /></a>
            @endif
            <p class="mt-6 text-sm text-slate-600">Harga per orang. Rincian yang termasuk dan tidak termasuk ada di halaman setiap paket.</p>
        @endif
    </section>

    {{-- Haji khusus --}}
    <section class="bg-cream" aria-labelledby="haji-khusus">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 md:py-16 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Izin {{ collect($permits)->firstWhere('code', 'PIHK')['short'] ?? 'PIHK' }}</p>
                <h2 id="haji-khusus" class="mt-1 font-serif text-3xl font-bold text-brand-dark">Haji Khusus (Haji Plus)</h2>
                <p class="mt-4 leading-relaxed text-slate-700">Haji dengan kuota resmi pemerintah dan nomor porsi resmi atas nama jemaah. Syarat, setoran awal, dan perkiraan masa tunggunya kami jelaskan secara terbuka.</p>
                <a href="{{ route('haji.index') }}" class="mt-6 inline-flex min-h-12 items-center gap-2 rounded-full bg-brand px-6 font-semibold text-white hover:bg-brand-dark">Pelajari haji khusus <x-site-icon name="arrow-right" class="size-4" /></a>
            </div>

            @if ($haji)
                <div class="rounded-2xl border border-gold/40 bg-white p-6">
                    <p class="font-serif text-xl font-bold text-brand-dark">{{ $haji->name }}</p>
                    <ul class="mt-4 space-y-2 text-slate-700">
                        @foreach (array_slice($haji->listOf('highlights'), 0, 4) as $highlight)
                            <li class="flex gap-2"><x-site-icon name="check" class="mt-0.5 text-wa" /> {{ $highlight }}</li>
                        @endforeach
                    </ul>
                    @if ($haji->lowestPrice())
                        <p class="mt-5 border-t border-slate-100 pt-4"><span class="text-sm text-slate-500">Mulai</span> <span class="text-2xl font-bold text-brand-dark">{{ $haji->money($haji->lowestPrice()) }}</span> <span class="text-sm text-slate-500">per orang</span></p>
                    @endif
                    <a href="{{ route('packages.show', $haji) }}" class="mt-2 inline-flex min-h-11 items-center gap-1 font-semibold text-brand hover:underline">Rincian paket haji <x-site-icon name="arrow-right" class="size-4" /></a>
                </div>
            @endif
        </div>
    </section>

    {{-- Why Amsa Nur: only claims backed by the brochures, the licences or the company profile. --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="mengapa">
        <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Mengapa Amsa Nur?</p>
        <h2 id="mengapa" class="mt-1 font-serif text-3xl font-bold text-brand-dark">Yang kami pegang untuk setiap jemaah</h2>

        <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <li class="rounded-2xl border border-slate-200 p-5">
                <x-site-icon name="shield-check" class="size-8 text-brand" />
                <h3 class="mt-3 font-semibold text-brand-dark">Berizin sejak {{ config('site.founded') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">Berdiri sejak {{ config('site.founded') }}, berizin PPIU untuk umroh dan PIHK untuk haji khusus. Nomornya kami tampilkan, caranya mengecek kami jelaskan.</p>
            </li>
            <li class="rounded-2xl border border-slate-200 p-5">
                <x-site-icon name="building" class="size-8 text-brand" />
                <h3 class="mt-3 font-semibold text-brand-dark">Kantor sendiri di Jakarta</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">Kantor pusat kami menempati gedung milik sendiri di Jl. Pramuka Raya, Jakarta Pusat. Silakan datang dan berkonsultasi langsung dengan tim kami.</p>
            </li>
            <li class="rounded-2xl border border-slate-200 p-5">
                <x-site-icon name="users" class="size-8 text-brand" />
                <h3 class="mt-3 font-semibold text-brand-dark">Manasik sebelum berangkat</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">Manasik dan bermalam di hotel Jakarta sebelum terbang sudah termasuk dalam paket.</p>
            </li>
            <li class="rounded-2xl border border-slate-200 p-5">
                <x-site-icon name="banknotes" class="size-8 text-brand" />
                <h3 class="mt-3 font-semibold text-brand-dark">Harga terbuka</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">Harga per-tipe kamar, biaya tambahan, dan biaya termasuk serta biaya tidak termasuk lainnya sudah tertulis jelas di setiap paket.</p>
            </li>
        </ul>
    </section>

    {{-- Check our licence yourself --}}
    <section class="mx-auto max-w-6xl px-4 pb-12 md:pb-16" aria-labelledby="cek-izin-kami">
        <div class="grid gap-6 rounded-3xl border-2 border-gold/50 bg-cream p-6 md:grid-cols-[1fr_auto] md:items-center md:p-10">
            <div>
                <h2 id="cek-izin-kami" class="font-serif text-2xl font-bold text-brand-dark md:text-3xl">Jangan hanya percaya. Cek izin kami sendiri.</h2>
                <p class="mt-3 max-w-2xl leading-relaxed text-slate-700">Sebelum membayar ke travel mana pun, pastikan nama dan nomor izinnya terdaftar di kanal resmi pemerintah. Kami tunjukkan langkahnya, termasuk untuk izin kami: {{ collect($permits)->pluck('short')->implode(' dan ') }}.</p>
            </div>
            <a href="{{ route('legal') }}#cara-cek-izin" target="_blank" rel="noopener" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-brand-dark px-6 font-semibold text-white hover:bg-brand">Lihat cara cek izin <x-site-icon name="external" class="size-4" /><span class="sr-only">(membuka tab baru)</span></a>
        </div>
    </section>

    @if (config('site.gallery'))
        <section class="mx-auto max-w-6xl px-4 pb-12 md:pb-16" aria-labelledby="dokumentasi">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Dokumentasi</p>
                    <h2 id="dokumentasi" class="mt-1 font-serif text-3xl font-bold text-brand-dark">Bersama jemaah Amsa Nur</h2>
                </div>
                @if (count(config('site.gallery')) > 4)
                    <a href="{{ route('about') }}#dokumentasi" class="inline-flex min-h-11 items-center gap-1 font-semibold text-brand hover:underline">Foto lainnya <x-site-icon name="arrow-right" class="size-4" /></a>
                @endif
            </div>
            <x-jemaah-gallery :limit="4" class="mt-8 md:grid-cols-4" />
        </section>
    @endif

    @if ($mentors->isNotEmpty())
        <section class="bg-cream" aria-labelledby="pembimbing">
            <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
                <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Pembimbing ibadah</p>
                <h2 id="pembimbing" class="mt-1 font-serif text-3xl font-bold text-brand-dark">Mengenal pembimbing ibadah kami</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    @foreach ($mentors as $mentor)
                        <x-mentor-card :mentor="$mentor" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="testimoni">
            <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Kata jemaah</p>
            <h2 id="testimoni" class="mt-1 font-serif text-3xl font-bold text-brand-dark">Pengalaman jemaah kami</h2>
            <ul class="mt-8 grid gap-6 md:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <li>
                        <figure class="h-full rounded-2xl border border-slate-200 p-6">
                            <blockquote class="leading-relaxed text-slate-700">&ldquo;{{ $testimonial->quote }}&rdquo;</blockquote>
                            <figcaption class="mt-4 text-sm">
                                <span class="font-semibold text-brand-dark">{{ $testimonial->name }}</span>
                                @if ($testimonial->city || $testimonial->trip)
                                    <span class="block text-slate-500">{{ collect([$testimonial->city, $testimonial->trip])->filter()->implode(' · ') }}</span>
                                @endif
                            </figcaption>
                        </figure>
                    </li>
                @endforeach
            </ul>
            @if (config('site.reviews_url'))
                <a href="{{ config('site.reviews_url') }}" class="mt-6 inline-flex min-h-11 items-center gap-1 font-semibold text-brand hover:underline" rel="noopener" target="_blank">Baca ulasan di Google <x-site-icon name="external" class="size-4" /></a>
            @endif
        </section>
    @endif

    @if ($articles->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="panduan">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Panduan</p>
                    <h2 id="panduan" class="mt-1 font-serif text-3xl font-bold text-brand-dark">Artikel terbaru</h2>
                </div>
                <a href="{{ route('articles.index') }}" class="inline-flex min-h-11 items-center gap-1 font-semibold text-brand hover:underline">Semua artikel <x-site-icon name="arrow-right" class="size-4" /></a>
            </div>
            <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <li><x-article-card :article="$article" heading-level="h3" /></li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="mx-auto max-w-3xl px-4 py-12 md:py-16" aria-labelledby="faq">
        <p class="text-sm font-semibold tracking-wide text-gold-dark uppercase">Tanya jawab</p>
        <h2 id="faq" class="mt-1 font-serif text-3xl font-bold text-brand-dark">Pertanyaan yang sering diajukan</h2>
        <x-faq-list :items="$faqs" class="mt-8" />
    </section>

    <section class="bg-pattern text-white" aria-labelledby="ajakan">
        <div class="mx-auto max-w-3xl px-4 py-14 text-center">
            <h2 id="ajakan" class="font-serif text-3xl font-bold">Siap merencanakan ibadah Anda?</h2>
            <p class="mt-3 text-slate-200">Tanyakan jadwal, kamar, dan dokumen yang perlu disiapkan. Tim kami menjawab lewat WhatsApp.</p>
            <x-whatsapp-link class="mt-8 inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-wa px-8 font-semibold text-white hover:bg-wa-dark">
                <x-site-icon name="whatsapp" /> Chat WhatsApp {{ config('site.phone') }}
            </x-whatsapp-link>
        </div>
    </section>
</x-layouts.site>
